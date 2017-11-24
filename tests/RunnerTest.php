<?php
declare(strict_types=1);
/**
 * Requires PHP version 7.0+
 * @author Richard Nusser
 * @copyright 2017 Richard Nusser
 * @license GPLv3 (see http://www.gnu.org/licenses/)
 */

namespace RN\CodeSnifferUtils\Tests;

use PHPUnit\Framework\TestCase;
use RN\CodeSnifferUtils\Utils\PropertyCast;

/**
 * Runner for phpcs XML test cases
 */
class RunnerTest extends TestCase
{
  private static $_phpcsCmd='/phpcs/vendor/bin/phpcs -v --report=csv --basepath=/phpcs/tests/files/';
  private static $_phpcbfCmd='/phpcs/vendor/bin/phpcbf';
  private static $_xmlPath='tests/cases';
  private static $_acceptableReturnValues=[0=>'success',1=>'validation failed',2=>'found fixable errors'];
  private static $_temporaryDirectories;


  /**
   * Called automatically by PHPUnit, sets up this test class
   */
  public static function setUpBeforeClass()
  {
    require_once(__DIR__.'/../src/autoloader.php');
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
   * Invokes phpcs on each .xml file in the XML path (tests/cases/ by default)
   * If .xml testcase is marked as automatically fixable this test will check whether phpcbf actually fixes all errors
   */
  public function testXMLCases()
  {
    foreach(new \DirectoryIterator(self::$_xmlPath) as $ti=>$file)
    {
      if($file->isDot() || preg_match('/^\..*\.swp$/',$file->getFilename()))
        continue;
      $message_prefix=$file->getFilename().': ';
      $fullpath=self::$_xmlPath.'/'.$file->getFilename();
      if($this->_isVerbose())
        echo "\ntesting ",$fullpath;

      $testcase=$this->_parseTestCase($fullpath);
      $expectations=$testcase['expectations'];
      $fixable=$testcase['fixable'];

      $this->_performPHPCSTest($fullpath,$expectations,$message_prefix);

      if($fixable)
      {
        //create empty temporary directory
        $tmp_basedir=sys_get_temp_dir();
        $timestamp=microtime(true);
        $dir=sprintf('%s/CodeSnifferUtils.test.%03d.%s.%06d',$tmp_basedir,$ti,date('Ymd.His',(int)$timestamp),($timestamp-(int)$timestamp)*1000000);
        self::$_temporaryDirectories[]=$dir;
        mkdir($dir);

        //copy source files and directories to temp dir
        foreach($testcase['sources'] as $source)
          $this->_copyRecursive($source,$dir);

        //run phpcbf on temp dir, this should fix all errors
        exec(self::$_phpcbfCmd.' --standard='.$fullpath.' '.$dir,$output,$rv);
        $this->assertEquals(1,$rv,$message_prefix.'phpcbf return value; should have fixed errors');

        //run phpcs on temp dir and see if there are 0 errors
        $expectations['errors']=[];
        $rv=$this->_performPHPCSTest($fullpath,$expectations,$message_prefix,' '.$dir);
        $this->assertEquals(0,$rv,$message_prefix.'phpcs return value: phpcbf should have fixed everything');
      }
    }
  }

  protected function _performPHPCSTest(string $fullpath, array $expectations, string $message_prefix, string $additional_cmdargs=''): int
  {
    $output=[];
    $rv=NULL;

    $pipes_specs=[0=>['pipe','r'],
                  1=>['pipe','w'],
                  2=>STDERR];
    $proc=proc_open(self::$_phpcsCmd.' --standard='.$fullpath.' '.$additional_cmdargs,$pipes_specs,$pipes);
    fclose($pipes[0]);
    $output=explode("\n",stream_get_contents($pipes[1]));
    fclose($pipes[1]);
    $rv=proc_close($proc);

    $printable_output="\n  ".implode("\n  ",$output);
    $this->_assertIsPHPCSValidationReturnValue($rv,$printable_output);

    if($this->_isDebug())
      echo $printable_output;

    $actuals=$this->_parseOutput($output);
    $this->assertEquals($expectations['file_count'],$actuals['file_count'],$message_prefix.'parsed file count');
    $this->assertEquals($expectations['errors'],$actuals['errors'],$message_prefix.'errors');

    return $rv;
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
    $source=rtrim(realpath(self::$_xmlPath.'/'.$source),'/');
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

  private function _parseTestCase(string $fullpath): array
  {
    $expectations=[];
    $xml=new \SimpleXMLElement(file_get_contents($fullpath));
    $expectations['file_count']=$xml->expectations->file_count->__toString();
    $expectations['errors']=[];
    foreach($xml->expectations->error as $error)
    {
      $file=$error->attributes()->file->__toString();
      $line=$error->attributes()->line->__toString();
      $type=$error->__toString();
      $expectations['errors'][]=['file'=>$file,'line'=>$line,'source'=>$type];
    }

    $fixable=false;
    $fixable_attr=$xml->expectations->attributes()->fixable;
    if($fixable_attr)
      $fixable=PropertyCast::toBool($fixable_attr->__toString(),'fixable');

    $sources=[];
    foreach($xml->file as $file)
      $sources[]=$file->__toString();

    return ['expectations'=>$expectations,'fixable'=>$fixable,'sources'=>$sources];
  }

  private function _parseOutput(array $output): array
  {
    $file_count=0;
    $columns=[];
    $data=[];
    $column_count=-1;
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
    }
    return ['file_count'=>$file_count,'errors'=>$data];
  }

  private function _isVerbose(): bool
  {
    return (bool)array_intersect(['-v','--verbose','--debug'],$_SERVER['argv']);
  }

  private function _isDebug(): bool
  {
    return in_array('--debug',$_SERVER['argv']);
  }
}
