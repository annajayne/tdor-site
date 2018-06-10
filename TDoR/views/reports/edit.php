<?php
    function is_report_edited($report, $updated_report)
    {
        if ( ($updated_report->name !== $report->name) ||
             ($updated_report->age !== $report->age) ||
             ($updated_report->photo_filename !== $report->photo_filename) ||
             ($updated_report->photo_source !== $report->photo_source) ||
             ($updated_report->date !== $report->date) ||
             ($updated_report->tgeu_ref !== $report->tgeu_ref) ||
             ($updated_report->location !== $report->location) ||
             ($updated_report->country !== $report->country) ||
             ($updated_report->cause !== $report->cause) ||
             ($updated_report->description !== $report->description) )
        {
            return true;
        }
        return false;
    }

?>

<!-- Script -->
<script>
    $(document).ready(function ()
    {
        $.datepicker.setDefaults(
        {
            dateFormat: 'dd M yy'
        });

        $(function ()
        {
            $("#datepicker").datepicker();
        });

        $('#cancel').click(function ()
        {
            go();
        });


    });
</script>

<?php
    if (ALLOW_REPORT_EDITING)
    {
        if (isset($_POST['submit']) )
        {
            $updated_report = new Report;
            $updated_report->set_from_report($report);

            $updated_report->name           = $_POST['name'];
            $updated_report->age            = $_POST['age'];
            $updated_report->photo_filename = $_POST['photo_filename'];
            $updated_report->photo_source   = $_POST['photo_source'];
            $updated_report->date           = date_str_to_iso($_POST['date']);
            $updated_report->tgeu_ref       = $_POST['tgeu_ref'];
            $updated_report->location       = $_POST['location'];
            $updated_report->country        = $_POST['country'];
            $updated_report->cause          = $_POST['cause'];
            $updated_report->description    = $_POST['description'];

            if (is_report_edited($report, $updated_report) )
            {
                Reports::update($updated_report);

                echo "<script>window.location.href='$report->permalink'</script>";
            }
        }

        echo '<h2>Edit Report</h2><br>';
        echo '<form action="" method="post"><div>';

        echo '<div class="grid_6"><label for="name">Name:<br></label><input type="text" name="name" id="name" value="'.$report->name.'" /></div>';
        echo '<div class="grid_6"><label for="age">Age:<br></label><input type="text" name="age" id="age" value="'.$report->age.'" /></div>';
        echo '<div class="grid_6"><label for="photo_filename">Photo filename:<br></label><input type="text" name="photo_filename" id="photo_filename" value="'.$report->photo_filename.'" /></div>';
        echo '<div class="grid_6"><label for="photo_source">Photo source:<br></label><input type="text" name="photo_source" id="photo_source" value="'.$report->photo_source.'" /></div>';
        echo '<div class="grid_6"><label for="date">Date:<br></label><input type="text" name="date" id="datepicker" class="form-control" placeholder="Date" value="'.date_str_to_display_date($report->date).'" /></div>';
        echo '<div class="grid_6"><label for="tgeu_ref">TGEU Ref:<br></label><input type="text" name="tgeu_ref" id="tgeu_ref" value="'.$report->tgeu_ref.'" /></div>';
        echo '<div class="grid_6"><label for="location">Location:<br></label><input type="text" name="location" id="location" value="'.$report->location.'" /></div>';
        echo '<div class="grid_6"><label for="country">Country:<br></label><input type="text" name="country" id="country" value="'.$report->country.'" /></div>';
        echo '<div class="grid_6"><label for="cause">Cause of death:<br></label><input type="text" name="cause" id="cause" value="'.$report->cause.'" /></div>';
        echo '<div class="grid_12"><label for="description">Description:<br></label><textarea name="description" style="width:100%; height:500px;">'.$report->description.'</textarea></div><br>';

        echo '<div class="grid_12" align="right">';
        echo   '<input type="submit" name="submit" value="Submit" />&nbsp;&nbsp;';
        echo   '<input type="button" name="cancel" id="cancel" value="Cancel" class="btn btn-success" onclick="javascript:history.back()" />';
        echo '</div></div></form>';
    }
?>


