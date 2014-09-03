#!/usr/bin/php -q
<?php

require_once 'simpletest/autorun.php';
SimpleTest :: prefer(new TextReporter());
set_include_path('../php' . PATH_SEPARATOR . get_include_path());
require_once 'NCBI/ESearch.php';

ini_set('memory_limit', '512M');

//error_reporting( E_STRICT );

class NCBIClientTest extends UnitTestCase
{
    public function __construct()
    {
        $this->common_params = array
        (
         'email' => 'your@email.address',
         'db'    => 'pubmed',
         'tool'  => 'your-tool-name',
         'term'  => self::generate_search_terms(),
        );
    }

    public function test_search_count()
    {
        $es = new NCBI_ESearch(
            $this->common_params + array
            (
             //'max_attempts' => 5, // TODO: need to fix this...
             'rettype' => 'count',
            )
        );
        $this->assertIsA( $es, 'NCBI_ESearch' );

        $result = $es->search();
        $count = (int)$result->Count;
        $this->assertPattern('/^\d+$/', $count);
        $this->assertTrue( $count >= 1 );

        $es_date_range = new NCBI_ESearch(
            $this->common_params + array
            (
             //'max_attempts' => 5, // TODO: need to fix this...
             'rettype' => 'count',
             //'end_date' => 'xxxx/xx/xx', // defaults to current date
             'start_date' => '2005/01/01',
            )
        );
        $this->assertIsA( $es_date_range, 'NCBI_ESearch' );

        $result_date_range = $es_date_range->search();
        $count_date_range = (int)$result_date_range->Count;
        $this->assertPattern('/^\d+$/', $count_date_range);
        $this->assertTrue( $count_date_range >= 1 );

        // Since the date range search is restricted to fewer dates,
        // we should have received far fewer results.
        $this->assertTrue( $count_date_range < $count );
    }

    public function test_search_history()
    {
        $es = new NCBI_ESearch(
            //'max_attempts' => 5, // Is there a default for this?
            $this->common_params + array
            (
             'rettype' => 'xml',
             'usehistory' => 'y', // TODO: This is the default now, anyway...
            )
        );
        
        $result = $es->search();

        $count = (int)$result->Count;
        $this->assertTrue( $count >= 1 );

        $query_key = $result->QueryKey;
        $this->assertPattern('/^\d+$/', $query_key);
        
        $web_env = $result->WebEnv;
        $this->assertPattern('/^\S+$/', $web_env);
        // These WebEnv's are loooong...
        $this->assertTrue(strlen($web_env) > 25);
    }

    protected function generate_search_terms() {
    
        $terms = array
        (
        'Abortion' => '"abortion, criminal" [mh] OR ("Abortion, Induced" [mh] AND ("classification" [sh] OR "economics" [sh] OR "education" [sh] OR "history" [sh] OR "jurisprudence" [sh] OR "mortality" [sh] OR "standards" [sh] OR "statistics and numerical data" [sh] OR "trends" [sh])) OR "abortion applicants" [mh] OR ("abortion" AND "Attitude of Health Personnel" [mh])',
        );
        
        reset($terms);
        return join(' OR ', array_values($terms));
    }

} // end class NCBIClientTest
