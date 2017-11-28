# General

This codebase's PHP\_CodeSniffer ("phpcs") "sniffs", i.e. individual code style rules, as described here reflect personal
preferences. Use whatever rules you find useful.

## Error Types

Custom rules output warnings and errors according to these general guidelines:

* definite styling violations generate errors
* uncertain styling violations that might actually be false positives generate warnings instead
* code analysis sniffs generally issue warnings - in severe cases errors
* if there's an internal error, e.g. if an unexpected case is encountered, a warning is issued


# Sniffs

Some of these rules extend PHP\_CodeSniffer's built-in sniffs.

## Capitalization

### BooleanNULLSniff

Booleans `true` and `false` must be lowercase, `NULL` must be uppercase.

Examples:

    $a=[true,false];  //this is OK

    $b=[True,False]; //nope, neither will work
    $c=[TRUE,FALSE]; //no good


    $d=NULL; //this is OK

    $e=Null; //no good
    $f=null; //nope

This is similar to C/C++.

This can be automatically fixed by phpcbf.


## Classes

### IndividualPropertiesSniff

Class properties should be declared individually:

    class A
    {
      public $a;      //this is OK
      public $b;      //this is OK
      public $c, $d,  //this is wrong: $d should be declared separately
             $e;      //this is wrong: newlines don't count either
    }

This currently isn't automatically fixable by phpcbf.

### MemberOrderingSniff

Class members must be in this order:

1. constants (e.g. `const A=1;`)
2. static properties (e.g. `public static $staticProperty;`)
3. static methods (e.g. `public static function staticMethod() {...}`)
4. instance properties (e.g. `public $property;`)
5. instance methods (e.g. `public function method() {...}`)

For example:

    class X
    {
      const A=1;

      public static $staticProperty;

      public static function staticMethod()
      {
      }

      public $property;

      public function method()
      {
      }
    }

This rule currently isn't automatically fixable by phpcbf, you'll need to fix the code manually.


## CodeAnalysis

The code analysis errors won't be fixed automatically by phpcbf as it's way too easy to introduce bugs.

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

### SPLExceptionNamespaceSniff

This rule checks for references to PHP's built-in SPL exceptions when used in namespaced code, e.g.:

    <?php
    namespace A\B;

    use \LogicException;

    try
    {
      throw new DomainException();   //invalid, DomainException is built-in but accessed as A\B\DomainException
      throw new LogicException();    //this is OK, as LogicException is imported above
      throw new \RuntimeException(); //this is OK, as it explicitly references the root namespace
      throw new CustomException();   //this is OK, it's not built-in
      throw new Some\Exception();    //this is ignored, namespace references are considered to be made on purpose
      throw new $x;                  //this is ignored, dynamic instantiations aren't checked
    }
    catch(DomainException $x)        //invalid, DomainException is probably accessed in the wrong namespace
    {
    }
    catch(LogicException | \RuntimeException | Some\Exception $e)  //this is OK: all 3 are valid references
    {
    }

The main reason to use this rule is because it's easy to forget the namespace reference when writing exception handlers.
This will catch those references before they result in runtime "class not found" errors.

### UnusedNamespaceImportSniff

This rule checks `use` statements for imported symbols from other namespaces that aren't being used in the file.

For example:

    use A\B;          //B gets a warning, it isn't being used anywhere
    use A\{C,D,E,F};  //C, E and F get a warning each; D is being used below
    use G\H as I;     //I gets a warning

    class Y
    {
      use D;
    }


## Commenting

### ConfigurableClassCommentSniff, ConfigurableFileCommentSniff

These extend PEAR.Commenting.ClassComment and PEAR.Commenting.FileComment: they accept a configurable list of required docblock tags
and can remove the @author tag's email requirement.

Most of the issues found by these rules aren't automatically fixable by phpcbf.

#### Required Tags Configuration

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

#### Author Email Configuration

By default the extended sniff requires the `Display Name <user@example.com>` @author tag format.

This can be changed to just require a name, for both sniffs separately:

    <property name="requireAuthorEmail" value="no"/>

If this is set to "no" but there seems to be an email address anyway, the setting is ignored and the email address will be validated as usual.

#### Expected Content Configuration

Both file and class comment sniffs can be configured to require specific content in some tags. If configured, any found tags'
contents will be compared against the configured expectations. Setting any of these doesn't make the tag required, this is handled
separately by the `requiredTags` property.

The following tags can be checked:

    <property name="categoryContent" value="my category"/>
    <property name="packageContent" value="package 1"/>
    <property name="subpackageContent" value="subpackage X"/>
    <property name="authorContent" value="Me"/>
    <property name="copyrightContent" value="2017 Some Person"/>
    <property name="licenseContent" value="proprietary"/>
    <property name="versionContent" value="1.0"/>

After confirming the tag content matches processing continues as usual, so make sure your expected content is valid for the tag.

The content you enter needs to be valid XML, you'll e.g. need to use &lt; (instead of <) and &gt; (instead of >).

Invalid contents for found tags can automatically be fixed by phpcbf.

### ConfigurableFunctionCommentSniff

This extends PEAR.Commenting.FunctionComment: it has a configurable minimum method visibility to require docblocks.

For example to require docblocks only for public methods use this:

    <rule ref="RN.Commenting.ConfigurableFunctionComment">
      <properties>
        <property name="minimumVisibility" value="public"/>
      </properties>
    </rule>

If you don't set the minimumVisibility property the default "private" will be used. Functions outside classes are always public.

Methods below minimum visibility do not require a docblock - if one is found anyway it will be validated.

