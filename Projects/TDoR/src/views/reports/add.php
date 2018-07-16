<!-- Script -->
<script>
    $(document).ready(function()
    {
        $.datepicker.setDefaults(
        {
            dateFormat: 'dd M yy'
        });

        $(function()
        {
            $("#datepicker").datepicker();
        });

        $('#cancel').click(function()
        {
            go();
        });


    });
</script>

<?php
    if (is_logged_in() )
    {
        $report                     = new Report();

        $datenow                    = new DateTime('now');

        $report->uid                = get_random_hex_string();
        $report->name               = 'Name Unknown';
        $report->age                = '';
        $report->photo_filename     = '';
        $report->photo_source       = '';
        $report->date               = date_str_to_display_date($datenow->format('d M Y') );
        $report->tgeu_ref           = '';
        $report->location           = '<Location>';
        $report->country            = '<Country>';
        $report->cause              = 'Not reported';
        $report->description        = '';
        $report->permalink          = get_permalink($report);

        if (isset($_POST['submit']) )
        {
            $report->name           = $_POST['name'];
            $report->age            = $_POST['age'];
            $report->photo_filename = $_POST['photo_filename'];
            $report->photo_source   = $_POST['photo_source'];
            $report->date           = date_str_to_iso($_POST['date']);
            $report->tgeu_ref       = $_POST['tgeu_ref'];
            $report->location       = $_POST['location'];
            $report->country        = $_POST['country'];
            $report->cause          = $_POST['cause'];
            $report->description    = $_POST['description'];
            $report->permalink          = get_permalink($report);

            if (Reports::add($report) )
            {
                echo "<script>window.location.href='$report->permalink'</script>";
            }
        }

        echo '<h2>Add Report</h2><br>';
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
