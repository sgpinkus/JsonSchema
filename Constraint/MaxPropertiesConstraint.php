<?php
namespace JsonSchema\Constraint;

use JsonSchema\Constraint\Constraint;
use JsonSchema\Constraint\Exception\ConstraintParseException;

/**
 * The maxProperties constraint.
 */
class MaxPropertiesConstraint extends Constraint
{
  private $maxProperties;

  public function __construct($maxProperties) {
    $this->maxProperties = (int)$maxProperties;
  }

  /**
   * @override
   */
  public static function getName() {
  	return 'maxProperties';
  }

  /**
   * @override
   */
  public function validate($doc, $context) {
    $valid = true;
    if(is_object($doc)) {
      if(count((array)$doc) > $this->maxProperties) {
        $valid = new ValidationError($this, "size of object > {$this->maxProperties}", $context);
      }
    }
    return $valid;
  }

  /**
   * @override
   */
  public static function build($context) {
    $doc = $context->maxProperties;
    if(!is_int($doc)) {
      throw new ConstraintParseException('The value MUST be an integer.');
    }
    if($doc < 0) {
      throw new ConstraintParseException('This integer MUST be greater than, or equal to 0.');
    }
    return new static($doc);
  }
}
