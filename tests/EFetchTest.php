<?php

namespace UmnLib\Core\Tests\NcbiEUtilsClient;

use \UmnLib\Core\NcbiEUtilsClient\Client;
use \UmnLib\Core\NcbiEUtilsClient\ESearch;
use \UmnLib\Core\NcbiEUtilsClient\EFetch;
use \UmnLib\Core\NcbiEUtilsClient\EFetchById;
use \UmnLib\Core\File\Set\DateSequence;

class EFetchTest extends \PHPUnit_Framework_TestCase
{
  protected function setUp()
  {
    $this->commonParams = array
      (
        'email' => getenv('NCBI_USER_EMAIL'),
        'tool'  => getenv('NCBI_USER_TOOL'),
      );
  }

  function testFetch()
  {
    $es = new ESearch(
      $this->commonParams + array
      (
        'db'    => 'pubmed',
        //'maxAttempts' => 5, // TODO: need to fix this...
        'rettype' => 'xml',
        'usehistory' => 'y', // TODO: This is the default now, anyway...
        'startDate' => '2005/01/01',
        //'endDate' => 'xxxx/xx/xx', // defaults to current date
        'term'  => self::generateSearchTerms(),
      )
    );
    $result = $es->search();

    $count = (int) $result->Count;
    $this->assertGreaterThanOrEqual(0, $count);

    $fileSet = new DateSequence(array(
      'directory' => dirname(__FILE__) . '/fixtures/fetched',
      'suffix' => '.xml',
    ));

    $ef = new EFetch(
      $this->commonParams + array
      (
        //'db'    => 'pubmed', // 'pubmed' is the default.
        'rettype' => 'citation',
        'retmode' => 'xml',
        'recordsPerDownload' => 1000,
        'fileSet' => $fileSet,
        'query_key' => (string) $result->QueryKey,
        'WebEnv' => (string) $result->WebEnv,
        'count' => $count,
      )
    );
    $this->assertInstanceOf('\UmnLib\Core\NcbiEUtilsClient\EFetch', $ef);

    // Clean out any already-existing files, e.g. from previous test runs:
    $fileSet->clear();

    $ef->fetch();
    $filenames = $fileSet->members();

    $downloadCount = 0;
    foreach ($filenames as $filename) {
      $xml = simplexml_load_file($filename);
      $downloadCount += count(array_keys($xml->xpath('//PubmedArticle')));
    }
    $this->assertEquals($count, $downloadCount);

    // Clean up:
    $fileSet->clear();
  }

  function testFetchById()
  {
    $fileSet = new DateSequence(array(
      'directory' => dirname(__FILE__) . '/fixtures/fetched-by-id',
      'suffix' => '.xml',
    ));

    // Clean out any already-existing files, e.g. from previous test runs:
    $fileSet->clear();

    $ef = new EFetchById(
      $this->commonParams + array
      (
        'db'    => 'pubmed',
        'ids' => array('18650511','18647987'), // PMIDs
        'rettype' => 'citation',
        'retmode' => 'xml',
        'fileSet' => $fileSet,
      )
    );

    $ef->fetch();
    $filenames = $fileSet->members();

    $downloadCount = 0;
    foreach ($filenames as $filename) {
      $xml = simplexml_load_file($filename);
      $downloadCount += count(array_keys($xml->xpath('//PubmedArticle')));
    }
    $this->assertEquals(2, $downloadCount);

    // Clean up:
    $fileSet->clear();
  }

  function testFetchByIdNlmCatalog()
  {
    $fileSet = new DateSequence(array(
      'directory' => dirname(__FILE__) . '/fixtures/fetched-by-id-nlmcatalog',
      'suffix' => '.xml',
    ));

    // Clean out any already-existing files, e.g. from previous test runs:
    $fileSet->clear();

    $ef = new EFetchById(
      $this->commonParams + array
      (
        'ids' => array('101566287','101565471'), // NlmUniqueID's
        'db' => 'nlmcatalog',
        'rettype' => 'citation',
        'retmode' => 'xml',
        'fileSet' => $fileSet,
      )
    );

    $ef->fetch();
    $filenames = $fileSet->members();

    $downloadCount = 0;
    foreach ($filenames as $filename) {
      $xml = simplexml_load_file($filename);
      $downloadCount += count(array_keys($xml->xpath('//NlmUniqueID')));
    }
    $this->assertEquals(2, $downloadCount);

    // Clean up:
    $fileSet->clear();
  }

  protected function generateSearchTerms() {
    $terms = array
      (
        'Abortion' => '"abortion, criminal" [mh] OR ("Abortion, Induced" [mh] AND ("classification" [sh] OR "economics" [sh] OR "education" [sh] OR "history" [sh] OR "jurisprudence" [sh] OR "mortality" [sh] OR "standards" [sh] OR "statistics and numerical data" [sh] OR "trends" [sh])) OR "abortion applicants" [mh] OR ("abortion" AND "Attitude of Health Personnel" [mh])',
      );

    reset($terms);
    return join(' OR ', array_values($terms));
  }
}
