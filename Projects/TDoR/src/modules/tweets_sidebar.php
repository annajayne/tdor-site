<?php
    /**
     *  Sidebar module.
     */
?>

<!-- sidebar -->    
<aside>
  <link rel="stylesheet" href="https://embedbsky.com/embedbsky.com-master-min.css" />
  <div id="embedbsky-com-timeline-embed"></div>
  <script>let containerWidth=0,containerHeight=1200;const getHtml=async t=>{const e=await fetch(t);return 200!==e.status?'<p><strong>No feed data could be located</p></strong>':e.text()};document.addEventListener('DOMContentLoaded',(async()=>{const t=(new Date).toISOString(),e=document.getElementById('embedbsky-com-timeline-embed');e.style.width="100%",e.style.height=`${containerHeight}px`;const n=await getHtml(`https://embedbsky.com/feeds/a981e051ec0ddb38f3332e2d18e72b5ab54c50fb57c4a113fc54c201308266a0.html?v=${t}`);e.innerHTML=n}));</script>
</aside><!-- #end sidebar -->
    