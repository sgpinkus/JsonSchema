<?php
namespace JsonSchema\Constraint;

use JsonSchema\Constraint\Constraint;
use JsonSchema\Constraint\Exception\ConstraintParseException;

/**
 * The required constraint.
 */
class RequiredConstraint extends Constraint
{
  private $required;

  public function __construct(array $required) {
    $this->required = $required;
  }

  /**
   * @override
   */
  public function validate($doc) {
    $valid = true;
    if(is_object($doc)) {
      $arrayDoc = (array)$doc;
      foreach($this->required as $key) {
        if(!isset($arrayDoc[$key])) {
          $valid = false;
          break;
        }
      }
    }
    return $valid;
  }

  /**
   * @override
   */
  public static function build($doc, $context = null) {
    if(!is_array($doc)) {
      throw new ConstraintParseException('This keyword\'s value MUST be an array');
    }
    if(sizeof($doc) < 1) {
      throw new ConstraintParseException('This keyword\'s value MUST be an array.  This array MUST have at least one element.');
    }
    return static($doc);
  }
}
