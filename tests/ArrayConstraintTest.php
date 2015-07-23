<?php

use \JsonSchema\Constraint\EmptyConstraint;
use \JsonSchema\Constraint\Exception\ConstraintParseException;

/**
 * Basic tests.
 */
class ArrayConstraintTest extends PHPUnit_Framework_TestCase
{
  public static function setUpBeforeClass() {
  }

  /**
   * Wrapped in an EmptyConstraint, but meh.
   */
  public function itemsConstraintDataProvider() {
    return [
      ['{"items": [{},{},{}], "additionalItems": true}', "[1,2]", false],
      ['{"items": [{},{},{}], "additionalItems": false}', "[1,2]", false],
      ['{"items": [{},{},{}], "additionalItems": true}', "[1,2,3]", true],
      ['{"items": [{},{},{}], "additionalItems": true}', "[1,2,3,4]", true],
      ['{"items": [{},{},{}], "additionalItems": false}', "[1,2,3,4]", false],
      ['{"items": [{},{},{}]}', "[1,2,3]", true],
      ['{"items": [{},{},{}]}', "[1,2,3,4]", true],
      ['{"items": [{}]}', "[1,2,3,false,\"3\"]", true],
      ['{"items": {}}', "[1,2,3,false,\"3\"]", true],
      ['{"items": {}, "additionalItems": true}', "[1,2,3,false,\"3\"]", true],
      ['{"items": {}, "additionalItems": false}', "[1,2,3,false,\"3\"]", true],
      ['{"items": {"minimum": 1}}', "[1,2,3]", true],
      ['{"items": {"minimum": 1}}', "[1,2,3,0]", false],
      ['{"items": [{"minimum": 1}]}', "[1,2,3,0]", true],
      ['{"items": [{"minimum": 1}], "additionalItems": false}', "[1,2,3,0]", false],
      ['{"items": {"minimum": 1}, "additionalItems": true}', "[1,2,3,0]", false],
    ];
  }

  /**
   * @dataProvider itemsConstraintDataProvider
   */
  public function testItemsConstraint($schemaDoc, $targetDoc, $valid) {
    $schemaDoc = json_decode($schemaDoc);
    $targetDoc = json_decode($targetDoc);
    $constraint = EmptyConstraint::build($schemaDoc);
    $this->assertEquals($constraint->validate($targetDoc), $valid);
  }

  /**
   * @expectedException \JsonSchema\Constraint\Exception\ConstraintParseException
   */
  public function testInvalidTypeConstraint() {
    $schemaDoc = json_decode('{"items": "numeric"}');
    $constraint = EmptyConstraint::build($schemaDoc);
  }
}
