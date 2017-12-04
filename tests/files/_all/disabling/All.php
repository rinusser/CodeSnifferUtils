<?php



namespace A\B\C;



use X\Y\UnusedNamespace;



class Violations
{

  public $_a, $b;



  const A=tRuE;



  public function _asdf( $x)
  {
    throw new LogicException();
  }

}

/**
 * @codingStandardsIgnoreRule RN.Capitalization.BooleanNULLSniff
 * @codingStandardsIgnoreRule RN.Classes.IndividualPropertiesSniff
 * @codingStandardsIgnoreRule RN.Classes.MemberOrderingSniff
 * @codingStandardsIgnoreRule RN.CodeAnalysis.IgnorableUnusedFunctionParameterSniff
 * @codingStandardsIgnoreRule RN.CodeAnalysis.SPLExceptionNamespaceSniff
 * @codingStandardsIgnoreRule RN.CodeAnalysis.UnusedNamespaceImportSniff
 * @codingStandardsIgnoreRule RN.Commenting.ConfigurableClassCommentSniff
 * @codingStandardsIgnoreRule RN.Commenting.ConfigurableFileCommentSniff
 * @codingStandardsIgnoreRule RN.Commenting.ConfigurableFunctionCommentSniff
 * @codingStandardsIgnoreRule RN.Files.DeclareStrictSniff
 * @codingStandardsIgnoreRule RN.Naming.CamelCaseMethodSniff
 * @codingStandardsIgnoreRule RN.Naming.PropertySniff
 * @codingStandardsIgnoreRule RN.Naming.SnakeCaseFunctionParametersSniff,RN.Naming.SnakeCaseFunctionSniff,RN.Naming.SnakeCaseVariableSniff
 * @codingStandardsIgnoreRule RN.Spacing.ClassSniff
 * @codingStandardsIgnoreRule RN.Spacing.ClosingBracketSniff
 * @codingStandardsIgnoreRule RN.Spacing.ConstSniff
 * @codingStandardsIgnoreRule RN.Spacing.FunctionParametersSniff
 * @codingStandardsIgnoreRule RN.Spacing.FunctionSniff
 * @codingStandardsIgnoreRule RN.Spacing.NamespaceSniff, RN.Spacing.OpeningBracketSniff
 * @codingStandardsIgnoreRule RN.Spacing.PropertySniff  RN.Spacing.UseSniff
 */
function Asdf($B)
{
  $ASDF=1;
  return 1;
}

class ShouldBeAbstract
{
  public static $x;
}


/**
 * use this docblock to register any additional rule exceptions without changing expected line numbers
 *
 * @codingStandardsIgnoreRule RN.CodeAnalysis.StaticOnlyAbstractClassSniff
 *
 * @return void
 */
function keep_at_end()
{
}
