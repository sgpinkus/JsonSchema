<?php
namespace JsonSchema\Constraint;

use JsonSchema\Constraint\ValidationError;

/**
 * Represents a validation error, so we can store some context. This is returned by Constraint::validate().
 * Some validation errors have a collection of many child validation errors.
 * You get the child errors if any with getIterator().
 */
class ValidationError implements \IteratorAggregate
{
  private $validationErrors = [];
  private $constraint;
  private $message;
  private $name;

  public function __construct(Constraint $constraint, $message) {
    $this->constraint = $constraint;
    $this->message = $message;
    $this->name = $constraint->getName();
  }

  public function getConstraint() {
    return $this->constraint;
  }

  public function getMessage() {
    return $this->message;
  }

  public function getName() {
    return $this->name;
  }

  public function getIterator() {
    return new \ArrayIterator($this->validationErrors);
  }

  public function addChild(ValidationError $child) {
    $this->validationErrors[] = $child;
  }
}
