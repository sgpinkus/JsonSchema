<?php
namespace JsonSchema\Constraint;

use JsonSchema\Constraint\Constraint;
use JsonSchema\Constraint\Exception\ConstraintParseException;

/**
 */
class TypeConstraint extends Constraint
{
  private $type;
  private static $types = ['array', 'boolean', 'integer', 'number', 'null', 'object', 'string'];

  public function __construct(array $type) {
    $this->type = $type;
  }

  /**
   * @override
   */
  public function validate($doc) {
    $valid = false;
    switch($this->type) {
      case 'array': {
        $valid = is_array($doc);
        break;
      }
      case 'boolean': {
        $valid = is_bool($doc);
        break;
      }
      case 'integer': {
        $valid = is_int($doc);
        break;
      }
      case 'number': {
        $valid = is_int($doc) || is_float($doc);
        break;
      }
      case 'null': {
        $valid = is_null($doc);
        break;
      }
      case 'object': {
        $valid = is_object($doc);
        break;
      }
      case 'string': {
        $valid = is_string($doc);
        break;
      }
    }
  }

  /**
   * @override
   */
  public static function canBuild($doc) {
    return is_array($doc) && sizeof($doc) > 0;
  }

  /**
   * @override
   */
  public static function build($doc, $context = null) {
    $constraint = null;

    if(!is_array($doc) || !is_string($doc)) {
      throw new ConstraintParseException('This keyword\'s value MUST be an array');
    }
    if(is_array($doc) && sizeof($doc) < 1) {
      throw new ConstraintParseException('This keyword\'s value MUST be an array. This array MUST have at least one element.');
    }

    if(is_array($doc)) {
      $constraints = [];
      foreach($doc as $value) {
        if(!is_string($value)) {
          throw new ConstraintParseException('The value of this keyword MUST be either a string or an array. If it is an array, elements of the array MUST be strings and MUST be unique.');
        }
        if(!in_array($value, static::$types)) {
          throw new ConstraintParseException('String values MUST be one of the seven primitive types defined by the core specification.');
        }
        $constraints[] = new static($value);
      }
      $constraint = new OneOfConstraint($constraints);
    }
    else {
      $constraint = new static($doc);
    }
    return $constraint;
  }
}
