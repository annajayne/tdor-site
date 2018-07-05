<?php
    require_once('misc.php');

    class Exporter
    {
        const COMMA         = ',';
        const QUOTE         =  '"';
        const TWO_QUOTES    = '""';
        const TRANS_FLAG    = 'trans_flag.jpg';

        private $csv_rows;
        private $photo_filenames;


        public function __construct($reports)
        {
            $this->csv_rows = self::get_csv_data($reports);
        }


        private function escape_field($field)
        {
            $field = str_replace(self::QUOTE, self::TWO_QUOTES, $field);

            if ( (strpos($field, ',') !== false) ||
                 (strpos($field, "\n") !== false) )
            {
                return self::QUOTE.$field.self::QUOTE;
            }
            return $field;
        }


        private function get_csv_data_line($report)
        {
            $photo_filename = !empty($report->photo_filename) ? $report->photo_filename : self::TRANS_FLAG;

            $line = self::escape_field($report->name).self::COMMA.
                    self::escape_field($report->age).self::COMMA.
                    self::escape_field($photo_filename).self::COMMA.
                    self::escape_field($report->photo_source).self::COMMA.
                    self::escape_field(date_str_to_display_date($report->date) ).self::COMMA.
                    self::escape_field($report->tgeu_ref).self::COMMA.
                    self::escape_field($report->location).self::COMMA.
                    self::escape_field($report->country).self::COMMA.
                    self::escape_field($report->cause).self::COMMA.
                    self::escape_field($report->description).self::COMMA.
                    self::escape_field(get_host().$report->permalink);

            return $line;
        }


        private function get_csv_data($reports)
        {
            $csv_rows[] = 'Name,Age,Photo,Photo source,Date,TGEU ref,Location,Country,Cause of death,Description,Permalink';

            foreach ($reports as $report)
            {
                $csv_rows[] = self::get_csv_data_line($report);

                if (!empty($report->photo_filename) )
                {
                    $this->photo_filenames[] = $report->photo_filename;
                }
            }
            return $csv_rows;
        }


        public function get_csv_rows()
        {
            return $this->csv_rows;
        }


        public function get_csv_text()
        {
            $text = '';

            foreach ($this->csv_rows as $line)
            {
                $text .= $line.PHP_EOL;
            }
            return $text;
        }


        public function write_csv_file($pathname)
        {
            $fp = fopen($pathname, 'w');

            if ($fp)
            {
                fwrite($fp, pack("CCC",0xef, 0xbb, 0xbf) );             // Add UTF-8 BOM
                fwrite($fp, self::get_csv_text() );

                fclose($fp);

                return true;
            }
            return false;
        }


        public function create_zip_archive($zip_file_pathname, $csv_file_pathname = '', $csv_file_path_in_zip_file = '')
        {
            $folder = dirname(__FILE__);

            $zip = new ZipArchive;

            $zip->open($zip_file_pathname, ZipArchive::CREATE | ZipArchive::OVERWRITE);

            if (!empty($csv_file_pathname) )
            {
                $zip->addFile($folder.'/'.$csv_file_pathname, $csv_file_path_in_zip_file);
            }

            $OK = $zip->addFile($folder.'/images/victim_default_photo.jpg', self::TRANS_FLAG);

            foreach ($this->photo_filenames as $photo_filename)
            {
                $zip->addFile($folder.'/data/photos/'.$photo_filename, 'photos/'.$photo_filename);
            }

            $zip->close();
        }

    }

?>