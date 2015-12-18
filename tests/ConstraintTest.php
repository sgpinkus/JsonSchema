<?php

use \JsonSchema\Constraint\EmptyConstraint;
use \JsonSchema\Constraint\Exception\ConstraintParseException;
use \JsonSchema\Constraint\ValidationError;

/**
 * Basic tests.
 */
abstract class ConstraintTest extends PHPUnit_Framework_TestCase
{
  /**
   * Constraints to test. Must be wrapped in an object / EmptyConstraint.
   */
  public abstract function constraintDataProvider();

  /**
   * Schema that violate JSON Schema syntax.
   */
  public abstract function invalidConstraintDataProvider();

  /**
   * @dataProvider constraintDataProvider
   */
  public function testConstraint($schemaDoc, $targetDoc, $valid) {
    $schemaDoc = json_decode($schemaDoc);
    $targetDoc = json_decode($targetDoc);
    $constraint = EmptyConstraint::build($schemaDoc);
    if($valid === true) {
      $this->assertEquals($constraint->validate($targetDoc, ""), true);
    }
    else {
      $this->assertInstanceOf('\JsonSchema\Constraint\ValidationError', $constraint->validate($targetDoc, ""));
    }
  }

  /**
   * @dataProvider invalidConstraintDataProvider
   * @expectedException JsonSchema\Constraint\Exception\ConstraintParseException
   */
  public function testInvalidConstraint($schemaDoc) {
    $schemaDoc = json_decode($schemaDoc);
    $constraint = EmptyConstraint::build($schemaDoc);
  }
}
