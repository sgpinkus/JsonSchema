<?php
namespace JsonSchema\Constraint;

use JsonSchema\Constraint\Constraint;
use JsonSchema\Constraint\Exception\ConstraintParseException;

/**
 */
class PropertyNamesConstraint extends Constraint
{
  private $propertyNames;

  public function __construct($propertyNames) {
    $this->propertyNames = $propertyNames;
  }

  /**
   * @override
   */
  public static function getName() {
    return 'propertyNames';
  }

  /**
   * @override
   */
   public function validate($doc, $context) {
     $valid = true;
     if(is_object($doc)) {
       foreach($doc as $i => $value) {
         $validation = $this->propertyNames->validate($i, "{$context}{$i}/");
         if($validation instanceof ValidationError) {
           $valid = new ValidationError($this, "One ore more propertyNames failed validation.", $context);
           $valid->addChild($validation);
           if(!$this->continueMode()) {
             break;
           }
         }
       }
     }
     return $valid;
   }

  /**
   * @override
   */
   public static function build($context) {
     $doc = $context->propertyNames;
     if(!(is_object($doc) || is_bool($doc))) {
       throw new ConstraintParseException("The value of 'propertyNames' MUST be an object.");
     }
     $constraint = EmptyConstraint::build($context->propertyNames);
     return new static($constraint);
   }
}
