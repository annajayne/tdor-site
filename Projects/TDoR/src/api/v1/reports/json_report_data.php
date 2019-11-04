<?php
    /**
     * JSON implementation file.
     *
     */



    /**
     * JSON data class for a single report.
     *
     */
    class JsonReportDataSummary implements JsonData
    {
        /** @var string                  The uid (a hexadecimal number in string form) of the report. */
        public  $uid;

        /** @var string                  The name of the victim. */
        public  $name;

        /** @var string                  The age of the victim. */
        public  $age;

        /** @var string                  The date of death for the victim if known; otherwise the best guess based on available data. */
        public  $date;

        /** @var boolean                 true if there is a photo associated with this report; false otherwise. */
        public  $has_photo;

        /** @var string                  The url of the thumbnail for the report. */
        public  $thumbnail_url;

        /** @var string                  A reference to the corresponding entry within the list the report appears in (e.g. TGEU or tdor.info) if any. */
        public  $source_ref;

        /** @var string                  The location (city, state etc.). */
        public  $location;

        /** @var string                  The country. */
        public  $country;

        /** @var double                  The latitude. */
        public  $latitude;

        /** @var double                  The longitude. */
        public  $longitude;

        /** @var string                  The cause of death if known. */
        public  $cause;

        /** @var string                  A permalink to the report. */
        public  $permalink;

        /** @var string                  The url of the QR code to the report. */
        public  $qrcode_url;

        /** @var string                  The date the report was created. */
        public  $date_created;

        /** @var string                  The date the report was last updated. */
        public  $date_updated;



        function set_from_report($report) 
        {
            $host = get_host();

            $data = "$host/data";

            $this->uid                  = $report->uid;
            $this->name                 = $report->name;
            $this->age                  = $report->age;
            $this->date                 = $report->date;
            $this->has_photo            = !empty($report->photo_filename) ? true : false;
            $this->thumbnail_url        = !empty($report->photo_filename) ? "$data/thumbnails/$report->photo_filename" : "$host/images/trans_flag.jpg";
            $this->source_ref           = $report->source_ref;
            $this->location             = $report->location;
            $this->country              = $report->country;
            $this->latitude             = $report->latitude;
            $this->longitude            = $report->longitude;
            $this->cause                = $report->cause;
            $this->permalink            = $host.get_permalink($report);
            $this->qrcode_url           = !empty($report->uid) ? "$data/qrcodes/$report->uid.png" : '';
            $this->date_created         = $report->date_created;
            $this->date_updated         = $report->date_updated;
        }
    
    }



    /**
     * JSON data class for a single report.
     *
     */
    class JsonReportData extends JsonReportDataSummary
    {
        /** @var string                 The url of the victim's photo. */
        public  $photo_url;

        /** @var string                 The source of the victim's photo. */
        public  $photo_source;

        /** @var string                 A textual description of what happened. */
        public  $description;

        /** @var string                 The text of a tweet describing the report. If not specified, default text will be generated and used. */
        public  $tweet;
 
  
        function set_from_report($report) 
        {
            $host = get_host();

            $data = "$host/data";

            parent::set_from_report($report);
            
            $this->photo_url            = !empty($report->photo_filename) ? "$data/photos/$report->photo_filename" : '';
            $this->photo_source         = $report->photo_source;
            $this->description          = $report->description;

            if (!empty($report->tweet) )
            {
                $host                   = get_host();
                $newline                = "\n";

                $this->tweet            = $report->tweet.$newline.$newline.$host.get_permalink($report);
            }
        }

    }


?>