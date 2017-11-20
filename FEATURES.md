# Sniffs

This codebase's PHP\_CodeSniffer (henceforth "phpcs") "sniffs", i.e. individual code style rules, as described here reflect personal preferences. Use whatever rules you find useful.

Some of these rules extend PHP\_CodeSniffer's built-in sniffs.

## Capitalization

### BooleanNULLSniff

Booleans `true` and `false` must be lowercase, `NULL` must be uppercase.

This is similar to C/C++.


## Classes

### MemberOrderingSniff

Class members must be in this order:

1. constants (e.g. `const A=1;`)
2. static properties (e.g. `public static $staticProperty;`)
3. static methods (e.g. `public static function staticMethod() {...}`)
4. instance properties (e.g. `public $property;`)
5. instance methods (e.g. `public function method() {...}`)


## CodeAnalysis

### IgnorableUnusedFunctionParameterSniff

This extends Generic.CodeAnalysis.UnusedFunctionParameterSniff: it can ignore function parameters marked as unused.

Start the docblock's parameter description with `(unused)` to mark unused parameters:

    /**
     * @param int $used   a used parameter
     * @param int $unused an unused parameter, will trigger an error
     * @param int $marked (unused) an unused parameter marked as such, will NOT trigger an error
     * @return void
     */
    public function method(int $used, int $unused, int $marked): void
    {
      echo $used;
    }


## Commenting

### ConfigurableClassCommentSniff, ConfigurableFileCommentSniff

These extend PEAR.Commenting.ClassComment and PEAR.Commenting.FileComment: they accept a configurable list of required docblock tags.

You can either disable all required tags, e.g. for classes' docblocks:

    <rule ref="RN.Commenting.ConfigurableClassComment">
      <properties>
        <property name="requiredTags" value=""/>
      </properties>
    </rule>

or pass an arbitrary list of required tags (you can optionally prefix the tags with '@'):

    <rule ref="RN.Commenting.ConfigurableFileComment">
      <properties>
        <property name="requiredTags" value="author,@license"/>
      </properties>
    </rule>

If you leave out the "requiredTags" property PEAR.Commenting.ClassComment's and PEAR.Commenting.FileComment's defaults will be used.

### ConfigurableFunctionCommentSniff

This extends PEAR.Commenting.FunctionComment: it has a configurable minimum method visibility to require docblocks.

For example to require docblocks only for public methods use this:

    <rule ref="RN.Commenting.ConfigurableFunctionComment">
      <properties>
        <property name="minimumVisibility" value="public"/>
      </properties>
    </rule>

If you don't set the minimumVisibility property the default "private" will be used. Functions outside classes are always public.


## Files

### DeclareStrictSniff

All PHP files must use `declare(strict_types=1)`;


## Spacing

A lot of the following spacing sniffs consider PHP docblocks as part of the statement, e.g. having code like this:

    function firstMethod()
    {
    }

    /**
     * docblock
     */
    function secondMethod()
    {
    }

counts as having 1 empty line between the methods.


### UseSniff

`use` declarations must:

* immediately follow a previous `<?php` opening tag or another `use` declaration, or
* be preceded by 1 empty line after `declare` or `namespace` declarations or a file's docblock, or
* be preceded by 0-1 empty lines after one-line comments (`//`), or
* (for use in lamba functions) be on the same line as the closing parenthesis

### NamespaceSniff

`namespace` declarations must:

* immediately follow a previous `<?php` opening tag or a `declare` statement, or
* be preceded by 1 empty line after a file's docblock, or
* be preceded by 0-1 empty lines after one-line comments (`//`)

### ClassSniff

Class definitions must:

* immediately follow a docblock, or
* be preceded by 1-2 empty lines after another class, function or statement, or
* be preceded by 0-2 empty lines after one-line comments or the PHP opening tag `<?php`

### OpeningBracketSniff

Opening curly brackets (`{`) must not be followed by empty lines.

### ClosingBracketSniff

Closing curly brackets (`}`) must not be preceded by empty lines.

### ConstSniff

Class constants must:

* immediately follow an opening curly bracket (`{`), or
* be preceded by 0-1 empty lines after another constant

### PropertySniff

Class properties must:

* immediately follow an opening curly bracket (`{`), or
* be preceded by 0-1 empty lines after another class property if either both are static or instance properties, or
* be preceded by 1-2 empty lines after another class property if one is a static and the other is an instance property, or
* be preceded by 1-2 empty lines after other statements

### FunctionSniff

Class methods must:

* immediately follow an opening curly bracket (`{`), or
* be preceded by 0-2 empty lines after another method if both are abstract and either static or not, or
* be preceded by 1-2 empty lines after another method if both are abstract and one is static while the other is not, or
* be preceded by 1-2 empty lines after non-abstract methods

### FunctionParametersSniff

Functions' parameters must:

* immediately follow an opening parenthesis (`(`), or
* be preceded by 1 blank after a comma, or
* immediately follow an equals sign (`=`), or
* span multiple lines

Additionally, if type hints are encountered, there must be exactly 1 blank between the type hint and the parameter.

Note that this sniff only applies to functions as they're being defined, e.g.:

    function asdf($x, $y) {...}

but not to function calls, e.g.:

    asdf(1,2);
