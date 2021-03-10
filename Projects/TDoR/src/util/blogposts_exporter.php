<?php
    /**
     * Export the specified blogposts.
     *
     */


    require_once('misc.php');
    require_once('display_utils.php');
    require_once('util/csv_exporter.php');



    /**
     * Class to export reports.
     *
     */
    class BlogpostsExporter extends CsvExporter
    {
        /** @var array                                  Image filenames. */
        public $image_filenames;



        /**
         * Constructor
         *
         * @param array $blogposts                      An array of blogposts to export.
         */
        public function __construct($blogposts)
        {
            $this->csv_rows = self::get_csv_data($blogposts);
        }


        /**
         * Get a line of CSV data for the specified blogpost
         *
         * @param Blogpost $blogpost                  The specified blogpost.
         * @return string                             The corresponding line of CSV data.
         */
        private function get_csv_data_line($blogpost)
        {
            $line = self::escape_field($blogpost->title).self::COMMA.
                    self::escape_field($blogpost->author).self::COMMA.
                    self::escape_field($blogpost->timestamp).self::COMMA.
                    self::escape_field($blogpost->thumbnail_filename).self::COMMA.
                    self::escape_field($blogpost->thumbnail_caption).self::COMMA.
                    self::escape_field($blogpost->content).self::COMMA.
                    self::escape_field($blogpost->permalink).self::COMMA.
                    self::escape_field($blogpost->draft).self::COMMA.
                    self::escape_field($blogpost->deleted);

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
            $csv_rows[] = 'Title,Author,Timestamp,Thumbnail Filename, Thumbnail Caption,Content,Permalink,Draft,Deleted';

            foreach ($reports as $report)
            {
                $csv_rows[] = self::get_csv_data_line($report);
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

            // Add support files - photos, thumbnails and QR codes.
            if (!empty($this->image_filenames) )
            {
                foreach ($this->image_filenames as $image_filename)
                {
                    if ( ($image_filename != '.') && ($image_filename != '..') )
                    {
                        $zip->addFile($folder.'/data/blog/images/'.$image_filename, 'images/'.$image_filename);
                    }
                }
            }

            $zip->close();
        }

    }

?>