<?php
namespace JsonSchema\Constraint;

use JsonSchema\Constraint\Constraint;
use JsonSchema\Constraint\Exception\ConstraintParseException;

/**
 * The required constraint.
 */
class RequiredConstraint extends Constraint
{
  private $required;

  public function __construct(array $required) {
    $this->required = $required;
  }

  /**
   * @override
   */
  public static function getName() {
    return 'required';
  }

  /**
   * @override
   */
  public function validate($doc, $context) {
    $notSet = [];
    $valid = true;
    if(is_object($doc)) {
      foreach($this->required as $key) {
        if(!isset($doc->$key)) {
          if(!$this->continueMode()) {
            $valid = new ValidationError($this, "One or more required properties missing: {$key}", $context);
            break;
          }
          $notSet[] = $key;
        }
      }
      if(!empty($notSet)) {
        $valid = new ValidationError($this, "Required properties missing: " . implode(',', $notSet) . ".", $context);
      }
    }
    return $valid;
  }

  /**
   * @override
   */
  public static function build($context) {
    $doc = $context->required;
    if(!is_array($doc)) {
      throw new ConstraintParseException('This keyword\'s value MUST be an array');
    }
    if(sizeof($doc) < 1) {
      throw new ConstraintParseException('This keyword\'s value MUST be an array.  This array MUST have at least one element.');
    }
    return new static($doc);
  }
}
