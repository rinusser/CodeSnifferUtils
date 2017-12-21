<?php
declare(strict_types=1);
/**
 * Requires PHP version 7.1+
 * @author Richard Nusser
 * @copyright 2017 Richard Nusser
 * @license GPLv3 (see http://www.gnu.org/licenses/)
 * @link https://github.com/rinusser/CodeSnifferUtils
 */

namespace RN\CodeSnifferUtils\Tests;

use RN\CodeSnifferUtils\Config\PropertyCast;

/**
 * Runner for phpcs XML test cases
 */
class RunnerTest extends PHPCSTestCase
{
  private const PHPCS_BASEPATH='/phpcs/tests/files/';
  protected const BASEPATH='tests/cases';

  private static $_phpcsCmd='/phpcs/vendor/bin/phpcs -v --report=csv ';
  private static $_phpcbfCmd='/phpcs/vendor/bin/phpcbf';
  private static $_acceptableReturnValues=[0=>'success',1=>'validation failed',2=>'found fixable errors'];
  private static $_temporaryDirectories;


  /**
   * Called automatically by PHPUnit, sets up this test class
   */
  public static function setUpBeforeClass()
  {
    require_once(__DIR__.'/XMLTestCase.php');
    self::$_temporaryDirectories=[];
  }

  /**
   * Called automatically by PHPUnit, cleans up after this test class
   */
  public static function tearDownAfterClass()
  {
    foreach(self::$_temporaryDirectories as $dir)
      self::_deleteRecursive($dir);
  }


  private static function _deleteRecursive(string $target): void
  {
    $dir_iterator=new \RecursiveDirectoryIterator($target,\RecursiveDirectoryIterator::SKIP_DOTS);
    $iterator=new \RecursiveIteratorIterator($dir_iterator,\RecursiveIteratorIterator::CHILD_FIRST);
    foreach($iterator as $entry)
      self::_deleteFileSystemEntry($entry->getPathName());
    self::_deleteFileSystemEntry($target);
  }

  private static function _deleteFileSystemEntry(string $entry): void
  {
    if(is_file($entry))
      unlink($entry);
    elseif(is_dir($entry))
      rmdir($entry);
    else
      throw new \LogicException('unhandled file system entry: neither file nor directory');
  }


  /**
   * Fetches XML testcases from the configured path (tests/cases/ by default) and prepares them for injection by the test runner
   *
   * @return array list of prepared testcases
   */
  public function fetchXMLCases(): array
  {
    return $this->_fetchXMLCasesInternal(function($filename) {
      return $filename[0]!=='_';
    });
  }

  /**
   * Invokes phpcs for each provided testcase
   * If .xml testcase is marked as automatically fixable this test will check whether phpcbf actually fixes all errors
   *
   * @dataProvider fetchXMLCases
   *
   * @param int    $index    the testcase's index
   * @param string $filename the testcase's filename
   */
  public function testXMLCase(int $index, string $filename)
  {
    $message_prefix=$filename.': ';
    $fullpath=self::BASEPATH.'/'.$filename;

    $testcase=$this->_parseTestCase($fullpath);
    if($testcase->skip)
      return $this->markTestSkipped();

    list(,$actuals)=$this->_performPHPCSTest($testcase,$message_prefix);

    if($actuals['fixables'])
    {
      //create empty temporary directory
      $tmp_basedir=sys_get_temp_dir();
      $timestamp=microtime(true);
      $dir=sprintf('%s/CodeSnifferUtils.test.%03d.%s.%06d',$tmp_basedir,$index,date('Ymd.His',(int)$timestamp),($timestamp-(int)$timestamp)*1000000);
      self::$_temporaryDirectories[]=$dir;
      mkdir($dir);

      //copy source files and directories to temp dir
      foreach($testcase->sources as $source)
        $this->_copyRecursive($source,$dir);

      //run phpcbf on temp dir, this should fix all errors
      exec(self::$_phpcbfCmd.' --standard='.$fullpath.' '.$dir,$output,$rv);
      $message=$message_prefix.'phpcbf return value; should have fixed errors';
      if($this->_isDebug())
        $message.="\n".var_export($output,true);
      $this->assertEquals(1,$rv,$message);

      //run phpcs on temp dir and see if there are 0 errors
      $testcase->expectedErrors=array_diff_key($actuals['errors'],array_flip($actuals['fixables']));
      list($rv,)=$this->_performPHPCSTest($testcase,$message_prefix.'after automatic fixing: ',$dir.'/phpcs/tests/files',$dir);
      if($testcase->expectedErrors)
        $this->assertEquals(1,$rv,$message_prefix.'phpcs return value: phpcbf should have left some errors');
      else
        $this->assertEquals(0,$rv,$message_prefix.'phpcs return value: phpcbf should have fixed everything');
    }
  }


