<?php

namespace UmnLib\Core\NcbiEUtilsClient;

use UmnLib\Core\ArgValidator;
use UmnLib\Core\File\Set\DateSequence;
/*
require_once 'NCBI/ESearch.php';
require_once 'NCBI/EFetch.php';
 */

class Client
{
  protected $db;
  protected $email;

  // Identifies the software using the NCBI service, e.g. 'umn-lib-extractor'
  protected $tool;

  // Maps to ESearch 'term'. This gives it a more descriptive name.
  protected $searchTerms;

  // For ESearch:
  protected $startDate;
  protected $endDate;

  // Maps to EFetch 'rettype'. This gives it a more descriptive name.
  //  TODO: other possibilities than 'citation'?
  protected $recordType;

  // TODO: For EFetch. Not sure I need to expose this via the Client. 
  //protected $retmode;

  // 'maxRecords' is a limit on the total number of records to extract.
  // If not set, will default to total number of records returned by the search.
  protected $maxRecords;

  // If a search returns many records, NCBI won't let us download them all at once,
  // and PHP may run out of memory if we try to download more than the default.
  // TODO: Look up the NCBI limit; may be 1000.
  protected $recordsPerDownload;

  // 'fileSet' is a set of files of downloaded records.
  protected $fileSet;

  function __construct(Array $args)
  {
    $validatedArgs = ArgValidator::validate(
      $args,
      array(
        'db' => array('is' => 'string', 'default' => 'pubmed'),
        'email' => array('is' => 'string'),
        'tool' => array('is' => 'string'),
        'searchTerms' => array('is' => 'string'),
        'startDate' => array('required' => false),
        'endDate' => array('required' => false),
        'recordType' => array('is' => 'string', 'default' => 'citation'),
        'maxRecords' => array('is' => 'int', 'required' => false),
        'recordsPerDownload' => array('is' => 'int', 'default' => 500),
        'fileSet' => array('instanceof' => '\UmnLib\Core\File\Set\DateSequence'),
      )
    );
    foreach ($validatedArgs as $property => $value) {
      $this->$property = $value;
    }
  }

  public function extract()
  {
    $searchParams = array(
      'db'    => $this->db(),
      'tool'  => $this->tool(),
      'email' => $this->email(),
      'term'  => $this->searchTerms(),
    );

    foreach (array('startDate','endDate') as $property) {
      if ($this->$property()) {
        $searchParams[$property] = $this->$property();
      }
    }

    $esearch = new ESearch($searchParams);
    $result = $esearch->search(); 

    $searchCount = (int) $result->Count; 
    $count = $searchCount;
    $maxRecords = $this->maxRecords();
    if (isset($maxRecords) && $searchCount > $maxRecords) {
      $count = $maxRecords;
    }

    $filenames = array(); 
    if ($count > 0) {
      $efetch = new EFetch(array(
        'db'    => $this->db(),
        'tool'  => $this->tool(),
        'email' => $this->email(),
        'rettype' => $this->recordType(),
        'recordsPerDownload' => $this->recordsPerDownload(),

        'fileSet' => $this->fileSet(),

        'count' => $count,
        'query_key' => (string) $result->QueryKey,
        'WebEnv' => (string) $result->WebEnv,
      ));
      $filenames = $efetch->fetch(); 
    }

    return array
      (
        'filenames' => $filenames,
        'esearch' => $esearch,
        'efetch' => $efetch,
      );
  }

  /**
   * @internal
   *           
   * Implements accessor methods.
   *                     
   * @param string $function The function/method name must be the same as the name of the property being accessed.
   * @param array $args Ignored and optional, since we implement only accessors here.
   * @return mixed The value of the property named by $function.
   */
  function __call($function, $args)
  {
    // Since we're handling only accessors here, the function name should
    // be the same as the property name:
    $property = $function;
    $class = get_class($this);
    $refClass = new \ReflectionClass($class);
    if (!$refClass->hasProperty($property)) {
      throw new \Exception("Method '$function' does not exist in class '$class'.");
    }
    return $this->$property;
  }
}
