<?php
namespace JsonSchema\Constraint;

use JsonSchema\Constraint\Constraint;
use JsonSchema\Constraint\Exception\ConstraintParseException;

/**
 *
 */
class SwitchConstraint extends Constraint
{
  private $conditionals;

  /**
   * @param $conditionals Array of parsed conditionals [if, then, continue]
   */
  public function __construct(array $conditionals) {
    $this->conditionals = $conditionals;
  }

  /**
   * @override
   */
  public static function getName() {
    return 'switch';
  }

  /**
   * @override
   */
  public function validate($doc, $context) {
    $valid = true;
    foreach($this->conditionals as $i => $conditional) {
      $ifValid = isset($conditional->if) ? $conditional->if->validate($doc, $context) : true;
      if($ifValid === true) {
        if(is_bool($conditional->then)) {
          if($conditional->then == false) {
            $valid = new ValidationError($this, "Switch broke out after " . ($i+1) . "/" . count($this->conditionals) . " without match", $context);
          }
          else {
            $valid = true;
            if(isset($conditional->continue) && $conditional->continue == true) {
              continue;
            }
            else {
              break;
            }
          }
        }
        // then its a schema.
        else {
          $valid = $conditional->then->validate($doc, $context);
          if($valid === true) {
            if(isset($conditional->continue) && $conditional->continue == true) {
              continue;
            }
            else {
              break;
            }
          }
          else {
            break; // todo..
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
    $doc = $context->switch;
    $conditions = [];
    if(!(is_array($doc))) {
      throw new ConstraintParseException('The value of "switch" MUST be an array.');
    }
    foreach($doc as $conditional) {
      $conditions[] = self::parseConditional($conditional);
    }
    return new static($conditions);
  }

  private static function parseConditional($doc) {
    if(!(is_object($doc))) {
      throw new ConstraintParseException('The items in a "switch" array MUST be objects.');
    }
    if(isset($doc->continue) && !is_bool($doc->continue)) {
      throw new ConstraintParseException('The value of "continue" MUST be a boolean.');
    }
    if(!isset($doc->then)) {
      throw new ConstraintParseException('"then" must be specified.');
    }
    if(!(is_bool($doc->then) || is_object($doc->then))) {
      throw new ConstraintParseException('"then" must be an object or boolean.');
    }
    if(is_object($doc->then)) {
      $doc->then = EmptyConstraint::build($doc->then);
    }
    if(isset($doc->if)) {
      if(!is_object($doc->if)) {
        throw new ConstraintParseException('The value of "if" MUST be an object.');
      }
      $doc->if = EmptyConstraint::build($doc->if);
    }
    return $doc;
  }
}
