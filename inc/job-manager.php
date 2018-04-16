<?php

class Listify_child_Job_Manager {

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

	function __construct() {
		add_filter( 'submit_job_form_fields', [ $this, 'submit_job_form_fields' ], 25 );
		add_filter( 'job_manager_job_listing_data_fields', [ $this, 'admin_add_price_field' ], 25 );

	}

	public function admin_add_price_field( $fields ) {
		$fields['_job_price'] = array(
			'label'       => __( 'Prijs (€)', 'job_manager' ),
			'type'        => 'text',
			'placeholder' => 'bijv. 20',
			'description' => ''
		);
		return $fields;
	}

	public function submit_job_form_fields( $fields ) {
		$fields['job']['job_price'] = array(
			'label'       => __( 'Prijs (€)', 'job_manager' ),
			'type'        => 'text',
			'required'    => true,
			'placeholder' => 'bijv. 20',
			'priority'    => 7
		);

		$fields['job']['job_location']['required'] = false;
		$fields['job']['job_location']['description'] = false;
		$fields['job']['job_location']['value'] =
			get_user_meta( get_current_user_id(), 'account_restaurantaddress', 'single' ) . ', ' .
			get_user_meta( get_current_user_id(), 'account_restaurantcity', 'single' );
		return $fields;
	}
}

Listify_child_Job_Manager::instance();