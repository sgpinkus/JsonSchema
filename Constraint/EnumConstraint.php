<?php
namespace JsonSchema\Constraint;

use JsonSchema\Constraint\Constraint;
use JsonSchema\Constraint\Exception\ConstraintParseException;

/**
 */
class EnumConstraint extends Constraint
{
  private $values;

  public function __construct(array $values) {
    $this->values = $values;
  }

  /**
   * @override
   */
  public static function getName() {
  	return 'enum';
  }

  /**
   * @override
   */
  public function validate($doc, $context) {
    $valid = new ValidationError($this, "Value not in enumeration.");
    foreach($this->values() as $constraint) {
      if($constraint->validate()) {
        $valid = true;
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
    if(!is_array($doc)) {
      throw new ConstraintParseException('This keyword\'s value MUST be an array');
    }
    if(sizeof($doc) < 1) {
      throw new ConstraintParseException('This keyword\'s value MUST be an array.  This array MUST have at least one element.');
    }
    return new static($doc);
  }
}
