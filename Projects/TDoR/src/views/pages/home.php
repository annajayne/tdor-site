<?php
    /**
     * Homepage content.
     * 
     */
?>

<p>The site is intended to act as a supporting resource for anyone organising Transgender Day of Remembrance (TDoR) events and is intended to be used alongside the official data collated by the <a href="https://transrespect.org/en/research/trans-murder-monitoring/" target="_blank">Trans Murder Monitoring Project</a> and published by <a href="https://tdor.tgeu.org/" target="_blank">Transgender Europe</a> (TGEU) in early November of each year.</p>
    
<p>A big thanks are due to the many trans activists and organisations worldwide who work tirelessly collate data on anti-trans violence and in particular to the members and admins of the <a href="https://www.facebook.com/groups/1570448163283501/" target="_blank">Trans Violence News</a> Facebook group, without whose support this site would not be possible.</p>

<p><p>All material presented is gathered from publicly available sources. <i>Please</i> don't read anything into the raw numbers. What matters is the lives lost, and their stories. Be warned though - some of their stories are horrific.</p>

<p><b>[TRIGGER WARNING: VIOLENCE. MURDER]</b></p>

<p><i><b>Please note:</b> This site is in development, and as such not everything works yet. If you see something that doesn't work or doesn't look right please <a href="mailto:tdor@translivesmatter.info">tell us</a>.<br /><br />Similarly, if you wish to notify us of a correction or additional information relating to a report presented here, or indeed details of a new report which isn't yet listed, please feel free to contact us via  <a href="mailto:tdor@translivesmatter.info">email</a>, <a href="https://twitter.com/tdorinfo" target="_blank">Twitter</a> or <a href="https://www.facebook.com/groups/1570448163283501/" target="_blank">Facebook</a>.</i></p>

<?php
    // Show the Reports, Facebook, Twitter and RSS feed buttons.
    // Note that this is basically a specialised version of show_social_links() in display_utils.php
    //
    $reports_url    = ENABLE_FRIENDLY_URLS ? '/reports' : '/?category=reports&action=index';
    $rss_attributes = 'from=1901-01-01&to=2099-12-31&country=all&filter=&action=rss';
    $rss_feed_url   = ENABLE_FRIENDLY_URLS ? "$reports_url?$rss_attributes" : "$reports_url&$rss_attributes";

    $url            = get_url();
    $newline        = '%0A';
    $tweet_text     = 'Remembering our Dead - remembering trans people lost to violence and suicide'.$newline.$newline.$url;

    $encoded_url    = rawurlencode($url);

    // SVG attributes. Note that the sizing and margins of the icons have been chosen to match the style of the "Reports" button alongside them.
    $svg_attributes = "width='43' style='margin: 10px 15px 10px 0px;'";

    echo '<div>';
    echo   '<a href="'.$reports_url.'" class="buttonlink"><b>Reports</b></a>';
    echo   "<a href='https://www.facebook.com/sharer/sharer.php?u=$encoded_url' title='Share on Facebook' target='_blank'><img src='/images/social/facebook.svg' $svg_attributes /></a>";
    echo   "<a href='https://twitter.com/home?status=$tweet_text' title='Tweet about this' target='_blank'><img src='/images/social/twitter.svg' $svg_attributes /></a>";
    echo   "<a href='$rss_feed_url' target='_blank'><img src='/images/rss.svg' alt='RSS' $svg_attributes /></a>";
    echo '</div>';

?>
