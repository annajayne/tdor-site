<?php
    /**
     * Import items from a CSV file generated from the official TDoR list.
     */


    /**
     * Class representing a TDoR import item.
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

        /** @var string                  A reference within the official TGEU data if known. */
        public  $tgeu_ref;

        /** @var string                  The location (city, state etc.). */
        public  $location;

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

        $has_country_field = false;

        while ( ($row = fgetcsv($fp, 0) ) !== FALSE)
        {
            if ( ($row_no === 0) && (count($row) >= 8) )
            {
                // Check header to see if there is a "Country" field
                $has_country_field = ($row[7] === 'Country');
            }

            if ( ($row_no > 0) && ($row[0] !== '') )
            {
                $item = new tdor_csv_item();

                $field = 0;

                $item->name             = $row[$field++];
                $item->age              = $row[$field++];
                $item->photo_filename   = $row[$field++];
                $item->photo_source     = $row[$field++];
                $item->date             = $row[$field++];
                $item->tgeu_ref         = $row[$field++];
                $item->location         = $row[$field++];

                if ($has_country_field)
                {
                    $item->country      = $row[$field++];
                }

                $item->cause            = $row[$field++];
                $item->description      = $row[$field++];
                $item->permalink        = $row[$field++];

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
                // or   'http://tdor.annasplace.me.uk/reports/<year><month>/<day>/name_location_country-uid'
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