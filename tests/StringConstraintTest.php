<?php

use \JsonSchema\Constraint\EmptyConstraint;
use \JsonSchema\Constraint\Exception\ConstraintParseException;

/**
 * Basic tests.
 */
class StringConstraintTest extends PHPUnit_Framework_TestCase
{
  public static function setUpBeforeClass() {
  }

  /**
   * Wrapped in an EmptyConstraint, but meh.
   */
  public function stringConstraintDataProvider() {
    return [
      ['{"maxLength": 0}', "233", true],
      ['{"maxLength": 0}', "\"\"", true],
      ['{"maxLength": 0}', "\"0\"", false],
      ['{"maxLength": 1}', "\0\"", true],
      ['{"minLength": 1}', "233", true],
      ['{"minLength": 1}', "\0\"", true],
      ['{"minLength": 1}', "\"\"", false],
      ['{"pattern": "/ABC/"}', "\"XABCX\"", true],
      ['{"pattern": "/ABC/"}', "\"XABX\"", false],
      ['{"pattern": "/ABC/"}', "I result in null", true],
    ];
  }

  /**
   * @dataProvider stringConstraintDataProvider
   */
  public function testStringConstraint($schemaDoc, $targetDoc, $valid) {
    $schemaDoc = json_decode($schemaDoc);
    $targetDoc = json_decode($targetDoc);
    $constraint = EmptyConstraint::build($schemaDoc);
    $this->assertEquals($constraint->validate($targetDoc), $valid);
  }
}
