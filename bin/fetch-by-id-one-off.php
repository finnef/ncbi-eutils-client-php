#!/usr/bin/php -q
<?php

require dirname(__FILE__) . '/../vendor/autoload.php';

use \UmnLib\Core\NcbiEUtilsClient\EFetchById;
use \UmnLib\Core\File\Set\DateSequence;

$env = getPhpUnitEnvVars(file_get_contents(dirname(__FILE__) . '/../phpunit.xml'));
$directory = (array_key_exists('1', $argv) && is_dir($argv[1])) ? $argv[1] : '.';

$fileSet = new DateSequence(array(
  'directory' => $directory,
  'suffix' => '.xml',
));

// Clean out any already-existing files:
$fileSet->clear();

$ef = new EFetchById(array(
  'email' => $env['NCBI_USER_EMAIL'],
  'db'    => 'pubmed',
  'tool'  => $env['NCBI_USER_TOOL'],
  //'ids' => array('13579126','13494634','13064353','13004201','12983522','12983491'), // PMIDs
  //'ids' => array('19998099'), // PMIDs
  'ids' => array(
    // Nanoethics PMIDs
        /*
        '17033654',
        '11702180',
        '17826113',
        '17254762',
        '18825403',
        '17454354',
        '16639314',
        '16723880',
        '17245366',
        '17193067',
        '19142939',
        '17997181',
        '16440317',
        '16582920',
         */
    // Not sure what these were... More Nanoethics?
        /*
        '19672990',
        '20040559',
        '16686751',
        '16728555',
         */
    // EthicShare date different than PubMed date.
    //'18427955', 
        /*
        '18546061',
        '18563629',
        '19685170',
         */
    // Not in PubMed?
    //'20883430',
    // field_datetime_published contains only the year, field_datetime_epublished is more precise:
    '20231248',
  ),
  'rettype' => 'citation',
  'retmode' => 'xml',
  'fileSet' => $fileSet,
));

$ef->fetch();

function getPhpUnitEnvVars($phpUnitXml)
{
  $xml = new \SimpleXMLElement($phpUnitXml);
  $env = array();
  foreach ($xml->php->env as $envElem) {
    unset($name, $value);
    foreach($envElem->attributes() as $k => $v) {
      $stringv = (string) $v;
      if ($k == 'name') {
        $name = $stringv;
      } else if ($k == 'value') {
        $value = $stringv;
      }
    }
    $env[$name] = $value;
  }
  return $env;
}

