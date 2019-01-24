<?php
    /**
     * Sitemap generation classes.
     */



    /**
     * Class representing a sitemap URL.
     */
    class SitemapUrl
    {
        /** @var string                  The URL. */
        public  $url;

        /** @var string                  The timestamp. */
        public  $timestamp;

        /** @var string                  How often the URL is updated - can be always, hourly, daily, weekly, monthly, yearly or never. */
        public  $change_freq;


        public function __construct($url, $timestamp, $change_freq)
        {
            $this->url          = $url;
            $this->timestamp    = $timestamp;
            $this->change_freq  = $change_freq;
        }
    }


    /**
     * Class to generate a sitemap.
     *
     * Note that a single sitemap must have no more than 50,000 URLs and must be no larger than 10MB (10,485,760 bytes), whether compressed or not.
     */
    class SitemapGenerator
    {
        /** @var array                  An array of SitemapUrl objects. */
        private  $urls;


        private function generate_url($url)
        {
            $newline = "\n";

            echo     '  <url>'.$newline;
            echo     '    <loc>'.$url->url.'</loc>'.$newline;;

            if (!empty($url->timestamp) )
            {
                echo '    <lastmod>'.$url->timestamp.'</lastmod>'.$newline;;
            }
            if (!empty($url->change_freq) )
            {
                echo '    <changefreq>'.$url->change_freq.'</changefreq>'.$newline;;
            }
            echo     '  </url>'.$newline;
        }


        public function generate()
        {           
            $newline = "\n";

            header('Content-type: application/xml');

            echo '<?xml version="1.0" encoding="UTF-8" ?>'.$newline;
            echo '<urlset xmlns="http://www.google.com/schemas/sitemap/0.84" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.google.com/schemas/sitemap/0.84 http://www.google.com/schemas/sitemap/0.84/sitemap.xsd">'.$newline;

            if (!empty($this->urls) )
            {
                foreach ($this->urls as $url)
                {
                    $this->generate_url($url);
                }
            }

            echo '</urlset>'.$newline;
        }


        function add($url, $timestamp = '', $change_freq = '')
        {
            $url            = new SitemapUrl(rtrim($url, '/'), $timestamp, $change_freq);

            $this->urls[]   = $url;
        }

    }

?>