This can't be fixed automatically with phpcbf.

#### Return Tag Exception for Test Code

This rule makes an exception for test methods: they don't require a `@return` tag. PHPUnit test methods never return anything
anyway, the @return tag would always list "void" or "NULL" and clutter the test sources.

Files within a "tests" directory with filenames ending in "Test.php" are considered tests. Within those test files the methods
`setUpBeforeClass()`, `tearDownAfterClass()`, `setUp()` and `tearDown()` are exempt from the @return tag requirement, as are any
other methods whose name starts with `test`.


## Files

### DeclareStrictSniff

All PHP files must use `declare(strict_types=1)`; like this:

    <?php
    declare(strict_types=1);

This won't be fixed automatically by phpcbf as it might introduce runtime errors as a result of declaring strict types.


## Naming

### PropertySniff

Class (and trait) properties must:

* start with an underscore if they're private or protected
* NOT start with an underscore if they're public
* start with a lowercase letter after the visibility-dependent leading underscore
* NOT contain any other underscores after the start

This won't be fixed automatically by phpcbf as it's very difficult to do reliably and may very well require changes across multiple
analyzed files.


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

The spacing errors currently aren't automatically fixable by phpcbf as this isn't straightforward to implement reliably.


### UseSniff

`use` declarations must:

* immediately follow a previous `<?php` opening tag or another `use` declaration, or
* be preceded by 1 empty line after `declare` or `namespace` declarations or a file's docblock, or
* be preceded by 0-1 empty lines after one-line comments (`//`), or
* (for use in lamba functions) be on the same line as the closing parenthesis

Example 1:

    <?php
    use A\B;    //no empty line between this and opening PHP tag above

Example 2:

    <?php
    namespace X;

    use A\B;    //this must be after an empty line since there's a namespace declaration above
    //use A\C;  //it's OK to comment out use clauses
    use A\D;

Example 3:

    $x=function() use ($x) {}   //no empty lines required if used in a lambda function

Example 4:

    class X
    {
      use Trait1;  //there must not be any empty lines above
      use Trait2;  //you can import multiple traits without empty lines in between

      //trait description
      use Trait3;  //you need a comment between multiple imports to allow an empty line in between
    }


### NamespaceSniff

`namespace` declarations must:

* immediately follow a previous `<?php` opening tag or a `declare` statement, or
* be preceded by 1 empty line after a file's docblock, or
* be preceded by 0-1 empty lines after one-line comments (`//`)

Example 1:

    <?php
    namespace X;   //no empty line between this and opening PHP tag above

Example 2:

    <?php
    /**
     * this is the file's docblock
     */

    namespace X;   //1 empty line between above docblock and namespace declaration

### ClassSniff

Class definitions must:

* immediately follow a docblock, or
* be preceded by 1-2 empty lines after file docblock, another class, function or statement, or
* be preceded by 0-2 empty lines after one-line comments or the PHP opening tag `<?php`

Example 1:

    <?php

    class A  //there can be 0-2 empty lines above
    {
    }

    /**
     * this docblock is part of class B
     * the above empty line counts as 1 empty line between classes A and B
     */
    class B
    {
    }

Example 2:

    <?php
    /**
     * this is a file docblock
     */

    /**
     * this is a class docblock, the above newline is OK since there's class AND file docblocks
     */
    class C
    {
    }

When there's empty lines between a docblock and a class, the docblock may be misplaced so an error is generated:

    <?php
    /**
     * this is invalid, might be a misplaced class docblock
     */

    class Invalid  //an error will be reported on this line!
    {
    }

### OpeningBracketSniff

Opening curly brackets (`{`) must not be followed by empty lines.

Example:

    function asdf()
    {
      $x=1;  //no empty line above!
    }

### ClosingBracketSniff

Closing curly brackets (`}`) must not be preceded by empty lines.

Example:

    class X
    {
      function asdf()
      {
      }  //no empty line below!
    }

### ConstSniff

Class constants must:

* immediately follow an opening curly bracket (`{`), or
* be preceded by 0-1 empty lines after another constant

Example:

    class X
    {
      const C1=1;
      const C2=2;

      const D1=3;
      const D2=4;
    }

### PropertySniff

Class properties must:

* immediately follow an opening curly bracket (`{`), or
* be preceded by 0-1 empty lines after another class property if either both are static or instance properties, or
* be preceded by 1-2 empty lines after another class property if one is a static and the other is an instance property, or
* be preceded by 1-2 empty lines after other statements

Example:

    class X
    {
      public static $a;
      public static $b;  //$a and $b are static, so there can be 0-1 empty lines above

      public static $c;  //this is OK too

      public $x;   //$c is static, $x isn't, so there must be 1-2 empty lines above
      public $y;   //$x and $y are instance properties, no empty line needed
    }

### FunctionSniff

Class methods must:

* immediately follow an opening curly bracket (`{`), or
* be preceded by 0-2 empty lines after another method if both are abstract and either static or not, or
* be preceded by 1-2 empty lines after another method if both are abstract and one is static while the other is not, or
* be preceded by 1-2 empty lines after non-abstract methods

Example:

    abstract class Y
    {
      /**
       * docblocks are considered part of the function, so this counts as 0 empty lines between
       * function asdf and the class's opening bracket
       */
      public static function asdf()
      {
      }

      abstract public static function f1(); //in general there need to be 1-2 empty lines above functions
      abstract public static function f2(); //unless they're both abstract and share the same static-ness

      abstract public static function f3(); //but they don't need to be grouped

      public static function f4();
      public static function f5(); //abstract instance methods can be grouped too


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
