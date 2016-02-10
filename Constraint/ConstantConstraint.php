<?php
namespace JsonSchema\Constraint;

use JsonSchema\Constraint\Constraint;
use JsonSchema\Constraint\Exception\ConstraintParseException;

/**
 */
class ConstantConstraint extends Constraint
{
  private $constant;

  public function __construct($constant) {
    $this->constant = $constant;
  }

  /**
   * @override
   */
  public static function getName() {
  	return 'constant';
  }

  /**
   * @override
   */
  public function validate($doc, $context) {
    $valid = true;
    if($doc !== $this->constant) {
      $valid = new ValidationError($this, "(" . gettype($doc) . ")$doc !== (" . gettype($this->constant) . "){$this->constant}", $context);
    }
    return $valid;
  }

  /**
   * @override
   */
  public static function build($context) {
    $doc = $context->constant;
    return new static($doc);
  }
}
