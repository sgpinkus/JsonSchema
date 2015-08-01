<?php
namespace JsonSchema\Constraint;

use JsonSchema\Constraint\Constraint;
use JsonSchema\Constraint\Exception\ConstraintParseException;

/**
 */
class MultipleOfConstraint extends Constraint
{
  private $divisor;

  public function __construct($divisor) {
    $this->divisor = $divisor;
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
  public function validate($doc) {
    $valid = true;
    if(is_int($doc) || is_float($doc)) {
      if($this->divisor == 0) {
        if($doc != 0) {
          $valid = new ValidationError($this, "$doc not a multiple of 0");
        }
      }
      else if(is_int($doc) && $doc%$this->divisor != 0) {
        $valid = new ValidationError($this, "$doc not a multiple of {$this->divisor}");
      }
      else if(is_float($doc) && ($doc/(float)$this->divisor) != round($doc/(float)$this->divisor)) {
        $valid = new ValidationError($this, "$doc not a multiple of {$this->divisor}");
      }
    }
    return $valid;
  }

  /**
   * @override
   */
  public static function build($doc, $context = null) {
    if(!(is_int($doc) || is_float($doc))) {
      throw new ConstraintParseException('The value of "multipleOf" MUST be a JSON number.');
    }
    if($doc <= 0) {
      throw new ConstraintParseException('This number MUST be strictly greater than 0.');
    }
    return new static($doc);
  }
}
