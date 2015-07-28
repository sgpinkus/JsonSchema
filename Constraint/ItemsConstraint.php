<?php
namespace JsonSchema\Constraint;

use JsonSchema\Constraint\Constraint;
use JsonSchema\Constraint\Exception\ConstraintParseException;

/**
 * The items constraint.
 * I'm interpreting the spec as saying additionalItems is irrelevant if items is not set.
 * The only issue is maybe additionalItems = false, + item undefined is valid and matches the empty array.
 * Well, the spec is stupid and I refuse to obey. Update the spec and let maxLength apply to an array.
 */
class ItemsConstraint extends Constraint
{
  private $items;
  private $additionalItems;

  /**
   * @input $items Mixed either EmptyConstraint or and array of EmptyConstraint.
   * @input $additionalItems Mixed either EmptyConstraint or bool.
   */
  public function __construct($items, $additionalItems = true) {
    $this->items = $items;
    $this->additionalItems = $additionalItems;
  }
  
  /**
   * @override
   */
  public static function getName() {
  	return 'items';
  }

  /**
   * Bit hairy.
   * Similarly to the properties constraint, a positional constraint only applies if the position is defined.
   * Thats a bit strange but pretty sure that is what the spec is saying.
   * Further more, note additionalItems is only relevant when items is an array.
   * @override
   */
  public function validate($doc) {
    $valid = true;
    if(is_array($doc)) {
      if(is_array($this->items)) {
        if($this->additionalItems == false && sizeof($doc) > sizeof($this->items)) {
          $valid = false;
        }
        else {
          foreach($this->items as $i => $constraint) {
            if(isset($doc[$i]) && !$constraint->validate($doc[$i])) {
              $valid = false;
              break;
            }
          }
        }
        if($valid == true && is_object($this->additionalItems)) {
          $additionalIndex = sizeof($items);
          for($i = sizeof($items); $i < sizeof($doc); $i++) {
            if(!$this->additionalItems->validate($doc[$i])) {
              $valid = false;
              break;
            }
          }
        }
      }
      // items is a single EmptyConstraint that must validate against all.
      else {
        foreach($doc as $value) {
          if(!$this->items->validate($value)) {
            $valid = false;
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
  public static function build($doc, $context = null) {
    $constraints = null;

    if(!(is_array($doc) || is_object($doc))) {
      throw new ConstraintParseException('The value MUST be either an object or an array.');
    }
    if(isset($context->additionalItems) && !(is_bool($context->additionalItems) || is_obect($context->additionalItems))) {
      throw new ConstraintParseException('The value of "additionalItems" MUST be either a boolean or an object.');
    }
    if(is_array($doc)) {
      foreach($doc as $value) {
        $constraints[] = EmptyConstraint::build($value);
      }
    }
    else {
      $constraints = EmptyConstraint::build($doc);
    }
    $additionalItems = isset($context->additionalItems) ? $context->additionalItems : true;
    if(is_object($additionalItems)) {
      $additionalItems = EmptyConstraint::build($additionalItems);
    }
    return new static($constraints, $additionalItems);
  }
}
