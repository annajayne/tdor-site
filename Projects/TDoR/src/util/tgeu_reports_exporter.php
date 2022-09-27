<?php
    /**
     * Export the specified reports in TGEU format. TEMPORARY IMPLEMENTATION
     *
     */


    require_once('util/misc.php');
    require_once('views/display_utils.php');
    require_once('util/csv_exporter.php');



    /**
     * Class to export reports.
     *
     */
    class TgeuReportsExporter extends CsvExporter
    {
        /** @var boolean                    Whether draft reports should be included. */
        private $show_report_status;

        /** @var array                      Array of photo filenames to add to the zipfile. */
        private $photo_filenames;

        /** @var array                      Array of thumbnail filenames to add to the zipfile. */
        private $thumbnail_filenames;

        /** @var array                      Array of qrcode filenames to add to the zipfile. */
        private $qrcode_filenames;

        /** @var array                      Associative map of country to subregion & region. */
        private $regions;


         /**
         * Constructor
         *
         * @param array $reports                    An array of reports to export.
         */
        public function __construct($reports)
        {
            $this->show_report_status = is_editor_user() || is_admin_user();

            // Read tgeu_country_regions.csv
            $this->regions = [];

            $is_heading = true;
            $csvFile = file('./util/tgeu_country_regions.csv');

            foreach ($csvFile as $line)
            {
                $row = str_getcsv($line);
                if ($is_heading)
                {
                    $is_heading = false;
                    continue;
                }

                $this->regions[$row[0]] = [$row[1], $row[2]]; // Country => Subregion, region
            }

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
            $newline                = "\n";

            $desc_and_links         = $this->split_report_desc($report);

            $photo_field            = $report->photo_filename;

            if (!empty($photo_field) && !empty($report->photo_source) )
            {
                $photo_field .= $newline.$newline."Source: ".$report->photo_source;
            }

            $incident_type_field    = $report->category;

            $homicide_type_field    = ($report->cause !== 'murdered') ? $report->cause : 'not reported';

            $subregion              = $this->regions[$report->country][0];
            $region                 = $this->regions[$report->country][1];

            $short_desc             = get_short_description($report, -1);
            $full_desc              = $desc_and_links[0];
            $links                  = get_host().$report->permalink;

            // Add the full description to the notes field
            $notes_field = $desc_and_links[0];

            if (!empty($report->birthdate) )
            {
                // Append the birthdate if we have one
                $notes_field .= $newline.$newline."$report->name was born on ".date_str_to_display_date($report->birthdate).".";
            }

            if (!empty($desc_and_links[1]) )
            {
                // Extract links & add after the permalink (search backwards through the description until a line is found which doesn't start with http:// or https://).
                $links .= $newline.$newline.$desc_and_links[1];
            }

            $line = self::COMMA.                                                                // Code
                    self::COMMA.                                                                // Date added
                    self::COMMA.                                                                // Template
                    self::escape_field($report->country).self::COMMA.                           // Country/territory
                    self::escape_field($subregion).self::COMMA.                                 // Subregion
                    self::escape_field($region).self::COMMA.                                    // Region
                    self::escape_field($report->location).self::COMMA.                          // City
                    self::escape_field($photo_field).self::COMMA.                               // Photo
                    self::escape_field($report->name).self::COMMA.                              // Name of the victim
                    self::COMMA.                                                                // Legal name of the victim (private information)
                    self::COMMA.                                                                // Sex assigned at birth (private information)
                    self::COMMA.                                                                // Gender identity or expression
                    self::COMMA.                                                                // Occupation
                    self::COMMA.                                                                // Migrant status
                    self::COMMA.                                                                // Nationality
                    self::COMMA.                                                                // Race or ethnicity
                    self::escape_field($report->age).self::COMMA.                               // Age range
                    self::COMMA.                                                                // Connection to LGBTIQ communities
                    self::COMMA.                                                                // Short description (local language)
                    self::escape_field($short_desc).self::COMMA.                                // Short description (English)
                    self::escape_field(date_str_to_display_date($report->date) ).self::COMMA.   // Date of the incident
                    self::COMMA.                                                                // Time of the incident
                    self::COMMA.                                                                // Type of the incident
                    self::escape_field($homicide_type_field).self::COMMA.                       // Type of homicide/murder
                    self::COMMA.                                                                // Type of location of the murder
                    self::COMMA.                                                                // Context of the incident (local language)
                    self::COMMA.                                                                // Context of the incident (English)
                    self::COMMA.                                                                // Number of perpetrators
                    self::COMMA.                                                                // Type of perpetrator(s)
                    self::COMMA.                                                                // Description of perpetrators (local language)
                    self::COMMA.                                                                // Description of perpetrators (English)
                    self::COMMA.                                                                // Basis for bias
                    self::COMMA.                                                                // Bias indicators
                    self::COMMA.                                                                // Clarification of bias indicator (local language)
                    self::COMMA.                                                                // Clarification of bias indicator (English)
                    self::COMMA.                                                                // Source of information
                    self::escape_field($links).self::COMMA.                                     // Links to source of information
                    self::COMMA.                                                                // Reported by
                    self::COMMA.                                                                // File (pdf or image) of the information reported
                    self::COMMA.                                                                // Response from local authorities
                    self::COMMA.                                                                // Details on response from local authorities (local language)
                    self::COMMA.                                                                // Details on response from local authorities (English)
                    self::COMMA.                                                                // Other institutions the case was reported to
                    self::COMMA.                                                                // Report details (local language or English)
                    self::COMMA.                                                                // Court case initiation
                    self::COMMA.                                                                // Court case description
                    self::COMMA.                                                                // Court decision
                    self::COMMA.                                                                // Link of the Court decision or case status
                    self::escape_field($notes_field).self::COMMA.                               // Notes
                    self::escape_field($report->latitude.', '.$report->longitude).self::COMMA.  // Geolocation
                    self::COMMA.                                                                // Documents
                    self::COMMA;                                                                // Attachments
                    self::COMMA;                                                                // Published

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
            $columns = [
                'Code (leave blank)',
                'Date added (leave blank)',
                'Template (leave blank)',
                'Country/Territory of the murder',
                'Subregion',
                'Region',
                'City of the murder',
                'Photo of the victim',
                'Name of the victim',
                'Legal name of the victim (private information)',
                'Sex assigned at birth (private information)',
                'Gender identity or expression',
                'Occupation',
                'Migrant status',
                'Nationality',
                'Race or ethnicity',
                'Age range',
                'Connection to LGBTIQ communities',
                'Short description (local language)',
                'Short description (English)',
                'Date of the incident',
                'Time of the incident',
                'Type of incident',
                'Type of homicide/murder',
                'Type of location of the murder',
                'Context of the incident (local language)',
                'Context of the incident (English)',
                'Number of perpetrators',
                'Type of perpetrator(s)',
                'Description of perpetrators (local language)',
                'Description of perpetrators (English)',
                'Basis for bias',
                'Bias indicators',
                'Clarification of bias indicator (local language)',
                'Clarification of bias indicator (English)',
                'Source of information',
                'Links to source of information',
                'Reported by',
                'File (pdf or image) of the information reported',
                'Response from local authorities',
                'Details on response from local authorities (local language)',
                'Details on response from local authorities (English)',
                'Other institutions the case was reported to',
                'Report details (local language or English) ',
                'Court case initiation',
                'Court case description',
                'Court decision',
                'Link of the Court decision or case status',
                'Notes',
                'Geolocation',
                'Documents',
                'Attachments',
                'Published'
                ];

            $header = '';

            foreach ($columns as $column)
            {
                $header .= $column.',';
            }

            $csv_rows[] = $header;

            foreach ($reports as $report)
            {
                if ($report->draft)
                {
                    continue;
                }

                if ($report->category != 'violence')
                {
                    continue;
                }

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
         * Get the links from the specified report
         *
         * @param Report $report                      The specified report.
         * @return array                              An array containing the description (first element) and links (second element).
         */
        private function split_report_desc($report)
        {
            $newline = "\n";

            // Read $report->description; search backwards for lines NOT starting with http:// or https://;
            $lines = preg_split("/\r\n|\n|\r/", $report->description);

            $desc  = '';
            $links = '';

            $total_lines = count($lines);

            if ($total_lines > 0)
            {
                for ($n = $total_lines - 1; $n >= 0; --$n)
                {
                    $line = $lines[$n];

                    if (!empty($line) && !str_begins_with($line, 'https://') && !str_begins_with($line, 'http://') )
                    {
                        $desc  = trim(implode($newline, array_slice($lines, 0, $n) ) );

                        if ( (n + 1) < ($total_lines - 1) )
                        {
                            $links = trim(implode($newline, array_slice($lines, $n + 1, $total_lines - 1) ) );
                        }
                        break;
                    }
                    else if ($n == 0)
                    {
                        // Special case if the "desc" field ONLY contains links
                        $links = $report->description;
                    }
                }
            }
            return array($desc, $links);
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

            // Add photos
            if (!empty($this->photo_filenames) )
            {
                foreach ($this->photo_filenames as $photo_filename)
                {
                    $zip->addFile($folder.'/data/photos/'.$photo_filename, 'photos/'.$photo_filename);
                }
            }

            $zip->close();
        }

    }

?>