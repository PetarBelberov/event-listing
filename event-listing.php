<?php
/**
 * Plugin Name: Event Listing
 * Description: An event listing plugin built with the DX Plugin Base framework
 * Plugin URI: https://github.com/PetarBelberov/event-listing
 * Author: Petar Belberov
 * Author URI: https://github.com/PetarBelberov
 * Version: 1.0
 * License: GPL2
 */


class EventListing {
	
	/**
	 * 
	 * Assign everything as a call from within the constructor
	 */
	public function __construct() {

		// add scripts and styles only available in admin
		add_action( 'wp_enqueue_scripts', array( $this, 'cpt_add_CSS' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'cpt_add_admin_CSS' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'cpt_add_admin_JS' ) );

		// register meta boxes for Pages (could be replicated for posts and custom post types)
		add_action( 'add_meta_boxes', array( $this, 'cpt_meta_boxes_callback' ) );

		// Add actions for storing value and fetching URL
		// use the wp_ajax_nopriv_ hook for non-logged users (handle guest actions)
		add_action( 'wp_ajax_store_ajax_value', array( $this, 'store_ajax_value' ) );
		add_action( 'wp_ajax_fetch_ajax_url_http', array( $this, 'fetch_ajax_url_http' ) );

		// Register activation and deactivation hooks
		register_activation_hook( __FILE__, 'cpt_on_activate_callback');
		register_deactivation_hook( __FILE__, 'cpt_on_deactivate_callback' );

		add_action('init', array($this, 'cpt_post_type_events'));
		add_action( 'save_post', array($this,'cpt_save_events_meta'), 1, 2);

		add_filter('single_template', array($this,'cpt_single_customtype'));
		add_filter('archive_template', array($this,'cpt_archive_customtype'));

		add_action('pre_get_posts', array($this,'cpt_pre_get_posts'));

	}	
	  
	function cpt_post_type_events() {
		$supports = array(
		  'title', // post title
		  'editor', // post content
		  'thumbnail', // featured images
		  'custom-fields' // custom fields
		);
		$labels = array(
		  'name' => _x('Events', 'plural'),
		  'singular_name' => _x('events', 'singular'),
		);
		$args = array(
		  'supports' => $supports,
		  'labels' => $labels,
		  'public' => true,
		  'query_var' => true,
		  'rewrite' => array('slug' => 'cpt_events'),
		  'has_archive' => true,
		  'hierarchical' => false,
		  'menu_icon' => 'dashicons-list-view',
		  'show_in_rest' => true,
		  'register_meta_box_cb' => array($this, 'cpt_meta_boxes_callback')
		);
		  register_post_type('events', $args);
	  }
 
/**
 * Output the HTML for the metabox.
 */
function cpt_events_output() {
	global $post;
 
	// Nonce field to validate form request came from current site
	wp_nonce_field( basename( __FILE__ ), 'event_fields' );

	// Get the datepicker data if it's already been entered
	$datepicker = get_post_meta( $post->ID, 'datepicker', true );

	// Get the location data if it's already been entered
	$location = get_post_meta( $post->ID, 'location', true );

	// Get the url data if it's already been entered
	$url = get_post_meta( $post->ID, 'url', true );

	// Output the fields
	echo '<label for="html">'. esc_html__('Date') . '</label>';
	echo '<input type="text" name="datepicker" id="cpt_datepicker" value="' . esc_textarea( $datepicker )  . '" class="widefat">';
	echo '<label for="html">'. esc_html__('Location') . '</label>';
	echo '<input type="text" name="location" value="' . esc_textarea( $location )  . '" class="widefat">';
	echo '<label for="html">'. esc_html__('URL') . '</label>';
	echo '<input type="text" name="url" value="' . esc_textarea( $url )  . '" class="widefat">';
}

/**
 * Save the metabox data
 */
public function cpt_save_events_meta( $post_id, $post ) {
	// Return if the user doesn't have edit permissions.
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return $post_id;
	}

	// Verify this came from the our screen and with proper authorization,
	// because save_post can be triggered at other times.
	if ( ! isset( $_POST['datepicker'] ) || ! isset( $_POST['location'] ) || ! isset( $_POST['url'] ) || ! wp_verify_nonce( $_POST['event_fields'], basename(__FILE__) ) ) {
		return $post_id;
	}

	// Now that we're authenticated, time to save the data.
	// This sanitizes the data from the field and saves it into an array $events_meta.
	$events_meta['datepicker'] = date_i18n("Y/m/d", strtotime( $_POST['datepicker'] ));
	$events_meta['location'] = esc_textarea( $_POST['location'] );
	$events_meta['url'] = esc_textarea( $_POST['url'] );

	// Cycle through the $events_meta array.
	// Note, in this example we just have one item, but this is helpful if you have multiple.
	foreach ( $events_meta as $key => $value ) :

