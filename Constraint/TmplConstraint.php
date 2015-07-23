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
    if(!is_string($doc)) {
      throw new ConstraintParseException('The value MUST be a string.');
    }
    return new static($doc);
  }
  }
}
