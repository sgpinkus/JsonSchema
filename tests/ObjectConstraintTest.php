<?php

use \JsonSchema\Constraint\EmptyConstraint;
use \JsonSchema\Constraint\Exception\ConstraintParseException;

/**
 * Basic tests.
 */
class ObjectConstraintTest extends PHPUnit_Framework_TestCase
{
  public static function setUpBeforeClass() {
  }

  /**
   * Wrapped in an EmptyConstraint, but meh.
   */
  public function objectConstraintDataProvider() {
    return [
      ['{"required": ["a","b","c"]}', '{"a":0, "b":1, "c":2}', true],
      ['{"required": ["a","b","c"]}', '{"a":0, "b":1, "c":2, "d": 4}', true],
      ['{"required": ["a","b","c"]}', '{"a":0, "b":1}', false],
      ['{"minProperties": 0}', '{"a":0, "b":1}', true],
      ['{"minProperties": 1}', '{"a":0, "b":1}', true],
      ['{"minProperties": 2}', '{"a":0, "b":1}', true],
      ['{"minProperties": 3}', '{"a":0, "b":1}', false],
      ['{"maxProperties": 2}', '{"a":0, "b":1}', true],
      ['{"maxProperties": 1}', '{"a":0, "b":1}', false],
      ['{"maxProperties": 0}', '{"a":0, "b":1}', false],
      ['{"maxProperties": 0}', '{}', true],
      ['{"properties": {"a":{}, "b":{}}}', '{"a":0, "b":1}', true],
      ['{"properties": {"a":{}, "b":{}}}', '{"a":0, "b":1, "c":2}', true],
      ['{"properties": {"a":{}, "b":{},"c":{}}}', '{"a":0, "b":1}', true],
      ['{"properties": {"a":{"type": "number"}}, "b":{}}', '{"a":0, "b":1}', true],
      ['{"properties": {"a":{"type": "string"}}, "b":{}}', '{"a":0, "b":1}', false],
      ['{"properties": {"a":{}, "b":{}}, "additionalProperties": false}', '{"a":0, "b":1}', true],
      ['{"properties": {"a":{}, "b":{}}, "additionalProperties": false}', '{"a":0, "b":1, "c":2}', false],
      ['{"properties": {"a":{}, "b":{}}, "additionalProperties": {}}', '{"a":0, "b":1, "c":2}', true],
      ['{"properties": {"a":{}, "b":{}}, "additionalProperties": {"type":"number"}}', '{"a":0, "b":1, "c":2}', true],
      ['{"properties": {"a":{}, "b":{}}, "additionalProperties": {"type":"string"}}', '{"a":0, "b":1, "c":2}', false]
    ];
  }

  /**
   * @dataProvider objectConstraintDataProvider
   */
  public function testObjectConstraints($schemaDoc, $targetDoc, $valid) {
    $schemaDoc = json_decode($schemaDoc);
    $targetDoc = json_decode($targetDoc);
    $constraint = EmptyConstraint::build($schemaDoc);
    $this->assertEquals($constraint->validate($targetDoc), $valid);
  }

  /**
   * Schema that violate JSON Schema syntax.
   */
  public function objectInvalidSchemaDataProvider() {
    return [
      ['{"required": 0}'],
      ['{"required": []}'],
      ['{"maxProperties": -1}'],
      ['{"minProperties": -1}'],
      ['{"properties": 0}'],
      ['{"properties": {}, "additionalProperties": "none"}']
    ];
  }

  /**
   * @dataProvider objectInvalidSchemaDataProvider
   * @expectedException \JsonSchema\Constraint\Exception\ConstraintParseException
   */
  public function testInvalidTypeConstraint($schemaDoc) {
    $schemaDoc = json_decode($schemaDoc);
    $constraint = EmptyConstraint::build($schemaDoc);
  }
}
