<?php
namespace JsonDoc;

use JsonDoc\Exception\ResourceNotFoundException;

/**
 * A place to stash some static JSON pointer related methods.
 */
class JsonPointer
{
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
}
