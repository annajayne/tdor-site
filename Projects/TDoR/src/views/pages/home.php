<?php
    /**
     * Homepage content.
     * 
     */
?>

<p>The site is intended to act as a supporting resource for anyone involved in Transgender Day of Remembrance (TDoR) events and is intended to be used alongside the official data collated by the <a href="https://transrespect.org/en/research/trans-murder-monitoring/" target="_blank" rel="noopener">Trans Murder Monitoring Project</a> and published by <a href="https://tdor.tgeu.org/" target="_blank" rel="noopener">Transgender Europe</a> (TGEU) in early November of each year.</p>
    
<p>A big thanks are due to the many trans activists and organisations worldwide who work tirelessly collate data on anti-trans violence and in particular to the members and admins of the <a href="https://www.facebook.com/groups/1570448163283501/" target="_blank"  rel="noopener">Trans Violence News</a> Facebook group, without whose support this site would not be possible.</p>

<p><p>All material presented is gathered from publicly available sources. <i>Please</i> don't read anything into the raw numbers. What matters is the lives lost, and their stories. Be warned though - some of their stories are horrific.</p>

<p><b>[TRIGGER WARNING: VIOLENCE. MURDER]</b></p>

<p><i><b>Please note:</b> If you want to help out or notify us of a correction or additional information about a report presented here (or indeed tell us about someone who you think should be listed, but isn't as yet), please contact us by <a href="/pages/contact" rel="nofollow">email</a> or via  <a href="https://bsky.app/profile/tdorinfo.bsky.social" target="_blank" rel="noopener">BlueSky</a> or <a href="https://twitter.com/TDoRinfo" target="_blank" rel="noopener">Twitter</a>. Links to relevant news reports, social media posts etc.can also be shared to <a href="https://www.facebook.com/groups/1570448163283501/" target="_blank" rel="noopener">Trans Violence News</a> (a private group, membership of which requires admin approval) on Facebook.</i></p>

<?php
    // Show the Reports, Facebook, Twitter and RSS feed buttons.
    // Note that this is basically a specialised version of show_social_links() in display_utils.php
    //
    $reports_url    = ENABLE_FRIENDLY_URLS ? '/reports' : '/?controller=reports&action=index';
    $rss_attributes = 'from=1901-01-01&to=2099-12-31&country=all&filter=&action=rss';
    $rss_feed_url   = ENABLE_FRIENDLY_URLS ? "$reports_url?$rss_attributes" : "$reports_url&$rss_attributes";

    $url            = get_url();
    $newline        = '%0A';
    $tweet_text     = 'Remembering our Dead - remembering trans people lost to violence and suicide'.$newline.$newline.$url;

    $encoded_url    = rawurlencode($url);

    // SVG attributes. Note that the sizing and margins of the icons have been chosen to match the style of the "Reports" button alongside them.
    $svg_attributes = "width='43' style='margin: 10px 15px 10px 0px;'";

    echo '<div>';
    echo   '<a href="'.$reports_url.'" class="button-dkred" title="View memorial pages by year, country and category"><b>Reports</b></a>';
    echo   '<a href="/pages/contact" class="button-green" title="Contact us by email"><b>Contact Us</b></a>';

    echo   "<a href='https://bsky.app/profile/tdorinfo.bsky.social' title='TDoRInfo (BlueSky)' target='_blank' rel='noopener'><img src='/images/social/bluesky.svg' $svg_attributes /></a>";
    echo   "<a href='https://twitter.com/TDoRinfo' title='TDoRInfo (Twitter)' target='_blank' rel='noopener'><img src='/images/social/twitter.svg' $svg_attributes /></a>";
    echo   "<a href='https://www.facebook.com/groups/1570448163283501/' title='Trans Violence News Facebook group' target='_blank' rel='noopener'><img src='/images/social/facebook.svg' $svg_attributes /></a>";
    echo   "<a href='$rss_feed_url' target='_blank' rel='noopener'><img src='/images/rss.svg' title='RSS feed' alt='RSS feed' $svg_attributes /></a>";
    echo '</div>';

?>
