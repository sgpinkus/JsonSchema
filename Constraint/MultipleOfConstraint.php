<?php
namespace JsonSchema\Constraint;

use JsonSchema\Constraint\Constraint;
use JsonSchema\Constraint\Exception\ConstraintParseException;

/**
 * Is the target a multiple of some number - which is either a double (PHP float same thing), or int.
 * Spec does not specify how to deall with floats. This implementation just casts the divisro to a double, and test for a remainder.
 * @bug 15.3 is not a multiple of 5.1 because loss of precision.
 */
class MultipleOfConstraint extends Constraint
{
  private $divisor;

  public function __construct($divisor) {
    $this->divisor = (float)$divisor;
  }

  /**
   * @override
   */
  public static function getName() {
  	return 'multipleOf';
  }

  /**
   * @override
   */
  public function validate($doc, $context) {
    $valid = true;
    if(is_int($doc) || is_float($doc)) {
      if($this->divisor == 0) {
        if($doc != 0) {
          $valid = new ValidationError($this, "$doc not a multiple of 0", $context);
        }
      }
      else {
        if(($doc/$this->divisor) != round($doc/$this->divisor)) {
          $valid = new ValidationError($this, "$doc not a multiple of {$this->divisor}", $context);
        }
      }
    }
    return $valid;
  }

  /**
   * @override
   */
  public static function build($context) {
    $doc = $context->multipleOf;
    if(!(is_int($doc) || is_float($doc))) {
      throw new ConstraintParseException('The value of "multipleOf" MUST be a JSON number.');
    }
    if($doc <= 0) {
      throw new ConstraintParseException('This number MUST be strictly greater than 0.');
    }
    return new static($doc);
  }
}
