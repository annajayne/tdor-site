<?php
    /**
     * Report utilities.
     *
     */

    require_once('exporter.php');



    /**
     * Report utility functions.
     *
     */
    class ReportUtils
    {
        /**
         * Create an export zipfile.
         *
         * @param Report $reports               The reports to export.
         * @param Report $filename              The filename (without extension) of the export file.
         * @param Report $export_folder         The path of the export folder.
         * @return boolean                      true if OK; false otherwise.
         */
        public static function create_export_zipfile($reports, $filename, $export_folder)
        {
            // Generate the export file
            //
            $exporter           = new Exporter($reports);

            $root               = $_SERVER["DOCUMENT_ROOT"];

            $csv_file_pathname  = "$export_folder/$filename.csv";
            $zip_file_pathname  = "$export_folder/$filename.zip";

            $exporter->write_csv_file($csv_file_pathname);
            $exporter->create_zip_archive("$root/$zip_file_pathname", $csv_file_pathname, "$filename.csv");

            ob_clean();
            ob_end_flush(); // Needed as otherwise Windows will report the zipfile to be corrupted (see https://stackoverflow.com/questions/13528067/zip-archive-sent-by-php-is-corrupted/13528263#13528263)

            return $zip_file_pathname;
        }


    }

?>
