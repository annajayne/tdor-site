<?php

    declare(strict_types=1);

    use PHPUnit\Framework\TestCase;


    final class ExporterTest extends TestCase
    {
        private $test_output_folder;
        private $test_report;


        public function setUp()
        {
            $this->test_output_folder = 'tests/output';

            if (!file_exists($this->test_output_folder) )
            {
                mkdir($this->test_output_folder);
            }

            $this->test_report = new Report;

            $this->test_report->name                   = "Anna-Jayne Metcalfe";
            $this->test_report->age                    = '19 (I wish!!)';
            $this->test_report->photo_filename         = '2018-06-14_Anna.jpg';
            $this->test_report->photo_source           = 'Facebook';
            $this->test_report->date                   = '14 Jun 2018';
            $this->test_report->tgeu_ref               = 'n/a';
            $this->test_report->location               = 'Bournemouth, Dorset';
            $this->test_report->country                = 'United Kingdom';
            $this->test_report->cause                  = 'Still here';
            $this->test_report->description            = 'multiline\nwaffle with "a quote"';
            $this->test_report->permalink              = '/test/annajayne';
        }


        public function tearDown()
        {
        }


        public function test_header()
        {
            $exporter                       = new Exporter(array() );

            $csv_rows                       = $exporter->get_csv_rows();

            $expected                       = 'Name,Age,Photo,Photo source,Date,TGEU ref,Location,Country,Cause of death,Description,Permalink';

            $this->assertEquals($expected,  $csv_rows[0]);
        }


        public function test_single_report_csv()
        {
            $exporter                       = new Exporter(array($this->test_report) );

            $csv_rows                       = $exporter->get_csv_rows();

            $expected                       = 'Anna-Jayne Metcalfe,19 (I wish!!),2018-06-14_Anna.jpg,Facebook,14 Jun 2018,n/a,"Bournemouth, Dorset",United Kingdom,Still here,multiline\nwaffle with ""a quote"",http://tdor.translivesmatter.info/test/annajayne';

            $this->assertEquals(2,          count($csv_rows) );
            $this->assertEquals($expected,  $csv_rows[1]);
        }


        public function test_write_csv()
        {
            $pathname = $this->test_output_folder.'/test_write_csv.csv';

            // If the test file exists, try to delete it
            if (file_exists($pathname) )
            {
                $this->assertEquals(true, unlink($pathname) );
            }

            // Make sure that the test file no longer exists
            $this->assertEquals(false, file_exists($pathname) );


            // Create test data
            $exporter = new Exporter(array($this->test_report) );

            // Write the generated CSV to disk
            $this->assertEquals(true, $exporter->write_csv_file($pathname) );

            // Check that the file exists and that the contents are the same as the raw CSV
            $this->assertEquals(true, file_exists($pathname) );

            $fp = fopen($pathname, 'r');
            $this->assertEquals(true, $fp ? true : false);
            $contents = fread($fp, filesize($pathname) );

            // Check the UTF-8 BOM and the contents of the file
            $this->assertEquals(pack("CCC", 0xef, 0xbb, 0xbf),  substr($contents, 0, 3) );
            $this->assertEquals($exporter->get_csv_text(),      substr($contents, 3) );
        }


        public function test_create_zipfile()
        {
            $csv_file_pathname = $this->test_output_folder.'/test_create_zipfile.csv';
            $zip_file_pathname = $this->test_output_folder.'/test_create_zipfile.zip';

            // If the test file exists, try to delete it
            if (file_exists($csv_file_pathname) )
            {
                $this->assertEquals(true, unlink($csv_file_pathname) );
            }

            if (file_exists($zip_file_pathname) )
            {
                $this->assertEquals(true, unlink($zip_file_pathname) );
            }

            // Make sure that the test files no longer exist
            $this->assertEquals(false, file_exists($csv_file_pathname) );
            $this->assertEquals(false, file_exists($zip_file_pathname) );

            // Create test data
            $exporter = new Exporter(array($this->test_report) );

            // Write the generated CSV to disk
            $this->assertEquals(true, $exporter->write_csv_file($csv_file_pathname) );

            // Check that the CSV file exists
            $this->assertEquals(true, file_exists($csv_file_pathname) );

            $exporter->create_zip_archive($zip_file_pathname);

            // Make sure that the zip fileexists
            $this->assertEquals(true, file_exists($zip_file_pathname) );
        }


    }

?>