<?php
    /**
     * Class to record changed items.
     *
     */


    /**
     * Class to record changed items.
     *
     */
    class DatabaseItemsChangeDetails
    {
        /** @var array                  Items added */
        public  $items_added;

        /** @var array                  Items updated */
        public  $items_updated;

        /** @var array                  Items deleted */
        public  $items_deleted;

        /** @var array                  Summary of changed properties, indexed by report */
        public  $changed_properties;



        public function __construct()
        {
            $this->items_added          = [];
            $this->items_updated        = [];
            $this->items_deleted        = [];
            $this->changed_properties   = [];
        }


        public function add($results)
        {
            $this->items_added          = array_merge($this->items_added,           $results->items_added);
            $this->items_updated        = array_merge($this->items_updated,         $results->items_updated);
            $this->items_deleted        = array_merge($this->items_deleted,         $results->items_deleted);
            $this->changed_properties   = array_merge($this->changed_properties,    $results->changed_properties);
        }
    }

?>
