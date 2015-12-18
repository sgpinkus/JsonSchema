<?php
namespace JsonSchema\Constraint;

use JsonSchema\Constraint\Constraint;
use JsonSchema\Constraint\Exception\ConstraintParseException;

/**
 */
class MinimumConstraint extends Constraint
{
  private $minimum;
  private $exclusive;

  public function __construct($minimum, $exclusive = false) {
    $this->minimum = $minimum;
    $this->exclusive = $exclusive;
  }

  /**
   * @override
   */
  public static function getName() {
  	return 'minimum';
  }

  /**
   * @override
   */
  public function validate($doc, $context) {
    $valid = true;
    if(is_int($doc) || is_float($doc)) {
      if($this->exclusive && $doc <= $this->minimum) {
        $valid = new ValidationError($this, "$doc <= {$this->minimum}");
      }
      else if($doc < $this->minimum) {
        $valid = new ValidationError($this, "$doc < {$this->minimum}");
      }
    }
    return $valid;
  }

  /**
   * @override
   */
  public static function build($doc, $context = null) {
    if(!(is_int($doc) || is_float($doc))) {
      throw new ConstraintParseException('The value of "minimum" MUST be a JSON number.');
    }
    if(isset($context->exclusiveMinimum) && !is_bool($context->exclusiveMinimum)) {
      throw new ConstraintParseException('The value of "exclusiveMinimum" MUST be a boolean.');
    }
    return new static($doc, !empty($context->exclusiveMinimum));
  }
}
