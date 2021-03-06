<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class WC_XR_Request_Contact_History extends WC_XR_Request {

	public function __construct( WC_XR_Settings $settings, $uid = null, $history = true ) {
		parent::__construct( $settings );

		$this->set_method( 'GET' );

		$endpoint = 'Contacts/' . $uid;

		if ($history) {
			$endpoint .= '/history';
		}
		$this->set_endpoint( $endpoint );
	}

}
