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
  public function validate($doc) {
    $valid = false;
    foreach($this->childConstraints as $constraint) {
      if($constraint->validate($doc)) {
        $valid = true;
        break;
      }
    }
    return $valid;
  }
}
