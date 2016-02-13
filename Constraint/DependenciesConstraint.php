<?php
namespace JsonSchema\Constraint;

use JsonSchema\Constraint\Constraint;
use JsonSchema\Constraint\Exception\ConstraintParseException;

/**
 * dependencies keyword.
 * Applies to objects. Is a set of <key then constraint> pairs.
 * <key then constant> pairs have two forms:
 *  - Schema Dependencies. Constraint is a schema. "it's the instance itself which must validate successfully, not the value associated with the property name."
 *  - Property Dependencies. Constraint is a string name of a key the object must also have.
 */
class DependenciesConstraint extends Constraint
{
  private $dependencies;

  public function __construct(array $dependencies) {
    $this->dependencies = $dependencies;
  }

  /**
   * @override
   */
  public static function getName() {
  	return 'dependencies';
  }

  /**
   * @override
   */
  public function validate($doc, $context) {
    $valid = true;
    if(is_object($doc)) {
      $arrayDoc = (array)$doc;
      foreach($arrayDoc as $key => $value) {
        if(isset($this->dependencies[$key])) {
          $dependency = $this->dependencies[$key];
          if(is_object($dependency)) {
            $validation = $dependency->validate($doc, $context); // the doc itself not the key..
            if($validation instanceof ValidationError) {
              if($valid === true) {
                $valid = new ValidationError($this, "One or more dependencies unmet.", $context);
              }
              $valid->addChild($validation);
              if(!$this->continueMode()) {
                break;
              }
            }
          }
          elseif(is_array($this->dependencies[$key])) {
            foreach($dependency as $requiredKey) {
              if(!isset($arrayDoc[$requiredKey])) {
                if($valid === true) {
                  $valid = new ValidationError($this, "One or more dependencies unmet.", $context);
                }
                $valid->addChild(new ValidationError($this, "When '$key' set '$requiredKey' required.", $context));
                if(!$this->continueMode()) {
                  break;
                }
              }
            }
          }
        }
      }
    }
    return $valid;
  }

  /**
   * @override
   */
  public static function build($context) {
    $doc = $context->dependencies;
    $dependencies = [];

    if(!is_object($doc)) {
      throw new ConstraintParseException('The value of "dependencies" MUST be an object.');
    }
    foreach($doc as  $key => $value) {
      if(is_object($value)) {
        $dependencies[$key] = EmptyConstraint::build($value);
      }
      elseif(is_array($value)) {
        $dependencies[$key] = $value;
      }
      else {
        throw new ConstraintParseException('The "dependencies" object must contain only object or array values.');
      }
    }
    return new static($dependencies);
  }
}
