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
    return "['constant', 'const']";
  }

  public static function getKeys() {
    return ['constant', 'const'];
  }


  /**
   * @override
   */
  public function validate($doc, $context) {
    $valid = true;
    if(!static::jsonTypeEquality($doc, $this->constant)) {
      $docString = "<some-value>";
      $constantString = "<some-value>";
      try {
        $docString = json_encode($doc);
      }
      catch(\Exception $e) {}
      try {
        $constantString = json_encode($this->constant);
      }
      catch(\Error $e) {}
      $valid = new ValidationError($this, "(" . gettype($doc) . ")$docString !== (" . gettype($this->constant) . ")$constantString", $context);
    }
    return $valid;
  }

  /**
   * @override
   */
  public static function build($context) {
    $doc = isset($context->constant) ? $context->constant : $context->const;
    return new static($doc);
  }
}
