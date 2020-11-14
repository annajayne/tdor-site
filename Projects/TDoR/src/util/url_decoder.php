<?php
    /**
     * URL decoder to convert pretty urls (e.g. "https://www.domainname.com/something") to raw urls such as "https://www.domainname.com/index.asp?controller=pages&action=something".
     *
     */

    require_once('controllers/controllers.php');


    /**
     * URL decoder
     *
     */
    class UrlDecoder
    {
        /** @var string                  The url to decode. */
        public  $url;

        /** @var string                  Header to send if the URL was not recognised. */
        public  $header;

        /** @var string                  The name of the controller. */
        public  $controller;

        /** @var string                  The name of the action. */
        public  $action;

        /** @var array                   A map of friendly URLs to the corresponding controller and action. */
        private $urlmap;

        /** @var array                   A map of legacy URLs which should redirect to the corresponding friendly URL, e.g. 'blog.php' => 'blog' */
        private $redirected_urls;



        /**
         * Constructor
         *
         */
        function __construct()
        {
            $this->urlmap           = array();
            $this->redirected_urls  = array();

            $account_controller     = new AccountController();
            $pages_controller       = new PagesController();
            $blog_controller        = new BlogController();
            $reports_controller     = new ReportsController();

            $this->add_controller_urls($account_controller->get_name(), $account_controller->get_actions(),   'account',          'welcome');
            $this->add_controller_urls($pages_controller->get_name(),   $pages_controller->get_actions(),     'pages',            'home');
            $this->add_controller_urls($blog_controller->get_name(),    $blog_controller->get_actions(),      'blog',             'index');
            $this->add_controller_urls($reports_controller->get_name(), $reports_controller->get_actions(),   'reports',          'index');

            $this->redirected_urls = array(
                                            'account/login.php'                                 => 'account/login',
                                            'account/logout.php'                                => 'account/logout',
                                            'account/register.php'                              => 'account/register'
                                         );
        }


        /**
         * Implementation function to add the URLs supported by the specified controller actions.
         *
         * @param string $controller_name             The name of the controller.
         * @param array $controller_actions           An array of actions supported by the controller.
         * @param string $root_url                    The root URL of the controller (e.g. "/pages").
         * @param string $ignore_action               Optional. The name of any action which should be considered as the root action for $root_url.
         */
        private function add_controller_urls($controller_name, $controller_actions, $root_url, $ignore_action = '')
        {
            foreach ($controller_actions as $action)
            {
                $url = $root_url;                                               // e.g. 'products/visual_lint';

                if (empty($ignore_action) || ($action !== $ignore_action) )     // e.g. 'home' or 'overview'
                {
                    $url .= '/'.$action;
                }

                $url = ltrim($url, '/');

                $this->urlmap[$url] = array($controller_name, $action);
            }
        }


        /**
         * Implementation function to strip off parameters from the given URL
         *
         * @param string $url                         The URL from which parameters should be stripped.
         * @return string                             The stripped URL.
         */
        private function strip_parameters($url)
        {
            $pos = strpos($url, '?');

            if ($pos !== false)
            {
                $url = substr($url, 0, $pos);
            }
            return $url;
        }


        /**
         * Return details of the supported URLs.
         *
         * @return array                              An array containing the supported URLs.
         */
        function get_urls()
        {
            return $this->urlmap;
        }


        /**
         * Get the redirected URL (if any) corresponding to the given legacy URL.
         *
         * @param string $url                         The legacy URL to redirect from.
         * @return string                             The redirected URL, or an empty string if there is no redirect for $url.
         */
        function get_redirected_url($url)
        {
            $slash = '/';
            $key = ltrim(rtrim($this->strip_parameters($url), $slash), $slash);

            if (array_key_exists($key, $this->redirected_urls) )
            {
                return $this->redirected_urls[$key];
            }
            return '';
        }


        /**
         * Decode the specified URL.
         *
         * @param string $url                         The url to decode.
         * @return boolean                            true if OK and execution should continue; false otherwise.
         */
        function decode($url)
        {
            // e.g. riverblade.co/support
            $path   = ltrim($url, '/');                             // Trim leading slash(es)...
            $path   = $this->strip_parameters($path);               // ...parameters
            $path   = rtrim($path, '/');                            //...and trailing slash(es)

            if ($path == 'index.php')                               // Support raw urls (e.g. 'index.php?controller=pages&action=home')
            {
                return false;
            }

            if (array_key_exists($path, $this->urlmap) )
            {
                $target = $this->urlmap[$path];

                if (!empty($target) )
                {
                    $this->controller   = $target[0];
                    $this->action       = $target[1];
                }
            }

            $elements = array_filter(explode('/', $path) );             // and split the path on them, removing any empty elements
            $element_count = count($elements);

            if ($element_count > 0)
            {
                // Reports
                if ( ($elements[0] === 'reports') || str_begins_with($elements[0], 'reports?') )
                {
                    $this->controller     = 'reports';

                    if ($element_count === 5)
                    {
                        $this->action     = 'show';
                    }
                    else if  ($element_count >= 1)
                    {
                        // '/report', '/report/' or '/report?', '/report/year/month/' etc.
                        $this->action     = 'index';
                    }
                    else
                    {
                        header('HTTP/1.1 404 Not Found');
                    }
                }

                // Blogposts
                if ( ($elements[0] === 'blog') || str_begins_with($elements[0], 'blog?') )
                {
                    $this->controller     = 'blog';

                    $uid = get_uid_from_friendly_url($url);

                    if (!empty($uid) )
                    {
                        $this->action     = 'show';
                    }
                    else if  ($element_count >= 1)
                    {
                        // '/blog', '/blog/' or '/blog?', '/blog/year/month/' etc.
                        $this->action     = 'index';
                    }
                    else
                    {
                        header('HTTP/1.1 404 Not Found');
                    }
                }

                if (empty($this->controller) || empty($this->action) )
                {
                    $this->controller = 'pages';
                    $this->action     = 'error';

                    header('HTTP/1.1 404 Not Found');
                }
            }
            return true;
        }


        /**
         * Return whether the URL was recognised.
         *
         * @return boolean                            true if the URL was recognised; false otherwise.
         */
        function is_url_recognised()
        {
            return empty($this->header);
        }


        /**
         * Return the header to send if the URL was not recognised.
         *
         * @return string                             The header to send if the URL was not recognised.
         */
        function get_header()
        {
            return $this->header;
        }


        /**
         * Get the controller corresponding to the specified URL.
         *
         * @return string                             The name of the controller.
         */
        function get_controller()
        {
            return $this->controller;
        }


        /**
         * Get the action corresponding to the specified URL.
         *
         * @return string                             The name of the action.
         */
        function get_action()
        {
            return $this->action;
        }


    }


?>