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
        const NAME               = 'name';
        const AGE                = 'age';
        const BIRTHDATE          = 'birthdate';
        const PHOTO              = 'photo';
        const PHOTO_SOURCE       = 'photo_source';
        const DATE               = 'date';
        const TDOR_LIST_REF      = 'tdor_list_ref';
        const LOCATION           = 'location';
        const ADDRESS            = 'address';
        const LOCALITY           = 'locality';
        const CITY               = 'town_or_city';
        const PROVINCE           = 'state_or_province';
        const COUNTRY            = 'country';
        const LATITUDE           = 'latitude';
        const LONGITUDE          = 'longitude';
        const CATEGORY           = 'category';
        const CAUSE              = 'cause';
        const DESCRIPTION        = 'desc';
        const TWEET              = 'tweet';
        const PERMALINK          = 'permalink';
        const STATUS             = 'status';


        function get_indices($row)
        {
            $field = 0;

            $column_indices[self::NAME]                     = $field++;
            $column_indices[self::AGE]                      = $field++;

            if (strpos($row[$field], 'Birthdate') !== FALSE)
            {
                // Is there a "Birthdate" column?
                $column_indices[self::BIRTHDATE]            = $field++;
            }

            $column_indices[self::PHOTO]                    = $field++;
            $column_indices[self::PHOTO_SOURCE]             = $field++;
            $column_indices[self::DATE]                     = $field++;
            $column_indices[self::TDOR_LIST_REF]            = $field++;


            // Check header to see if there is an "Address" field
            if (strpos($row[$field], 'Address') !== FALSE)
            {
                $column_indices[self::ADDRESS]              = $field++;
            }

            // Check header to see if there is a "Locality" field
            if (strpos($row[$field], 'Locality') !== FALSE)
            {
                $column_indices[self::LOCALITY]             = $field++;
            }

            // Check header to see if there is a "City" or "Municipality" field
            if ( (strpos($row[$field], 'City') !== FALSE) ||  (strpos($row[$field], 'Municipality') !== FALSE) )
            {
                $column_indices[self::CITY]                 = $field++;
            }
            else
            {
                // Town/City replaces "Location"
                $column_indices[self::LOCATION]          = $field++;
            }

            // Check header to see if there is a "State/Province" field
            if (strpos($row[$field], 'State') !== FALSE)
            {
                $column_indices[self::PROVINCE]             = $field++;
            }

            // Check header to see if there is a "Country" field
            if ($row[$field] === 'Country')
            {
                $column_indices[self::COUNTRY]              = $field++;
            }

            if ($row[$field] === 'Latitude')
            {
                $column_indices[self::LATITUDE]             = $field++;
                $column_indices[self::LONGITUDE]            = $field++;
            }

            // Check header to see if there is a "Category" field
            if ($row[$field] === 'Category')
            {
                $column_indices[self::CATEGORY]             = $field++;
            }

            $column_indices[self::CAUSE]                    = $field++;
            $column_indices[self::DESCRIPTION]              = $field++;

            // Check header to see if there is a "Tweet" field
            if (strpos($row[$field], 'Tweet') !== FALSE)
            {
                $column_indices[self::TWEET]                = $field++;
            }

            $column_indices[self::PERMALINK]                = $field++;

            // Check to see if there is a "Status" field
            if ( (count($row) > $field) && (strpos($row[$field], 'Status') !== FALSE) )
            {
                $column_indices[self::STATUS]               = $field++;
            }
            return $column_indices;
        }

    }


    /**
     * Class representing a TDoR CSV file import item.
     */
    class tdor_csv_item
    {
        // e.g. Array ( [0] => Fany Diniz [1] => 30 [2] => [3] => 03-Jan-18 [4] => [5] => Bel�m (Brazil) [6] => shot [7] => Fany was shot dead by two men on a motorcycle at around 9 pm on Riachuelo Street in the Campina neighborhood of Bel�m. 3 days earlier another trans woman, Silvia Gomes Marques, had been killed at the same spot. Her murder was also attributed to two murderers on a motorcycle. https://homofobiamata.wordpress.com/2018/01/03/fany-diniz-trab-sexo-tiros-pa-belem/ http://www.thompsonmota.com.br/2018/01/travesti-e-morta-tiros-por.html http://www.portalparanews.com.br/noticia/pa/belem/policia/travesti-e-assassinada-no-centro-de-belem https://www.diarioonline.com.br/noticias/policia/noticia-476925-.html )

        /** @var string                  The UID (a hexadecimal number in string form) of the report. */
        public  $uid;

        /** @var boolean                 true if the report is a draft. */
        public  $draft;

        /** @var string                  The name of the victim. */
        public  $name;

        /** @var string                  The age of the victim. */
        public  $age;

        /** @var string                  The birthdate of the victim. */
        public  $birthdate;

        /** @var string                  The filename of the victim's photo. */
        public  $photo_filename;

        /** @var string                  The source of the victim's photo. */
        public  $photo_source;

        /** @var string                  The date of death for the victim if known; otherwise the best guess based on available data. */
        public  $date;

        /** @var string                  A reference to the corresponding entry within the TDoR list (if any) the report appears in (e.g. TGEU or tdor.info). */
        public  $tdor_list_ref;

        /** @var string                  The location (city, state etc.). */
        public  $location;

        /** @var string                  The state or province. */
        public  $province;

        /** @var string                  The country. */
        public  $country;

        /** @var double                  The latitude. */
        public  $latitude;

        /** @var double                  The longitude. */
        public  $longitude;

        /** @var string                  The catgory. */
        public  $category;

        /** @var string                  The cause of death if known. */
        public  $cause;

        /** @var string                  A textual description of what happened. */
        public  $description;

         /** @var string                 The text of a tweet describing the report. If not specified, default text will be generated. */
        public  $tweet;

        /** @var string                  A permalink to the report. */
        public  $permalink;

       /** @var string                   The date the report was created. */
        public  $date_created;

        /** @var string                  The date the report was last updated. */
        public  $date_updated;


        /**
         * Constructor
         *
         */
        public function __construct()
        {
                $this->draft = false;
        }


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

                $item->name                 = trim($row[$column_indices[$columns::NAME]]);
                $item->age                  = trim($row[$column_indices[$columns::AGE]]);

                if (array_key_exists($columns::BIRTHDATE, $column_indices) )
                {
                    $item->birthdate        = trim($row[$column_indices[$columns::BIRTHDATE]]);
                }

                $item->photo_filename       = trim($row[$column_indices[$columns::PHOTO]]);
                $item->photo_source         = trim($row[$column_indices[$columns::PHOTO_SOURCE]]);
                $item->date                 = trim($row[$column_indices[$columns::DATE]]);

                $item->tdor_list_ref        = trim($row[$column_indices[$columns::TDOR_LIST_REF]]);

                // The location and town/city can currently be specified as a single "Location" field or separate "Address", "Locality" and "Town/City" fields.
                //
                // As we parse the latter we write the town/city into the tdor_csv_item::location property.
                // Note that the "Address" and "Locality" fields are not yet used by the site so are skipped during the import.
                if (array_key_exists($columns::CITY, $column_indices) )
                {
                    $item->location         = trim($row[$column_indices[$columns::CITY]]);
                }
                else
                {
                    $item->location         = trim($row[$column_indices[$columns::LOCATION]]);
                }

                if (array_key_exists($columns::CATEGORY, $column_indices) )
                {
                    $item->category         = trim($row[$column_indices[$columns::CATEGORY]]);
                }

                $item->cause                = trim($row[$column_indices[$columns::CAUSE]]);

                $item->cause                = trim($row[$column_indices[$columns::CAUSE]]);
                $item->description          = trim($row[$column_indices[$columns::DESCRIPTION]]);
                $item->permalink            = trim($row[$column_indices[$columns::PERMALINK]]);

                $province_index             = null;
                $country_index              = null;
                $latitude_index             = null;
                $longitude_index            = null;
                $tweet_index                = null;
                $status_index               = null;

                if (array_key_exists($columns::PROVINCE, $column_indices) )
                {
                    $province_index         = trim($column_indices[$columns::PROVINCE]);
                }
                if (array_key_exists($columns::COUNTRY, $column_indices) )
                {
                    $country_index          = trim($column_indices[$columns::COUNTRY]);
                }
                if (array_key_exists($columns::LATITUDE, $column_indices) )
                {
                    $latitude_index         = $column_indices[$columns::LATITUDE];
                }
                if (array_key_exists($columns::LONGITUDE, $column_indices) )
                {
                    $longitude_index        = $column_indices[$columns::LONGITUDE];
                }
                if (array_key_exists($columns::TWEET, $column_indices) )
                {
                    $tweet_index            = trim($column_indices[$columns::TWEET]);
                }
                if (array_key_exists($columns::STATUS, $column_indices) )
                {
                    $status_index           = trim($column_indices[$columns::STATUS]);
                }

                if ($province_index != null)
                {
                    $item->province         = trim($row[$province_index]);
                }

                if ($country_index != null)
                {
                    $item->country          = trim($row[$country_index]);
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

                if ($tweet_index != null)
                {
                    $item->tweet            = trim($row[$tweet_index]);
                }

                if ( ($status_index != null) && ($row[$status_index] == 'Draft') )
                {
                    $item->draft            = true;
                }

                // Workaround for dates of the form "17/May/2018", which will otherwise fail to parse [Anna 14.11.2018]
                $item->date = str_replace('/', '-', $item->date);

                // If the TDoR list ref is not empty and starts with a numeric (which we assume is the beginning of a date), prepend "tgeu/".
                if (!empty($item->tdor_list_ref) )
                {
                    if (ctype_digit($item->tdor_list_ref[0]) )
                    {
                        $item->tdor_list_ref = 'tgeu/'.$item->tdor_list_ref;
                    }
                }

                $location = explode('(', $item->location);

                if (count($location) === 2)
                {
                    // Code to handle legacy locations of the form "City, State (Country)"
                    $country = explode(')', $location[1]);

                    if (count($country) === 2)
                    {
                        $item->location = trim($location[0]);
                        $item->country  = trim($country[0]);
                    }
                }

                // If the province is specified, append it to the location
                // NB this is temporary until the province field is supported in the database.
                if (!empty($item->province) && (strpos($item->location, $item->province) === FALSE) )
                {
                    $item->location = !empty($item->location) ? ($item->location.', '.$item->province) : $item->province;
                    $item->province = '';
                }

                // Parse the permalink and extract the uid (or "slug")
                //
                // e.g. 'http://localhost:8286/index.php?controller=reports&action=show&uid=905872ca'
                // or   'http://tdor.translivesmatter.info/reports/<year><month>/<day>/name_location-country_uid'
                $query = parse_url($item->permalink, PHP_URL_QUERY);

                if (!empty($query) )
                {
                    $params = array();

                    parse_str($query, $params);

                    if (!empty($params['uid']) )
                    {
                        $item->uid = $params['uid'];
                    }
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