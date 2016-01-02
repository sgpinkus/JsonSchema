<?php
namespace JsonDocs;

use JsonDocs\JsonNullLoader;
use JsonDocs\JsonRefPriorityQueue;
use JsonDocs\JsonRef;
use JsonDocs\Exception\JsonDecodeException;
use JsonDocs\Exception\ResourceNotFoundException;
use JsonDocs\Exception\JsonReferenceException;

/**
 * Instances of this class dereference and then provide access to a cache of dereferenced JSON documents.
 * Basically its a cache of deserialized, dereferenced JSON docs keyed by the absolute URI (scheme+domain+path part only) of the document.
 * That key URI can be provided by the client. Or, if the client does not provide it, we use the top id field if its present and is an absolute URI.
 * Loading JSON that contains JSON refs and dereferencing it are closely coupled. So this class has both loading and deref responsibilities.
 * Json References are literally replaced with PHP references to other loaded documents in the internal cache.
 * Supports retrieving part of a doc by JSON Pointer. Note however, when loading a document the fragment part of a URIs is ignored.
 * Actually loading raw data from remote (or local) sources pointed at by URIs is delegated to JsonLoader.
 * For notes on the Json Reference specification see the following.
 * @see http://tools.ietf.org/html/draft-pbryan-zyp-json-ref-03
 * @see http://json-schema.org/latest/json-schema-core.html#anchor25
 * @see https://github.com/json-schema/json-schema/wiki/$ref-traps
 * @see https://github.com/json-schema/json-schema/wiki/The-%22id%22-conundrum#how-to-fix-that
 */
class JsonDocs implements \IteratorAggregate
{
  private $cache = [];
  private $loader;

  /**
   * Init. Use Null loader which refuses to load external refs by default for security.
   * @input $loader JsonLoader optional loader.
   */
  public function __construct(JsonLoader $loader = null) {
    $this->loader = $loader ? $loader : new JsonNullLoader();
  }

  /**
   * Get a reference to a deserialized, dereferenced JSON document data structure.
   * This is a collection of whole resources. This fragment part of $uri is stripped and ignored.
   * Use the optional $doc parameter to override loading of the document via the Loader.
   * $doc param is required to be a serialized JSON doc *string*. This avoids possibility of passing in an already derefd doc, and makes deep clone eassier. $doc param can decode to any type.
   * It possible the desired URI of the document is stored in the top level id field. If no Uri is explicitly passed try and use that.
   * @input $uri Uri an absolute URI.
   * @input $doc string optional JSON document structure.
   * @returns mixed reference to the loaded JSON object data structure.
   * @throws JsonLoaderException, JsonDecodeException, JsonCacheException
   */
  public function get(Uri $uri = null, $doc = null) {
    if(!(isset($uri) || isset($doc))) {
      throw new \InvalidArgumentException("Need a URI, a document, or a both to load successfully");
    }
    if(isset($doc) && !is_string($doc)) {
      throw new \InvalidArgumentException("Expected string, got " . gettype($doc));
    }
    if(isset($doc)) {
      try {
        $doc = json_decode($doc);
        if($doc === null) {
          throw new \InvalidArgumentException("Invalid argument. Failed to decode provided JSON document");
        }
      }
      // @todo enable ErrorExceptions.
      catch(ErrorException $e) {
        throw new \InvalidArgumentException("Invalid argument. Failed to decode provided JSON document");
      }
      // Now we have decode the doc we can try find a URI if required.
      if(!isset($uri)) {
        try {
          $uri = new Uri($doc->id);
        }
        catch(Exception $e) {
          throw new \InvalidArgumentException("If URI parameter is not passed the JSON document must have a top level id field that is a valid URI");
        }
      }
    }
    $keyUri = self::normalizeKeyUri($uri);
    if(isset($this->cache[$keyUri.''])) {
      return $this->cache[$keyUri.'']['doc'];
    }
    $doc = $this->load($uri, new JsonRefPriorityQueue(), false, $doc);
    return $doc;
  }

  /**
   * Just deref an existing JSON document data structure.
   * @see get().
   */
  public function deRef(Uri $uri, $doc) {
    return $this->get($uri, $doc);
  }

