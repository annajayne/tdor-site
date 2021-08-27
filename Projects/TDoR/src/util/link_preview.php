<?php
    /**
     *  Link preview implementation.
     */
    require_once('util/string_utils.php');
    require_once('util/path_utils.php');
    require_once('models/page_metadata_table.php');



    /**
     * Determine the most appropriate file extension for the specified image.
     *
     * @param string $image_url          The URL of the image.
     */
    function get_image_ext($image_url)
    {
        $ext = '';

        $type = @exif_imagetype($image_url);

        switch ($type)
        {
            case IMAGETYPE_GIF:         $ext = 'gif';       break;
            case IMAGETYPE_JPEG:        $ext = 'jpg';       break;
            case IMAGETYPE_PNG:         $ext = 'png';       break;
            case IMAGETYPE_WEBP:        $ext = 'webp';      break;
            default:                                        break;
        }
        return $ext;
    }


    /**
     *  Link preview metadata for a page.
     */
    class LinkPreviewMetadata
    {
        /** @var string                         The name of the site. */
        public  $site_name;

        /** @var string                         The title of the page. */
        public  $title;

        /** @var string                         A description of the page. */
        public  $description;

        /** @var string                         The URL of the page. */
        public  $url;

        /** @var string                         The URL of the associated link preview image, if any. */
        public  $image_url;


        /**
         * Constructor
         *
         */
        public function __construct()
        {
            $this->site_name        = '';
            $this->title            = '';
            $this->description      = '';
            $this->url              = '';
            $this->image_url        = '';
        }


    }


    /**
     *  Link preview metadata for a page.
     */
    class LinkPreviewMetadataReader
    {
        /** @var string                         The URL to read metadata from. */
        public $url          = null;


        /**
         * Constructor
         *
         * @param string $url                   The URL to read the link preview for.
         */
        public function __construct($url)
        {
            $url_bits = parse_url($url);

            if (empty($url_bits['scheme']) )
            {
                $this->url = append_path(get_host(), $url);
            }
            else
            {
                $this->url = $url;
            }
        }


        /**
         * Read the meta tags from the given page content
         *
         * @param string $page_content          The HTML for the page.
         */
        private function read_meta_tags($page_content)
        {
            $meta_tags = [];

            $doc = new DOMDocument();

            @$doc->loadHTML($page_content);

            $meta = $doc->getElementsByTagName('meta');

            foreach ($meta as $element)
            {
                $tag = [];
                foreach ($element->attributes as $node)
                {
                    $tag[$node->name] = $node->value;
                }
                $meta_tags[]= $tag;
            }
            return $meta_tags;
        }


        /**
         * Get the metadata read from the URL.
         *
         * @return LinkPreviewMetadata          The metadata for the URL.
         */
        public function get_metadata()
        {
            // Read the contents of the linked page. Note that we need to be careful
            // here if running in a single threaded server (e.g. under PHP Tools)
            // as a query to ourself will hang - hence we skip URLs of that type.
            //
            // Note that this should NEVER happen in a production environment,
            // as the server will be multithreaded by necessity.
            //
            if ( (get_host() != raw_get_host() ) && str_begins_with($url, get_host() ) )
            {
                return false;
            }

            $context = stream_context_create(
                                                array(
                                                    "http" => array(
                                                        "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36"
                                                    )
                                                )
                                            );

            $page_content = @file_get_contents($this->url, false, $context);

            if ($page_content !== false)
            {
                $metadata           = new LinkPreviewMetadata();

                $meta_tags          = $this->read_meta_tags($page_content);

                $metadata->url      = $this->url;
                $metadata->host     = parse_url($this->url, PHP_URL_HOST);

                foreach ($meta_tags as $tag)
                {
                    $tag_content    = isset($tag['content']) ? $tag['content'] : '';

                    if ( (isset($tag['name']) ) && ($tag['name'] === 'description') )
                    {
                        $metadata->description = $tag_content;
                    }
                    else if (isset($tag['property']) )
                    {
                        switch ($tag['property'])
                        {
                            case 'og:site_name':
                                $metadata->site_name = $tag_content;
                                break;

                            case 'og:title':
                            case 'twitter:title':
                                $metadata->title = $tag_content;
                                break;

                            case 'og:description':
                            case 'twitter:description':
                                $metadata->description = $tag_content;
                                break;

                            case 'og:image':
                            case 'twitter:image':
                                $metadata->image_url = $tag_content;
                                break;

                            case 'og:url':
                                $metadata->url = $tag_content;
                                break;

                            default:
                                break;
                        }
                    }
                }

                if (empty($metadata->site_name) )
                {
                    $metadata->site_name    = parse_url($this->url, PHP_URL_HOST);
                }
                if (empty($metadata->title) )
                {
                    $metadata->title        = $metadata->site_name;
                }

                if (empty($metadata->title) )
                {
                    $title_pattern = '/<title>(.+)<\/title>/i';
                    $title = '';
                    preg_match_all($title_pattern, $page_content, $title, PREG_PATTERN_ORDER);

                    if (!is_array($title[1]) )
                    {
                        $metadata->title = $title[1];
                    }
                    else
                    {
                        if (count($title[1]) > 0)
                        {
                            $metadata->title = $title[1][0];
                        }
                    }
                }

                if (empty($metadata->image_url) )
                {
                    // If a link preview image URL was not found in the meta tags look for one in the content
                    $img_pattern = '/<img[^>]*'.'src=[\"|\'](.*)[\"|\']/Ui';

                    $images = [];
                    preg_match_all($img_pattern, $page_content, $images, PREG_PATTERN_ORDER);

                    $total_images = count($images[1]);
                    if ($total_images > 0)
                    {
                        $images = $images[1];
                    }

                    foreach ($images as $image_url)
                    {
                        $metadata->host;
                        if (!parse_url($image_url, PHP_URL_HOST) )
                        {
                            $image_url = append_path(parse_url($this->url, PHP_URL_SCHEME).'://'.parse_url($this->url, PHP_URL_HOST), $image_url);
                        }

                        $image_properties = getimagesize($image_url);

                        if ($image_properties)
                        {
                            list($width, $height, $type, $attr) = $image_properties;

                            if ($width >= 600) // Select an image of at least 600px width
                            {
                                $metadata->image_url = $image_url;
                                break;
                            }
                        }
                    }
                }
                return $metadata;
            }
            return false;
        }

    }


    /**
     *  Link preview cache class.
     */
    class LinkPreviewCache
    {
        /** @var db_credentials                 The credentials of the database. */
        public $db;



        /**
         * Constructor
         */
        public function __construct()
        {
            $this->db = new db_credentials();
        }


        /**
         * Has page metadata been cached for the specified URL?
         *
         * @param string $url                           The URL.
         * @return boolean                              true if the metadata for the URL exists in the cache, false otherwise.
         */
        public function is_cached($url)
        {
            $url_parts = parse_url($url);

            if (empty($url_parts['scheme']) )
            {
                $url = append_path(get_host(), $url);
            }

            $metadata_table = new PageMetadataTable($this->db);

            if ($metadata_table->get_metadata($url) != null)
            {
                return true;
            }
            return false;
        }


        /**
         * Retrieve the page metadata for the specified URL.
         *
         * @param string $url                           The URL.
         * @return LinkPreviewMetadata                  The metadata if found, or false otherwise.
         */
        public function get_cached_metadata($url)
        {
            $url_parts = parse_url($url);

            if (empty($url_parts['scheme']) )
            {
                $url = append_path(get_host(), $url);
            }

            $metadata_table = new PageMetadataTable($this->db);

            $item = $metadata_table->get_metadata($url);

            if ($item != null)
            {
                $page_metadata                  = new LinkPreviewMetadata();

                $page_metadata->url             = $item->url;
                $page_metadata->host            = $item->host;
                $page_metadata->site_name       = $item->site_name;
                $page_metadata->title           = $item->title;
                $page_metadata->description     = $item->description;
                $page_metadata->image_url       = $item->image_url;

                return $page_metadata;
            }
            return false;
        }


        /**
         * Cache the specified page metadata.
         *
         * @param string $url                           The URL from which the metadata was read.
         * @param LinkPreviewMetadata $page_metadata    The metadata of the page.
         * @return LinkPreviewMetadata                  The metadata, with the image thumbail URL updated to reference a local copy in the cache folder.
         */
        public function cache_metadata($url, $page_metadata)
        {
            $url_parts = parse_url($url);

            if (empty($url_parts['scheme']) )
            {
                $url = append_path(get_host(), $url);
            }

            $existing_item  = false;
            $metadata_table = new PageMetadataTable($this->db);

            $item           = $metadata_table->get_metadata($url);

            if ( ($item != null) && !empty($item->uid) )
            {
                $existing_item = true;
            }

            if (!$existing_item)
            {
                $item       = new PageMetadataItem();
                $item->uid  = get_random_hex_string();

                //if (!empty($page_metadata->image_url) )
                //{
                //    // Copy the thumbnail file locally (using a unique filename for the url to avoid name clashes)
                //    $thumbnail_ext                  = get_image_ext($page_metadata->image_url);

                //    if (empty($thumbnail_ext) )
                //    {
                //        // We can't tell what it is, so take a guess at what its most likely to be
                //        $thumbnail_ext = 'jpg';
                //    }

                //    $local_thumbnail_pathname       = "$this->cache_files_folder_path/$item->uid.$thumbnail_ext";

                //    $local_thumbnail_full_pathname  = append_path(get_root_path(), $local_thumbnail_pathname);

                //    if (file_exists($local_thumbnail_full_pathname) )
                //    {
                //        unlink($local_thumbnail_full_pathname);
                //    }

                //    $context = stream_context_create(
                //                        array(
                //                            "http" => array(
                //                                "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36"
                //                            )
                //                        )
                //                    );


                //    if (copy($page_metadata->image_url, $local_thumbnail_full_pathname, $context) )
                //    {
                //        $item->image_url = append_path('', '/'.$local_thumbnail_pathname);
                //    }
                //}
            }

            $item->url                  = $url;
            $item->host                 = !empty($page_metadata->host) ? $page_metadata->host : parse_url($url, PHP_URL_HOST);
            $item->site_name            = !empty($page_metadata->site_name) ? $page_metadata->site_name : '';
            $item->title                = $page_metadata->title;
            $item->description          = $page_metadata->description;
            $item->timestamp	        = gmdate("Y-m-d H:i:s");

            if (empty($item->image_url) )
            {
                $item->image_url        = $page_metadata->image_url;
            }

            if ($existing_item)
            {
                $metadata_table->update_metadata($item);
            }
            else
            {
                $metadata_table->add_metadata($item);
            }
            return $page_metadata;
        }

    }


    /**
     *  Link preview class.
     */
    class LinkPreview
    {
        /** @var LinkPreviewMetadata            The metadata read for the specified URL. */
        public $page_metadata;


        /**
         * Constructor
         *
         * @param string $url                   The URL to read the link preview for.
         * @param LinkPreviewCache $cache       Link preview cache object.
         */
        public function __construct($url, $cache = null)
        {
            if ($cache)
            {
                $this->page_metadata = $cache->get_cached_metadata($url);
            }

            if (!$this->page_metadata)
            {
                $reader = new LinkPreviewMetadataReader($url);

                $this->page_metadata = $reader->get_metadata();

                if ($cache && $this->page_metadata)
                {
                    $cache->cache_metadata($url, $this->page_metadata);
                }
            }
        }


        /**
         * Get the HTML for the link preview
         *
         * @return boolean                      true if the link preview was read successfully; false otherwise.
         */
        public function read_ok()
        {
            return ($this->page_metadata !== false) ? true : false;
        }


        /**
         * Get the HTML for the link preview
         *
         * @return string                       The HTML for the link preview, or false if it could not be generated.
         */
        public function get_html()
        {
            $html = '';

            if ($this->page_metadata !== false)
            {
                $metadata    = $this->page_metadata;

                $url         = $metadata->url;

                // If we're running on a dev machine, this is a workaround to ensure that all internal link previews
                // route appropriately.
                if (get_host() != raw_get_host() )
                {
                    if (str_begins_with($url, get_host() ) )
                    {
                        $url = str_replace(get_host(), '', $url);
                    }
                }

                $html        = "<div class='link-preview-container'>";
                $html       .=   "<a href='$url' class='link-preview' target='_blank' rel='nofollow'>";
                $html       .=     "<div class='link-area'>";

                if (!empty($metadata->image_url) )
                {
                    $html   .=       "<div ><img src='$metadata->image_url' class='og-image' alt='Preview image'></div>";
                }

                $html       .=       "<div>";
                $html       .=         "<div class='og-title'>$metadata->title</div>";
                $html       .=         "<div class='og-description'>$metadata->description</div>";
                $html       .=         "<div class='og-host'>$metadata->host</div>";
                $html       .=       "</div>";
                $html       .=     "</div>";
                $html       .=   "</a>";
                $html       .= "</div>";
            }
            return $html;
        }

    }

?>