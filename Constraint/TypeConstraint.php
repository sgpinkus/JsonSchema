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

  public function __construct($type) {
    if(!in_array($type, static::$types)) {
      throw new \InvalidArgumentException("Not a valid type '$type'");
    }
    $this->type = $type;
  }

  /**
   * @override
   */
  public static function getName() {
    return 'type';
  }

  /**
   * @override
   */
  public function validate($doc, $context) {
    $valid = true;
    switch($this->type) {
      case 'array': {
        $valid = is_array($doc) ? true : new ValidationError($this, "not an array");
        break;
      }
      case 'boolean': {
        $valid = is_bool($doc) ? true : new ValidationError($this, "not a boolean");
        break;
      }
      case 'integer': {
        $valid = is_int($doc) ? true : new ValidationError($this, "not an integer");
        break;
      }
      case 'number': {
        $valid = (is_int($doc) || is_float($doc)) ? true : new ValidationError($this, "not a number");
        break;
      }
      case 'null': {
        $valid = is_null($doc) ? true : new ValidationError($this, "not null");
        break;
      }
      case 'object': {
        $valid = is_object($doc) ? true : new ValidationError($this, "not an object");
        break;
      }
      case 'string': {
        $valid = is_string($doc) ? true : new ValidationError($this, "not an string");
        break;
      }
    }
    return $valid;
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

    if(!(is_array($doc) || is_string($doc))) {
      throw new ConstraintParseException('The value MUST be either a string or an array.');
    }
    if(is_array($doc) && sizeof($doc) < 1) {
      throw new ConstraintParseException('If it is an array, this array MUST have at least one element.');
    }

    if(is_array($doc)) {
      $constraints = [];
      foreach($doc as $value) {
        if(!is_string($value)) {
          throw new ConstraintParseException('The value MUST be either a string or an array. If it is an array, elements of the array MUST be strings and MUST be unique.');
        }
        try {
          $constraints[] = new static($value);
        }
        catch(\InvalidArgumentException $e) {
          throw new ConstraintParseException('String values MUST be one of the seven primitive types defined by the core specification.');
        }
      }
      $constraint = new OneOfConstraint($constraints);
    }
    else {
      try {
        $constraint = new static($doc);
      }
      catch(\InvalidArgumentException $e) {
        throw new ConstraintParseException('String values MUST be one of the seven primitive types defined by the core specification.');
      }
    }
    return $constraint;
  }
}
