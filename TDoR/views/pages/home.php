
<p>TDoR reports are collated by the <a href="https://transrespect.org/en/research/trans-murder-monitoring/" target="_blank">Trans Murder Monitoring Project</a> and published by <a href="https://tdor.tgeu.org/" target="_blank">Transgender Europe</a> (TGEU) in early November of each year. In addition, trans activists and local organisations worldwide collate data on a continuous basis so that we don't have to wait until November to learn who we've lost.</p>
    
<p>The intention is that the information presented on this site can act as a supporting resource for anyone organising a Transgender Day of Remembrance (TDoR) Event.</p>

<p>A big thanks are due to the members and admins of the <a href="https://www.facebook.com/groups/1570448163283501/" target="_blank">Trans Violence News</a> Facebook group, without whose support this site would not be possible.</p>

<p><p>All material presented is gathered from publicly available sources. <i>Please</i> don't read anything into the raw numbers. What matters is the lives lost, and their stories. Be warned though - some of their stories are horrific.</p>

<p><b>[TRIGGER WARNING: VIOLENCE. MURDER]</b></p>

<p>&nbsp;</p>
<p><i><b>Important note:</b> This site is in the early stages of development, and as such not everything works yet. In particular, links to reports on specific victims are likely to change. If you need to refer to reports relating to a particular victim, please make a note of their name and the corresponding date to ensure that you can find them again.</i></p>


<?php
    $reports_url = ENABLE_FRIENDLY_URLS ? '/reports' : '/?category=reports&action=index';

    echo '<a href="'.$reports_url.'"><b>Reports</b></a>';

    $url        = get_url();
    $newline    = '%0A';

    show_social_links($url, 'Remembering our Dead - remembering trans people lost to violence and suicide'.$newline.$newline.$url);
?>
