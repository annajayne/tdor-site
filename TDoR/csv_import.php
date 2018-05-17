<?php
    class tdor_csv_item
    {
        // e.g. Array ( [0] => Fany Diniz [1] => 30 [2] => [3] => 03-Jan-18 [4] => [5] => Belém (Brazil) [6] => shot [7] => Fany was shot dead by two men on a motorcycle at around 9 pm on Riachuelo Street in the Campina neighborhood of Belém. 3 days earlier another trans woman, Silvia Gomes Marques, had been killed at the same spot. Her murder was also attributed to two murderers on a motorcycle. https://homofobiamata.wordpress.com/2018/01/03/fany-diniz-trab-sexo-tiros-pa-belem/ http://www.thompsonmota.com.br/2018/01/travesti-e-morta-tiros-por.html http://www.portalparanews.com.br/noticia/pa/belem/policia/travesti-e-assassinada-no-centro-de-belem https://www.diarioonline.com.br/noticias/policia/noticia-476925-.html )
        public  $uid;
        public  $name;
        public  $age;
        public  $photo_filename;
        public  $photo_source;
        public  $date;
        public  $tgeu_ref;
        public  $location;
        public  $country;
        public  $cause;
        public  $description;
        public  $description_html;
        public  $permalink;
    }


    function read_csv_file($filename)
    {
        $fp = fopen($filename,'r+');

        fwrite($fp, $CsvString);
        rewind($fp); //rewind to process CSV

        $row_no = 0;

        $items = array();

        while ( ($row = fgetcsv($fp, 0) ) !== FALSE)
        {
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
                $item->cause            = $row[$field++];
                $item->description      = $row[$field++];
                $item->description_html = $row[$field++];
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