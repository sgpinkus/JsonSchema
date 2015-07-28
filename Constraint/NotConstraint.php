<?php
namespace JsonSchema\Constraint;

/**
 * Not some EmptyConstraint.
 */
class NotConstraint extends Constraint
{
  private $innerConstraint;

  public function __construct(EmptyConstraint $innerConstraint) {
    $this->innerConstraint = $innerConstraint;
  }
  
  /**
   * @override
   */
  public static function getName() {
  	return 'not';
  }

  /**
   * @override
   */
  public function validate($doc) {
    return ! $this->innerConstraint->validate($doc);
  }

  /**
   * @override
   */
  public static function build($doc, $context = null) {
    return new static(EmptyConstraint::build($doc));
  }
}
