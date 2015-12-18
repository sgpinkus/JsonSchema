<?php
namespace JsonSchema\Constraint;

use JsonSchema\Constraint\Exception\ConstraintParseException;

/**
 * Represents any object. This is the start symbol.
 */
class EmptyConstraint extends Constraint
{
	/**
	 * @override
	 */
	public static function getName() {
		return '{}';
	}

  /** Map of valid empty constraint properties to symbols/constraint class names. */
  private static $childSymbols = [
    'allOf' => 'JsonSchema\Constraint\AllOfConstraint',
    'anyOf' => 'JsonSchema\Constraint\AnyOfConstraint',
    'oneOf' => 'JsonSchema\Constraint\OneOfConstraint',
    'enum' => 'JsonSchema\Constraint\EnumConstraint',
    'type' => 'JsonSchema\Constraint\TypeConstraint',
    'not' => 'JsonSchema\Constraint\NotConstraint',
    'minimum' => 'JsonSchema\Constraint\MinimumConstraint',
    'maximum' => 'JsonSchema\Constraint\MaximumConstraint',
    'multipleOf' => 'JsonSchema\Constraint\MultipleOfConstraint',
    'minLength' => 'JsonSchema\Constraint\MinLengthConstraint',
    'maxLength' => 'JsonSchema\Constraint\MaxLengthConstraint',
    'pattern' => 'JsonSchema\Constraint\PatternConstraint',
    'minItems' => 'JsonSchema\Constraint\MinItemsConstraint',
    'maxItems' => 'JsonSchema\Constraint\MaxItemsConstraint',
    'items' => 'JsonSchema\Constraint\ItemsConstraint',
    'uniqueItems' => 'JsonSchema\Constraint\UniqueItemsConstraint',
    'minProperties' => 'JsonSchema\Constraint\MinPropertiesConstraint',
    'maxProperties' => 'JsonSchema\Constraint\MaxPropertiesConstraint',
    'required' => 'JsonSchema\Constraint\RequiredConstraint',
    'properties' => 'JsonSchema\Constraint\PropertiesConstraint',
    'patternProperties' => 'JsonSchema\Constraint\PatternPropertiesConstraint',
  ];
  /** All the constraints that are found in the given object. */
  private $childConstraints = [];

  /**
   * Construct the empty constraint.
   * @input $childConstraints Result of this constraint is these constraints are ANDed together.
   */
  public function __construct(array $childConstraints) {
    $this->childConstraints = $childConstraints;
  }

  /**
   * Validate some JSON doc against this symbol.
   * Although its not clearly stated in the spec, all child constraints must pass. I.e. its an allOf.
   * @override
   */
  public function validate($doc) {
    $valid = true;
    foreach($this->childConstraints as $constraint) {
      $validation = $constraint->validate($doc);
      if($validation instanceof ValidationError) {
        if($valid === true) {
          $valid = new ValidationError($this, "Not all constraints passed. All required to pass.");
        }
        $valid->addChild($validation);
        if(!$this->continueMode()) {
          break;
        }
      }
    }
    return $valid;
  }

  /**
   * Build the constraint. Recursively.
   * @input $doc the JSON Schema document structure. This document is marked up with code.
   * @throws SymbolParseException.
   * @override
   */
  public static function build($doc, $context = null) {
    $propertyHit = false;
    $codeKey = '$code';
    $childConstraints = [];
    $constraint = new EmptyConstraint([]);

    if(is_object($doc)) {
      $doc->$codeKey = $constraint;
    }
    if(!($doc instanceof \StdClass)) {
      throw new ConstraintParseException();
    }
    foreach($doc as $property => $value) {
      if(self::skipProperty($property)) {
        continue;
      }
      else {
        if(isset(self::$childSymbols[$property])) {
          $symbolClass = self::$childSymbols[$property];
          $newSymbol = $symbolClass::build($value, $doc);
          $newSymbol->setContext($doc);
          $childConstraints[] = $newSymbol;
        }
        else if(is_object($value) && !isset($value->$codeKey)) {
          // For every property that is not a valid constraint build more JSON Schema on it.
          self::build($value, $doc);
        }
      }
    }

    $constraint->childConstraints = $childConstraints;
    return $constraint;
  }

  /**
   * Don't try and expand anything beginning with $ and id.
   * Not standard but no harm done really.
   */
  public static function skipProperty($name) {
    return (strpos($name, '$') === 0 || $name == 'id');
  }
}
