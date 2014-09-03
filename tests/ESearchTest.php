<?php

namespace UmnLib\Core\Tests\NcbiEUtilsClient;

use \UmnLib\Core\NcbiEUtilsClient\ESearch;

class ESearchTest extends \PHPUnit_Framework_TestCase
{
  protected function setUp()
  {
    $this->commonParams = array
      (
        'email' => getenv('NCBI_USER_EMAIL'),
        'db'    => 'pubmed',
        'tool'  => getenv('NCBI_USER_TOOL'),
        'term'  => self::generateSearchTerms(),
      );
  }

  function testSearchCount()
  {
    $es = new ESearch(
      $this->commonParams + array
      (
        //'maxAttempts' => 5, // TODO: need to fix this...
        'rettype' => 'count',
      )
    );
    $this->assertInstanceOf('\UmnLib\Core\NcbiEutilsClient\ESearch', $es);

    $result = $es->search();
    $count = (int) $result->Count;
    $this->assertGreaterThanOrEqual(1, $count);

    $esDateRange = new ESearch(
      $this->commonParams + array
      (
        'startDate' => '2005/01/01',
        //'maxAttempts' => 5, // TODO: need to fix this...
        'rettype' => 'count',
        //'endDate' => 'xxxx/xx/xx', // defaults to current date
      )
    );
    $this->assertInstanceOf('\UmnLib\Core\NcbiEutilsClient\ESearch', $esDateRange);

    $resultDateRange = $esDateRange->search();
    $countDateRange = (int) $resultDateRange->Count;
    $this->assertGreaterThanOrEqual(1, $countDateRange);

    // Since the date range search is restricted to fewer dates,
    // we should have received far fewer results.
    $this->assertLessThanOrEqual($count, $countDateRange);
  }

  function testSearchHistory()
  {
    $es = new ESearch(
      //'maxAttempts' => 5, // Is there a default for this?
      $this->commonParams + array
      (
        'rettype' => 'xml',
        'usehistory' => 'y', // TODO: This is the default now, anyway...
      )
    );

    $result = $es->search();

    $count = (int) $result->Count;
    $this->assertGreaterThanOrEqual(1, $count);

    // Not sure how to test this, or why I should...
    $queryKey = (int) $result->QueryKey;
    $this->assertGreaterThanOrEqual(1, $queryKey);

    $webEnv = (string) $result->WebEnv;
    // These WebEnv's are loooong...
    $this->assertTrue(strlen($webEnv) > 25);
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