  /**
   * Fetches disabling XML testcases from the configured path (tests/cases/ by default) and prepares them for injection by the test runner
   *
   * @return array list of disabling testcases
   */
  public function fetchDisablingCases()
  {
    return $this->_fetchXMLCasesInternal(function($filename) {
      return $filename[0]==='_';
    });
  }

  /**
   * Tests if disabling annotations works
   *
   * @dataProvider fetchDisablingCases
   *
   * @param int    $index    (unused) the testcase's index
   * @param string $filename the testcase's filename
   */
  public function testDisablingCase(int $index, string $filename)
  {
    $message_prefix=$filename;
    $fullpath=self::BASEPATH.'/'.$filename;

    $testcase=$this->_parseTestCase($fullpath);
    $this->_performPHPCSTest($testcase,$message_prefix.' ignoring annotations: ',self::PHPCS_BASEPATH,'--ignore-annotations');
    $testcase->expectedErrors=[];
    $this->_performPHPCSTest($testcase,$message_prefix.' including annotations: ');
  }

  protected function _performPHPCSTest(XMLTestCase $testcase, string $message_prefix, string $basepath=self::PHPCS_BASEPATH, string $additional_args=''): array
  {
    $output=[];
    $rv=NULL;

    $pipes_specs=[0=>['pipe','r'],
                  1=>['pipe','w'],
                  2=>STDERR];
    $cmd=self::$_phpcsCmd.' --basepath='.$basepath.' --standard='.$testcase->filename.' '.$additional_args;
    if($this->_isDebug())
      echo "\n  CMD: ",$cmd,"\n";
    $proc=proc_open($cmd,$pipes_specs,$pipes);
    fclose($pipes[0]);
    $output=explode("\n",stream_get_contents($pipes[1]));
    fclose($pipes[1]);
    $rv=proc_close($proc);

    $printable_output="\n  ".implode("\n  ",$output);
    $this->_assertIsPHPCSValidationReturnValue($rv,$printable_output);

    if($this->_isDebug())
      echo $printable_output;

    foreach($output as $row)
      if(strpos($row,',Internal.Exception,')!==false)
        $this->fail('phpcs reported an error');

    $actuals=$this->_parseOutput($output);

    $this->assertEquals($testcase->expectedFileCount,$actuals['file_count'],$message_prefix.'parsed file count');
    $this->_assertErrorList($testcase->expectedErrors,$actuals['errors'],$message_prefix.'errors');

    return [$rv,$actuals];
  }

  protected function _assertErrorList(array $expected, array $actual, string $message): void
  {
    //remove identical items found in $expected and $actual, but only once each to allow for duplicates in either
    foreach($expected as $te=>$item)
    {
      foreach($actual as $tc=>$candidate)
      {
        if($item===$candidate)
        {
          unset($expected[$te]);
          unset($actual[$tc]);
          break;
        }
      }
    }

    $error_parts=array_filter([$this->_renderErrorsListIfNotEmpty($expected,'are missing',      '-'),
                               $this->_renderErrorsListIfNotEmpty($actual,  'weren\'t expected','+')]);

    if($error_parts)
      $this->fail($message."\n\n".implode("\n",$error_parts));
    $this->assertEmpty($error_parts); //this is just to increase the assert count so the stats show the test finished successfully
  }

  private function _renderErrorsListIfNotEmpty(array $errors, string $description, string $type)
  {
    if(!$errors)
      return false;
    return "These errors $description:\n".$this->_generateErrorsDiffOutput($errors,$type);
  }

