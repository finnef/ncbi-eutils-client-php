<?php

namespace UmnLib\Core\NcbiEUtilsClient;

use Guzzle\Http\Url;
use Guzzle\Http\QueryString;

class EUtil
{
  protected $httpClient;
  function httpClient()
  {
    if (isset($this->httpClient)) return $this->httpClient;
    $httpClient = new \Guzzle\Http\Client();
    $this->httpClient = $httpClient;
    return $httpClient;
  }

  protected $maxAttempts = 10;

  public function baseUri()
  {
    return 'http://eutils.ncbi.nlm.nih.gov/entrez/eutils/';
  }

  // TODO: Why can't this be "static"?
  protected $utils = array
    (
      'search',
      'fetch', 
    );
  protected $util;
  public function util()
  {
    return $this->util;
  }
  protected function setUtil($util)
  {
    if (!in_array($util, $this->utils)) {
      throw new \InvalidArgumentException("Invalid util '$util'");
    }
    $this->util = 'e' . $util . '.fcgi';
  }

  // TODO: Why can't this be "static"?
  protected $formats = array
    (
      'raw',
      'simplexml', 
    );
  protected $formatter;
  public function formatter()
  {
    return $this->formatter;
  }
  protected function setFormatter($format)
  {
    if (!in_array($format, $this->formats)) {
      throw new \InvalidArgumentException("Invalid format '$format'");
    }
    $this->formatter = 'format' . ucfirst($format);
  }

  function __construct($params)
  {
    $this->setUtil( $params['util'] );
    $this->setFormatter( $params['format'] );
  } 

  // Added to try to stop memory leaks, based on:
  // http://paul-m-jones.com/?p=262
  // Should be no longer necessary, since we don't subclass HTTP_Request anymore...
    /*
    function __destruct() {
        //echo "In NCBI_eUtil::__destruct ...\n";
        unset( $this->_response, $this->_sock, $this->_url );
    }
     */

  function sendRequest($params) {
    // Must use a POST request, otherwise the NCBI server returns...
    // status code: 414
    // reason phrase: Request-URI Too Large
    $request = $this->httpClient()->post($this->baseUri() . $this->util());

    $queryString = new QueryString();
    // Allow multiple instances of the same key:
    $queryString->setAggregator(new \Guzzle\Http\QueryAggregator\DuplicateAggregator());
    foreach ($params as $key => $values) {
      if (!is_array($values)) {
        $values = array( $values );
      }
      foreach ($values as $value) {
        $queryString->add($key, $value);
      }
    }
    $request->addPostFields($queryString);

    $requestFailed = true;
    //$maxAttempts = 10;
    //for ($i = 1; $i <= $maxAttempts; $i++) {

        /* Seems pointless to log this...
        error_log("maxAttempts = " . $this->maxAttempts);
        error_log("request_type = " . $this->request_type);
         */

    for ($i = 1; $i <= $this->maxAttempts; $i++) {
      unset($response);
      $response = $request->send();

      // TODO: Add parsing of response to look for errors,
      // and throw exceptions if necessary.
      $statusCode = $response->getStatusCode();
      if ($statusCode > 499 and $statusCode < 600) {
        error_log("Attempt $i: NCBI Entrez server response: $statusCode");
        continue;
      }
      $requestFailed = false;
      break;
    }
    if ($requestFailed) {
      throw new \RuntimeException(
        "Giving up on " . $this->util() . " after " . $this->maxAttempts . " attempts."
      );
    }

    $result = $response->getBody();
    //echo $result; 
    $formatter = $this->formatter();
    return $this->$formatter($result);        
  }

  public function formatRaw($result)
  {
    return $result;
  }

  public function formatSimplexml($result)
  {
    return new \SimpleXMLElement($result);
  }
}
