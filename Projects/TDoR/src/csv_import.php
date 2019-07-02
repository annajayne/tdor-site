<?php
    /**
     * Import items from a CSV file generated from the official TDoR list.
     */



    /**
     * Class representing the columns in a TDoR CSV import file.
     */
    class tdor_csv_columns
    {
        // Keys for CSV file column indices
        public const NAME           = 'name';
        public const AGE            = 'age';
        public const PHOTO          = 'photo';
        public const PHOTO_SOURCE   = 'photo_source';
        public const DATE           = 'date';
        public const SOURCE_REF     = 'source_ref';
        public const LOCATION       = 'location';
        public const COUNTRY        = 'country';
        public const LATITUDE       = 'latitude';
        public const LONGITUDE      = 'longitude';
        public const CAUSE          = 'cause';
        public const DESCRIPTION    = 'desc';
        public const PERMALINK      = 'permalink';


        function get_indices($row)
        {
            $field = 0;

            $column_indices[self::NAME]                     = $field++;
            $column_indices[self::AGE]                      = $field++;
            $column_indices[self::PHOTO]                    = $field++;
            $column_indices[self::PHOTO_SOURCE]             = $field++;
            $column_indices[self::DATE]                     = $field++;
            $column_indices[self::SOURCE_REF]               = $field++;
            $column_indices[self::LOCATION]                 = $field++;

            if (count($row) >= 8)
            {
                // Check header to see if there is a "Country" field
                if ($row[7] === 'Country')
                {
                    $column_indices[self::COUNTRY]          = $field++;
                }

                if (count($row) >= 10)
                {
                    if ($row[$field] === 'Latitude')
                    {
                        $column_indices[self::LATITUDE]     = $field++;
                        $column_indices[self::LONGITUDE]    = $field++;
                    }
                }

                $column_indices[self::CAUSE]                = $field++;
                $column_indices[self::DESCRIPTION]          = $field++;
                $column_indices[self::PERMALINK]            = $field++;
            }
            return $column_indices;
        }

    }


    /**
     * Class representing a TDoR CSV file import item.
     */
    class tdor_csv_item
    {
        // e.g. Array ( [0] => Fany Diniz [1] => 30 [2] => [3] => 03-Jan-18 [4] => [5] => Belém (Brazil) [6] => shot [7] => Fany was shot dead by two men on a motorcycle at around 9 pm on Riachuelo Street in the Campina neighborhood of Belém. 3 days earlier another trans woman, Silvia Gomes Marques, had been killed at the same spot. Her murder was also attributed to two murderers on a motorcycle. https://homofobiamata.wordpress.com/2018/01/03/fany-diniz-trab-sexo-tiros-pa-belem/ http://www.thompsonmota.com.br/2018/01/travesti-e-morta-tiros-por.html http://www.portalparanews.com.br/noticia/pa/belem/policia/travesti-e-assassinada-no-centro-de-belem https://www.diarioonline.com.br/noticias/policia/noticia-476925-.html )

        /** @var string                  The UID (a hexadecimal number in string form) of the report. */
        public  $uid;

        /** @var string                  The name of the victim. */
        public  $name;

        /** @var string                  The age of the victim. */
        public  $age;

        /** @var string                  The filename of the victim's photo. */
        public  $photo_filename;

        /** @var string                  The source of the victim's photo. */
        public  $photo_source;

        /** @var string                  The date of death for the victim if known; otherwise the best guess based on available data. */
        public  $date;

        /** @var string                  A reference to the corresponding entry within the list the report appears in (e.g. TGEU or tdor.info) if any. */
        public  $source_ref;

        /** @var string                  The location (city, state etc.). */
        public  $location;

        /** @var double                  The latitude. */
        public  $latitude;

        /** @var double                  The longitude. */
        public  $longitude;

        /** @var string                  The country. */
        public  $country;

        /** @var string                  The cause of death if known. */
        public  $cause;

        /** @var string                  A textual description of what happened. */
        public  $description;

        /** @var string                  A permalink to the report. */
        public  $permalink;

        /** @var string                  The date the report was created. */
        public  $date_created;

        /** @var string                  The date the report was last updated. */
        public  $date_updated;



        /**
         * Determine whether the report has a location.
         *
         * @return boolean                  true if the report has a location; false otherwise.
         */
        function has_location()
        {
            return !empty($this->location) && ($this->location != '-') && ($this->location != $this->country);
        }
    }


    /**
     * Read the specified CSV file.
     *
     * @param string $filename      The filename of the CSV file.
     * @return int                  The number of items read.
     */
    function read_csv_file($filename)
    {
        $fp = fopen($filename,'r+');

        $csv_str = '';
        fwrite($fp, $csv_str);
        rewind($fp);                 // rewind to process CSV

        $row_no = 0;

        $items = array();

        $columns            = new tdor_csv_columns();

        $column_indices     = null;

        while ( ($row = fgetcsv($fp, 0) ) !== FALSE)
        {
            if ($row_no === 0)
            {
                $column_indices = $columns->get_indices($row);
            }

            if ( ($row_no > 0) && ($row[0] !== '') )
            {
                $item = new tdor_csv_item();

                $item->name                 = $row[$column_indices[$columns::NAME]];
                $item->age                  = $row[$column_indices[$columns::AGE]];
                $item->photo_filename       = $row[$column_indices[$columns::PHOTO]];
                $item->photo_source         = $row[$column_indices[$columns::PHOTO_SOURCE]];
                $item->date                 = $row[$column_indices[$columns::DATE]];

                $item->source_ref           = $row[$column_indices[$columns::SOURCE_REF]];
                $item->location             = $row[$column_indices[$columns::LOCATION]];

                $item->cause                = $row[$column_indices[$columns::CAUSE]];
                $item->description          = $row[$column_indices[$columns::DESCRIPTION]];
                $item->permalink            = $row[$column_indices[$columns::PERMALINK]];

                $country_index              = $column_indices[$columns::COUNTRY];
                $latitude_index             = $column_indices[$columns::LATITUDE];
                $longitude_index            = $column_indices[$columns::LONGITUDE];

                if ($country_index != null)
                {
                    $item->country          = $row[$country_index];
                }

                if ( ($latitude_index != null) && ($longitude_index != null) )
                {
                    $latitude_str           = $row[$latitude_index];
                    $longitude_str          = $row[$longitude_index];

                    if (!empty($latitude_str) )
                    {
                        $item->latitude     = floatval($latitude_str);
                        $item->longitude    = floatval($longitude_str);
                    }
                }

                // Workaround for dates of the form "17/May/2018", which will otherwise fail to parse [Anna 14.11.2018]
                $item->date = str_replace('/', '-', $item->date);

                // If the source ref is not empty and starts with a numeric (which we assume is the beginning of a date), prepend "tgeu/".
                if (!empty($item->source_ref) )
                {
                    if (ctype_digit($item->source_ref[0]) )
                    {
                        $item->source_ref = 'tgeu/'.$item->source_ref;
                    }
                }

                $location = explode('(', $item->location);

                if (count($location) === 2)
                {
                    $country = explode(')', $location[1]);

                    if (count($country) === 2)
                    {
                        $item->location = trim($location[0]);
                        $item->country  = trim($country[0]);
                    }
                }

                // Parse the permalink and extract the uid (or "slug")
                //
                // e.g. 'http://localhost:8286/index.php?category=reports&action=show&uid=905872ca'
                // or   'http://tdor.translivesmatter.info/reports/<year><month>/<day>/name_location_country-uid'
                $query = parse_url($item->permalink, PHP_URL_QUERY);

                if (!empty($query) )
                {
                    $params = array();
                    parse_str($query, $params);

                    $item->uid = $params['uid'];
                }
                else
                {
                    $uid_len = 8;
                    if (strlen($item->permalink) > $uid_len)
                    {
                        $item->uid = substr($item->permalink, -$uid_len);
                    }
                }

                $items[$row_no] = $item;
            }

            ++$row_no;
        }
        return $items;
    }

?>