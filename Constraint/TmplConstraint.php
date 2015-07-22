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
  public function validate($doc) {
    return true;
  }

  /**
   * @override
   */
  public static function build($doc, $context = null) {
    return new self();
  }

  /**
   * @override
   */
  public static function canBuild($doc) {
    return true;
  }
}
