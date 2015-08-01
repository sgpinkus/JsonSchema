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
   * Ensure items in an array match the per item constraints specified. Bit hairy.
   * Similarly to the properties constraint, a positional constraint only applies if the position is defined in the target
   * Thats a bit strange but pretty sure that is what the spec is saying.
   * Further more, note additionalItems is only relevant when items is an array.
   * @override
   */
  public function validate($doc) {
    $valid = true;
    if(is_array($doc)) {
      if(is_array($this->items)) {
        if($this->additionalItems == false && sizeof($doc) > sizeof($this->items)) {
          $valid = new ValidationError($this, "No additional items allowed");
        }
        else {
          foreach($this->items as $i => $constraint) {
            if(isset($doc[$i])) {
              $validation = $constraint->validate($doc[$i]);
              if($validation instanceof ValidationError) {
                if($valid === true) {
                  $valid = new ValidationError($this, "One or more items failed to validate.");
                }
                if(!$this->continueMode()) {
                  break;
                }
                $valid->addChild($validation);
              }
            }
          }
        }
        // If we reach here additionalItems are allowed, but they must pass additionalItems constraint if specified.
        if($valid === true && is_object($this->additionalItems)) {
          $additionalIndex = sizeof($items);
          for($i = sizeof($items); $i < sizeof($doc); $i++) {
            $validation = $this->additionalItems->validate($doc[$i]);
            if($validation instanceof ValidationError) {
              if($valid === true) {
                $valid = new ValidationError($this, "One or more additional items failed validation.");
              }
              if(!$this->continueMode()) {
                break;
              }
              $valid->addChild($validation);
            }
          }
        }
      }
      // items is a single EmptyConstraint that must validate against all.
      else {
        foreach($doc as $value) {
          $validation = $this->items->validate($value);
          if($validation instanceof ValidationError) {
            $valid = new ValidationError($this, "One ore more items failed validation.");
            if(!$this->continueMode()) {
              break;
            }
            $valid->addChild($validation);
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
