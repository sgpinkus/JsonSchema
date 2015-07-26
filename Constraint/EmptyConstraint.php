<?php
namespace JsonSchema\Constraint;

use JsonSchema\Constraint\Exception\ConstraintParseException;

/**
 * Represents any object. This is the start symbol.
 */
class EmptyConstraint extends Constraint
{
  /** map of valid empty constraint properties to symbols/constraint class names. */
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
   */
  public function validate($doc) {
    $valid = true;
    foreach($this->childConstraints as $constraint) {
      if(!$constraint->validate($doc)) {
        $valid = false;
        break;
      }
    }
    return $valid;
  }

  /**
   * Build the constraint. Recursively.
   * @input $doc the JSON Schema document structure. This document is marked up.
   * @throws SymbolParseException.
   * @override
   */
  public static function build($doc, $context = null) {
    $propertyHit = false;
    $codeKey = '$code';
    $childConstraints = [];
    $doc->$codeKey = true;

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
          $childConstraints[] = $newSymbol;
        }
        else if(is_object($value) && !isset($value->$codeKey)) {
          // For every property that is not a valid constraint build more JSON Schema on it.
          self::build($value, $doc);
        }
      }
    }

    $constraint = new EmptyConstraint($childConstraints);
    $doc->$codeKey = $constraint;
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
