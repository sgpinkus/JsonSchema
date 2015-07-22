<?php
namespace PhpJsonSchema;
use JsonDoc\JsonDoc;

/**
 * Instances of this class hold a valid JSON Schema validator.
 * Also provides resolution of pointers to valid JSON Schema validators.
 */
class JsonSchema
{
  private $doc;
  private $rootSymbol;

  /**
   * Construct a validator from a JsonDoc.
   * Requires a JsonDoc so we can provide pointers to clients of this class.
   * Note the doc structure underlying the JsonDoc is mutated.
   */
  public function __construct(JsonDoc $doc) {
    $this->doc = $doc;
    $this->rootSymbol = new EmptySymbol($doc->getDoc());
  }

  public function validate($doc) {
  }
}
