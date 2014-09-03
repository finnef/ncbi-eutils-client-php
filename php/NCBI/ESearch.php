<?php

require_once 'NCBI/eUtil.php';
require_once 'Moose.php';

class NCBI_ESearch extends Moose
{

    protected function properties()
    {
        self::has('email',      array('is' => 'protected',));
        self::has('db',         array('is' => 'protected', 'default' => 'pubmed',));
        self::has('tool',       array('is' => 'protected',));

        // other values: 'count', ...?
        self::has('rettype',    array('is' => 'protected', 'default' => 'xml',));

        self::has('usehistory', array('is' => 'protected', 'default' => 'y',));
        self::has('term',       array('is' => 'protected',));

        self::has('start_date', array('is' => 'protected', 'required' => 0,));
        self::has('end_date', array('is' => 'protected', 'required' => 0,));

        self::has('eutil',
            array
            (
             'is' => 'protected',
             'default_function' => create_function(
                 '',
                 'return new NCBI_eUtil(array("format" => "simplexml", "util" => "search"));'
             ),
            )
        );
        self::has('common_params',
            array
            (
             'is' => 'protected',
             'default' => array('email','db','tool','rettype','usehistory','term',),
            )
        );
    }
    
    protected function BUILD()
    {
        if ($this->end_date() && !($this->start_date())) {
            throw new Exception("An end_date requires a start_date");
        }

        if ($this->start_date()) {
            $date_range = '(' . $this->start_date() . ':';
            if ($this->end_date()) {
                $end_date = $this->end_date();
            } else {
                $end_date = date('Y/m/d', time());
            }
            $date_range .= $end_date . '[edat])';
            $this->set_term( $this->term() . " AND $date_range" );
        }
    }

    function search() {
        $params = array();
        foreach ($this->common_params() as $param) {
            $params[$param] = $this->$param();
        }
        return $this->eutil()->send_request($params);
    }

} // end class NCBI_ESearch
