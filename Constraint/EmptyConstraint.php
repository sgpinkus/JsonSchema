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
    'JsonSchema\Constraint\AllOfConstraint',
    'JsonSchema\Constraint\AnyOfConstraint',
    'JsonSchema\Constraint\OneOfConstraint',
    'JsonSchema\Constraint\EnumConstraint',
    'JsonSchema\Constraint\TypeConstraint',
    'JsonSchema\Constraint\NotConstraint',
    'JsonSchema\Constraint\MinimumConstraint',
    'JsonSchema\Constraint\MaximumConstraint',
    'JsonSchema\Constraint\MultipleOfConstraint',
    'JsonSchema\Constraint\MinLengthConstraint',
    'JsonSchema\Constraint\MaxLengthConstraint',
    'JsonSchema\Constraint\PatternConstraint',
    'JsonSchema\Constraint\MinItemsConstraint',
    'JsonSchema\Constraint\MaxItemsConstraint',
    'JsonSchema\Constraint\ItemsConstraint',
    'JsonSchema\Constraint\ContainsConstraint',
    'JsonSchema\Constraint\UniqueItemsConstraint',
    'JsonSchema\Constraint\MinPropertiesConstraint',
    'JsonSchema\Constraint\MaxPropertiesConstraint',
    'JsonSchema\Constraint\PropertyNamesConstraint',
    'JsonSchema\Constraint\RequiredConstraint',
    'JsonSchema\Constraint\PropertiesConstraint',
    'JsonSchema\Constraint\FormatConstraint',
    'JsonSchema\Constraint\DependenciesConstraint',
    'JsonSchema\Constraint\ConstantConstraint',
    'JsonSchema\Constraint\SwitchConstraint',
  ];
  /** All the constraints that are found in the given object. */
  private $childConstraints = [];

  /**
   * Construct the empty constraint.
   * @input $childConstraints Result of this constraint is results of these constraints ANDed together.
   */
  public function __construct(array $childConstraints = []) {
    $this->childConstraints = $childConstraints;
  }

  /**
   * Add a constraint.
   * @todo make this a little more fail safe.
   */
  public static function addConstraint($constraintClass) {
    self::$childSymbols[] = $constraintClass;
  }

  /**
   * Validate some JSON doc against this symbol.
   * Although its not clearly stated in the spec, all child constraints must pass. I.e. its an allOf.
   * @override
   */
  public function validate($doc, $context) {
    $valid = true;
    foreach($this->childConstraints as $constraint) {
      $validation = $constraint->validate($doc, $context);
      if($validation instanceof ValidationError) {
        if($valid === true) {
          $valid = new ValidationError($this, "Not all constraints passed. All required to pass.", $context);
        }
        $valid->addChild($validation);
        if(!$this->continueMode()) {
          break;
        }
      }
    }
    return $valid;
  }

  public static function wants($doc, array $docKeys) {
    return $doc === true;
  }

  /**
   * Build empty constraint recursively.
   * Any keys not caught by loaded symbols are also built if they are valid JSON Schema.
   * @input $doc the JSON Schema document structure. This document is marked up with code.
   * @throws SymbolParseException.
   * @override
   */
  public static function build($doc) {
    $propertyHit = false;
    $constraint = new EmptyConstraint([]);

    if($doc === false) {
      $constraint = new FalseConstraint();
    }
    elseif ($doc === true) {
      $constraint = new EmptyConstraint();
    }
    elseif(!($doc instanceof \StdClass)) {
      throw new ConstraintParseException();
    }
    elseif(isset($doc->{'$code'})) {
      $constraint = $doc->{'$code'};
    }
    else {
      $doc->{'$code'} = $constraint;
      $childConstraints = [];
      $remainingKeys = array_keys((array)$doc); // init collection of keys not handled by a symbol.
      foreach(self::$childSymbols as $symbol) {
        if($symbol::wants($doc, $remainingKeys)) {
          $newSymbol = $symbol::build($doc);
          $newSymbol->setContext($doc);
          $childConstraints[] = $newSymbol;
          $remainingKeys = self::removeCaughtKeys($remainingKeys, $symbol::getKeys());
          if(!self::keysLeft($remainingKeys)) {
            break;
          }
        }
      }
      foreach($remainingKeys as $key) {
        if(is_object($doc->$key) && !self::skipProperty($key)) {
          self::build($doc->$key);
        }
      }
      $constraint->childConstraints = $childConstraints;
    }
    return $constraint;
  }

  /**
   * Remove keys that a constraint bulit on, so they are not treated as EmptyConstraints.
   */
  public static function removeCaughtKeys(array $remainingKeys, array $caughtKeys) {
    return array_diff($remainingKeys, $caughtKeys);
  }

  /**
   * Test if we need to keep asking symbols to parse object.
   */
  public static function keysLeft(array $remainingKeys) {
    foreach($remainingKeys as $key) {
      if(!self::skipProperty($key)) {
        return true;
      }
    }
    return false;
  }

  /**
   * Don't try and expand anything beginning with $.
   * Not standard but no harm done really.
   */
  public static function skipProperty($name) {
    return (strpos($name, '$') === 0);
  }
}
