<?php
namespace JsonSchema\Constraint;

use JsonSchema\Constraint\Constraint;
use JsonSchema\Constraint\Exception\ConstraintParseException;

/**
 * Both items, and addtionalItems constraints.
 * Suppose target=[1,2,3] and:
 *  - `items` is an array: The schema array is effectively trimmed to size of target. I.e [a,b,c,d,e] => [a,b,c]. `1` *must* match `a` etc.
 *  - `items` is an array shorter than target, say [a,b]: [1,2] => [a,b]. The remaining [3] is addressed by addtionalItems.
 *  - `items` is an object: each item in target must validate against the JSON schema in the items object. addtionalItems is irrelevant - there are none.
 * Use case - empty array = {'items': [], 'addtionalItems': false}
 * This interpretation may or may not deviate slightly from the spec. But it makes sense doesn't it!
 * @see http://json-schema.org/latest/json-schema-validation.html#anchor37
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
   * Validate array of items.
   * @see ItemsConstraint
   * @override
   */
  public function validate($doc, $context) {
    $valid = true;
    if(is_array($doc)) {
      if(is_array($this->items)) {
        if($this->additionalItems === false && sizeof($doc) > sizeof($this->items)) {
          $valid = new ValidationError($this, "No additional items allowed", $context);
        }
        else {
          foreach($this->items as $i => $constraint) {
            if(isset($doc[$i])) {
              $validation = $constraint->validate($doc[$i], "{$context}{$i}/");
              if($validation instanceof ValidationError) {
                if($valid === true) {
                  $valid = new ValidationError($this, "One or more items failed to validate.", $context);
                }
                $valid->addChild($validation);
                if(!$this->continueMode()) {
                  break;
                }
              }
            }
          }
        }
        // If we reach here additionalItems are allowed, but they must pass additionalItems constraint if specified.
        if($valid === true && is_object($this->additionalItems)) {
          for($i = sizeof($this->items); $i < sizeof($doc); $i++) {
            $validation = $this->additionalItems->validate($doc[$i], "{$context}{$i}/");
            if($validation instanceof ValidationError) {
              if($valid === true) {
                $valid = new ValidationError($this, "One or more additional items failed validation.", $context);
              }
              $valid->addChild($validation);
              if(!$this->continueMode()) {
                break;
              }
            }
          }
        }
      }
      // items is a single EmptyConstraint that must validate against all.
      else {
        foreach($doc as $i => $value) {
          $validation = $this->items->validate($value, "{$context}{$i}/");
          if($validation instanceof ValidationError) {
            $valid = new ValidationError($this, "One ore more items failed validation.", $context);
            $valid->addChild($validation);
            if(!$this->continueMode()) {
              break;
            }
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
    $constraints = null;
    $doc = $context->items;

    if(!(is_array($doc) || is_object($doc) || is_bool($doc))) {
      throw new ConstraintParseException("The value of 'items' MUST be either an object or an array.");
    }
    if(isset($context->additionalItems) && !(is_bool($context->additionalItems) || is_object($context->additionalItems))) {
      throw new ConstraintParseException("The value of 'additionalItems' MUST be either a boolean or an object.");
    }
    if(is_array($doc)) {
      $constraints = [];
      foreach($doc as $value) {
        $constraints[] = EmptyConstraint::build($value);
      }
    }
    else {
      $constraints = EmptyConstraint::build($context->items);
    }
    $additionalItems = isset($context->additionalItems) ? $context->additionalItems : true;
    if(is_object($additionalItems)) {
      $additionalItems = EmptyConstraint::build($additionalItems);
    }
    return new static($constraints, $additionalItems);
  }
}
