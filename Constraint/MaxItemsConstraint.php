<?php
namespace JsonSchema\Constraint;

use JsonSchema\Constraint\Constraint;
use JsonSchema\Constraint\Exception\ConstraintParseException;

/**
 * The maxItems constraint.
 */
class MaxItemsConstraint extends Constraint
{
  private $maxItems;

  public function __construct($maxItems) {
    $this->maxItems = (int)$maxItems;
  }

  /**
   * @override
   */
  public static function getName() {
  	return 'maxItems';
  }

  /**
   * @override
   */
  public function validate($doc, $context) {
    $valid = true;
    if(is_array($doc)) {
      if(count($doc) > $this->maxItems) {
        $valid = new ValidationError($this, "Number of items > {$this->maxItems}", $context);
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
