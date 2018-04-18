<?php 
  
  echo '<p>Posts:</p>';

  foreach ($posts as $post)
  {
    echo "<p>$post->author&nbsp;-&nbsp;<a href='?controller=posts&action=show&id=$post->id'>read</a></p>";
  }
?>