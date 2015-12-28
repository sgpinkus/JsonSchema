<?php

use \JsonSchema\Constraint\EmptyConstraint;
use \JsonSchema\Constraint\Exception\ConstraintParseException;

/**
 * Basic tests.
 */
class EnumConstraintTest extends ConstraintTest
{
  public function constraintDataProvider() {
    return [
      ['{"enum": [1,2,3,4]}', 1, true],
      ['{"enum": [1,2,3,4]}', 5, false],
      ['{"enum": [[],{},true]}', "[]", true],
      ['{"enum": [[],{},true]}', 5, false],
      //['{"enum": [[],{},true]}', "{}", true]
    ];
  }

  /**
   *
   */
  public function invalidConstraintDataProvider() {
    return [['{"enum": 7}']];
  }
}
