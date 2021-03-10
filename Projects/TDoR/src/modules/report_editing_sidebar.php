<?php
    /**
     *  Report editing sidebar.
     */

    function get_report_editing_sidebar_text()
    {
        $pathname                   = get_root_path().'/views/reports/report_editing_help.md';

        $text                       = file_get_contents($pathname);

        $text                       = mb_convert_encoding($text, 'UTF-8', mb_detect_encoding($text, 'UTF-8, ISO-8859-1', true));
        
        return $text;
    }


    $text                           = get_report_editing_sidebar_text();

    $parsedown                      = new ParsedownExtraPlugin();

    $parsedown->links_attr          = array();
    $parsedown->links_external_attr = array('rel' => 'nofollow noopener', 'target' => '_blank');

    $html = $parsedown->text($text);

    
    echo "<p>&nbsp;</p>";


    // BODGE alert: I'm using a table for indentation here (bad me!) because trying to use CSS here had odd effects
    // There could be a mismatched <div> somewhwere, but I haven't found it so far [Anna 7.11.2020]
    echo '<div style="font-size:80%; font-style:italic;"><table><tr><td width="50"><td>';

    echo $html;

    echo '</td></td></tr></table></div>';

?>