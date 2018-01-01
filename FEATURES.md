# General

This codebase's PHP\_CodeSniffer ("phpcs") "sniffs", i.e. individual code style rules, as described here reflect personal
preferences. Use whatever rules you find useful.

## Error Types

Custom rules output warnings and errors according to these general guidelines:

* definite styling violations generate errors
* uncertain styling violations that might actually be false positives generate warnings instead
* code analysis sniffs generally issue warnings - in severe cases errors
* if there's an internal error, e.g. if an unexpected case is encountered, a warning is issued

## File Exceptions

There are cases where individual files in an application have widespread violations of particular code style rules, for
example when it's necessary to implement interfaces or extend classes with different naming rules. In such cases it's
possible to add rule exceptions for individual files in the files' docblocks, e.g. (the "Sniff" suffix is optional):

    <?php
    /**
     * @codingStandardsIgnoreRule RN.Capitalization.BooleanNULL
     */

This will ignore the Capitalization.BooleanNULL rule for that one file.

Technically _any_ docblock will do, but to avoid confusion about the exception's scope it's suggested that you stick to
the file docblock.


# Sniffs

Some of these rules extend PHP\_CodeSniffer's built-in sniffs.

## Capitalization

### BooleanNULLSniff

Checks boolean and NULL cases. The expected cases can be configured, by default they're lowercase for `true` and `false`,
uppercase for `NULL` (similar to C/C++).

Booleans and NULLs are configured separately:

    <rule ref="RN.Capitalization.BooleanNULL">
      <properties>
        <property name="booleanCase" value="ucfirst"/>
        <property name="nullCase" value="upper"/>
      </properties>
    </rule>

Valid settings are "lower" for lowercase (e.g. `null`), "ucfirst" to capitalize the first letter (e.g. `Null`) and "upper"
for uppercase (e.g. `NULL`).

Examples for default settings:

    $a=[true,false];  //this is OK

    $b=[True,False]; //nope, neither will work
    $c=[TRUE,FALSE]; //no good


    $d=NULL; //this is OK

    $e=Null; //no good
    $f=null; //nope

Unexpected boolean/NULL cases found by this sniff can be automatically fixed by phpcbf.


## Classes

### ClassDeclarationSniff

This extends PSR1.Classes.ClassDeclaration - all it does is adding the option to disable the sniff in individual files.

This can be used to add exceptions for files that need to reside in the root namespace because of external requirements.

### ExplicitConstVisibilitySniff

Class constants should have an explicit visibility set:

    class A
    {
      //these are OK: they have an explicit visibility set
      public const K=1;
      protected const L=2;
      private const M=3;

      //this will fail if the PHP version is >=7.1
      const X=4;
    }

This sniff won't issue errors if the effective PHP version (after phpcs configuration) isn't at least 7.1. Found errors
won't be fixed automatically as it's unclear what visibility should be set.

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

Class members are checked for a given order. The default order is this:

1. constants (e.g. `const A=1;`)
2. trait uses (e.g. `use SomeTrait;`)
3. static properties (e.g. `public static $staticProperty;`)
4. static methods (e.g. `public static function staticMethod() {...}`)
5. instance properties (e.g. `public $property;`)
6. constructor (e.g. `public function __construct() {...}`)
7. instance methods (e.g. `public function method() {...}`)

For example:

    class X
    {
      const A=1;

      use SomeTrait;

      public static $staticProperty;

      public static function staticMethod()
      {
      }

      public $property;

      public function __construct()
      {
      }

      public function method()
      {
      }
    }

The expected order can be configured by assigning a number to each member type: a class member may not be located after
another member with a higher number:

    <rule ref="RN.Classes.MemberOrdering">
      <properties>
        <property name="constOrder" value="1"/>
        <property name="traitUseOrder" value="2"/>
        <property name="staticPropertyOrder" value="3"/>
        <property name="staticMethodOrder" value="4"/>
        <property name="instancePropertyOrder" value="5"/>
        <property name="constructorOrder" value="6"/>
        <property name="instanceMethodOrder" value="7"/>
      </properties>
    </rule>

