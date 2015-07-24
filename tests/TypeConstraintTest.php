<?php

use \JsonSchema\Constraint\EmptyConstraint;
use \JsonSchema\Constraint\Exception\ConstraintParseException;

/**
 * Basic tests.
 */
class TypeConstraintTest extends PHPUnit_Framework_TestCase
{
  private static $basicJson;
  private static $basicRefsJson;

  public static function setUpBeforeClass() {
  }

  /**
   * Results in a TypeConstraint wrapped in an EmptyConstraint, but meh.
   */
  public function typeConstraintDataProvider() {
    return [
      ['{"type": "boolean"}', "false", true],
      ['{"type": "boolean"}', "true", true],
      ['{"type": "boolean"}', "null", false],
      ['{"type": "boolean"}', "1", false],
      ['{"type": "boolean"}', "1.0", false],
      ['{"type": "boolean"}', "\"1\"", false],
      ['{"type": "boolean"}', "[]", false],
      ['{"type": "boolean"}', "{}", false],
      ['{"type": "null"}', "true", false],
      ['{"type": "null"}', "null", true],
      ['{"type": "null"}', "1", false],
      ['{"type": "null"}', "1.0", false],
      ['{"type": "null"}', "\"1\"", false],
      ['{"type": "null"}', "[]", false],
      ['{"type": "null"}', "{}", false],
      ['{"type": "integer"}', "true", false],
      ['{"type": "integer"}', "integer", false],
      ['{"type": "integer"}', "1", true],
      ['{"type": "integer"}', "1.0", false],
      ['{"type": "integer"}', "\"1\"", false],
      ['{"type": "integer"}', "[]", false],
      ['{"type": "integer"}', "{}", false],
      ['{"type": "number"}', "true", false],
      ['{"type": "number"}', "number", false],
      ['{"type": "number"}', "1", true],
      ['{"type": "number"}', "1.0", true],
      ['{"type": "number"}', "\"1\"", false],
      ['{"type": "number"}', "[]", false],
      ['{"type": "number"}', "{}", false],
      ['{"type": "string"}', "true", false],
      ['{"type": "string"}', "string", false],
      ['{"type": "string"}', "1", false],
      ['{"type": "string"}', "1.0", false],
      ['{"type": "string"}', "\"1\"", true],
      ['{"type": "string"}', "[]", false],
      ['{"type": "string"}', "{}", false],
      ['{"type": "array"}', "true", false],
      ['{"type": "array"}', "array", false],
      ['{"type": "array"}', "1", false],
      ['{"type": "array"}', "1.0", false],
      ['{"type": "array"}', "\"1\"", false],
      ['{"type": "array"}', "[]", true],
      ['{"type": "array"}', "{}", false],
      ['{"type": "object"}', "true", false],
      ['{"type": "object"}', "object", false],
      ['{"type": "object"}', "1", false],
      ['{"type": "object"}', "1.0", false],
      ['{"type": "object"}', "\"1\"", false],
      ['{"type": "object"}', "[]", false],
      ['{"type": "object"}', "{}", true],
      ['{"type": ["object", "number"]}', "{}", true],
      ['{"type": ["array", "number"]}', "{}", false]
    ];
  }

  /**
   * @dataProvider typeConstraintDataProvider
   */
  public function testTypeConstraint($schemaDoc, $targetDoc, $valid) {
    $schemaDoc = json_decode($schemaDoc);
    $targetDoc = json_decode($targetDoc);
    $constraint = EmptyConstraint::build($schemaDoc);
    $this->assertEquals($constraint->validate($targetDoc), $valid);
  }

  /**
   * Schema that violate JSON Schema syntax.
   */
  public function typeInvalidSchemaDataProvider() {
    return [
      ['{"type": "OBJECT"}'],
      ['{"type": "numeric"}'],
      ['{"type": ["numeric"]}']
    ];
  }

  /**
   * @dataProvider typeInvalidSchemaDataProvider
   * @expectedException \JsonSchema\Constraint\Exception\ConstraintParseException
   */
  public function testInvalidTypeConstraint($schemaDoc) {
    $schemaDoc = json_decode($schemaDoc);
    $constraint = EmptyConstraint::build($schemaDoc);
  }
}
