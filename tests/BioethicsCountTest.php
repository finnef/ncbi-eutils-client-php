<?php

namespace UmnLib\Core\Tests\NcbiEUtilsClient;

use \UmnLib\Core\NcbiEUtilsClient\ESearch;

class BioethicsCountTest extends \PHPUnit_Framework_TestCase
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
  }

  protected function generateSearchTerms()
  {
    $terms = array
      (
        'Basic' => 'bioethics[ALL]',
      );
    reset($terms);
    return join(' OR ', array_values($terms));
  }
}
