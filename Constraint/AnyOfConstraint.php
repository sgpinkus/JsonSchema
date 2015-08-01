<?php
namespace JsonSchema\Constraint;

use JsonSchema\Constraint\SomeOfConstraint;

/**
 */
class AnyOfConstraint extends SomeOfConstraint
{
  /**
   * @override
   */
  public static function getName() {
    return 'anyOf';
  }

  /**
   * @override
   */
  public function validate($doc) {
    $valid = new ValidationError($this, "No constraints passed. At least one required.");;
    foreach($this->childConstraints as $constraint) {
      $validation = $constraint->validate($doc);
      if($validation instanceof ValidationError) {
        if($this->continueMode()) {
          $valid->addChild($validation);
        }
      }
      else {
        $valid = true;
        break;
      }
    }
    return $valid;
  }
}