Only PHP5 constructors (`__construct()`) will be recognized, PHP4 constructors will be treated as regular methods. The RN
standard includes a separate rule that will throw errors on old style constructors asking to rename them to `__construct`.

If two (or more) class member types share the same order they can be mixed freely relative to each other, e.g. having
equal `instancePropertyOrder` and `instanceMethodOrder` would allow for this:

    abstract class SameOrder
    {
      private $_prop1;
      abstract public function getProp1();

      private $_prop2;
      abstract public function getProp2();
    }

This rule currently isn't automatically fixable by phpcbf, you'll need to fix the code manually.


## CodeAnalysis

The code analysis errors won't be fixed automatically by phpcbf as it's way too easy to introduce bugs.

### AbortedControlStructureSniff

This attempts to find prematurely aborted control structures by looking for semicolons where control structure bodies
should be.

For example:

    if($a) {}              //this is OK: empty bodies are not covered in this sniff
    elseif($b);            //this gets a warning: there is no body

    foreach($x as $y);     //warning

    while($c);             //warning

    for($i=0;$i<10;$i++);  //warning

    switch($x);            //warning

    do
    {
      while(...);          //this gets a warning: it's an empty while loop
    } while(...);          //this is OK, it's part of a do-while loop

### DoubleSemicolonSniff

This will find semicolons following other semicolons with nothing but optional whitespaces and comments in between.
The `for` loop separators are exempt from this.

For example:

    $x=1;;   //this triggers a warning

    $y=1;
    ;        //this triggers a warning

    for(;;) {}  //this is OK

Nested code inside `for` loops' parentheses, e.g. in closures, is currently not checked.

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

### MaximumPHPVersionSniff

This sniff ensures PHP files don't exceed a configured maximum PHP version. This version can be set with:

    <rule ref="RN.CodeAnalysis.MaximumPHPVersion">
      <properties>
        <property name="maximumVersion" value="7.1"/>
      </properties>
    </rule>

By default there is no maximum version set.

For example, using the above configuration the following code will trigger a warning:

    function asdf(object $x)  //the "object" type hint was introduced in PHP 7.2, but 7.1 was configured as maximum
    {
    }

The PHP version checks are in `src/Checkers/FileVersion/`, only the language versions implemented there will be
recognized. The implementations aren't exhaustive, e.g. currently they don't check for new built-in functions added in
a given PHP version.

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

### StaticOnlyAbstractClassSniff

This rule checks whether classes (not extending other classes) containing static members only are abstract. Class
constants are ignored, but instance properties, instance methods and trait imports drop the abstract requirement.

For example:

    abstract class AbstractClass  //this is OK, it's already static
    {
      public static $staticProperty;
      public static function staticMethod() {}
    }

    class TraitImport             //this is OK: the class imports a trait that might bring instance members
    {
      use SomeTrait;
      public static $staticProperty;
    }

    class Extending extends X     //this is OK: the parent might bring instance members
    {
      public static function staticMethod() {}
    }

This rule won't be fixed by phpcbf automatically as the class may be instantiated somewhere, breaking that code.

### UnusedNamespaceImportSniff

This rule checks `use` statements for imported symbols from other namespaces that aren't being used in the file.

For example:

    use A\B;          //B gets a warning, it isn't being used anywhere
    use A\{C,D,E,F};  //E and F get a warning each; C and D are used below
    use G\H as I;     //I gets a warning

    class Y extends C
    {
      use D;
    }

### UnusedVariableSniff

This rule finds defined but then unused variables. Currently only variables within functions or closures are checked.
Function parameters are ignored, they're handled elsewhere.

For example:

    function f(array $par1, $par2) //$par2 is unused, but a function parameter
    {
      global $a;        //$a is invalid, it's never used
      [$b,$c]=$par1;    //$b is invalid, it's never used
      list($d)=$par1;   //OK: $d is used further down
      SomeClass::$c=3;  //OK: $c isn't actually being written to
      $this->$d=3;      //OK: $d isn't actually being written to

      foreach($par1 as $x=>[$y,$z])     //$z is invalid, it's never used
      {
        yield function() use ($x,$y) {  //$y is invalid, it's not used within the closure
          return $x;
        };
        echo $y;
      }
    }


## Commenting

### ClassCommentSniff, FileCommentSniff

