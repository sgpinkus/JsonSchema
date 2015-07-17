<?php
namespace JsonSchema\Symbol;

/**
 * Abstract base class for all JSON Schema symbols.
 * Interpreting the serialization and building the symbol are closely coupled.
 * Thus the builder for a symbolls is coupled to the symbols class itself - build().
 * This is the ~ "Interpreter Pattern" apparently.
 */
abstract class Symbol
{
  /**
   * Can we parse the given doc into a symbol.
   */
  public static abstract function canBuild($doc);

  /**
   * Parse the docs into a symbols.
   * @returns Symbol.
   */
  public static abstract function build($doc);

  /**
   * @returns true|false depending on whether the doc validates|doesnt on this symbol.
   */
  public abstract function validate($doc);
}
