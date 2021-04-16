<?php
namespace JsonSchema\Constraint;

use JsonSchema\Constraint\Constraint;
use JsonSchema\Constraint\Exception\ConstraintParseException;

/**
 */
class FalseConstraint extends Constraint
{

  public function __construct() {
  }

  /**
   * @override
   */
  public static function getName() {
  	return '';
  }

  /**
   * @override
   */
  public function validate($doc, $context) {
    return new ValidationError($this, "false!", $context);
  }

  /**
   * @override
   */
  public static function wants($doc, $docKeys) {
    return $doc === false;
  }

  /**
   * @override
   */
  public static function build($context) {
    return new static();
  }
}
