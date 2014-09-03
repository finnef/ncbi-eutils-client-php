#!/usr/bin/php -q
<?php

set_include_path('../php' . PATH_SEPARATOR . get_include_path());
require_once 'NCBI/EFetch/ByID.php';
require_once 'File/Set/DateSequence.php';

ini_set('memory_limit', '512M');

//error_reporting( E_STRICT );

$file_set = new File_Set_DateSequence(array(
    //'directory' => getcwd() . '/fetched_by_id',
    //'directory' => getcwd() . '/nanoethics',
    //'directory' => getcwd() . '/date_discrepancy',
    'directory' => getcwd() . '/one_off',
    'suffix' => '.xml',
));

// Clean out any already-existing files, e.g. from previous test runs:
$file_set->clear();

$ef = new NCBI_EFetch_ByID(array(
    'email' => 'your@email.address',
    'db'    => 'pubmed',
    'tool'  => 'your-tool-name',
    //'ids' => array('13579126','13494634','13064353','13004201','12983522','12983491'), // PMIDs
    //'ids' => array('19998099'), // PMIDs
    'ids' => array(
        // Nanoethics PMIDs
        /*
        '17033654',
        '11702180',
        '17826113',
        '17254762',
        '18825403',
        '17454354',
        '16639314',
        '16723880',
        '17245366',
        '17193067',
        '19142939',
        '17997181',
        '16440317',
        '16582920',
        */
        // Not sure what these were... More Nanoethics?
        /*
        '19672990',
        '20040559',
        '16686751',
        '16728555',
        */
        // EthicShare date different than PubMed date.
        //'18427955', 
        /*
        '18546061',
        '18563629',
        '19685170',
        */
        // Not in PubMed?
        //'20883430',
        // field_datetime_published contains only the year, field_datetime_epublished is more precise:
        '20231248',
    ),
    'rettype' => 'citation',
    'retmode' => 'xml',
    'file_set' => $file_set,
));

$ef->fetch();
