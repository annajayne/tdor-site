<?php
    /**
     * Export country by country and category by category summaries by calendar year or TDoR period
     *
     */

    require_once('util/path_utils.php');                // For append_path()
    require_once('util/csv_exporter.php');

    /**
     * Class to export country by country or category by category summaries by period or year.
     *
     */
    class StatsExporter extends CsvExporter
    {
         /**
         * Constructor
         *
         * @param array $counts                  An array containing report counts.
         */
        public function __construct($counts)
        {
            $this->csv_rows = self::get_csv_data($counts);
        }


        /**
         * Get a line of CSV data for the specified row
         *
         * @param Report $row              An array containing the report counts for the current year, indexed by country name.
         * @return string                       The corresponding line of CSV data.
         */
        private function get_csv_data_line($row_title, $row)
        {
            $line = $row_title.self::COMMA.$row['total'];

            $keys = array_keys($row);

            foreach ($keys as $key)
            {
                if ($key != 'total')
                {
                    $count = $row[$key];

                    $line = $line . self::COMMA . $count;
                }
            }
            return $line;
        }


        /**
         * Get lines of CSV data for the specified reports
         *
         * @param array $rows                   An array containing report counts data for the specified ròws.
         * @return array                        An array containing the corresponding lines of CSV data.
         */
        private function get_csv_data($rows)
        {
            $header = '';

            $row_titles = array_keys($rows);

            $n = 0;

            $totals = [];

            foreach ($rows as $row)
            {
                $row_title = $row_titles[$n++];

                $keys = array_keys($row);

                if (empty($header))
                {
                    $header = 'Year/Period,Total';

                    foreach ($keys as $key)
                    {
                        if ($key != 'total')
                        {
                            $header = $header.self::COMMA.self::escape_field(ucwords($key));
                        }
                    }

                    $csv_rows[] = $header;
                }

                foreach ($keys as $key)
                {
                    $prev_count = $totals[$key];

                    $totals[$key] = $prev_count + $row[$key];
                }

                $csv_rows[] = self::get_csv_data_line($row_title, $row);
            }

            // Add totals
            $csv_rows[] = self::get_csv_data_line('Totals', $totals);

            return $csv_rows;
        }
    }


    class StatsExporterArchive
    {
        /** @var string                  Export folder path. */
        private  $export_folder;

        /** @var array                  The number of reports per country and calendar year. */
        private $yearly_country_counts;

        /** @var array                  The number of reports per category and calendar year. */

        private $yearly_category_counts;

        /** @var array                  The number of reports per country and TDoR period. */
        private $tdor_period_country_counts;

        /** @var array                  The number of reports per category and TDoR period. */
        private $tdor_period_category_counts;


        private $csv_file_pathnames;

        public function __construct($reports_table)
        {
            $query_params = null;
            $this->yearly_country_counts        = $reports_table->get_yearly_report_counts($query_params, BREAKDOWN_BY_COUNTRY);
            $this->yearly_category_counts       = $reports_table->get_yearly_report_counts($query_params, BREAKDOWN_BY_CATEGORY);

            $this->tdor_period_country_counts   = $reports_table->get_tdor_period_report_counts($query_params, BREAKDOWN_BY_COUNTRY);
            $this->tdor_period_category_counts  = $reports_table->get_tdor_period_report_counts($query_params, BREAKDOWN_BY_CATEGORY);
        }

        public function write($export_folder)
        {
            $this->export_folder = $export_folder;

            $this->csv_file_pathnames[]   = $this->write_csv_file($this->yearly_country_counts, $export_folder, 'yearly_reports_by_country');
            $this->csv_file_pathnames[]   = $this->write_csv_file($this->yearly_category_counts, $export_folder, 'yearly_reports_by_category');
            $this->csv_file_pathnames[]   = $this->write_csv_file($this->tdor_period_country_counts, $export_folder, 'tdor_period_reports_by_country');
            $this->csv_file_pathnames[]   = $this->write_csv_file($this->tdor_period_category_counts, $export_folder, 'tdor_period_reports_by_category');
        }


        /**
         * Create a zip archive of the exported CSV files at the specified location.
         *
         * @param string $zip_file_pathname           The pathname of the zip file to create.
         */
        public function create_zip_archive($zip_file_pathname)
        {
            $root_path              = get_root_path();

            $zip                    = new ZipArchive;

            $zip_file_full_pathname = append_path(get_root_path(), $zip_file_pathname);

            $OK                     = $zip->open($zip_file_full_pathname, ZipArchive::CREATE | ZipArchive::OVERWRITE);

            foreach ($this->csv_file_pathnames as $pathname)
            {
                $full_pathname      = append_path($root_path, $pathname);

                $zip->addFile($full_pathname, basename($pathname));
            }

            $zip->close();
        }


        // Implementation
        private function write_csv_file($counts, $export_folder, $basename)
        {
            $exporter = new StatsExporter($counts);

            $csv_file_pathname = "$export_folder/$basename.csv";
            $exporter->write_csv_file($csv_file_pathname);

            return $csv_file_pathname;
        }
    }


    if (!is_bot(get_user_agent()))
    {
        $ip = $_SERVER['REMOTE_ADDR'] . '_';

        if (strpos($ip, ':') !== false)
        {
            $ip = '';
        }

        $date = date("Y-m-d\TH_i_s");

        $basename = 'tdor_stats';
        $filename = $basename . '_' . $ip . $date;

        $export_folder = 'data/export';

        $zip_file_pathname      = "$export_folder/$filename.zip";

        $db = new db_credentials();
        $reports_table = new Reports($db);

        $exporter = new StatsExporterArchive($reports_table, $export_folder);
        $exporter->write($export_folder);

        $exporter->create_zip_archive($zip_file_pathname);

        ob_clean();
        ob_end_flush(); // Needed as otherwise Windows will report the zipfile to be corrupted (see https://stackoverflow.com/questions/13528067/zip-archive-sent-by-php-is-corrupted/13528263#13528263)

        // Download the export file
        header("Content-Description: File Transfer");
        header("Content-Type: applicaton/zip");
        header("Content-Disposition: attachment; filename=" . basename($zip_file_pathname));

        readfile($zip_file_pathname);

        exit();
    }

?>