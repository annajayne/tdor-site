<?php
    /**
     *  Link preview implementation.
     */
    require_once('util/string_utils.php');
    require_once('util/path_utils.php');


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
            $this->url = $url;
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
            $file = fopen($this->url,'r');

            if ($file)
            {
                $metadata = new LinkPreviewMetadata();

                $page_content = file_get_contents($this->url);

                fclose($file);

                $meta_tags = $this->read_meta_tags($page_content);

                $metadata->url = $this->url;
                $metadata->host = parse_url($this->url, PHP_URL_HOST);

                foreach ($meta_tags as $tag)
                {
                    $tag_content = isset($tag['content']) ? $tag['content'] : '';

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

                        $size = getimagesize($image_url);

                        if ($size)
                        {
                            list($width, $height, $type, $attr) = $size;

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
        /** @var string                         The path of the cache folder. */
        public $cache_files_folder_path;

        /** @var string                         The filename of the cache file. */
        public $cache_file_pathname;

        /** @var array                          The cache. */
        public  $cache;


        /**
         * Constructor
         *
         * @param string $cache_file_pathname   The pathname of the cache file
         */
        public function __construct($cache_file_pathname)
        {
            $this->cache                        = [];

            $this->cache_file_pathname          = $cache_file_pathname;

            $cache_file_full_pathname           = append_path(get_root_path(), $this->cache_file_pathname);

            $this->cache_files_folder_path      = pathinfo($cache_file_pathname, PATHINFO_DIRNAME);

            if (file_exists($cache_file_full_pathname) )
            {
                $this->cache                    = parse_ini_file($cache_file_full_pathname, TRUE);
            }
        }


        /**
         * Has page metadata been cached for the specified URL?
         *
         * @param string $url                           The URL.
         * @return boolean                              true if the metadata for the URL exists in the cache, false otherwise.
         */
        public function is_cached($url)
        {
            if (isset($this->cache[$url]) )
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
            if (isset($this->cache[$url]) )
            {
                $item                           = $this->cache[$url];

                $page_metadata                  = new LinkPreviewMetadata();

                $page_metadata->url             = $item['url'];
                $page_metadata->host            = $item['host'];
                $page_metadata->site_name       = $item['site_name'];
                $page_metadata->title           = $item['title'];
                $page_metadata->description     = $item['description'];
                $page_metadata->image_url       = $item['image_url'];

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
            $item                               = isset($this->cache[$url]) ? $this->cache[$url] : [];

            $uid                                = '';
            if (isset($item['uid']) )
            {
                $uid                            = $item['uid'];
            }
            else
            {
                $uid                            = get_random_hex_string();

                $item['uid']                    = $uid;
            }

            if (!empty($page_metadata->image_url) )
            {
                // Copy the thumbnail file locally (using a unique filename for the url to avoid name clashes)
                $thumbnail_ext                  = pathinfo(strtok($page_metadata->image_url, '?'), PATHINFO_EXTENSION);

                $local_thumbnail_pathname       = "$this->cache_files_folder_path/$uid.".$thumbnail_ext;

                $local_thumbnail_full_pathname  = append_path(get_root_path(), $local_thumbnail_pathname);

                if (file_exists($local_thumbnail_full_pathname) )
                {
                    unlink($local_thumbnail_full_pathname);
                }

                if (copy($page_metadata->image_url, $local_thumbnail_full_pathname) )
                {
                    $page_metadata->image_url   = append_path('', '/'.$local_thumbnail_pathname);
                }
            }

            $item['url']                        = $page_metadata->url;
            $item['host']                       = $page_metadata->host;
            $item['site_name']                  = $page_metadata->site_name;
            $item['title']                      = $page_metadata->title;
            $item['description']                = $page_metadata->description;
            $item['image_url']                  = $page_metadata->image_url;
            $item['timestamp']                  = gmdate("Y-m-d H:i:s");

            $this->cache[$url]                  = $item;

            write_ini_file(append_path(get_root_path(), $this->cache_file_pathname), $this->cache);

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
            if ($cache && $cache->is_cached($url) )
            {
                $this->page_metadata = $cache->get_cached_metadata($url);
            }
            else
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

                $html        = "<div class='link-preview-container'>";
                $html       .=   "<a href='$metadata->url' class='link-preview' target='_blank' rel='nofollow'>";
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