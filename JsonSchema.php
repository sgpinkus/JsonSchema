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
  public function __construct($doc, array $customConstraints = []) {
    $this->doc = $this->tryDecode($doc);
    EmptyConstraint::addConstraints($customConstraints);
    $this->rootSymbol = EmptyConstraint::build($this->doc);
  }

  /**
   * Validate $doc against the schema.
   * @input $doc Mixed the target of validation.
   * @input $pointer A JSON Pointer pointing into the schema.
   */
  public function validate($doc, $pointer = "/") {
    $schema = $this->rootSymbol;
    $codeProp = EmptyConstraint::getCodeProp();
    if($pointer !== "/") {
      $schema = JsonDocs::getPointer($this->doc, $pointer);
      if(!isset($schema->$codeProp)) {
        throw new \InvalidArgumentException("Could not resolve pointer $pointer");
      }
      $schema = $schema->$codeProp;
    }
    return $schema->validate($doc, "/");
  }

  /**
   * Sometimes client pass string expecting it do be decoded. In that case input is a string. But
   * decoded value could be a string too ... in that case if it decodes, return decoded value.
   * Imperfect but failure modes are very edge.
   */
  private function tryDecode($doc) {
    if(is_string($doc)) {
      $_doc = json_decode($doc);
      return $_doc !== null ? $_doc: $doc;
    }
    return $doc;
  }
}
