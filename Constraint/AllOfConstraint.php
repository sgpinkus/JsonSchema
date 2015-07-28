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
  public function validate($doc) {
    $valid = true;
    foreach($this->childConstraints as $constraint) {
      if(!$constraint->validate($doc)) {
        $valid = false;
        break;
      }
    }
    return $valid;
  }
}
