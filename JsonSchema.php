<?php
namespace JsonSchema;
use JsonDoc\JsonDocs;
use JsonSchema\Constraint\EmptyConstraint;

/**
 * Instances of this class hold a valid JSON Schema validator.
 * Also provides resolution of pointers to valid JSON Schema validators.
 */
class JsonSchema
{
  private $doc;
  private $rootSymbol;

  /**
   * Construct a validator from a JSON document data structure.
   * Note the $doc structure underlying the JsonDocs is mutated to aid in stuff.
   */
  public function __construct($doc) {
    $this->doc = $this->parseDoc($doc);
    $this->rootSymbol = EmptyConstraint::build($this->doc);
  }

  /**
   * Validate $doc against the schema.
   * @input $doc Mixed the target of validation.
   * @input $pointer A JSON Pointer pointing into the schema.
   */
  public function validate($doc, $pointer = "/") {
    $code = '$code';
    $doc = $this->parseDoc($doc);
    $schema = $this->rootSymbol;
    if($pointer !== "/") {
      $schema = JsonDocs::getPointer($this->doc, $pointer);
      if(!isset($schema->$code)) {
        throw new \InvalidArgumentException("Could not resolve pointer $pointer");
      }
      $schema = $schema->$code;
    }
    return $schema->validate($doc, "/");
  }

  /**
   * Helper to parse input str|object to a decoded JSON object used internally.
   */
  private function parseDoc($doc) {
    if(is_string($doc)) {
      $doc = json_decode($doc);
    }
    if(!($doc instanceof \StdClass)) {
      throw new \InvalidArgumentException("Could not pass doc. Must be valid JSON string or object.");
    }
    return $doc;
  }
}
