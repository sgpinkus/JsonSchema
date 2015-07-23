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
  public function propertiesConstraintDataProvider() {
    return [
    ];
  }

  /**
   * @dataProvider itemsConstraintDataProvider
   */
  public function testPropertiesConstraint($schemaDoc, $targetDoc, $valid) {
    $schemaDoc = json_decode($schemaDoc);
    $targetDoc = json_decode($targetDoc);
    $constraint = EmptyConstraint::build($schemaDoc);
    $this->assertEquals($constraint->validate($targetDoc), $valid);
  }

  /**
   * @expectedException \JsonSchema\Constraint\Exception\ConstraintParseException
   */
  public function testInvalidTypeConstraint() {
    $schemaDoc = json_decode('{"properties": "numeric"}');
    $constraint = EmptyConstraint::build($schemaDoc);
  }
}
