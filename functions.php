<?php

/**

 * Listify child theme.

 */

include 'inc/listify.php';
include 'inc/job-manager.php';

function listify_child_styles() {

    wp_enqueue_style( 'listify-child', get_stylesheet_uri() );

}

add_action( 'wp_enqueue_scripts', 'listify_child_styles', 999 );

/**
 * Attach custom data to the listing object.
 *
 * This does not output anything, it only passes the information to the Javascript template.
 */
add_filter( 'listify_get_listing_to_array', function( $data, $listing ) {
  // Get our custom value (this is the post_date from the WP_Post object). Update accordingly.
  $date = $listing->get_object()->post_date;
  
  // Only add if our custom field is not empty.
  if ( '' !== $date ) {
    $data['date'] = $date;
  }
  
  // Return modified list of data.
  return $data;
}, 10, 2 );
/**
 * Output our custom field in the Javascript template.
 *
 * This placeholder is replaced by the value of the data set above.
 */
add_action( 'listify_content_job_listing_meta', function() {
  global $post;

  $price = get_post_meta( $post->ID, '_job_price', true );
} );

/* Add info on single listing page */

function custom_listify_single_job_listing_meta() {
	global $post;

	echo '<br/>';
	echo '<div class="custom-info" >' . $post->author . '</div>'; // Change $post->post_date with your own meta key or text
}
add_action( 'listify_single_job_listing_meta', 'custom_listify_single_job_listing_meta', 40 );

add_action( 'single_job_listing_meta_end', 'display_job_price_data' );

function display_job_price_data() {
  global $post;

  $price = get_post_meta( $post->ID, '_job_price', true );

  if ( $price ) {
    echo '<div class="dish_price">' . __( '' ) . ' €' . esc_html( $price ) . '</div>';
  }
}

/**
 * This can either be done with a filter (below) or the field can be added directly to the job-filters.php template file!
 *
 * job-manager-filter class handling was added in v1.23.6
 */
add_action( 'job_manager_job_filters_search_jobs_start', 'filter_by_price_field' );
function filter_by_price_field() {
	?>
	<div class="search_prices">
		<label for="search_prices"><?php _e( 'Prijs', 'wp-job-manager' ); ?></label>
		<select name="filter_by_price" class="job-manager-filter">
			<option value=""><?php _e( 'Elke prijs', 'wp-job-manager' ); ?></option>
			<option value="upto10"><?php _e( 'Tot €10', 'wp-job-manager' ); ?></option>
			<option value="10-20"><?php _e( '€10 tot €20', 'wp-job-manager' ); ?></option>
			<option value="20-25"><?php _e( '€20 tot €25', 'wp-job-manager' ); ?></option>
			<option value="over25"><?php _e( '€25+', 'wp-job-manager' ); ?></option>
		</select>
	</div>
	<?php
}
/**
 * This code gets your posted field and modifies the job search query
 */
add_filter( 'job_manager_get_listings', 'filter_by_price_field_query_args', 10, 2 );
function filter_by_price_field_query_args( $query_args, $args ) {
	if ( isset( $_POST['form_data'] ) ) {
		parse_str( $_POST['form_data'], $form_data );
		// If this is set, we are filtering by price
		if ( ! empty( $form_data['filter_by_price'] ) ) {
			$selected_range = sanitize_text_field( $form_data['filter_by_price'] );
			switch ( $selected_range ) {
				case 'upto10' :
					$query_args['meta_query'][] = array(
						'key'     => '_job_price',
						'value'   => '10',
						'compare' => '<',
						'type'    => 'NUMERIC'
					);
				break;
				case 'over25' :
					$query_args['meta_query'][] = array(
						'key'     => '_job_price',
						'value'   => '25',
						'compare' => '>=',
						'type'    => 'NUMERIC'
					);
				break;
				default :
					$query_args['meta_query'][] = array(
						'key'     => '_job_price',
						'value'   => array_map( 'absint', explode( '-', $selected_range ) ),
						'compare' => 'BETWEEN',
						'type'    => 'NUMERIC'
					);
				break;
			}
			// This will show the 'reset' link
			add_filter( 'job_manager_get_listings_custom_filter', '__return_true' );
		}
	}
	return $query_args;
}