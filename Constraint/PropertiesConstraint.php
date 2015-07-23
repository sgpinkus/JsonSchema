<?php
namespace JsonSchema\Constraint;

use JsonSchema\Constraint\Constraint;
use JsonSchema\Constraint\Exception\ConstraintParseException;

/**
 * The properties constraint.
 * Note patternProperties is implemented as a completely separate constraint.
 * @see ItemsConstraint.php. This is ~C&P from properties constraint.
 */
class ItemsConstraint extends Constraint
{
  private $properties;
  private $additionalProperties;

  /**
   * @input $properties assoc array of EmptyConstraint.
   * @input $additionalProperties Mixed either an EmptyConstraint or bool.
   */
  public function __construct(array $properties, $additionalProperties = true) {
    $this->properties = $properties;
    $this->additionalProperties = $additionalProperties;
  }

  /**
   * Bit hairy.
   * @override
   */
  public function validate($doc) {
    $valid = true;
    if(is_object($doc)) {
      $arrayDoc = (array)$doc;
      if(sizeof($this->properties) > sizeof($arrayDoc)) {
        $valid = false;
      }
      else if($this->additionalProperties == false && sizeof($this->properties) != sizeof($doc)) {
        $valid = false;
      }
      else {
        foreach($this->properties as $i => $constraint) {
          if(!(isset($arrayDoc[$i]) && $constraint->validate($arrayDoc[$i]))) {
            $valid = false;
            break;
          }
        }
      }
      if($valid == true && is_object($this->additionalProperties)) {
        foreach($arrayDoc as $i => $value) {
          if(!(isset($this->properties[$i]) || $this->additionalProperties->validate($arrayDoc[$i]))) {
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

    if(!is_object($doc)) {
      throw new ConstraintParseException('The value MUST be either an object.');
    }
    if(isset($context->additionalProperties) && !(is_bool($context->additionalProperties) || is_obect($context->additionalProperties))) {
       throw new ConstraintParseException('The value of "additionalProperties" MUST be either a boolean or an object.');
    }
    foreach($doc as $key => $value) {
      $constraints[$key] = EmptyConstraint::build($value);
    }
    return new static($constraints, (isset($context->additionalProperties) ? $context->additionalProperties : true));
  }
}