These extend PEAR.Commenting.ClassComment and PEAR.Commenting.FileComment: they accept a configurable list of required docblock tags
and can remove the @author tag's email requirement.

Most of the issues found by these rules aren't automatically fixable by phpcbf.

#### Required Tags Configuration

You can either disable all required tags, e.g. for classes' docblocks:

    <rule ref="RN.Commenting.ClassComment">
      <properties>
        <property name="requiredTags" value=""/>
      </properties>
    </rule>

or pass an arbitrary list of required tags (you can optionally prefix the tags with '@'):

    <rule ref="RN.Commenting.FileComment">
      <properties>
        <property name="requiredTags" value="author,@license"/>
      </properties>
    </rule>

If you leave out the "requiredTags" property PEAR.Commenting.ClassComment's and PEAR.Commenting.FileComment's defaults will be used.

Missing tags can only be inserted automatically by phpcbf if there's an expected content configured for the tag.

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

The content you enter needs to be valid XML, you'll e.g. need to use `&lt;` (instead of <) and `&gt;` (instead of >).

Invalid contents for found tags can automatically be fixed by phpcbf.

#### Expected PHP Version

File comment docblocks are required to show a PHP version somewhere before the tags. This version can be configured to
require a specific version, e.g. 7.1:

    <property name="requiredPHPVersion" value="7.1"/>

Violations can be fixed automatically by phpcbf.

By default there is no expected version.

### FunctionCommentSniff

This extends PEAR.Commenting.FunctionComment: it has a configurable minimum method visibility to require docblocks.

For example to require docblocks only for public methods use this:

    <rule ref="RN.Commenting.FunctionComment">
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

Files within a "tests" directory with filenames ending in "Test.php" or "TestCase.php" are considered tests. Within
those test files the methods `setUpBeforeClass()`, `tearDownAfterClass()`, `setUp()` and `tearDown()` are exempt from
the @return tag requirement, as are any other methods whose name starts with `test`.


## Files

### DeclareStrictSniff

All PHP files must use `declare(strict_types=1)`; like this:

    <?php
    declare(strict_types=1);

This won't be fixed automatically by phpcbf as it might introduce runtime errors as a result of declaring strict types.


## Naming

Naming errors won't be fixed automatically by phpcbf as it's very difficult to do reliably and may very well require changes across
multiple analyzed files.

### SnakeCaseFunctionSniff

Functions outside classes/traits must:

* NOT start with an underscore (except for PHP magic functions)
* consist of lowercase letters, numbers and underscores

For example:

    <?php
    function i_am_valid() {}
    function me2() {}

### CamelCaseMethodSniff, PropertySniff

Class (and trait) methods and properties must:

* start with an underscore if they're private or protected
* NOT start with an underscore if they're public (except for PHP magic methods)
* start with a lowercase letter after the visibility-dependent leading underscore
* NOT contain any other underscores after the start

Sometimes there are outside requirements to violate the class member naming rules (e.g. inheritance or interfaces), in these cases
it's possible to make an exception for the class member by adding `CSU.IgnoreName` in a comment on the same line.

For example:

    class A
    {
      public static $ok;
      protected static $_isValid;

      public $iAmValid;
      protected $_soAmI;
      private $_me2;
      private $Very_invalid;  //CSU.IgnoreName - property name will be ignored


      public function __get($key) {}  //magic methods are ignored

      public function valid() {}
      public function _Nope() {} //CSU.IgnoreName - invalid but ignored
      protected function _validToo() {}
      private function _same() {}
    }

### SnakeCaseFunctionParametersSniff

Function/method and closure parameters must:

* start with a lowercase letter
* be in snake\_case

For example:

    <?php
    function valid($ok, $ok_too) {}

    function invalid1($Not_ok) {}    //invalid: starts with an uppercase letter
    function invalid2($_neither) {}  //invalid: starts with an underscore
    function invalid3($norThis) {}   //invalid: isn't in snake case

    class A
    {
      public function valid($ok)
      {
        $x=function($ok_too) {};
      }

      public function invalid($camelCase)  //invalid: parameter isn't in snake case
      {
        $y=function($wrongAgain) {};       //invalid: closure parameters are checked too
      }
    }

