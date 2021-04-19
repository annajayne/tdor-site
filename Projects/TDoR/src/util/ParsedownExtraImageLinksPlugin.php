<?php
    /**
     * ParsedownExtraLightboxPlugin - extends ParsedownExtraPlugin to add clickable lightbox links to inline images
     *
     */

    require_once('lib/parsedown/Parsedown.php');                            // https://github.com/erusev/parsedown
    require_once('lib/parsedown/ParsedownExtra.php');                       // https://github.com/erusev/parsedown-extra
    require_once('lib/parsedown/ParsedownExtraPlugin.php');                 // https://github.com/tovic/parsedown-extra-plugin#automatic-relnofollow-attribute-on-external-links




    class ParsedownExtraImageLinksPlugin extends ParsedownExtraPlugin
    {
        /** @var boolean                Whether inline images should be wrapped with links. */
        public $add_image_links = false;

        /** @var string                 The 'target' attribute to add to image links. */
        public $image_links_target_attr = '';

        /** @var string                 The 'rel' attribute to add to image links. */
        public $image_links_rel_attr = '';



        protected function blockImageComplete($Block)
        {
            $Block = ParsedownExtraPlugin::blockImageComplete($Block);

            if ($this->add_image_links)
            {
                $img_element = $Block['element']['elements'][0];

                $caption = '';

                if (count($Block['element']['elements']) >= 2)
                {
                    $caption = $Block['element']['elements'][1]['rawHtml'];
                }

                $img_src = $img_element['element']['attributes']['src'];

                $a_element['name'] = 'a';
                $a_element['attributes'] = array('href' => $img_src, 'title' => $caption);

                if (!empty($this->image_links_target_attr) )
                {
                    $a_element['attributes']['target'] = $this->image_links_target_attr;
                }

                if (!empty($this->image_links_rel_attr) )
                {
                    $a_element['attributes']['rel'] = $this->image_links_rel_attr;
                }

                $a_element['elements'][0] = $img_element;

                $Block['element']['elements'][0]['element'] = $a_element;
            }
            return $Block;
        }

    }

?>
