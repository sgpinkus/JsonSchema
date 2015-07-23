<?php

use \JsonSchema\Constraint\EmptyConstraint;
use \JsonSchema\Constraint\Exception\ConstraintParseException;

/**
 * Basic tests.
 */
class EmptyConstraintTest extends PHPUnit_Framework_TestCase
{
  private static $basicJson;
  private static $basicRefsJson;

  public static function setUpBeforeClass() {
  }

  public function emptyConstraintDataProvider() {
    return [
      ['{}', false, true],
      ['{}', 5, true],
      ['{}', 5.6, true],
      ['{}', "String", true],
      ['{}', '{}', true],
      ['{}', '{"a":0, "b":1, "c":2}', true],
      ['{"a":0, "b":1, "c":2, "d":{}}', false, true],
      ['{"a":0, "b":1, "c":2, "d":{}}', 5, true],
      ['{"a":0, "b":1, "c":2, "d":{}}', 5.6, true],
      ['{"a":0, "b":1, "c":2, "d":{}}', "String", true],
      ['{"a":0, "b":1, "c":2, "d":{}}', '{}', true],
      ['{"a":0, "b":1, "c":2, "d":{}}', '{"a":0, "b":1, "c":2}', true]
    ];
  }

  /**
   * @dataProvider emptyConstraintDataProvider
   */
  public function testEmptyConstraint($schemaDoc, $targetDoc, $valid) {
    $schemaDoc = json_decode($schemaDoc);
    $targetDoc = json_decode($targetDoc);
    $constraint = EmptyConstraint::build($schemaDoc);
    $this->assertEquals($constraint->validate($targetDoc), $valid);
  }

  public function exceptionalConstraintDataProvider() {
    return [
      ['{"allOf": []}', false, true],
      ['{"anyOf": []}', false, true],
      ['{"oneOf": []}', false, true],
      ['{"not": []}', false, true],
      ['{"enum": []}', false, true],
      ['{"type": []}', false, true]
    ];
  }

  /**
   * @expectedException JsonSchema\Constraint\Exception\ConstraintParseException
   * @dataProvider exceptionalConstraintDataProvider
   */
  public function testExceptionalConstraint($schemaDoc, $targetDoc, $valid) {
    $schemaDoc = json_decode($schemaDoc);
    $targetDoc = json_decode($targetDoc);
    $constraint = EmptyConstraint::build($schemaDoc);
  }
}