  /**
   * Document at $uri is loaded.
   */
  public function exists(Uri $uri) {
    $keyUri = self::normalizeKeyUri($uri);
    return isset($this->cache[$keyUri.'']);
  }

  public function count() {
    return count($this->cache);
  }

  /**
   * Return a part of a document pointed to by $uri.
   * @input $uri absolute URI, with optional fragment part.
   * @returns mixed reference to the loaded JSON object data structure.
   * @throws ResourceNotFoundException
   */
  public function &pointer(Uri $uri) {
    $keyUri = self::normalizeKeyUri($uri);
    $pointer = $uri->fragment ? $uri->fragment : "";

    if(!isset($this->cache[$keyUri.''])) {
      throw new \ResourceNotFoundException("Resource $keyUri not loaded");
    }

    return self::getPointer($this->cache[$keyUri.'']['doc'], $pointer, $this->cache[$keyUri.'']['ids']);
  }

  /**
   * @override
   */
  public function getIterator() {
    return new \ArrayIterator($this->cache);
  }

  /**
   * Fully load un-dereferenced JSON Documents at given URI.
   * Collect all refs that need to to be resolved into a priority queue.
   * Before we begin dereferencing we make sure all JSON doc resources that are refered to are loaded by calling this method recursively.
   * This method should only be called by get(), and itself.
   * @input $uri of the resource to load. Must be fully qualified.
   * @input $refQueue a collection in which to store the refs we find.
   * @input $replaceId bool whether to replace the `id` field with the normalized URI the resource is loaded from.
   * @see get()
   */
  private function load(Uri $uri, \SplPriorityQueue $refQueue, $replaceId = false, $doc = null) {
    $tempRefs = [];
    $keyUri = self::normalizeKeyUri($uri);

    if(isset($this->cache[$keyUri.''])) {
      return $this->cache[$keyUri.'']['doc'];
    }

    if($doc === null) {
      $doc = $this->loader->load($keyUri);
      $doc = json_decode($doc);
      if($doc === null) {
        throw new JsonDecodeException(json_last_error());
      }
    }
    if(isset($doc->id) && $replaceId) {
      $doc->id = $keyUri.'';
    }
    $identities = [];
    $refUris = [];
    self::parseDoc($doc, $refQueue, $refUris, $identities, $keyUri);
    $this->cache[$keyUri.''] = ['doc' => $doc, 'ids' => $identities];

    foreach($refUris as $uri) {
      $this->load($uri, $refQueue, false);
    }

    $this->_deRef($refQueue);
    return $doc;
  }

  /**
   * Remove all Json References ($ref) from loaded docs, replacing $ref object with PHP references to the pointed to value.
   * There are a three special cases to consider; refs to refs, refs to refs that are circular, refs through refs.
   * We are simply not allowing refs to refs - first two cases. Refs through refs may work depending on the order of resolution.
   * To make this "may work" ness predictable a PriorityQueue has been used.
   * Must be called after all referenced docs are loaded by load().
   * @input $refQueue A priority queue of refs that need dereferencing.
   * @todo Handle circular refs and ref to a refs properly.
   * @see get().
   * @see JsonRefPriorityQueue
   */
  private function _deRef(\SplPriorityQueue $refQueue) {
    while(!$refQueue->isEmpty()) {
      $jsonRef = $refQueue->extract();
      $pointerUri = $jsonRef->getUri();
      $ref =& $jsonRef->getRef();
      $target =& $this->pointer($pointerUri);
      if(self::isJsonRef($target)) {
        throw new JsonReferenceException("JSON Reference to JSON Reference is not allowed");
      }
      $ref = $target;
    }
  }

