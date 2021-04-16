<?php
namespace JsonSchema\Constraint;

use JsonSchema\Constraint\Constraint;
use JsonSchema\Constraint\Exception\ConstraintParseException;

/**
 * The uniqueItems constraint.
 */
class UniqueItemsConstraint extends Constraint
{
  private $unique;

  /**
   * @input $unique Bool whether to apply uniqueness at all.
   */
  public function __construct($unique) {
    $this->unique = (bool)$unique;
  }

  /**
   * @override
   */
  public static function getName() {
    return 'uniqueItems';
  }

  /**
   * @override
   */
  public function validate($doc, $context) {
    if(is_array($doc) && $this->unique) {
      $seen = [];
      foreach($doc as $i => $a) {
        foreach($seen as $j => $b) {
          if(Constraint::jsonTypeEquality($a, $b)) {
            return new ValidationError($this, "Non unique item found: item $j matches $i.", $context);
          }
        }
        $seen[] = $a;
      }
    }
    return true;
  }

  /**
   * @override
   */
  public static function build($context) {
    $doc = $context->uniqueItems;

    if(!is_bool($doc)) {
      throw new ConstraintParseException('The value MUST be a boolean.');
    }

    return new static($doc);
  }
}
