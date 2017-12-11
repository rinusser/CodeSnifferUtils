<?php
declare(strict_types=1);
/**
 * requires PHP version 7.1+
 * @author Richard Nusser
 * @copyright 2017 Richard Nusser
 * @license GPLv3 (see http://www.gnu.org/licenses/)
 * @link https://github.com/rinusser/CodeSnifferUtils
 */

namespace RN\CodeSnifferUtils\Utils;

use PHP_CodeSniffer\Files\File;
use RN\CodeSnifferUtils\Utils\NoImplicitProperties;

/**
 * Interceptor for unused function parameter sniff: removes parameters marked as unused from expected parameter list
 */
class IgnorableUnusedFunctionParameterFileProxy extends File
{
  //disallow access to undeclared properties
  use NoImplicitProperties;


  private $_target;


  /**
   * Constructor for class, requires target object to forward calls to
   *
   * @param File $target the File object to forward calls to
   */
  public function __construct(File $target)
  {
    $this->_target=$target;
  }


  /**
   * Works like File::getMethodParameters() but will skip those marked as unused
   *
   * @param int $stack_ptr the phpcs context
   * @return array list of method parameters, minus supposedly unused ones
   */
  public function getMethodParameters($stack_ptr)
  {
    $tokens=$this->_target->getTokens();
    $unused_parameters[]=[];
    $docblock_end=$this->_target->findPrevious(T_DOC_COMMENT_CLOSE_TAG,$stack_ptr-1,NULL,false);
    if($tokens[$docblock_end]['line']>=$tokens[$stack_ptr]['line']-1)
    {
      $docblock_start=$tokens[$docblock_end]['comment_opener'];
      foreach($tokens[$docblock_start]['comment_tags'] as $ti=>$offset)
      {
        if($tokens[$offset]['content']!=='@param')
          continue;
        $end=isset($tokens[$docblock_start]['comment_tags'][$ti+1])?$tokens[$docblock_start]['comment_tags'][$ti+1]-1:$docblock_end-1;
        $docrow_parts=preg_split('/ /',trim($this->_target->getTokensAsString($offset+1,$end-$offset-2)),4,PREG_SPLIT_NO_EMPTY);
        if(!empty($docrow_parts[2])&&$docrow_parts[2]==='(unused)')
          $unused_parameters[]=$docrow_parts[1];
      }
    }

    $rv=[];
    foreach($this->_target->getMethodParameters($stack_ptr) as $par)
      if(!in_array($par['name'],$unused_parameters))
        $rv[]=$par;
    return $rv;
  }

  /**
   * see File::getTokens()
   *
   * @return array
   */
  public function getTokens()
  {
    return $this->_target->getTokens();
  }

  /**
   * see File::addWarning()
   *
   * @param mixed $warning   see File
   * @param mixed $stack_ptr see File
   * @param mixed $code      see File
   * @param mixed $data      see File
   * @param mixed $severity  see File
   * @param mixed $fixable   see File
   * @return mixed see File
   */
  public function addWarning($warning, $stack_ptr, $code, $data=[], $severity=0, $fixable=false)
  {
    return $this->_target->addWarning($warning,$stack_ptr,$code,$data,$severity,$fixable);
  }

  /**
   * see File::findNext()
   *
   * @param mixed $types   see File
   * @param mixed $start   see File
   * @param mixed $end     see File
   * @param mixed $exclude see File
   * @param mixed $value   see File
   * @param mixed $local   see File
   * @return mixed see File
   */
  public function findNext($types, $start, $end=NULL, $exclude=false, $value=NULL, $local=false)
  {
    return $this->_target->findNext($types,$start,$end,$exclude,$value,$local);
  }
}
