<?php
namespace JsonSchema\Constraint;

use JsonSchema\Constraint\Constraint;
use JsonSchema\Constraint\Exception\ConstraintParseException;

/**
 * The someOf constraint.
 */
abstract class SomeOfConstraint extends Constraint
{
  protected $childConstraints = [];

  /**
   * @input $childConstraints Array of EmptyConstraints.
   */
  public function __construct(array $childConstraints) {
    $this->childConstraints = $childConstraints;
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
    $propertyHit = false;
    $childConstraints = [];

    if(!is_array($doc)) {
      throw new ConstraintParseException('This keyword\'s value MUST be an array');
    }
    if(sizeof($doc) < 1) {
      throw new ConstraintParseException('This keyword\'s value MUST be an array.  This array MUST have at least one element.');
    }
    foreach($doc as $of) {
      $childConstraints[] = EmptyConstraint::build($of);
    }
    return new static($childConstraints);
  }
}
