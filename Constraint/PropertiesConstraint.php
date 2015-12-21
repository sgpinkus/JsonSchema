<?php
namespace JsonSchema\Constraint;

use JsonSchema\Constraint\Constraint;
use JsonSchema\Constraint\Exception\ConstraintParseException;

/**
 * All three properties related constraints.
 * These three constraints are interelated so cannot be addressed independently - bit of a PITA really.
 * This implementation treats true, {} as equivalent valus of additionalProperties.
 * This may or may not deviat from the spec, but is more logical and intuitive than any alternate interpretation.
 * @see http://json-schema.org/latest/json-schema-validation.html#anchor64
 */
class PropertiesConstraint extends Constraint
{
  private $properties;
  private $patternProperties;
  private $additionalProperties;

  /**
   * @input $properties assoc array of EmptyConstraint.
   * @input $additionalProperties Mixed either an EmptyConstraint or bool.
   */
  public function __construct(array $properties = [], array $patternProperties = [], $additionalProperties = true) {
    $this->properties = $properties;
    $this->patternProperties = $patternProperties;
    $this->additionalProperties = $additionalProperties;
  }

  /**
   * @override
   */
  public static function getName() {
    return "['properties', 'patternProperties', 'additionalProperties']";
  }

  /**
   * @override
   */
  public static function getKeys() {
    return ['properties', 'patternProperties', 'additionalProperties'];
  }

  /**
   * Ensure properties of object match given constraints.
   * Properties only apply if the property is defined on the target.
   * @bug Minor. In continueMode first error becomes the parent error, while following errors push onto it.
   * @override
   */
  public function validate($doc, $context) {
    $valid = true;
    if(is_object($doc)) {
      $arrayDoc = (array)$doc;
      foreach($this->properties as $i => $constraint) {
        if(isset($arrayDoc[$i])) {
          $validation = $constraint->validate($arrayDoc[$i], $i);
          unset($arrayDoc[$i]);
          if($validation instanceof ValidationError) {
            if($valid === true) {
              $valid = new ValidationError($this, "One or more properties failed to validate.", $context);
            }
            $valid->addChild($validation);
            if(!$this->continueMode()) {
              break;
            }
          }
        }
      }
      if($valid === true || $this->continueMode()) {
        foreach($this->patternProperties as $pattern => $constraint) {
          foreach($arrayDoc as $docKey => $docItem) {
            if(preg_match($pattern, $docKey)) {
              $validation = $constraint->validate($docItem, $pattern);
              unset($arrayDoc[$docKey]);
              if($validation instanceof ValidationError) {
                if($valid === true) {
                  $valid = new ValidationError($this, "One or more pattern properties failed to validate.", $context);
                }
                $valid->addChild($validation);
                if(!$this->continueMode()) {
                  break;
                }
              }
            }
          }
        }
      }
      if($valid === true || $this->continueMode()) {
        if(is_object($this->additionalProperties)) {
          foreach($arrayDoc as $i => $value) {
            $validation = $this->additionalProperties->validate($arrayDoc[$i], $i);
            if($validation instanceof ValidationError) {
              if($valid === true) {
                $valid = new ValidationError($this, "One or more pattern properties failed to validate.", $context);
              }
              $valid->addChild($validation);
              if(!$this->continueMode()) {
                break;
              }
            }
          }
        }
        elseif($this->additionalProperties === false) {
          if(sizeof($arrayDoc) > 0) {
            if($valid === true) {
              $valid = new ValidationError($this, "Additional properties found.", $context);
            }
            else {
              $vali->addChild(new ValidationError($this, "Additional properties found.", $context));
            }
          }
        }
      }
    }
    return $valid;
  }

  /**
   * We only care about the context not doc here.
   * properties, additionalProperties, or patternProperties must be set in context.
   * @override
   */
  public static function build($context) {
    $constraints = [];
    $properties = [];
    $patternProperties = [];
    $additionalProperties = true;

    if(!(isset($context->properties) || isset($context->patternProperties) || isset($context->additionalProperties))) {
      throw new ConstraintParseException('One of properties, additionalProperties, or patternProperties must be set for PropertiesConstraint.');
    }
    if(isset($context->properties)) {
      $properties = self::buildPropertyConstraints($context->properties);
    }
    if(isset($context->patternProperties)) {
      $patternProperties = self::buildPatternPropertyConstraints($context->patternProperties);
    }
    if(isset($context->additionalProperties)) {
      $additionalProperties = self::buildAdditionalPropertyConstraints($context->additionalProperties);
    }
    return new static($properties, $patternProperties, $additionalProperties);
  }

  /**
   *
   */
  public static function buildPropertyConstraints($properties) {
    $constraints = [];
    if(!is_object($properties)) {
      throw new ConstraintParseException('The value MUST be an object.');
    }
    foreach($properties as $key => $value) {
      $constraints[$key] = EmptyConstraint::build($value);
    }
    return $constraints;
  }

  /**
   *
   */
  public static function buildPatternPropertyConstraints($properties) {
    $constraints = [];
    if(!is_object($properties)) {
      throw new ConstraintParseException('The value MUST be an object.');
    }
    foreach($properties as $key => $value) {
      if(preg_match($key, "0") === false) {
        throw new ConstraintParseException("The keys of 'patternProperties' must be valid regexps.");
      }
      $constraints[$key] = EmptyConstraint::build($value);
    }
    return $constraints;
  }

  /**
   *
   */
  public static function buildAdditionalPropertyConstraints($additionalProperties) {
    $constraint = true;
    if(is_bool($additionalProperties)) {
      $constraint = $additionalProperties;
    }
    elseif(is_object($additionalProperties)) {
      $constraint = EmptyConstraint::build($additionalProperties);
    }
    else {
      throw new ConstraintParseException('The value MUST be either a boolean or object.');
    }
    return $constraint;
  }
}
