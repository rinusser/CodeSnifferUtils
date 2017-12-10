<?php
declare(strict_types=1);
/**
 * requires PHP version 7.0+
 * @author Richard Nusser
 * @copyright 2017 Richard Nusser
 * @license GPLv3 (see http://www.gnu.org/licenses/)
 * @link https://github.com/rinusser/CodeSnifferUtils
 */

namespace RN\CodeSnifferUtils\Utils;

use PHP_CodeSniffer\Files\File;

/**
 * Individual file configuration for sniffs
 */
trait PerFileSniffConfig
{
  private $_configsPerFile=[];

  private function _getFileConfig(File $file): FileConfig
  {
    $filename=$file->getFilename();
    if(!array_key_exists($filename,$this->_configsPerFile))
    {
      $config=new FileConfig();
      $tag=0;
      while($tag!==false)
      {
        $tag=$file->findNext([T_DOC_COMMENT_TAG],$tag+1,NULL,false,'@codingStandardsIgnoreRule');
        if($tag===false)
          break;
        foreach(FileUtils::getTokensOnLineAfter($file,$tag) as $token)
          if($token['code']===T_DOC_COMMENT_STRING)
            $config->disabledSniffs=array_merge($config->disabledSniffs,preg_split('/[ ,;|]/',$token['content'],-1,PREG_SPLIT_NO_EMPTY));
      }
      $this->_configsPerFile[$filename]=$config;
    }
    return $this->_configsPerFile[$filename];
  }

  protected function _isDisabledInFile(File $file): bool
  {
    if(in_array('--ignore-annotations',$_SERVER['argv']))
      return false;
    $name=str_replace(['RN\CodeSnifferUtils\Sniffs','PHP_CodeSniffer\Standards\RN\Sniffs','PHP_CodeSniffer\RN\Sniffs'],'RN',get_called_class());
    $name=str_replace('\\','.',$name);
    if(substr($name,-5)==='Sniff')
      $othername=substr($name,0,-5);
    else
      $othername=$name.'Sniff';
    return (bool)array_intersect([$name,$othername],$this->_getFileConfig($file)->disabledSniffs);
  }
}
