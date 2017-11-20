<?php
declare(strict_types=1);
/**
 * Requires PHP version 7.0+
 * @author Richard Nusser <do.not@con.tact>
 * @copyright 2017 Richard Nusser
 * @license GPLv3 (see http://www.gnu.org/licenses/)
 */

namespace RN\CodeSnifferUtils\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Runner for phpcs XML test cases
 */
class RunnerTest extends TestCase
{
  private static $_dockerCmd='/phpcs/vendor/bin/phpcs -v --report=csv --basepath=/phpcs/tests/files/';
  private static $_xmlPath='tests/cases';
  private static $_acceptableReturnValues=[0=>'success',1=>'validation failed'];


  /**
   * Invokes phpcs on each .xml file in the XML path (tests/cases/ by default)
   */
  public function testXMLCases()
  {
    foreach(new \DirectoryIterator(self::$_xmlPath) as $file)
    {
      if($file->isDot() || preg_match('/^\..*\.swp$/',$file->getFilename()))
        continue;
      $fullpath=self::$_xmlPath.'/'.$file->getFilename();
      if($this->_isVerbose())
        echo "\ntesting ",$fullpath;
      $output=[];
      $rv=NULL;

      $pipes_specs=[0=>['pipe','r'],
                    1=>['pipe','w'],
                    2=>STDERR];
      $proc=proc_open(self::$_dockerCmd.' --standard='.$fullpath,$pipes_specs,$pipes);
      fclose($pipes[0]);
      $output=explode("\n",stream_get_contents($pipes[1]));
      fclose($pipes[1]);
      $rv=proc_close($proc);

      $printable_output="\n  ".implode("\n  ",$output);
      $was_acceptable_rv=in_array($rv,array_keys(self::$_acceptableReturnValues));
      if(!$was_acceptable_rv)
      {
        echo $printable_output;
        $this->fail('phpcs return value should be '.$this->_assembleAcceptableReturnValueString().', got "'.$rv.'" instead');
      }

      if($this->_isDebug())
        echo $printable_output;

      $expectations=$this->_parseExpectations($fullpath);
      $actuals=$this->_parseOutput($output);
      $this->assertEquals($expectations['file_count'],$actuals['file_count'],'parsed file count');
      $this->assertEquals($expectations['errors'],$actuals['errors'],$file->getFilename());
    }
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

  private function _parseExpectations(string $fullpath): array
  {
    $rv=[];
    $xml=new \SimpleXMLElement(file_get_contents($fullpath));
    $rv['file_count']=$xml->expectations->file_count->__toString();
    $rv['errors']=[];
    foreach($xml->expectations->error as $error)
    {
      $file=$error->attributes()->file->__toString();
      $line=$error->attributes()->line->__toString();
      $type=$error->__toString();
      $rv['errors'][]=['file'=>$file,'line'=>$line,'source'=>$type];
    }
    return $rv;
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
      if($file_count<1)
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
