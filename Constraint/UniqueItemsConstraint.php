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
  public function validate($doc) {
    $valid = true;
    if(is_array($doc) && $this->unique) {
      $h = [];
      foreach($doc as $v) {
        $sv = serialize($v);
        if(isset($h[$sv])) {
           $valid = false;
           break;
        }
        $h[$sv] = true;
      }
    }
    return $valid;
  }

  /**
   * @override
   */
  public static function build($doc, $context = null) {

    if(!is_bool($doc)) {
      throw new ConstraintParseException('The value MUST be a boolean.');
    }

    return new static($doc);
  }
}
