<?php
namespace JsonSchema\Constraint;

use JsonSchema\Constraint\ValidationError;

/**
 * Represents a validation error, so we can store some context. This is returned by Constraint::validate().
 * Some validation errors have a collection of many child validation errors.
 * You get the child errors if any with getIterator().
 * @todo need to be able to print better context for errors.
 */
class ValidationError implements \IteratorAggregate
{
  private $validationErrors = [];
  private $constraint;
  private $message;
  private $constraintName;
  private $context;
  const NO_PRINT_CONSTRAINTS = ['{}'];

  /**
   * Init.
   * @input $constraint The Constraint that failed.
   * @input $message string describing how Constraint failed.
   * @input $context string something to indicate what part of the doc caused the failure.
   */
  public function __construct(Constraint $constraint, $message, $context) {
    $this->constraint = $constraint;
    $this->message = $message;
    $this->constraintName = $constraint->getName();
    $this->context = $context;
  }

  public function getConstraint() {
    return $this->constraint;
  }

  public function getMessage() {
    return $this->message;
  }

  public function getName() {
    return $this->constraintName;
  }

  public function getContext() {
    return $this->context;
  }

  public function getIterator() {
    return new \ArrayIterator($this->validationErrors);
  }

  public function __toString() {
    return $this->toStringRec();
  }

  private function toStringRec($depth = 0) {
    $str = "";
    if(!in_array($this->getName(), ValidationError::NO_PRINT_CONSTRAINTS)) {
      $str = str_repeat("  ", $depth) .
        "doc_path:" . $this->getContext() . "; " .
        "constraint:" . $this->getName() . "; " .
        "message:" .$this->getMessage() ."\n";
      $depth++;
    }
    foreach($this->getIterator() as $error) {
      $str .= $error->toStringRec($depth);
    }
    return $str;
  }

  public function addChild(ValidationError $child) {
    $this->validationErrors[] = $child;
  }
}
