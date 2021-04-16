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

  public function __construct($maximum, $exclusive = false) {
    $this->maximum = $maximum;
    $this->exclusive = $exclusive;
  }

  /**
   * @override
   */
  public static function getName() {
    return "['maximum', 'exclusiveMaximum']";
  }

  public static function getKeys() {
    return ['maximum', 'exclusiveMaximum'];
  }

  /**
   * @override
   */
  public function validate($doc, $context) {
    $valid = true;
    if(is_int($doc) || is_float($doc)) {
      if($this->exclusive && $doc >= $this->maximum) {
        $valid = new ValidationError($this, "$doc >= {$this->maximum}", $context);
      }
      else if($doc > $this->maximum) {
        $valid = new ValidationError($this, "$doc > {$this->maximum}", $context);
      }
    }
    return $valid;
  }

  /**
   * v06 made incompatible change to exclusiveMaximum: must now be a number. But BWC is still very
   * possible so we do that ...
   * @override
   */
  public static function build($context) {
    $maximum = isset($context->maximum) ? $context->maximum : null;
    $exclusiveMaximum = isset($context->exclusiveMaximum) ? $context->exclusiveMaximum : null;
    if(isset($maximum)) {
      if(!(is_int($maximum) || is_float($maximum))) {
        throw new ConstraintParseException('The value of "maximum" MUST be a JSON number.');
      }
      if(isset($exclusiveMaximum) && !is_bool($exclusiveMaximum)) {
        throw new ConstraintParseException('The value of "exclusiveMaximum" MUST be a boolean when "maximum" also set.');
      }
      return new static($maximum, $exclusiveMaximum);
    }
    elseif(is_int($exclusiveMaximum) || is_float($exclusiveMaximum)) {
      return new static($exclusiveMaximum, true);
    }
    else {
      throw new ConstraintParseException('The value of "exclusiveMaximum" MUST be a JSON number when "maximum" is not also set.');
    }
  }
}
