<?php
namespace JsonSchema\Constraint;

use JsonSchema\Constraint\Constraint;
use JsonSchema\Constraint\Exception\ConstraintParseException;

/**
 * The pattern constraint.
 */
class PatternConstraint extends Constraint
{
  private $pattern;

  public function __construct($pattern) {
    $this->pattern = $pattern;
  }

  /**
   * @override
   */
  public function validate($doc) {

  }

  /**
   * @todo PCRE valid not necessarily ECMA.
   * @override
   */
  public static function build($doc, $context = null) {
    if(!is_string($doc)) {
      throw new ConstraintParseException('The value MUST be a string.');
    }
    error_clear_last();
    @preg_match($doc, "0");
    if(error_get_last()) {
      throw new ConstraintParseException('This string SHOULD be a valid regular expression, according to the ECMA 262 regular expression dialect.');
    }
    return new static($doc);
  }
}
