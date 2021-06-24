<?php
$event_title = get_the_title();
$event_datepicker = get_post_meta(get_the_ID(), 'datepicker', true );
$event_location = get_post_meta(get_the_ID(), 'location', true );
$event_url = get_post_meta(get_the_ID(), 'url', true );
date_default_timezone_set('GMT');
$google_calendar_datepicker = date_i18n("Ymd\THis\Z", strtotime($event_datepicker));

get_header();

/* Start the Loop */
while (have_posts()) : the_post(); ?>
    <?php if (!empty($event_datepicker) && !empty($event_location) && !empty($event_url)) : ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            <div id="entry-content">
                <div class="row">
                    <div class="post-content">
                        <?php echo get_the_post_thumbnail(); ?>
                    </div>
                    <div class='slider-content'>
                        <div class='slider-text'>
                            <h1 id="post-heading"><?php _e(the_title()) ?></h1>
                        </div>
                    </div> 
                    <div class="post-content">
                        <?php echo get_the_content(); ?>
                    </div>
                </div>
                <div id="meta-field">
                    <p class="post-heading"><?php _e(date_i18n("d/m/Y", strtotime($event_datepicker))); ?></p>
                    <p class="post-heading"><?php _e($event_location); ?></p>
                    <a href="<?php echo esc_url($event_url); ?> ">
                        <p class="post-heading"><?php _e($event_url); ?></p>
                    </a>
                    <iframe width="640" height="480" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://maps.google.it/maps?q=<?php echo esc_html($event_location); ?>&output=embed"></iframe>
                        <a href="https://www.google.com/calendar/render?action=TEMPLATE&text=<?php echo esc_html__($event_title); ?>&dates=<?php echo $google_calendar_datepicker . '/' . $google_calendar_datepicker; ?>&location=<?php echo esc_html__($event_location); ?>&details=Event+URL:+<?php echo esc_url($event_url); ?>&sf=true&output=xml">
                    <input type="submit" value="Add to Calendar"/>
                    </a>
                </div>
            </div>
            <!-- .entry-content -->
        </article>
    <?php else : ?>
        <div id="empty">
            <h2><?php _e('Empty'); ?></h2>
        </div>
    <?php endif; ?>
<!-- #post-<?php the_ID(); ?> -->
<?php endwhile; // End of the loop.
get_footer();
?>