<?php
namespace JsonSchema\Constraint;

use JsonSchema\Constraint\Constraint;
use JsonSchema\Constraint\Exception\ConstraintParseException;


/**
 * Valid if at least one item in array matches subschema, as opposed to items: [{ ... }] which
 * is valid if all items match.
 */
class ContainsConstraint extends Constraint
{
  private $contains;

  /**
   * @input $items Mixed either EmptyConstraint or and array of EmptyConstraint.
   * @input $additionalItems Mixed either EmptyConstraint or bool.
   */
  public function __construct($contains) {
    $this->contains = $contains;
  }

  /**
   * @override
   */
  public static function getName() {
    return 'contains';
  }

  /**
   * @override
   */
   public function validate($doc, $context) {
     $valid = true;
     if(is_array($doc)) {
       $valid = false;
       foreach($doc as $i => $value) {
         $validation = $this->contains->validate($value, "{$context}{$i}/");
         if($validation === true) {
           $valid = true;
           break;
         }
       }
       if(!$valid) {
         $valid = new ValidationError($this, "Contains no matching value", $context);
       }
     }
     return $valid;
   }

  /**
   * @override
   */
  public static function build($context) {
    $doc = $context->contains;
    if(!(is_object($doc) || is_bool($doc))) {
      throw new ConstraintParseException("The value of 'contains' MUST be an object.");
    }
    $constraint = EmptyConstraint::build($context->contains);
    return new static($constraint);
  }
}
