#!/usr/bin/php -q
<?php

require_once 'simpletest/autorun.php';
SimpleTest :: prefer(new TextReporter());
set_include_path('../php' . PATH_SEPARATOR . get_include_path());
require_once 'NCBI/ESearch.php';
require_once 'NCBI/EFetch.php';
require_once 'NCBI/EFetch/ByID.php';
require_once 'File/Set/DateSequence.php';

ini_set('memory_limit', '512M');

//error_reporting( E_STRICT );

class NCBIEFetchTest extends UnitTestCase
{
    public function __construct()
    {
        $this->term = self::generate_search_terms();
        $this->common_params = array
        (
         'email' => 'your@email.address',
         'tool'  => 'your-tool-name',
        );

        $this->es = new NCBI_ESearch(
            $this->common_params + array
            (
             'db'    => 'pubmed',
             //'max_attempts' => 5, // TODO: need to fix this...
             'rettype' => 'xml',
             'usehistory' => 'y', // TODO: This is the default now, anyway...
             'start_date' => '2005/01/01',
             //'end_date' => 'xxxx/xx/xx', // defaults to current date
             'term'  => $this->term,
            )
        );

        $this->result = $this->es->search();
        $this->count = (int) $this->result->Count;
        $this->query_key = $this->result->QueryKey;
        $this->web_env = $this->result->WebEnv;
    }

    public function test_fetch()
    {
        // Sanity check. Tried to do this in the constructor, but it
        // fails there, probably due to incomplete initialization of SimpleTest
        // at that point.
        $this->assertTrue( $this->count > 0 );

        $file_set = new File_Set_DateSequence(array(
            'directory' => getcwd() . '/fetched',
            'suffix' => '.xml',
        ));

        $ef = new NCBI_EFetch(
            $this->common_params + array
            (
             //'db'    => 'pubmed', // 'pubmed' is the default.
             'rettype' => 'citation',
             'retmode' => 'xml',
             'records_per_download' => 1000,
             'file_set' => $file_set,
             'query_key' => $this->query_key,
             'WebEnv' => $this->web_env,
             'count' => $this->count,
            )
        );
        $this->assertIsA( $ef, 'NCBI_EFetch' );

        // Clean out any already-existing files, e.g. from previous test runs:
        $file_set->clear();

        $ef->fetch();
        $file_names = $file_set->members();

        $download_count = 0;
        foreach ($file_names as $file_name) {
            $xml = simplexml_load_file( $file_name );
            $download_count += count(array_keys( $xml->xpath('//PubmedArticle') ));
        }
        $this->assertTrue( $download_count == $this->count );

        // Clean up:
        $file_set->clear();
    }

    public function test_fetch_by_id()
    {
        $file_set = new File_Set_DateSequence(array(
            'directory' => getcwd() . '/fetched_by_id',
            'suffix' => '.xml',
        ));

        // Clean out any already-existing files, e.g. from previous test runs:
        $file_set->clear();

        $ef = new NCBI_EFetch_ByID(
            $this->common_params + array
            (
             'db'    => 'pubmed',
             'ids' => array('18650511','18647987'), // PMIDs
             'rettype' => 'citation',
             'retmode' => 'xml',
             'file_set' => $file_set,
            )
        );

        $ef->fetch();
        $file_names = $file_set->members();

        $download_count = 0;
        foreach ($file_names as $file_name) {
            $xml = simplexml_load_file( $file_name );
            $download_count += count(array_keys( $xml->xpath('//PubmedArticle') ));
        }
        $this->assertTrue( $download_count == 2 );

        // Clean up:
        $file_set->clear();
    }

    public function test_fetch_by_id_nlmcatalog()
    {
        $file_set = new File_Set_DateSequence(array(
            'directory' => getcwd() . '/fetched_by_id_nlmcatalog',
            'suffix' => '.xml',
        ));

        // Clean out any already-existing files, e.g. from previous test runs:
        $file_set->clear();

        $ef = new NCBI_EFetch_ByID(
            $this->common_params + array
            (
             'ids' => array('101566287','101565471'), // NlmUniqueID's
             'db' => 'nlmcatalog',
             'rettype' => 'citation',
             'retmode' => 'xml',
             'file_set' => $file_set,
            )
        );

        $ef->fetch();
        $file_names = $file_set->members();

        $download_count = 0;
        foreach ($file_names as $file_name) {
            $xml = simplexml_load_file( $file_name );
            $download_count += count(array_keys( $xml->xpath('//NlmUniqueID') ));
        }
        $this->assertTrue( $download_count == 2 );

        // Clean up:
        $file_set->clear();
    }

    protected function generate_search_terms() {
    
        $terms = array
        (
        'Abortion' => '"abortion, criminal" [mh] OR ("Abortion, Induced" [mh] AND ("classification" [sh] OR "economics" [sh] OR "education" [sh] OR "history" [sh] OR "jurisprudence" [sh] OR "mortality" [sh] OR "standards" [sh] OR "statistics and numerical data" [sh] OR "trends" [sh])) OR "abortion applicants" [mh] OR ("abortion" AND "Attitude of Health Personnel" [mh])',
        );
        
        reset($terms);
        return join(' OR ', array_values($terms));
    }

} // end class NCBIEFetchTest
