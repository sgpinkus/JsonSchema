<?php
namespace JsonSchema\Constraint;

/**
 * Abstract base class for all JSON Schema constraints.
 * Interpreting the JSON serialization and building the constraint are closely coupled.
 * Thus the builder for a constraints is coupled into constraint class itself in the static build() method.
 * This is the ~ "Interpreter Pattern" according to the GoF.
 */
abstract class Constraint
{
  private $context = null;
  private $continueMode = false;

  /**
   * @input $doc Mixed the vale to valid.
   * @input $context String the name or label of the value if appropriate.
   * @returns Mixed true|ValidationError depending on whether the doc validates|doesnt on this symbol.
   */
  public abstract function validate($doc, $context);

  /**
   * Parse the docs into a symbols.
   * Note the $doc input may be mutated/marked up in process of building. All mutations are additive and shall be stored in properties named "^\$.*"
   * $context is needed since some constraints are dependent on other constraints occuring in same level. Example minimum, minimumExclusive.
   * @input $context Mixed the context the targe was found in.
   * @returns Constraint.
   */
  public static abstract function build($context);

  /**
   * Get the name / keyword of the constraint. All constraints have a unique one.
   */
  public static abstract function getName();

  /**
   * Get the JSON Schema keywords this symbol parses.
   * Convenience default impl. May not be applicable.
   * @returns Array of JSON Schema keywords this constraint parses.
   */
  public static function getKeys() {
    $name = [static::getName()];
    return $name;
  }

  /**
   * Can we build a constraint?
   * Convenience default impl. May not be applicable.
   * @input StdClass the document at the given level.
   * @input Array the keys of the doc at that level. An optimization.
   */
  public static function wants($doc, array $docKeys) {
    $wants = false;
    foreach(static::getKeys() as $symbol) {
      if(in_array($symbol, $docKeys)) {
        $wants = true;
        break;
      }
    }
    return $wants;
  }

  /**
   * A constraint exists in the context of a JSON Schema document.
   * This context may be needed to access associated metadata about the constraint.
   * @input $context StdClass. The context is always an object because the constraint is always a property of one.
   */
  public function setContext(\StdClass $context) {
    $this->context = $context;
  }

  /**
   * A constraint exists in the context of a JSON document.
   * The context is always an object. This context may be needed.
   */
  public function getContext() {
    return $this->context;
  }

  /**
   * @see continueMode.
   */
  public function setContinueMode($c) {
     $this->continueMode = $c;
  }

  /**
   * Whether validation should continue and find all errors even if it can short cut.
   */
  public function continueMode() {
    return $this->continueMode;
  }
}
