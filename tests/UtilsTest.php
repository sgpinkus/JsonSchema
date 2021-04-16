<?php

use \JsonSchema\Constraint\EmptyConstraint;
use \JsonSchema\Constraint\Exception\ConstraintParseException;
use \JsonSchema\Constraint\Constraint;
use PHPUnit\Framework\TestCase;


class UtilsTest extends TestCase
{
  public function testJsonTypeEquality() {
    $this->assertTrue(Constraint::jsonTypeEquality(1, 1.0));
    $this->assertFalse(Constraint::jsonTypeEquality(0, 1.0));
    $this->assertFalse(Constraint::jsonTypeEquality("1", "1.0"));
    $this->assertTrue(Constraint::jsonTypeEquality("1.0", "1.0"));
    $this->assertTrue(Constraint::jsonTypeEquality(json_decode('{"a": 1, "b": 2}'), json_decode('{"b": 2, "a": 1}')));
    $this->assertFalse(Constraint::jsonTypeEquality(json_decode('{"a": 2, "b": 2}'), json_decode('{"b": 2, "a": 1}')));
  }
}