### SnakeCaseVariableSniff

Variables must:

* start with a lowercase letter
* be in snake\_case

Use of function/method parameters will be ignored, as will closure's "use" imports and PHP's superglobals.

Only the first occurrence of an incorrectly named variable within a given scope will be reported.

For example:

    function($Par)
    {
      $Par=1;       //this is OK: it's a function parameter
      $some_var2=2; //this is OK: it's in snake case

      $x=function($P1) use ($Par) {
        $MyVar=$P1+$Par;   //$MyVar will be reported, $P1 is an argument and $Par is being imported
        $MyVar++;          //this won't trigger an error as $MyVar has already been reported above
      };

      echo $_SERVER['argc'];  //superglobals are ignored
    }


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

These errors are fixable by phpcbf.

### ClosingBracketSniff

Closing curly brackets (`}`) must not be preceded by empty lines.

Example:

    class X
    {
      function asdf()
      {
      }  //no empty line below!
    }

### ClosureOpeningBracketSniff

Closure's opening curly brackets (`{`) must be on the same line as the `function` keyword:

    $x=function() {};   //this is OK

    $y=function() {     //this is OK
      return 1;
    };

    $z=function()       //this is invalid, there shouldn't be a newline
    {
    };

Violations are automatically fixable by phpcbf, as long as there aren't any comments between the closure and the opening
bracket and the preceding part of the closure is in a single line.

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

Errors are automatically fixable by phpcbf.

### FunctionCallParametersSniff

Function calls' parameters must not be surrounded by whitespaces, e.g.:

    f(1, 2);  //"1" is valid, "2" is invalid because there's a whitespace before
    f( 3 ,4); //"3" is invalid because it's surrounded by whitespace, 4 is valid
    f(5 );    //5 is invalid because there's a whitespace after

    f(1,      //"1" is valid
      2);     //"2" is valid too, as long as there are no whitespaces after the above comma

If there are multiple function calls in contiguous lines their parameters may be left-aligned in a grid. Numbers may
also be right- or dot-aligned. For example:

    some_function(   'a',  -1,-2.0);  //OK because it lines up with the call below
    another_function(NULL,123, 4.11); //OK because the dots line up

Additionally, empty parameter lists must not contain whitespaces:

    f();      //this is valid
    f( );     //this isn't

Most errors found by this sniff are fixable by phpcbf. Spacing errors in vertically aligned calls are not.

### AssignmentOperatorsSniff

Assignment operators and the array double arrow must not be surrounded by whitespaces, unless aligned vertically.
If there are multiple, identical assignment operators in contiguous lines they may be preceded by spaces as long as at
least one of the operators immediately follows the assignee expression.

For example:

    //these are OK: no spaces around assignment operators
    $a=1;
    $aa=1;
    $aaa=1;

    //these are OK: operators aligned vertically
    $b  &=1;
    $bb &=1;
    $bbb&=1;

    //these are OK: no whitespaces
    $x=[1=>1,
        11=>2,
        111=>3];

    //these are OK; aligned vertically
    $y=[1  =>1,
        11 =>2,
        111=>3];

    $c  += 1;  //invalid: no whitespaces after operators allowed, even when aligned
    $cc +=1;
    $ccc+=1;

    $d *=1;    //invalid: aligned operators aren't identical
    $dd^=1;

These errors currently aren't automatically fixable.

### SeparatorSniff

Commas and semicolons must not follow any whitespaces.

By default commas in function call argument lists aren't checked as they're already covered by
FunctionCallParametersSniff. These commas can be included in this sniff by setting the `includeFunctionCallCommas`
property:

    <property name="includeFunctionCallCommas" value="yes"/>

Example:

    function f($a, $b)  //this is OK
    {
      $x=4;             //this is OK

      $y=[1 , 2];       //invalid: space before comma
      $z=3 ;            //invalid: space before semicolon

      asdf(1 , 2);      //OK by default, invalid if includeFunctionCallCommas is enabled
    }

Whitespaces before separators can be removed by phpcbf automatically. Semicolons directly following other semicolons
across multiple lines won't be fixed as this conflicts with another automatic fixer.

This sniff will also handle spacing *after* separators later.
