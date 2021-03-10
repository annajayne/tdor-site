<?php
    /**
     * Export the specified reports.
     *
     */


    require_once('misc.php');
    require_once('display_utils.php');
    require_once('util/csv_exporter.php');



    /**
     * Class to export reports.
     *
     */
    class ReportsExporter extends CsvExporter
    {
        /**  The filename of an image representing the trans flag. */
        const TRANS_FLAG = 'trans_flag.jpg';

        /** @var boolean                    Whether draft reports should be included. */
        private $show_report_status;

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
            $this->show_report_status = is_editor_user() || is_admin_user();

            $this->csv_rows = self::get_csv_data($reports);
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

            $summary_text       = get_summary_text($report);
            $tweet_text         = !empty($report->tweet) ? $report->tweet : $summary_text['desc'];

            $line = self::escape_field($report->name).self::COMMA.
                    self::escape_field($report->age).self::COMMA.
                    self::escape_field(date_str_to_display_date($report->birthdate) ).self::COMMA.
                    self::escape_field($photo_filename).self::COMMA.
                    self::escape_field($report->photo_source).self::COMMA.
                    self::escape_field($photo_thumbnail).self::COMMA.
                    self::escape_field(date_str_to_display_date($report->date) ).self::COMMA.
                    self::escape_field($report->tdor_list_ref).self::COMMA.
                    self::escape_field($report->location).self::COMMA.
                    self::escape_field($report->country).self::COMMA.
                    self::escape_field($report->country_code).self::COMMA.
                    self::escape_field($report->latitude).self::COMMA.
                    self::escape_field($report->longitude).self::COMMA.
                    self::escape_field($report->category).self::COMMA.
                    self::escape_field($report->cause).self::COMMA.
                    self::escape_field($report->description).self::COMMA.
                    self::escape_field($tweet_text).self::COMMA.
                    self::escape_field(get_host().$report->permalink).self::COMMA.
                    self::escape_field($qrcode_filename);

            if ($this->show_report_status)
            {
                $line .= self::COMMA.($report->draft ? 'Draft' : 'Published');
            }
            return $line;
        }


        /**
         * Get lines of CSV data for the specified reports
         *
         * @param array $reports                      An array containing CSV data for the specified reports.
         * @return array                              An array containing the corresponding lines of CSV data.
         */
        private function get_csv_data($reports)
        {
            $header = 'Name,Age,Birthdate,Photo,Photo source,Thumbnail,Date,TDoR list ref,Location,Country,Country Code,Latitude,Longitude,Category,Cause of death,Description,Tweet,Permalink,QR code';

            if ($this->show_report_status)
            {
                $header .= ',Status';
            }

            $csv_rows[] = $header;

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
         * Create a zip archive containing the given CSV file and any photos it references.
         *
         * @param string $zip_file_pathname           The pathname of the zip file to create.
         * @param string $csv_file_pathname           The pathname of the CSV file.
         * @param string $csv_file_path_in_zip_file   The path of the CSV file within the zip file.
         */
        public function create_zip_archive($zip_file_pathname, $csv_file_pathname = '', $csv_file_path_in_zip_file = '')
        {
            $folder = get_root_path();

            $zip = new ZipArchive;

            $OK = $zip->open($zip_file_pathname, ZipArchive::CREATE | ZipArchive::OVERWRITE);

            if (!empty($csv_file_pathname) )
            {
                $zip->addFile($folder.'/'.$csv_file_pathname, $csv_file_path_in_zip_file);
            }

            $OK = $zip->addFile($folder.'/images/trans_flag.jpg', self::TRANS_FLAG);


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