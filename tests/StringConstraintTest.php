<?php

use \JsonSchema\Constraint\EmptyConstraint;
use \JsonSchema\Constraint\Exception\ConstraintParseException;

/**
 * Basic tests.
 */
class StringConstraintTest extends ConstraintTest
{
  public static function setUpBeforeClass() {
  }

  /**
   * Wrapped in an EmptyConstraint, but meh.
   */
  public function constraintDataProvider() {
    return [
      ['{"maxLength": 0}', "233", true],
      ['{"maxLength": 0}', "\"\"", true],
      ['{"maxLength": 0}', "\"0\"", false],
      ['{"maxLength": 1}', "\0\"", true],
      ['{"minLength": 1}', "233", true],
      ['{"minLength": 1}', "\0\"", true],
      ['{"minLength": 1}', '""', false],
      ['{"minLength": 1}', '"\u8888"', true],
      ['{"maxLength": 1}', '"\u8888"', true],
      ['{"minLength": 3}', '"x\u8888x"', true],
      ['{"maxLength": 3}', '"x\u8888x"', true],
      ['{"pattern": "/ABC/"}', "\"XABCX\"", true],
      ['{"pattern": "/ABC/"}', "\"XABX\"", false],
      ['{"pattern": "/ABC/"}', "I result in null", true],
    ];
  }

  public function invalidConstraintDataProvider() {
    return [
      ['{"maxLength": -1}'],
      ['{"minLength": -1}'],
      // ['{"pattern": "not a pattern"}']
    ];
  }
}
