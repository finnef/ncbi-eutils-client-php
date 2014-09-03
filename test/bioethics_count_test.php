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

        echo "count = $count\n";
    }

    protected function generate_search_terms() {
    
        $terms = array
        (
        'Basic' => 'bioethics[ALL]',
        );
        
        reset($terms);
        return join(' OR ', array_values($terms));
    }

} // end class NCBIClientTest
