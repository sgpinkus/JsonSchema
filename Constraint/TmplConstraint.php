<?php
namespace JsonSchema\Constraint;

use JsonSchema\Constraint\Constraint;
use JsonSchema\Constraint\Exception\ConstraintParseException;

/**
 */
class TmplConstraint extends Constraint
{
  /**
   * @override
   */
  public function validate($doc, $context) {
    return true;
  }

  /**
   * @override
   */
  public static function build($context) {
    $name = static::getName();
    $doc = $context->$name;
    if(!is_string($doc)) {
      throw new ConstraintParseException('The value MUST be a string.');
    }
    return new static($doc);
  }
}