  /**
   * Find and stash all JSON Refs, their referenced URIs, and object with and `id` in a JSON doc.
   * Can't use standard recursive iterator here because references + iterators don't work together.
   * @input $doc a decoded JSON doc.
   * @input $refQueue a queue for stuffing found JSON Refs into.
   * @input $refUris array for stash the absolute URIS from the refs in.
   * @intpu $identities array for stashing objects with identities in.
   * @input $baseUri the current base URI used for resolving relative JSON Ref pointers found.
   * @throws JsonReferenceException
   */
  public static function parseDoc(&$doc, \SplPriorityQueue $refQueue, array &$refUris, array &$identities, Uri $baseUri, $depth = 0) {
    defined('DEBUG') && print __METHOD__ . " $baseUri\n";
    if(is_object($doc) || is_array($doc)) {
      foreach($doc as $key => &$value) {
        defined('DEBUG') && print "\t$key\n";
        if(self::getId($value) && self::isJsonRef($value)) {
          throw new JsonReferenceException("Illegal JSON Schema. An object may not have both of 'id' and '\$ref'");
        }
        elseif($key != "properties" && self::getId($value)) { // An "id" is not a keyword in some contexts (properties AFAICT).
          $id = self::getId($value);
          if(isset($identities[$id])) {
            throw new JsonReferenceException("Duplicate id '$id' found");
          }
          $identities[$id] = &$value;
        }
        elseif(self::isJsonRef($value)) {
          $refUri = $baseUri->resolveRelativeUriOn(new Uri(self::getJsonRefPointer($value)));
          defined('DEBUG') && print "\tFOUND REF $refUri\n";
          $jsonRef = new JsonRef($value, $refUri, -1*$depth);
          $refQueue->insert($jsonRef, $jsonRef);
          $refUris[] = $refUri;
        }
        else if(is_object($value) || is_array($value)) {
          self::parseDoc($value, $refQueue, $refUris, $identities, $baseUri, $depth+1);
        }
      }
    }
  }

  /**
   * Traverse a JSON document data structure to find pointer reference. Not very useful as public method.
   * @input $doc Decoded JSON data structure, and its ids ['doc' => $doc, 'ids' => ids];
   * @input $pointer String JSON Pointer. Example "/x/y/0/z".
   * @return reference to the pointed to value. Note return by *reference*.
   * @throws ResourceNotFoundException
   */
  public static function &getPointer($doc, $pointer, array $ids = []) {
    if(strlen($pointer) === 0) { // { $ref: "#" }.
      return $doc;
    }
    elseif(substr($pointer, 0, 1) !== "/") { // id ref.
      if(isset($ids[$pointer])) {
        return $ids[$pointer];
      }
      else {
        throw new ResourceNotFoundException("Could not find id=$pointer in document");
      }
    }
    else { // pointer ref.
      $parts = explode("/", $pointer);
      $currentPointer = "";
      $doc =& $doc;

      foreach($parts as $part) {
        if($part == "") {
          continue;
        }

        $part = str_replace('~1', '/', $part);
        $part = str_replace('~0', '~', $part);
        $currentPointer .= "/$part";

        if(is_object($doc)) {
          if(isset($doc->$part)) {
            $doc = &$doc->$part;
          }
          else {
            throw new ResourceNotFoundException("Could not find ref=$pointer in document. Failed at $currentPointer");
          }
        }
        else if(is_array($doc)) {
          if(isset($doc[$part])) {
            $doc = &$doc[$part];
          }
          else {
            throw new ResourceNotFoundException("Could not find $pointer in document. Failed at $currentPointer");
          }
        }
        else {
          throw new ResourceNotFoundException("Could not find $pointer in document. Failed at $currentPointer. Not traversable");
        }
      }
      return $doc;
    }
  }

  /**
   * Get the pointer from a JSON Ref.
   */
  public static function getJsonRefPointer($o) {
    $refVar = '$ref';
    $ref = null;
    if(is_object($o) && isset($o->$refVar)) {
      $ref = $o->$refVar;
    }
    return $ref;
  }

  public static function isJsonRef($o) {
    return self::getJsonRefPointer($o);
  }

  public static function getId($o) {
    return (is_object($o) && isset($o->id)) ? $o->id : null;
  }

  /**
   * Prepare Uri.
   */
  public static function normalizeKeyUri(Uri $uri) {
    $keyUri = clone $uri;
    unset($keyUri->fragment);
    return $keyUri;
  }
}

class JsonDocsException extends \Exception {}
