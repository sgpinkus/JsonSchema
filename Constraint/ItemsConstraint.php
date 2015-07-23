<?php
namespace JsonSchema\Constraint;

use JsonSchema\Constraint\Constraint;
use JsonSchema\Constraint\Exception\ConstraintParseException;

/**
 * The items constraint.
 * I'm interpreting the spec as saying additionalItems is irrelevant if not items.
 * The only issue is maybe additionalItems = false, + item undef is valid and matches the empty array.
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
   * Bit hairy.
   * @override
   */
  public function validate($doc) {
    $valid = true;
    if(is_array($doc)) {
      if(is_array($this->items)) {
        if(sizeof($this->items) > sizeof($doc)) {
          $valid = false;
        }
        else if($this->additionalItems == false && sizeof($this->items) != sizeof($doc)) {
          $valid = false;
        }
        else {
          foreach($this->items as $i => $constraint) {
            if(!$constraint->validate($doc[$i])) {
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
      else { // items is a single EmptyConstraint that must vlaidate against all.
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
    return new static($constraints, (isset($context->additionalItems) ? $context->additionalItems : true));
  }
}
