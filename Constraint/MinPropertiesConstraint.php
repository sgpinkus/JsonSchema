<?php
namespace JsonSchema\Constraint;

use JsonSchema\Constraint\Constraint;
use JsonSchema\Constraint\Exception\ConstraintParseException;

/**
 * The minProperties constraint.
 */
class MinPropertiesConstraint extends Constraint
{
  private $minProperties;

  public function __construct($minProperties) {
    $this->minProperties = (int)$minProperties;
  }

  /**
   * @override
   */
  public static function getName() {
  	return 'minProperties';
  }

  /**
   * @override
   */
  public function validate($doc, $context) {
    $valid = true;
    if(is_object($doc)) {
      if(count((array)$doc) < $this->minProperties) {
        $valid = new ValidationError($this, "count object < {$this->minProperties}");
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
