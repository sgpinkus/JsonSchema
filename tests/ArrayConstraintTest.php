<?php

use \JsonSchema\Constraint\EmptyConstraint;
use \JsonSchema\Constraint\Exception\ConstraintParseException;

/**
 * Basic tests.
 */
class ArrayConstraintTest extends ConstraintTest
{
  public function constraintDataProvider() {
    return [
      ['{"minItems": 0}', '[1,2]', true],
      ['{"minItems": 1}', '[1,2]', true],
      ['{"minItems": 2}', '[1,2]', true],
      ['{"minItems": 3}', '[1,2]', false],
      ['{"maxItems": 2}', '[1,2]', true],
      ['{"maxItems": 1}', '[1,2]', false],
      ['{"maxItems": 0}', '[1,2]', false],
      ['{"maxItems": 0}', '[]', true],
      ['{"items": [{},{},{}], "additionalItems": true}', "[1,2]", true],
      ['{"items": [{},{},{}], "additionalItems": false}', "[1,2]", true],
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
      ['{"uniqueItems": false}', "[1,2,3,0]", true],
      ['{"uniqueItems": true}', "[1,2,3,0]", true],
      ['{"uniqueItems": true}', "[1,2,3,3]", false]
    ];
  }

  public function invalidConstraintDataProvider() {
    return [
      ['{"items": "numeric"}'],
      ['{"items": 2}'],
      ['{"uniqueItems": "true"}']
    ];
  }
}
