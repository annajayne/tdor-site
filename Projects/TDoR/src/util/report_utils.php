 <?php
     /**
      * Support functions for reports
      *
      */


    /**
     * Determine if the given report appears in the list of contributions
     *
     * @param Report $report                The report which may or may not appear in $contributions
     * @param array $contributions          An array containing the contributions by the given user
     *
     */
    function is_contributed_report($report, $contributions)
    {
        // If the current user is a contributor, is this one of their contributed reports?
        foreach ($contributions as $contribution)
        {
            if ($report->uid == $contribution->uid)
            {
                return true;
            }
        }
        return false;
    }

?>
