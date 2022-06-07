<?php
/* 
Template Name: Events
*/

query_posts(array(
   'post_type' => 'events',
   'post_status' => array('publish', 'future'),
   'posts_per_page' => -1
));
?>

<div id="events-list">
   <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
      <h2>
         <a href="<?php the_permalink() ?>"><?php the_title(); ?></a>
      </h2>
      <p><?php the_excerpt(); ?></p>
      <?php endwhile; ?>
   <?php endif; ?>
</div>
