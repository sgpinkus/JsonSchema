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

  /**
   * @returns true|false depending on whether the doc validates|doesnt on this symbol.
   */
  public abstract function validate($doc);

  /**
   * Parse the docs into a symbols. The $doc input may be mutated/marked up.
   * Some constraints are dependent on other constraints occuring in same level.
   * Example minimum, minimumExclusive. Thus need to provide context to build.
   * @input $doc Mixed JSON schema document data structure.
   * @input $context Mixed the context $doc was found in.
   * @returns Constraint.
   */
  public static abstract function build($doc, $context = null);

  /**
   * Get the name / keyword of the constraint. All constraints have a unique one.
   */
  public static abstract function getName();

  /**
   * A constraint exists in the context of a JSON Schema document.
   * This context may be needed to access associated metadataabout the constraint.
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
}
