<?php

use \JsonSchema\Constraint\EmptyConstraint;
use \JsonSchema\Constraint\Exception\ConstraintParseException;

/**
 * Basic tests.
 */
class ConstantConstraintTest extends ConstraintTest
{
  public function constraintDataProvider() {
    return [
      ['{"constant": 1}', "1", true],
      ['{"constant": 1}', "0", false],
      ['{"constant": 1}', '"1"', false],
      ['{"constant": 1}', '"one"', false],
      ['{"constant": true}', "true", true],
      ['{"constant": true}', "0", false],
      ['{"constant": true}', "1", false],
      ['{"constant": true}', '"true"', false],
      ['{"constant": "false"}', '"false"', true],
      ['{"constant": "false"}', '"true"', false],
      ['{"constant": null}', 'null', true],
      ['{"constant": null}', '0', false],
      ['{"constant": null}', '1', false],
    ];

  }

  /**
   * Fake invalid.
   */
  public function invalidConstraintDataProvider() {
    return [
      ['{"minimum": "numeric"}']
    ];
  }
}
