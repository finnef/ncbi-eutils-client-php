<?php

require_once 'HTTP/Request.php';

class NCBI_eUtil
{
    protected $max_attempts = 10;

    public function base_uri()
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
    protected function set_util( $util )
    {
        if (!in_array($util, $this->utils)) {
            throw new Exception("Invalid util '$util'");
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
    protected function set_formatter( $format )
    {
        if (!in_array($format, $this->formats)) {
            throw new Exception("Invalid format '$format'");
        }
        $this->formatter = 'format_' . $format;
    }

    function __construct($params)
    {
        $this->set_util( $params['util'] );
        $this->set_formatter( $params['format'] );
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
    
    function send_request($params) {
    
        $http = new HTTP_Request( $this->base_uri() . $this->util() );

        $http->setMethod(HTTP_REQUEST_METHOD_POST);

        foreach ($params as $key => $values) {
            if (!is_array($values)) {
                $values = array( $values );
            }
            foreach ($values as $value) {
                $http->addPostData($key, $value);
            }
        }
    
        $request_failed = true;
        //$max_attempts = 10;
        //for ($i = 1; $i <= $max_attempts; $i++) {
        
        /* Seems pointless to log this...
        error_log("max_attempts = " . $this->max_attempts);
        error_log("request_type = " . $this->request_type);
        */
    
        for ($i = 1; $i <= $this->max_attempts; $i++) {
            unset( $status );
            $status = $http->sendRequest();
            // This certainly needs tests!
            if (PEAR::isError($status)) { 
                //TODO: Added support for user-provided log file.
                // See http://us2.php.net/manual/en/function.error-log.php#80152
                error_log("Attempt $i: " . $status->getMessage()); 
                continue;
            } 
            // TODO: Add parsing of response to look for errors,
            // and throw exceptions if necessary.
            $code = (int) $http->getResponseCode();
            if ($code > 499 and $code < 600) {
                error_log("Attempt $i: NCBI Entrez server response: $code");
                continue;
            }
            $request_failed = false;
            break;
        }
        if ($request_failed) {
            throw new Exception(
                "Giving up on " . $this->util() . " after " . $this->max_attempts . " attempts."
            );
        }
        
        $result = $http->getResponseBody();
        //echo $result; 
        $formatter = $this->formatter();
        return $this->$formatter( $result );        
    }

    public function format_raw($result)
    {
        return $result;
    }

    public function format_simplexml($result)
    {
        return new SimpleXMLElement($result);
    }
} // end class NCBI_eUtil
