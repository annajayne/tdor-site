<?php
    /**
     * Search page.
     *
     */
?>

<?php
    require_once('models/reports.php');
    require_once('views/reports/reports_table_view_impl.php');
    require_once('views/reports/reports_thumbnails_view_impl.php');
    require_once('views/reports/reports_map_view_impl.php');
    require_once('views/reports/reports_details_view_impl.php');


    $search_for = '';
    $view_as    = 'list';

    if (isset($_GET['search']) )
    {
        $search_for     = $_GET['search'];
    }

    if (isset($_GET['view']) )
    {
        $view_as        = $_GET['view'];
    }

    if (isset($_POST['submit']) )
    {
        $search_for     = $_POST['search_for'];
    }
?>

<script>
    function get_search_url(view_as, search_for)
    {
      <?php
      $url = ENABLE_FRIENDLY_URLS ? '/pages/search?' : '/index.php?controller=pages&action=search&';
      echo "var url = '$url'";
      ?>

        url += 'search=' + search_for;
        url += '&view=' + view_as;

        return url;
    }


    function get_search_text()
    {
        var ctrl = document.getElementById("search_for");

        return ctrl.value;
    }


    function get_view_as_selection()
    {
        var ctrl = document.getElementById("view_as");

        return ctrl.options[ctrl.selectedIndex].value;
    }


    function onselchange_view_as()
    {
        var view_as     = get_view_as_selection();
        var search      = get_search_text();

        url = get_search_url(view_as, search);

        window.location.href = url;
    }
</script>

<?php

    // Search for
    echo '<form action="" method="POST" enctype="multipart/form-data">';
    echo   '<div class="grid_12">';
    echo     '<label for="name">Search for:<br></label>';
    echo     '<input type="text" name="search_for" id="search_for" value="'.$search_for.'" style="width:90%;" />';
    echo     '<input type="submit" name="submit" value="Submit" />&nbsp;&nbsp;';
    echo   '</div>';
    echo '</form>';

    echo '<div class="grid_12">';

    if (!empty($search_for) )
    {
        $db                             = new db_credentials();
        $reports_table                  = new Reports($db);

        $query_params                   = new ReportsQueryParams();

        $query_params->filter           = $search_for;
        $query_params->sort_field       = 'date';
        $query_params->sort_ascending   = false;

        $reports                        = $reports_table->get_all($query_params);

        $report_count   = count($reports);

        echo   'View as:<br />'.get_view_combobox_code($view_as, 'onchange="onselchange_view_as();"').'<br><br><hr>';

        echo '<b>'.$report_count.' '.get_report_count_caption($report_count).' found</b><br><br>';

        switch ($view_as)
        {
            case 'list':
                show_summary_table($reports);
                break;

            case 'thumbnails':
                show_thumbnails($reports);
                break;

            case 'map':
                show_summary_map($reports);
                break;

            case 'details':
                show_details($reports);
                break;
        }
    }
    echo   '</div>';

?>
