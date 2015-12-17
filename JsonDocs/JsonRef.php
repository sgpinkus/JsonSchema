<?php
namespace JsonDocs;

/**
 * Stores a PHP src variable reference and a pointer it is to be resolved to.
 * Tightly related to the algorithm for dereferencing a JSON Doc. Only used there.
 */
class JsonRef
{
  private $srcRef;
  private $pointer;
  private $jsonRef;
  private $priority;

  /**
   * Construct JsonRef. Expect the URI should be absolute.
   * @input $srcRef the varaiable that should be resolved to the pointer.
   * @input $jsonRef a URI. Should be absolute but not enforced.
   */
  public function __construct(&$srcRef, Uri $jsonRef, $priority) {
    $this->srcRef =& $srcRef;
    $this->jsonRef = $jsonRef;
    $this->pointer = $jsonRef->fragment ? preg_replace("#/+#", "/", $jsonRef->fragment) : "/"; // Empty pointer replaced with / (same thing).
    $this->priority = $priority;
  }

  /**
   * Get the JSON reference by reference.
   */
  public function &getRef() {
    return $this->srcRef;
  }

  public function setRef(&$ref) {
    $this->srcRef = $ref;
  }

  /**
   * Get the pointer.
   */
  public function getPointer() {
    return $this->pointer;
  }

  /**
   * Get the full URI.
   */
  public function getUri() {
    return clone $this->jsonRef;
  }

  /**
   * Total hierachical ordering, path segments over alphabetical order.
   */
  public function compare(JsonRef $that) {
    if($this->priority > $that->priority ) {
      return 1;
    }
    else if($this->priority < $that->priority ) {
      return -1;
    }
    else {
      if($this->pointer < $that->pointer) {
        return 1;
      }
      else if($this->pointer > $that->pointer) {
        return -1;
      }
      else {
        return 0;
      }
    }
  }
}
