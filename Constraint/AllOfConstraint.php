<?php
namespace JsonSchema\Constraint;

use JsonSchema\Constraint\SomeOfConstraint;

/**
 */
class AllOfConstraint extends SomeOfConstraint
{
  /**
   * @override
   */
  public static function getName() {
    return 'allOf';
  }

  /**
   * @override
   */
  public function validate($doc, $context) {
    $valid = true;
    foreach($this->childConstraints as $constraint) {
      $validation = $constraint->validate($doc, $context);
      if($validation instanceof ValidationError) {
        if($valid === true) {
          $valid = new ValidationError($this, "Not all constraints passed. All required to pass.", $context);
        }
        $valid->addChild($validation);
        if(!$this->continueMode()) {
          break;
        }
      }
    }
    return $valid;
  }
}
