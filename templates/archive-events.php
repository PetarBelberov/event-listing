<?php
/* 
Template Name: Events
*/
get_header();

query_posts(array(
   'post_type' => 'events',
   'post_status' => array('publish', 'future'),
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
<?php get_footer(); ?>