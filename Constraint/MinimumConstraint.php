<?php
namespace JsonSchema\Constraint;

use JsonSchema\Constraint\Constraint;
use JsonSchema\Constraint\Exception\ConstraintParseException;

/**
 * The minimum constraint.
 */
class MinimumConstraint extends Constraint
{
  private $minimum;

  public function __construct($minimum, $exclusive = false) {
    $this->minimum = $minimum;
    $this->exclusive = $exclusive;
  }

  /**
   * @override
   */
  public static function getName() {
    return "['minimum', 'exclusiveMinimum']";
  }

  public static function getKeys() {
    return ['minimum', 'exclusiveMinimum'];
  }

  /**
   * @override
   */
  public function validate($doc, $context) {
    $valid = true;
    if(is_int($doc) || is_float($doc)) {
      if($this->exclusive && $doc <= $this->minimum) {
        $valid = new ValidationError($this, "$doc <= {$this->minimum}", $context);
      }
      else if($doc < $this->minimum) {
        $valid = new ValidationError($this, "$doc < {$this->minimum}", $context);
      }
    }
    return $valid;
  }

  /**
   * v06 made incompatible change to exclusiveMinimum: must now be a number. But BWC is still very
   * possible so we do that ...
   * @override
   */
  public static function build($context) {
    $minimum = isset($context->minimum) ? $context->minimum : null;
    $exclusiveMinimum = isset($context->exclusiveMinimum) ? $context->exclusiveMinimum : null;
    if(isset($minimum)) {
      if(!(is_int($minimum) || is_float($minimum))) {
        throw new ConstraintParseException('The value of "minimum" MUST be a JSON number.');
      }
      if(isset($exclusiveMinimum) && !is_bool($exclusiveMinimum)) {
        throw new ConstraintParseException('The value of "exclusiveMinimum" MUST be a boolean when "minimum" also set.');
      }
      return new static($minimum, $exclusiveMinimum);
    }
    elseif(is_int($exclusiveMinimum) || is_float($exclusiveMinimum)) {
      return new static($exclusiveMinimum, true);
    }
    else {
      throw new ConstraintParseException('The value of "exclusiveMinimum" MUST be a JSON number when "minimum" is not also set.');
    }
  }
}
