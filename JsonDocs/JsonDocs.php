<?php
namespace JsonDocs;

use JsonDocs\JsonNullLoader;
use JsonDocs\JsonRefPriorityQueue;
use JsonDocs\JsonRef;
use JsonDocs\Exception\JsonDecodeException;
use JsonDocs\Exception\ResourceNotFoundException;
use JsonDocs\Exception\JsonReferenceException;

/**
 * Instances of this class maintain a cache of dereferenced JSON documents and provide access to those documents.
 * Basically its a cache of deserialized, dereferenced JSON docs keyed by the scheme+domain+path part of absolute URIs.
 * Loading JSON that contains JSON refs and dereferencing it are closely coupled. So this class also contains the deref logic.
 * Supports retrieving part of a doc by JSON Pointer. Note however, the fragment part of loaded URIs is ignored.
 * Actually loading raw data from remote (or local) sources pointed at by URIs is delegated to JsonLoader.
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
   * Fragment part of URIs is silently ignored.
   * Use the optional $doc parameter if you've already loaded and decoded the document but still need to deref it.
   * @input $uri Uri an absolute URI.
   * @input $doc Mixed optional JSON document structure.
   * @returns mixed reference to the loaded JSON object data structure.
   * @throws JsonLoaderException, JsonDecodeException, JsonCacheException
   */
  public function get(Uri $uri, $doc = null) {
    $keyUri = self::normalizeKeyUri($uri);
    if(isset($this->cache[$keyUri.''])) {
      return $this->cache[$keyUri.''];
    }
    return $this->_get($uri, $doc);
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
   */
  public function &pointer(Uri $uri) {
    $keyUri = self::normalizeKeyUri($uri);
    $pointer = $uri->fragment ? $uri->fragment : "";

    if(!isset($this->cache[$keyUri.''])) {
      throw new ResourceNotFoundException("Resource $keyUri not loaded");
    }

    return self::getPointer($this->cache[$keyUri.''], $pointer);
  }

  /**
   * @override
   */
  public function getIterator() {
    return new \ArrayIterator($this->cache);
  }

  /**
   * Internal function called by get().
   * load() and _deRef() must be called in sequence. They are coupled by a queue of refs that load() builds, _deRef() uses.
   * @input $uri a normalized Uri.
   */
  private function _get(Uri $uri, $eDoc = null) {
    $queue = new JsonRefPriorityQueue();
    $doc = $this->load($uri, $queue, true, $eDoc);
    $this->_deRef($queue);
    return $doc;
  }

  /**
   * Fully load un-dereferenced JSON Documents at given URI.
   * Collect all refs that need to to be resolved into a priority queue.
   * Before we begin dereferencing we make sure all JSON doc resources that are refered to are loaded by calling this method recursively.
   * This method should only be called by _get(), and itself.
   * @input $uri of the resource to load. Must be fully qualified.
   * @input $queue a collection in which to store the refs we find.
   * @input $replaceId bool whether to replace the `id` field with the normalized URI the resource is loaded from.
   * @see _get()
   */
  private function load(Uri $uri, \SplPriorityQueue $queue, $replaceId = false, $doc = null) {
    $tempRefs = [];
    $keyUri = self::normalizeKeyUri($uri);

    if(isset($this->cache[$keyUri.''])) {
      return $this->cache[$keyUri.''];
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
    self::queueAllRefs($doc, $queue, $keyUri);
    $this->cache[$keyUri.''] = $doc;

    // Now we have to make sure all the resources that are refered to are loaded.
    // But this empties the queue so need to stuff back into another one...
    $stuffingQueue = new JsonRefPriorityQueue();
    $resourceUris = [];
    foreach($queue as $jsonRef) {
      $resourceUris[] = $jsonRef->getUri();
      $stuffingQueue->insert($jsonRef, $jsonRef);
    }

    foreach($resourceUris as $uri) {
      $this->load($uri, $queue, false);
    }

    $this->_deRef($stuffingQueue);
    return $doc;
  }

  /**
   * Remove all Json References ($ref) from loaded docs, replacing $ref object with PHP references to the pointed to value.
   * There are a three special cases to consider; refs to refs, refs to refs that are circular, refs through refs.
   * We are simply not allowing refs to refs - first two cases. Refs through refs may work depending on the order of resolution.
   * Must be called after all referenced docs are loaded by load().
   * @see _get().
   * @input $queue A priority queue of refs that need dereferencing.
   * @todo Handle circular refs and ref to a refs properly.
   */
  private function _deRef(\SplPriorityQueue $queue) {
    while(!$queue->isEmpty()) {
      $jsonRef = $queue->extract();
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
   * Find all JSON Refs in a JSON doc and stuff them into a queue for later processing.
   * Can't use standard recursive iterator here because references + iterators don't work together.
   * Also handles rebasing a base URI based on the value of an 'id' field of objects.
   * @input $doc a decoded JSON doc.
   * @input $queue a queue for stuffing found JSON Refs into.
   * @input $baseUri the current base URI used for resolving relative JSON Ref pointers found.
   */
  public static function queueAllRefs(&$doc, \SplPriorityQueue $queue, Uri $baseUri, $depth = 0) {
    defined('DEBUG') && print __METHOD__ . " $baseUri\n";
    if(is_object($doc) || is_array($doc)) {
      if(is_object($doc) && isset($doc->id) && is_string($doc->id)) {
        $baseUri = $baseUri->resolveRelativeUriOn(new Uri($doc->id));
      }
      foreach($doc as $key => &$value) {
        defined('DEBUG') && print "\t$key\n";
        if(self::isJsonRef($value)) {
          $refUri = $baseUri->resolveRelativeUriOn(new Uri(self::getJsonRefPointer($value)));
          defined('DEBUG') && print "\tFOUND REF $refUri\n";
          $jsonRef = new JsonRef($value, $refUri, -1*$depth);
          $queue->insert($jsonRef, $jsonRef);
        }
        else if(is_object($value) || is_array($value)) {
          self::queueAllRefs($value, $queue, $baseUri, $depth+1);
        }
      }
    }
  }

  /**
   * Traverse a JSON document data structure to find pointer reference.
   * @input $doc Decoded JSON data structure.
   * @input $pointer String JSON Pointer. Example "/x/y/0/z".
   * @return reference to the pointed to value. Note return by *reference*.
   */
  public static function &getPointer($doc, $pointer) {
    $parts = explode("/", $pointer);
    $currentPointer = "";
    $doc= &$doc;

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
          throw new ResourceNotFoundException("Could not find $pointer in document. Failed at $currentPointer");
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

  /**
   * Prepare Uri.
   */
  public static function normalizeKeyUri(Uri $uri) {
    $keyUri = clone $uri;
    unset($keyUri->query);
    unset($keyUri->fragment);
    return $keyUri;
  }
}

class JsonDocsException extends \Exception {}
