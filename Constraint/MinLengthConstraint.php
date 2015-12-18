<?php
namespace JsonSchema\Constraint;

use JsonSchema\Constraint\Constraint;
use JsonSchema\Constraint\Exception\ConstraintParseException;

/**
 * The minLength constraint.
 */
class MinLengthConstraint extends Constraint
{
  private $minLength;

  public function __construct($minLength) {
    $this->minLength = (int)$minLength;
  }

  /**
   * @override
   */
  public static function getName() {
  	return 'minLength';
  }

  /**
   * @override
   */
  public function validate($doc, $context) {
    $valid = true;
    if(is_string($doc)) {
      if(strlen($doc) < $this->minLength) {
        $valid = new ValidationError($this, "strlen($doc) < {$this->minLength}", $context);
      }
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
