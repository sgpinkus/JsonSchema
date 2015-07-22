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
    'not' => 'JsonSchema\Constraint\NotConstraint'
  ];
  /** All the constraints that are found in the given object. */
  private $childConstraints = [];

  /**
   * Construct the empty constraint.
   * @input $childConstraints Result of this constraint is these constraints are ANDed together.
   */
  private function __construct(array $childConstraints) {
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
   * @override
   */
  public static function canBuild($doc) {
    return is_object($doc);
  }

  /**
   * Build the constraint. Recursively.
   * @input $doc the JSON Schema document structure. This document is marked up.
   * @throws SymbolParseException.
   * @override
   */
  public static function build($doc, $context = null) {
    $propertyHit = false;
    $childConstraints = [];

    if(!self::canBuild($doc)) {
      throw new ConstraintParseException();
    }

    foreach($doc as $property => $value) {
      if(self::skipProperty($property)) {
        continue;
      }
      else {
        if(isset(self::$childSymbols[$property])) {
          $symbolClass = self::$childSymbols[$property];
          $newSymbol = $symbolClass::build($value);
          $childConstraints[] = $newSymbol;
        }
        else if(self::canBuild($value)) {
          // For every property that is not a valid constraint build more JSON Schema on it.
          self::build($value);
        }
      }
    }

    $constraint = new EmptyConstraint($childConstraints);
    $codeKey = '$code';
    $doc->$codeKey = $constraint;
    return $constraint;
  }

  /**
   * Don't try and expand anything beginning with $.
   * Not standard but no harm done really.
   */
  public static function skipProperty($name) {
    return strpos($name, '$') === 0;
  }
}
