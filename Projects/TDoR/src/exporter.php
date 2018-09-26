<?php
    /**
     * Export the specified reports.
     *
     */


    require_once('misc.php');


    /**
     * Class to export reports.
     *
     */
    class Exporter
    {
        /**  A comma. */
        const COMMA         = ',';

        /**  A double quote (i.e. "). */
        const QUOTE         =  '"';

        /**  A pair of double quote (i.e. ""). */
        const TWO_QUOTES    = '""';

        /**  The filename of an image representing the trans flag. */
        const TRANS_FLAG    = 'trans_flag.jpg';


        /** @var array                      Array of rows of CSV data. */
        private $csv_rows;

        /** @var array                      Array of photo filenames to add to the zipfile. */
        private $photo_filenames;

        /** @var array                      Array of thumbnail filenames to add to the zipfile. */
        private $thumbnail_filenames;

        /** @var array                      Array of qrcode filenames to add to the zipfile. */
        private $qrcode_filenames;


         /**
         * Constructor
         *
         * @param array $reports                    An array of reports to export.
         */
        public function __construct($reports)
        {
            $this->csv_rows = self::get_csv_data($reports);
        }


        /**
         * Quote the given field if it contains any commas or newlines.
         *
         * @param string $field                       A string containing the given field value.
         * @return string                             The contents of $field, quoted if necessary.
         */
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


        /**
         * Get a line of CSV data for the specified report
         *
         * @param Report $report                      The specified report.
         * @return string                             The corresponding line of CSV data.
         */
        private function get_csv_data_line($report)
        {
            $photo_filename     = !empty($report->photo_filename) ? "photos/$report->photo_filename" : '';
            $photo_thumbnail    = !empty($report->photo_filename) ? "thumbnails/$report->photo_filename" : self::TRANS_FLAG;
            $qrcode_filename    = !empty($report->uid) ? "qrcodes/$report->uid.png" : '';

            $line = self::escape_field($report->name).self::COMMA.
                    self::escape_field($report->age).self::COMMA.
                    self::escape_field($photo_filename).self::COMMA.
                    self::escape_field($report->photo_source).self::COMMA.
                    self::escape_field($photo_thumbnail).self::COMMA.
                    self::escape_field(date_str_to_display_date($report->date) ).self::COMMA.
                    self::escape_field($report->tgeu_ref).self::COMMA.
                    self::escape_field($report->location).self::COMMA.
                    self::escape_field($report->country).self::COMMA.
                    self::escape_field($report->cause).self::COMMA.
                    self::escape_field($report->description).self::COMMA.
                    self::escape_field(get_host().$report->permalink).self::COMMA.
                    self::escape_field($qrcode_filename);

            return $line;
        }


        /**
         * Get lines of CSV data for the specified reports
         *
         * @param array $reports                      An array containing the specified reports.
         * @return array                              An array containing the corresponding lines of CSV data.
         */
        private function get_csv_data($reports)
        {
            $csv_rows[] = 'Name,Age,Photo,Photo source,Thumbnail,Date,TGEU ref,Location,Country,Cause of death,Description,Permalink,QR code';

            foreach ($reports as $report)
            {
                $csv_rows[] = self::get_csv_data_line($report);

                if (!empty($report->photo_filename) )
                {
                    $this->photo_filenames[]        = $report->photo_filename;
                    $this->thumbnail_filenames[]    = $report->photo_filename;
                }

                if (!empty($report->uid) )
                {
                    $this->qrcode_filenames[]       = "$report->uid.png";
                }
            }
            return $csv_rows;
        }


        /**
         * Get an array of CSV text lines.
         *
         * @return array                             An array of lines of CSV text.
         */
        public function get_csv_rows()
        {
            return $this->csv_rows;
        }


        /**
         * Get the CSV text.
         *
         * @return sting                              The CSV text.
         */
        public function get_csv_text()
        {
            $text = '';

            foreach ($this->csv_rows as $line)
            {
                $text .= $line.PHP_EOL;
            }
            return $text;
        }


        /**
         * Write the CSV file.
         *
         * @param string $pathname                    The pathname of the CSV file to create.
         * @return boolean                            true if written OK; false otherwise.
         */
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


        /**
         * Create a zip archive containing the given CSV file and any photos it references.
         *
         * @param string $zip_file_pathname           The pathname of the zip file to create.
         * @param string $csv_file_pathname           The pathname of the CSV file.
         * @param string $csv_file_path_in_zip_file   The path of the CSV file within the zip file.
         */
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


            // Add support files - photos, thumbnails and QR codes.
            if (!empty($this->photo_filenames) )
            {
                foreach ($this->photo_filenames as $photo_filename)
                {
                    $zip->addFile($folder.'/data/photos/'.$photo_filename, 'photos/'.$photo_filename);
                }
             }

            if (!empty($this->thumbnail_filenames) )
            {
                foreach ($this->thumbnail_filenames as $thumbnail_filename)
                {
                    $zip->addFile($folder.'/data/thumbnails/'.$thumbnail_filename, 'thumbnails/'.$thumbnail_filename);
                }
             }

            if (!empty($this->qrcode_filenames) )
            {
                foreach ($this->qrcode_filenames as $qrcode_filename)
                {
                    $zip->addFile($folder.'/data/qrcodes/'.$qrcode_filename, 'qrcodes/'.$qrcode_filename);
                }
             }

            $zip->close();
        }

    }

?>