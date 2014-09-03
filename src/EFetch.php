<?php

namespace UmnLib\Core\NcbiEUtilsClient;

use UmnLib\Core\ArgValidator;

class EFetch
{
  //  e.g. 'umn-lib-extractor'
  protected $tool;

  //  TODO: other possibilities than 'citation'?
  protected $rettype;

  //  TODO: other possibilities than 'xml'?
  protected $retmode;

  // Generated by NCBI.
  protected $query_key;
  protected $WebEnv;

  // These are parameters to send on every search request.
  protected $commonParams;

  protected $count;
  protected $db;
  protected $email;
  protected $recordsPerDownload;
  protected $fileSet;
  protected $date;
  protected $eutil;

  function __construct(Array $args)
  {
    $validatedArgs = ArgValidator::validate(
      $args,
      array(
        'count' => array('is' => 'int', 'default' => 0),
        'db' => array('is' => 'string', 'default' => 'pubmed'),
        'email' => array('is' => 'string'),
        'tool' => array('is' => 'string'),
        'rettype' => array('is' => 'string', 'default' => 'citation'),
        'retmode' => array('is' => 'string', 'default' => 'xml'),
        'query_key' => array('is' => 'string'),
        'WebEnv' => array('is' => 'string'),
        'recordsPerDownload' => array('is' => 'int', 'default' => 500),
        'commonParams' => array('is' => 'array', 'default' => array('email','db','tool','rettype','retmode','query_key','WebEnv')),
        'fileSet' => array('instanceof' => '\UmnLib\Core\File\Set\DateSequence'),
        'date' => array(
          'is' => 'string',
          'builder' => function () { return date('Ymd'); },
        ),
        'eutil' => array(
          'instanceof' => '\UmnLib\Core\NcbiEutilsClient\EUtil',
          'builder' => function () { return new \UmnLib\Core\NcbiEUtilsClient\EUtil(array('format' => 'raw', 'util' => 'fetch')); },
        ),
      )
    );

    foreach ($validatedArgs as $property => $value) {
      $this->$property = $value;
    }
  }

  public function fetch()
  {
    $recordsPerDownload = $this->recordsPerDownload();
    // TODO: This still may not be right. Enumerate the cases!
    $retmax = (
      $this->count() < $recordsPerDownload ?
      $this->count() :
      $recordsPerDownload
    );
    $retend = $retmax - 1;
    $retstart = 0;

    // output
    $filenames = array();

    while (1) {
      unset($result, $e, $filename, $file, $params);

      $params = array
        (
          'retmax' => $retmax,
          'retstart' => $retstart,
        );
      foreach ($this->commonParams() as $param) {
        $params[$param] = $this->$param();
      }

      try {
        $result = $this->eutil()->sendRequest($params);
      } catch (\Exception $e) {
        error_log($e->getMessage());
      }
      if (!isset($e)) {
        $filename = $this->fileSet()->add(); 
        file_put_contents($filename, $result);
        $filenames[] = $filename;
      }

      // This break was here only for testing...
      //break;

      if ($retend >= $this->count() - 1) break;

      // increment for next iteration
      $retstart += $retmax;
      $retend += $retmax;

      // TODO: This isn't right. Not sure that this condition will ever be true.
      // Shouldn't this be $retend, not $retmax?
      if ($retmax > $this->count()) $retmax = $this->count();
    }

    return $filenames;
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
      throw new \RuntimeException("Method '$function' does not exist in class '$class'.");
    }
    return $this->$property;
  }
}
