<?php
namespace JsonSchema\Symbol;

/**
 * Represents any object.
 */
class EmptySymbol
{
  private $doc;
  private $children;

  private function __construct(StdClass $doc) {
    $this->doc = $doc;
  }

  public static function canBuild($doc) {
    return is_object($doc);
  }

  public static function build($doc) {
    if(!self::canBuild($doc)) {
      throw new JsonSchemaSymbolException();
    }
    return new EmptySymbol($doc);
  }

  public function validate($doc) {
    // foreach childrenn ...
    return true;
  }
}
