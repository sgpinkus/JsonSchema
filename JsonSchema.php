<?php
namespace JsonSchema;
use JsonDoc\JsonPointer;
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
   * Note the $doc structure underlying the JsonDoc is mutated to aid in stuff.
   */
  public function __construct(\StdClass $doc) {
    $this->doc = $doc;
    $this->rootSymbol = EmptyConstraint::build($doc);
  }

  /**
   * Validate $doc against the schema.
   * @input $doc Mixed the target of validation.
   * @input $pointer A JSON Pointer pointing into the schema.
   */
  public function validate($doc, $pointer = null) {
    $code = '$code';
    $schema = $this->rootSymbol;
    if($pointer) {
      $schema = JsonPointer::getPointer($this->doc, $pointer);
      if(!isset($schema->$code)) {
        throw new \InvalidArgumentException("Invalid pointer.");
      }
      $schema = $schema->$code;
    }
    return $schema->validate($doc);
  }
}
