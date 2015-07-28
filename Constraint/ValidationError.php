<?php
namespace JsonSchema\Constraint;

use JsonSchema\Constraint\ValidationError;

/**
 * Represents a validation error, so we can store some context. This is returned by Constraint::validate().
 * Some validation errors have a collection of many child validation errors.
 * I'm betting you can do waht RecursiveTreeIterator with RecursiveIterators somehow with just IteratorAggregate.
 */
abstract class ValidationError implements IteratorAggregate;
{
  private $validationErrors = [];

  public function getConstraint() {
    return $this->constraint;
  }

  public function getMessage() {
    return $this->message();
  }

  public function getIterator() {
    return new \ArrayIterator($this->validationErrors);
  }

  public function addChild(\ValidationError $child) {
    $this->validationErrors[] = $child;
  }
}
