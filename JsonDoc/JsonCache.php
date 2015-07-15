<?php
namespace JsonDoc;
use JsonDoc\Exception\JsonDecodeException;
use JsonDoc\Exception\ResourceNotFoundException;

/**
 * Instances of this class maintain a cache of dereferenced JSON documents and provide access to those documents.
 * Basically its a cache of deserialized, dereferenced JSON docs keyed by the scheme+domain+path part of absolute URIs.
 * Supports retrieving part of a doc by JSON Pointer. Note however, the fragment part of loaded URIs is ignored.
 * Actually loading raw data from remote (or local) sources pointed at by URIs is delegated to JsonLoader.
 */
class JsonCache
{
  private $cache = [];
  private $loader;

  /**
   * Init.
   */
  public function __construct(JsonLoader $loader) {
    $this->loader = $loader;
  }

  /**
   * Get a reference to a deserialized, dereferenced JSON document data structure.
   * Fragment part is silently ignored.
   * @input $uri Uri an absolute URI.
   * @returns mixed reference to the loaded JSON object data structure.
   * @throws JsonLoaderException, JsonDecodeException, JsonCacheException
   */
  public function get(Uri $uri) {
    $keyUri = self::normalizeKeyUri($uri);
    if(isset($this->cache[$keyUri.''])) {
      return $this->cache[$keyUri.''];
    }
    return $this->_get($uri);
  }

  /**
   * Document at $uri is loaded.
   */
  public function exists(Uri $uri) {
    $keyUri = self::normalizeKeyUri($uri);
    return isset($this->cache[$keyUri]);
  }

  /**
   * Return a part of a document pointed to by $uri.
   * @input $uri absolute URI, with optional fragment part.
   * @returns mixed reference to the loaded JSON object data structure.
   */
  public function &pointer(Uri $uri) {
    $keyUri = self::normalizeKeyUri($uri);
    $pointer = isset($uri->fragment) ? $uri->fragment : "";

    if(!isset($cache[$keyUri])) {
      throw new ResourceNotFoundException();
    }

    return self::getPointer($this->cache[$keyUri], $pointer);
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
   * Internal function called by get().
   * load() and deRef() must be called in sequence. They are coupled by a queue of refs that load() builds, deRef() uses.
   */
  private function _get(Uri $uri) {
    $queue = new JsonRefPriorityQueue();
    $doc = $this->load($uri, $queue);
    $this->deRef($queue);
    $this->cache[$keyUri.''] = $doc;
    return $doc;
  }

  /**
   * Fully load un-dereferenced JSON Documents at given URI.
   * Collect all refs that need to to be resolved into a priority queue.
   * Before we begin dereferencing we make sure all required JSON doc resources that are refered to are loaded by callig this method recursively.
   * This method is only called by _get(), and itself.
   * @see _get()
   */
  private function load(Uri $uri, JsonRefPriorityQueue $queue) {
    $tempRefs = [];
    $keyUri = self::normalizeKeyUri($uri);

    if(isset($this->cache[$keyUri.''])) {
      return $this->cache[$keyUri.''];
    }

    $doc = $this->loader->load($keyUri);
    $doc = json_decode($doc);
    if($doc == null) {
      throw new JsonDecodeException(json_last_error());
    }
    self::queueAllRefs($doc, $queue, $keyUri);
    $this->cache[$keyUri.''] = $doc;

    // Now we have to make sure all the resources that are refered to are loaded.
    $resourceUris = [];
    foreach($queue as $jsonRef) {
      $resourceUris[] = $jsonRef->getUri();
    }
    foreach($resourceUris as $uri) {
      $this->load($uri, $queue);
    }
    self::deRef($queue);
    return $doc;
  }

  /**
   * Find all JSON Refs in a JSON doc and stuff them into a queue for later processing.
   * Can't use standard recursive iterator here because references iterators don't work together.
   * Also handles rebasing a base URI based on the value of an 'id' field of objects.
   * @input $doc a decoded JSON doc.
   * @input $queue a queue for stuffing found JSON Refs into.
   * @input $baseUri the current base URI used for resolving relative JSON Ref pointers found.
   */
  public static function queueAllRefs(&$doc, JsonRefPriorityQueue $queue, Uri $baseUri) {
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
          $jsonRef = new JsonRef($value, $refUri);
          $queue->insert($jsonRef, $jsonRef);
        }
        else if(is_object($value) || is_array($value)) {
          self::queueAllRefs($value, $queue, $baseUri);
        }
      }
    }
  }

  /**
   * Remove all Json References ($ref) from loaded docs, replacing $ref object with PHP references to the pointed to value.
   * Must be called after all referenced docs are loaded by load().
   * @see _get().
   * @input $refs A priority queue of refs that need dereferencing.
   */
  private function deRef(JsonRefPriorityQueue $refs) {
    var_dump($refs);
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

/**
 * A JsonRef PQueue. Used by JsonCache in dereferencing.
 */
class JsonRefPriorityQueue extends \SplPriorityQueue
{
  public function compare(JsonRef $a, JsonRef $b) {
    return $a->compare($b);
  }
}

class JsonCacheException extends \Exception {}
require_once 'loader.php';
$x = new JsonCache(new JsonLoader());
