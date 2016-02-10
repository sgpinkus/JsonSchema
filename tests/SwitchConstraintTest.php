<?php

use \JsonSchema\Constraint\EmptyConstraint;
use \JsonSchema\Constraint\Exception\ConstraintParseException;

/**
 * Basic tests.
 */
class SwitchConstraintTest extends ConstraintTest
{
  public function constraintDataProvider() {
    return [
      ['{"switch": [{"if": {}, "then": true}]}', "1", true],
      ['{"switch": [{"if": {}, "then": false}]}', "1", false],
      ['{"switch": [{"if": {}, "then": true, "continue": true},{"then": true}]}', "1", true],
      ['{"switch": [{"if": {}, "then": true, "continue": true},{"then": false}]}', "1", false],
      ['{"switch": [{"if": {"type": "integer"}, "then": {"type": "integer"}, "continue": true}]}', "1", true],
      ['{"switch": [{"if": {"type": "integer"}, "then": {"type": "object"}, "continue": true}]}', "1", false],
      ['{"switch": [{"if": {"type": "object"}, "then": {"type": "integer"}, "continue": true}]}', "1", true],
    ];

  }

  /**
   * Fake invalid.
   */
  public function invalidConstraintDataProvider() {
    return [
      ['{"switch": {}}'],
      ['{"switch": {"if": {}}}'],
    ];
  }
}