  private function _generateErrorsDiffOutput(array $errors, string $type): string
  {
    $rvs=[];
    foreach($errors as $error)
      $rvs[]=" $type file: $error[file]\n $type line: $error[line]\n $type source: $error[source]\n";
    return implode("\n",$rvs);
  }

  private function _assertIsPHPCSValidationReturnValue(int $rv, ?string $output=NULL)
  {
    $was_acceptable_rv=in_array($rv,array_keys(self::$_acceptableReturnValues));
    if(!$was_acceptable_rv)
    {
      if($output!==NULL)
        echo $output;
      $this->fail('phpcs return value should be '.$this->_assembleAcceptableReturnValueString().', got "'.$rv.'" instead');
    }
  }

  private function _copyRecursive(string $source, string $destination): void
  {
    $source=rtrim(realpath(self::BASEPATH.'/'.$source),'/');
    $target=$destination.'/'.$source;
    mkdir(dirname($target),0755,true);
    if(is_file($source))
      copy($source,$target);
    elseif(is_dir($source))
    {
      mkdir($target);
      $target=$target.'/';
      $dir_iterator=new \RecursiveDirectoryIterator($source,\RecursiveDirectoryIterator::SKIP_DOTS);
      $iterator=new \RecursiveIteratorIterator($dir_iterator,\RecursiveIteratorIterator::SELF_FIRST);
      foreach($iterator as $entry)
      {
        if($entry->isFile())
          copy($entry->getPathName(),$target.$iterator->getSubPathName());
        elseif($entry->isDir())
          mkdir($target.$iterator->getSubPathName());
        else
          throw new \LogicException('unhandled source entry: neither file nor directory');
      }
    }
    else
      throw new \LogicException('unhandled source entry: neither file nor directory');
  }

  private function _assembleAcceptableReturnValueString(): string
  {
    $parts=[];
    foreach(self::$_acceptableReturnValues as $tk=>$tv)
      $parts[]=sprintf("%d (%s)",$tk,$tv);
    $last_part=array_pop($parts);
    $first_parts=implode(', ',$parts);
    $parts=[];

    if($first_parts)
      $parts=[$first_parts];
    $parts[]=$last_part;
    return implode(' or ',$parts);
  }

  private function _parseTestCase(string $fullpath): XMLTestCase
  {
    $xml=new \SimpleXMLElement(file_get_contents($fullpath));
    $file_count=(int)$xml->expectations->file_count->__toString();
    $errors=[];
    foreach($xml->expectations->error as $error)
    {
      $file=$error->attributes()->file->__toString();
      $line=$error->attributes()->line->__toString();
      $type=$error->__toString();
      $errors[]=['file'=>$file,'line'=>$line,'source'=>$type];
    }

    $sources=[];
    foreach($xml->file as $file)
      $sources[]=$file->__toString();

    $testcase=new XMLTestCase($fullpath,$sources,$file_count,$errors);

    $skip_raw=$xml->expectations->attributes()->skip;
    if($skip_raw && PropertyCast::toBool($skip_raw->__toString(),'skip attribute'))
      $testcase->skip=true;

    return $testcase;
  }

  private function _parseOutput(array $output): array
  {
    $file_count=0;
    $columns=[];
    $data=[];
    $fixables=[];
    $column_count=-1;
    $ti=0;
    foreach($output as $row)
    {
      $row=trim($row);
      if(!$row)
        continue;
      $is_processing_row=preg_match('/^Processing /',$row);
      if($is_processing_row)
      {
        $file_count++;
        continue;
      }
      if($file_count<1 || !$columns&&!preg_match('/^File,([^,]+,){4,}/',$row))
        continue;
      if(!$columns)
      {
        $columns=array_map('strtolower',explode(',',$row));
        $column_count=count($columns);
        continue;
      }
      $data_row=str_getcsv($row);
      if(count($data_row)!==$column_count)
        continue;
      $full_data=array_combine($columns,$data_row);
      $data[]=array_intersect_key($full_data,array_flip(['file','line','source']));
      if($full_data['fixable']>0)
        $fixables[]=$ti;
      $ti++;
    }
    return ['file_count'=>$file_count,'errors'=>$data,'fixables'=>$fixables];
  }
}