		// Don't store custom data twice
		if ( 'check' === $post->post_type ) {
			return;
		}

		if ( get_post_meta( $post_id, $key, false ) ) {
			// If the custom field already has a value, update it.
			update_post_meta( $post_id, $key, $value );
		} else {
			// If the custom field doesn't have a value, add it.
			add_post_meta( $post_id, $key, $value);
		}

		if ( ! $value ) {
			// Delete the meta key if there's no value
			delete_post_meta( $post_id, $key );
		}
	endforeach;
}


// Single Template
function cpt_single_customtype($single_template){
	global $post;
  
	   if ($post->post_type == 'events' ) {
			$single_template = plugin_dir_path(__FILE__) . 'templates/single-event.php';
	   }
	   return $single_template;
  }
  
  // Archive Template
  function cpt_archive_customtype($archive_template){
		global $post;
  
	   if ($post->post_type == 'events' ) {
			$archive_template = plugin_dir_path(__FILE__) . 'templates/archive-events.php';
	   }
	   return $archive_template;
  }
	
  function cpt_pre_get_posts( $query ) {
		
	if( is_admin() ) {
	  return $query; 
	}
  
	if( isset($query->query_vars['post_type']) && $query->query_vars['post_type'] == 'events') {
	  $query->set('orderby', 'meta_value_num'); 
	  $query->set('meta_key', 'datepicker');   
	  $query->set('order', 'DESC'); 
	}
	return $query;
  }

	/**
	 *
	 * Adding JavaScript scripts for the admin pages only
	 *
	 * Loading existing scripts from wp-includes or adding custom ones
	 *
	 */
	public function cpt_add_admin_JS( $hook ) {
		wp_enqueue_script( 'jquery-script', 'https://code.jquery.com/jquery-1.12.4.js', array( "jquery" ));
		wp_enqueue_script( 'jquery-script-ui', 'https://code.jquery.com/ui/1.12.1/jquery-ui.js');
		wp_enqueue_script( 'custom-script', plugins_url('/js/cpt-custom.js', __FILE__));
	}
	
	/**
	 * 
	 * Add CSS styles
	 * 
	 */
	public function cpt_add_CSS() {
		// Enqueue the custom styling
		wp_enqueue_style( 'custom-css', plugins_url('scss/custom.css', __FILE__));
	}
	
	/**
	 *
	 * Add admin CSS styles - available only on admin
	 *
	 */
	public function cpt_add_admin_CSS( $hook ) {
		wp_enqueue_style( 'jquery-css', // wrapped for brevity
            '//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css', [], null );
	}
	
	/**
	 * 
	 *  Adds a metabox to the right side of the screen under the “Publish” box
	 *   
	 */
	public function cpt_meta_boxes_callback() {
		add_meta_box(
			'cpt_events',
			'Events',
			array($this, 'cpt_events_output'),
			'events',
			'side',
			'default'
		);
	}
	
	/**
	 * Callback for saving a simple AJAX option with no page reload
	 */
	public function store_ajax_value() {
		if( isset( $_POST['data'] ) && isset( $_POST['data']['cpt_option_from_ajax'] ) ) {
			update_option( 'cpt_option_from_ajax' , $_POST['data']['cpt_option_from_ajax'] );
		}	
		die();
	}
	
	/**
	 * Callback for getting a URL and fetching it's content in the admin page
	 */
	public function fetch_ajax_url_http() {
		if( isset( $_POST['data'] ) && isset( $_POST['data']['cpt_url_for_ajax'] ) ) {
			$ajax_url = $_POST['data']['cpt_url_for_ajax'];
			
			$response = wp_remote_get( $ajax_url );
			
			if( is_wp_error( $response ) ) {
				echo json_encode( __( 'Invalid HTTP resource', 'dxbase' ) );
				die();
			}
			
			if( isset( $response['body'] ) ) {
				if( preg_match( '/<title>(.*)<\/title>/', $response['body'], $matches ) ) {
					echo json_encode( $matches[1] );
					die();
				}
			}
		}
		echo json_encode( __( 'No title found or site was not fetched properly', 'dxbase' ) );
		die();
	}
	
}


/**
 * Register activation hook
 *
 */
function cpt_on_activate_callback() {
	// Trigger our function that registers the custom post type plugin.
	$cpt_register = new EventListing();
	$cpt_register->cpt_post_type_events();
    // Clear the permalinks after the post type has been registered.
    flush_rewrite_rules(); 
}

/**
 * Register deactivation hook
 *
 */
function cpt_on_deactivate_callback() {
	// Unregister the post type, so the rules are no longer in memory.
    unregister_post_type( 'events' );
    // Clear the permalinks to remove our post type's rules from the database.
    flush_rewrite_rules();
}

// Initialize everything
$EventListing = new EventListing();
