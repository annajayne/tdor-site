<?php
    /**
     * API demo
     *
     */

    require_once("./models/reports.php");


    $api_key                    = isset($_SESSION['api_key']) ? $_SESSION['api_key'] : '';


    // Set the range for the "reports" query to be the most recent TDoR period.
    $tdor_year                  = date("Y");
    $report_date_range          = Reports::get_date_range();

    if ($report_date_range[1] < $tdor_year)
    {
        // If there are no reports in the current year, use the most recent date for which we have data.
        $tdor_year = $report_date_range[1];
    }

    $date_from_str              = ($tdor_year - 1).'-10-01';
    $date_to_str                = $tdor_year.'-09-30';


    // Choose a random report to use as an example url/uid in the text
    $count                      = Reports::get_count();

    $id                         = mt_rand(1, $count);

    $report                     = Reports::find($id);

    $host                       = raw_get_host();

    $example_report_url         = $host.get_permalink($report);
    $example_report_uid         = $report->uid;

    $example_reports_query_url  = $host.'/api/v1/reports?key=<api-key>&from=<date>&to=<date>&country=<country>&filter=<filter>';
    $example_report_query_url   = $host.'/api/v1/reports?key=<api-key>&url=<url>&uid=<uid>';
?>


<script>
    // Your API key
    function get_api_key()
    {
        return document.getElementById("api-key").value;
    }


    // Implementation function to return the URL of the web service
    //
    function get_web_service_url(query_params)
    {
        var host = window.location.protocol + "//" + window.location.host;

        var url = host + "/api/v1/reports";

        if (query_params != "")
        {
            return url + "?" + query_params;
        }
        return url;
    }


    // Implementation function to query the web service and update the UI accordingly
    //
    function query(query_params, query_url_ctrl_id, result_ctrl_id)
    {
        var query_url_ctrl  = document.getElementById(query_url_ctrl_id);
        var result_ctrl     = document.getElementById(result_ctrl_id);

        var url = get_web_service_url(query_params);

        query_url_ctrl.innerHTML    = url;
        result_ctrl.innerHTML       = "";

        var response = null;

        fetch(url).then(response => response.json() ).
            then(function(response)
            {
                result_ctrl.innerHTML = JSON.stringify(response, undefined, 2);
            }).
            catch(function(error)
            {
                console.error("Error:", error);
            });
    }


    // Query multiple reports
    //
    function onclick_query_reports()
    {
        var from_date   = $("#datepicker_from").val();
        var to_date     = $("#datepicker_to").val();

        if (from_date != "")
        {
            from_date = date_to_iso(from_date);
        }

        if (to_date != "")
        {
            to_date = date_to_iso(to_date);
        }

        var country = document.getElementById("country").value;
        var filter = document.getElementById("filter").value;

        var params = "key=" + get_api_key() +
                     "&from=" + from_date +
                     "&to=" + to_date +
                     "&country=" + country +
                     "&filter=" + filter;

        query(params, "reports_query_web_service_url", "reports_query_result");
    }


    // Query single report
    //
    function onclick_query_report()
    {
        var url = document.getElementById("url").value;
        var uid = document.getElementById("uid").value;

        if (url != "" || uid != "")
        {
            var params = "key=" + get_api_key() +
                         "&url=" + url +
                         "&uid=" + uid;

            query(params, "report_query_web_service_url", "report_query_result");
        }
        else
        {
            // Not valid - clear
            var url = get_web_service_url("");

            document.getElementById("report_query_web_service_url").innerHTML = url;
            document.getElementById("report_query_result").innerHTML = "";
        }
    }
</script>


<script>
    $(document).ready(function()
    {
        $.datepicker.setDefaults(
        {
            dateFormat: "dd M yy"
        });

        $(function()
        {
            $("#datepicker_from").datepicker();
            $("#datepicker_to").datepicker();
        });

    });
</script>


<h2>API</h2>

<p>This website has a JSON API which you can use to remotely retrieve data on reports of trans people lost to violence in its many forms.</p>

<p>This can be used to construct data visualisation and analysis implementations such as (for example) those produced by members of the <a href="https://www.twitter.com/R_Forwards" target="_blank" rel="noopener">R Foundation</a> for Transgender Day of Remembrance 2018 (see  <a href="https://github.com/rlgbtq/TDoR2018" target="_blank">https://github.com/rlgbtq/TDoR2018</a> and  <a href="https://github.com/CaRdiffR/tdor" target="_blank">https://github.com/CaRdiffR/tdor</a>).</p>

<p>This page allows you to run sample queries on the API, and examine the responses that result. To use it you will need an API key, which you can obtain by <a href="javascript:window.location.replace('/account')">registering an account and logging in</a>.</p>

<p>If you are not logged in and already have an API key, you can enter it below:</p>

<div class="grid_12">API key:<br /><input type="text" name="api-key" id="api-key" value="<?php echo $api_key; ?>" style="width:100%;" /></div>

<p>We hope that the format of the responses will prove to be self-explanatory. If you have any queries, please feel free to contact <a href="mailto:tdor@translivesmatter.info">tdor@translivesmatter.info</a> or <a href="https://www.twitter.com/tdorinfo" target="_blank" rel="noopener">@TDoRinfo</a>.</p>



<p>&nbsp;</p>
<h3>Getting summary data on multiple reports</h3>
<p>To retrieve summary data for multiple reports, enter some parameters to guide your search, for example the dates, country or an arbitrary filter string:</p>

<div class="grid_6">From Date:<br /><input type="text" name="datepicker_from" id="datepicker_from" class="form-control" placeholder="" value="<?php echo date_str_to_display_date($date_from_str);?>" /></div>
<div class="grid_6">To Date:<br /><input type="text" name="datepicker_to" id="datepicker_to" class="form-control" placeholder="" value="<?php echo date_str_to_display_date($date_to_str);?>" /></div>

<div class="grid_12">Country:<br /><input type="text" name="country" id="country" style="width:100%;" /></div>
<div class="grid_12">Filter:<br /><input type="text" name="filter" id="filter" style="width:100%;" /></div>

<div class="grid_11">Query URL:<br /><div id="reports_query_web_service_url"><?php echo htmlentities($example_reports_query_url); ?></div></div>
<div class="grid_1"><br /><input type="button" name="get" id="get" value="Go"  style="width:100%;" onclick="onclick_query_reports();" /></div>

<div class="grid_12">Response:<br /><textarea id="reports_query_result" style="width:100%;" rows="25" readonly></textarea></div>


<p>&nbsp;</p>
<h3>Getting detailed data on a specific report</h3>
<p>To retrieve detailed data (including a full description) for a specific report, enter either its URL (e.g. <b><?php echo $example_report_url;?></b>) or UID (the 8 digit hex string at the end of the URL - <b><?php echo $example_report_uid;?></b> in this particular case):</p>

<div class="grid_12">URL:<br /><input type="text" name="url" id="url" style="width:100%;" /></div>
<div class="grid_12">UID:<br /><input type="text" name="uid" id="uid" /></div>

<div class="grid_11">Query URL:<br /><div id="report_query_web_service_url"><?php echo htmlentities($example_report_query_url); ?></div></div>
<div class="grid_1"><br /><input type="button" name="get" id="get" value="Go"  style="width:100%;" onclick="onclick_query_report();" /></div>

<div class="grid_12">Response:<br /><textarea id="report_query_result" style="width:100%;" rows="25" readonly></textarea></div>

