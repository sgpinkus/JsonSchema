<?php
namespace JsonSchema\Constraint;

use JsonSchema\Constraint\Constraint;
use JsonSchema\Constraint\Exception\ConstraintParseException;

/**
 * The maxLength constraint.
 */
class MaxLengthConstraint extends Constraint
{
  private $maxLength;

  public function __construct($maxLength) {
    $this->maxLength = (int)$maxLength;
  }

  /**
   * @override
   */
  public function validate($doc) {
    $valid = true;
    if(is_string($doc)) {
      $valid = strlen($doc) <= $this->maxLength;
    }
    return $valid;
  }

  /**
   * @override
   */
  public static function build($doc, $context = null) {
    if(!is_int($doc)) {
      throw new ConstraintParseException('The value MUST be an integer.');
    }
    if($doc < 0) {
      throw new ConstraintParseException('This integer MUST be greater than, or equal to 0.');
    }
    return new static($doc);
  }
}
