<?php
namespace PhpJsonSchema;
use JsonDoc\JsonPointer;

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
   * Note the $doc structure underlying the JsonDoc is mutated to aid in stuff.
   */
  public function __construct($doc) {
    $this->doc = $doc;
    $this->rootSymbol = new EmptySymbol($doc);
  }

  /**
   * Validate $doc against the schema.
   * @input $doc Mixed the target of validation.
   * @input $pointer A JSON Pointer pointing into the schema.
   */
  public function validate($doc, $pointer = null) {
    $schema = $this->rootSymbol;
    if($pointer) {
      $schema = JsonPointer::getPointer($this->doc, $pointer);
    }
    return $schema->validate($doc);
  }
}
