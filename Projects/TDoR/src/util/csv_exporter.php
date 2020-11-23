<?php
    /**
     * Abstract class to export CSV data.
     *
     */


    /**
     * Abstract class to export CSV data.
     *
     */
    class CsvExporter
    {
        /**  A comma. */
        const COMMA         = ',';

        /**  A double quote (i.e. "). */
        const QUOTE         =  '"';

        /**  A pair of double quote (i.e. ""). */
        const TWO_QUOTES    = '""';

        /** @var array                              Array of rows of CSV data. */
        protected $csv_rows;


        /**
         * Quote the given field if it contains any commas or newlines.
         *
         * @param string $field                     A string containing the given field value.
         * @return string                           The contents of $field, quoted if necessary.
         */
        public function escape_field($field)
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
         * Get an array of CSV text lines.
         *
         * @return array                            An array of lines of CSV text.
         */
        public function get_csv_rows()
        {
            return $this->csv_rows;
        }


        /**
         * Get the CSV text.
         *
         * @return string                           The CSV text.
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
         * @param string $pathname                  The pathname of the CSV file to create.
         * @return boolean                          true if written OK; false otherwise.
         */
        public function write_csv_file($pathname)
        {
            $fp = fopen($pathname, 'w');

            if ($fp)
            {
                fwrite($fp, pack("CCC", 0xef, 0xbb, 0xbf) );             // Add UTF-8 BOM
                fwrite($fp, self::get_csv_text() );

                fclose($fp);

                return true;
            }
            return false;
        }


    }
?>