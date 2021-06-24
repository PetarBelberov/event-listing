<?php
/* 
Template Name: Events
*/
get_header();

query_posts(array(
   'post_type' => 'events'
)); ?>

<div class="events-list">
 <?php while (have_posts()) : the_post(); ?>
   <h2>
      <a href="<?php the_permalink() ?>"><?php the_title(); ?></a>
   </h2>
   <p><?php the_excerpt(); ?></p>
<?php endwhile; ?>
</div>

<?php get_footer(); ?>