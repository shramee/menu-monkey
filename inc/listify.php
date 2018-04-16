<?php

class Listify_child_Listify_Tweaks {

	/** @var self Instance */
	private static $_instance;

	/**
	 * Returns instance of current calss
	 * @return self Instance
	 */
	public static function instance() {
		if ( ! self::$_instance ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function __construct() {
		add_action( 'get_user_metadata', [ $this, 'filter_user_metadata' ], 11, 4 );
		add_action( 'listify_widget_author_biography', [ $this, 'filter_author_bio' ], 11, 4 );
		add_action( 'listify_get_listing_to_array', [ $this, 'listing_data' ], 11, 4 );
	}

	public function filter_user_metadata( $val, $id, $meta, $single ) {
		if ( $meta == 'shipping_city' ) {
			$val = get_user_meta( $id, 'account_restaurantaddress', $single );
		} else if ( $meta == 'nickname' ) {
			$val = get_user_meta( $id, 'account_restaurantname', $single );
		} else if ( $meta == 'shipping_state' ) {
			$val = get_user_meta( $id, 'account_restaurantcity', $single );
		}
		return $val;
	}

	public function listing_data( $data ) {
		$data['restaurant'] = get_user_meta( $data['object']->post_author, 'account_restaurantname', 1 );
		$data['price'] = wc_price( get_post_meta( $data['id'], '_job_price', 1 ) );

		return $data;
	}

	public function filter_author_bio( $bio ) {
		$id = get_the_author_meta( 'ID' );
		ob_start();
		?>
		<aside id="listify_widget_author_biography-4" class="widget widget--author widget--author-sidebar listify_widget_author_biography">
			<h3 class="widget-title widget-title--author widget--author-sidebar %s">
				<?php echo get_user_meta( $id, 'account_restaurantname', 1 ) ?>
			</h3>
			<div class="author-location">
				<?php
				echo get_user_meta( $id, 'account_restaurantaddress', 1 ) . ', ' . get_user_meta( $id, 'account_restaurantcity', 1 );
				?>
			</div>
			<?php echo get_user_meta( $id, 'description', 1 ); ?>
		</aside>

		<?php
		return ob_get_clean();
	}
}

Listify_child_Listify_Tweaks::instance();