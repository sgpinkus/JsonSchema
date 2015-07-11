<?php
namespace JsonDoc;
use JsonDoc\Exception\JsonDecodeException;
use JsonDoc\Exception\ResourceNotFoundException;
require_once 'JsonLoader.php';
require_once 'Uri.php';
require_once 'Exception/JsonDecodeException.php';
require_once 'Exception/ResourceNotFoundException.php';

/**
 * Instances of this class maintain a cache of dereferenced JSON documents and provide access to those documents.
 * Basically its a cache of deserialized, dereferenced JSON docs keyed by the domain part of absolute URIs.
 * Supports retrieving aprt of a doc by JSON Pointer, but the fragment part of loaded URI is ignored.
 * Actually loading raw data from remote (or local) sources pointed at by URIS is delegated to JsonLoader.
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

    $doc = $this->loader->load($keyUri);
    $doc = json_decode($doc);
    if($doc == null) {
      throw new JsonDecodeException(json_last_error());
    }

    self::dereference($doc);
    $this->cache[$keyUri.''] = $doc;
    return $doc;
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

    return self::_pointer($this->cache[$keyUri], $pointer);
  }

  /**
   * Travers JSON document structure to find pointer reference. Return by reference.
   * @input $doc Decoded JSON data structure.
   * @input $pointer String JSON Pointer. Example "/x/y/0/z".
   */
  public static function &_pointer($doc, $pointer) {
    $parts = explode("/", $pointer);
    $currentPointer = "";
    $doc= &$doc;
    var_dump($parts);

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
   * Remove all Json References ($ref) from the document.
   */
  public static function dereference($doc) {
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

class JsonCacheException extends \Exception {}


$c = new JsonCache(new JsonLoader());
$doc = json_decode(file_get_contents('./tests/data/test.json'));
var_dump($c::_pointer($doc, ''));
