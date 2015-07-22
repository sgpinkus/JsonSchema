<?php
namespace JsonSchema\Constraint;

use JsonSchema\Constraint\SomeOfConstraint;

/**
 */
class OneOfConstraint extends SomeOfConstraint
{
  /**
   * @override
   */
  public function validate($doc) {
    $valid = false;
    foreach($this->childConstraints as $constraint) {
      if($constraint->validate($doc) && $valid == false) {
        $valid = true;
      }
      else if($constraint->validate($doc) && $valid == true) {
        $valid = false;
        break;
      }
    }
    return $valid;
  }
}
