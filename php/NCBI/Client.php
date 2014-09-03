<?php

require_once 'NCBI/ESearch.php';
require_once 'NCBI/EFetch.php';
require_once 'Moose.php';

class NCBI_Client extends Moose
{
    protected function properties()
    {
        self::has('db',         array('is' => 'protected', 'default' => 'pubmed',));
        self::has('email',      array('is' => 'protected',));

        // Identifies the software using the NCBI service, e.g. 'your-tool-name'
        self::has('tool',       array('is' => 'protected',));

        // Maps to ESearch 'term'. This gives it a more descriptive name.
        self::has('search_terms', array('is' => 'protected',));

        // For ESearch:
        self::has('start_date', array('is' => 'protected', 'required' => 0,));
        self::has('end_date', array('is' => 'protected', 'required' => 0,));

        // Maps to EFetch 'rettype'. This gives it a more descriptive name.
        //  TODO: other possibilities than 'citation'?
        self::has('record_type', array('is' => 'protected', 'default' => 'citation',));

        // TODO: For EFetch. Not sure I need to expose this via the Client. 
        //self::has('retmode',    array('is' => 'protected', 'default' => 'xml',));

        // 'max_records' is a limit on the total number of records to extract.
        // If not set, will default to total number of records returned by the search.
        self::has('max_records', array('is' => 'protected', 'required' => 0,));

        // If a search returns many records, NCBI won't let us download them all at once,
        // and PHP may run out of memory if we try to download more than the default.
        // TODO: Look up the NCBI limit; may be 1000.
        self::has('records_per_download', array('is' => 'protected', 'default' => 500,));

        // 'file_set' is a set of files of downloaded records.
        self::has('file_set',   array('is' => 'protected',));
    }

    public function extract()
    {
        $search_params = array(
            'db'    => $this->db(),
            'tool'  => $this->tool(),
            'email' => $this->email(),
            'term'  => $this->search_terms(),
        );

        foreach (array('start_date','end_date') as $property) {
            if ($this->$property()) {
                $search_params[$property] = $this->$property();
            }
        }

        $esearch = new NCBI_ESearch( $search_params );
        $result = $esearch->search(); 

        $search_count = (int) $result->Count; 
        $count = $search_count;
        $max_records = $this->max_records();
        if (isset($max_records) && $search_count > $max_records) {
            $count = $max_records;
        }
 
        $file_names = array(); 
        if ($count > 0) {
            $efetch = new NCBI_EFetch(array(
                'db'    => $this->db(),
                'tool'  => $this->tool(),
                'email' => $this->email(),
                'rettype' => $this->record_type(),
                'records_per_download' => $this->records_per_download(),
    
                'file_set' => $this->file_set(),
    
                'count' => $count,
                'query_key' => $result->QueryKey,
                'WebEnv' => $result->WebEnv,
            ));
            $file_names = $efetch->fetch(); 
        }

        return array
        (
         'file_names' => $file_names,
         'esearch'    => $esearch,
         'efetch'     => $efetch,
        );
    }

} // end class NCBI_Client
