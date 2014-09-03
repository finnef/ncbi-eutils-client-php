<?php


namespace UmnLib\Core\NcbiEUtilsClient;

use UmnLib\Core\ArgValidator;

class ESearch
{
    protected $email;
    protected $db;
    protected $tool;

    // other values: 'count', ...?
    protected $rettype;

    protected $usehistory;
    protected $term;
    protected $startDate;
    protected $endDate;
    protected $eutil;
    protected $commonParams;

  function __construct(Array $args)
  {
    $validatedArgs = ArgValidator::validate(
      $args,
      array(
        'email' => array('is' => 'string'),
        'db' => array('is' => 'string', 'default' => 'pubmed'),
        'tool' => array('is' => 'string'),
        'rettype' => array('is' => 'string', 'default' => 'xml'),
        'usehistory' => array('is' => 'string', 'default' => 'y'),
        'term' => array('is' => 'string'),
        'startDate' => array('required' => false),
        'endDate' => array('required' => false),
        'commonParams' => array('is' => 'array', 'default' => array('email','db','tool','rettype','usehistory','term')),
        'eutil' => array(
          'instanceof' => '\UmnLib\Core\NcbiEutilsClient\EUtil',
          'builder' => function () { return new \UmnLib\Core\NcbiEUtilsClient\EUtil(array('format' => 'simplexml', 'util' => 'search')); },
        ),
      )
    );

    //echo "validateArgs = "; print_r($validatedArgs);

    foreach ($validatedArgs as $property => $value) {
      $this->$property = $value;
    }

    $startDate = $this->startDate();
    $endDate = $this->endDate();
    if ($endDate && !$startDate) {
      throw new \InvalidArgumentException('An endDate requires a startDate');
    }

    if ($startDate) {
      $dateRange = '(' . $startDate . ':';
      if (!$endDate) {
        $endDate = date('Y/m/d', time());
      }
      $dateRange .= $endDate . '[edat])';
      $this->term = $this->term() . " AND $dateRange";
    }
  }

  function search() {
    $params = array();
    foreach ($this->commonParams() as $param) {
      $params[$param] = $this->$param();
    }
    return $this->eutil()->sendRequest($params);
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
