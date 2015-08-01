<?php

use \JsonSchema\Constraint\EmptyConstraint;
use \JsonSchema\Constraint\Exception\ConstraintParseException;

/**
 * Basic tests.
 */
class NumericConstraintTest extends ConstraintTest
{
  public function constraintDataProvider() {
    return [
      ['{"minimum": 1}', "2", true],
      ['{"minimum": 1}', "1", true],
      ['{"minimum": 1}', "0", false],
      ['{"minimum": 1}', "-1", false],
      ['{"minimum": 0, "exclusiveMinimum": true}', "1", true],
      ['{"minimum": 0, "exclusiveMinimum": true}', "0", false],
      ['{"maximum": 1}', "0", true],
      ['{"maximum": 1}', "1", true],
      ['{"maximum": 1}', "2", false],
      ['{"maximum": 1}', "3", false],
      ['{"maximum": 1, "exclusiveMaximum": true}', "2", false],
      ['{"maximum": 1, "exclusiveMaximum": true}', "1", false],
      ['{"maximum": 1, "exclusiveMaximum": true}', "0", true],
      ['{"multipleOf": 1}', "1", true],
      ['{"multipleOf": 2}', "3", false],
    ];
  }

  public function invalidConstraintDataProvider() {
    return [
      ['{"minimum": "numeric"}']
    ];
  }
}
