<?php
namespace JsonSchema\Constraint;

use JsonSchema\Constraint\Constraint;
use JsonSchema\Constraint\Exception\ConstraintParseException;

/**
 * The maximum constraint.
 */
class MaximumConstraint extends Constraint
{
  private $maximum;
  private $exclusive;

  public function __construct($maximum, $exclusive = false) {
    $this->maximum = $maximum;
    $this->exclusive = $exclusive;
  }

  /**
   * @override
   */
  public function validate($doc) {
    $valid = true;
    if(is_int($doc) || is_float($doc)) {
      if($this->exclusive) {
        $valid = $doc < $this->maximum;
      }
      else {
        $valid = $doc <= $this->maximum;
      }
    }
    return $valid;
  }

  /**
   * @override
   */
  public static function build($doc, $context = null) {
    if(!(is_int($doc) || is_float($doc))) {
      throw new ConstraintParseException('The value of "maximum" MUST be a JSON number.');
    }
    if(isset($context->exclusiveMaximum) && !is_bool($context->exclusiveMaximum)) {
      throw new ConstraintParseException('The value of "exclusiveMaximum" MUST be a boolean.');
    }
    return new static($doc, !empty($context->exclusiveMaximum));
  }
}
