<?php
namespace JsonSchema\Constraint;

/**
 * Abstract base class for all JSON Schema constraints.
 * Interpreting the serialization and building the constraint are closely coupled.
 * Thus the builder for a constraints is coupled into constraint class itself in the static build() method.
 * This is the ~ "Interpreter Pattern" according to the GoF.
 */
abstract class Constraint
{
  /**
   * @returns true|false depending on whether the doc validates|doesnt on this symbol.
   */
  public abstract function validate($doc);

  /**
   * Parse the docs into a symbols. The $doc input may be mutated/marked up.
   * Some constraints are dependent on other constraints occuring in same level.
   * Example minimum, minimumExclusive. Thus need to provide constext to build.
   * @input $doc Mixed JSON schema document data structure.
   * @input $context Mixed the context $doc was found in.
   * @returns Constraint.
   */
  public static abstract function build($doc, $context = null);

  /**
   * Can we parse the given doc into a symbol.
   * Not using, don't need this.
   * @input $doc A JSON schema document data structure.
   */
  //public static abstract function canBuild($doc);
}
