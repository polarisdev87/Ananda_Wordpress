<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class WC_XR_Contact_Manager {

	/**
	 * @var WC_XR_Settings
	 */
	private $settings;

	/**
	 * WC_XR_Contact_Manager constructor.
	 *
	 * @param WC_XR_Settings $settings
	 */
	public function __construct( WC_XR_Settings $settings ) {
		$this->settings = $settings;
	}

	public function get_all_contacts() {
		$request = new WC_XR_Request_Contact($this->settings);

		$request->do_request();
		$xml_response = $request->get_response_body();

		return $xml_response;
	}

	/**
	 * @param WC_Order $order
	 *
	 * @return WC_XR_Address
	 */
	public function get_address_by_order( $order ) {

		// Setup address object
		$address = new WC_XR_Address();

		$old_wc = version_compare( WC_VERSION, '3.0', '<' );

		// Set line 1
		$billing_address_1 = $old_wc ? $order->billing_address_1 : $order->get_billing_address_1();
		$address->set_line_1( $billing_address_1 );

		// Set city
		$billing_city = $old_wc ? $order->billing_city : $order->get_billing_city();
		$address->set_city( $billing_city );

		// Set region
		$billing_state = $old_wc ? $order->billing_state : $order->get_billing_state();
		$address->set_region( $billing_state );

		// Set postal code
		$billing_postcode = $old_wc ? $order->billing_postcode : $order->get_billing_postcode();
		$address->set_postal_code( $billing_postcode );

		// Set country
		$billing_country = $old_wc ? $order->billing_country : $order->get_billing_country();
		$address->set_country( $billing_country );

		// Set AttentionTo
		$billing_first_name = $old_wc ? $order->billing_first_name : $order->get_billing_first_name();
		$billing_last_name  = $old_wc ? $order->billing_last_name : $order->get_billing_last_name();
		$address->set_attentionto( $billing_first_name . ' ' . $billing_last_name );

		// Set line 2
		$billing_address_2 = $old_wc ? $order->billing_address_2 : $order->get_billing_address_2();
		if ( strlen( $billing_address_2 ) > 0 ) {
			$address->set_line_2( $billing_address_2 );
		}

		// Return address object
		return $address;
	}

	/**
	 * Returns a xero contact ID based on an email address if one is found
	 * null otherwise
	 * @param  string $email
	 * @return string|null
	 */
	public function get_id_by_email( $email ) {

		if ( ! $email ) {
			return null;
		}

		$contact_request = new WC_XR_Request_Contact( $this->settings, $email );

		$transient_key = 'wc_xero_contact_id_' . md5( $email );
		if ( get_transient( $transient_key ) ) {
			return get_transient( $transient_key );
		}
		$contact_request->do_request();
		$xml_response = $contact_request->get_response_body_xml();

		if ( 'OK' == $xml_response->Status
		     && ! empty( $xml_response->Contacts )
		     && $xml_response->Contacts->Contact->ContactID->__toString() ) {

				$contact_id  = $xml_response->Contacts->Contact->ContactID->__toString();
				set_transient( $transient_key, $contact_id, 31 * DAY_IN_SECONDS );
				return $contact_id;

		}

		return null;
	}

	/**
	 * @param WC_Order $order
	 *
	 * @return WC_XR_Contact
	 */
	public function get_contact_by_order( $order ) {
		// Setup Contact object
		$contact = new WC_XR_Contact();

		$old_wc = version_compare( WC_VERSION, '3.0', '<' );

		$billing_company    = $old_wc ? $order->billing_company : $order->get_billing_company();
		$billing_first_name = $old_wc ? $order->billing_first_name : $order->get_billing_first_name();
		$billing_last_name  = $old_wc ? $order->billing_last_name : $order->get_billing_last_name();

		// Set Invoice name
		if ( strlen( $billing_company ) > 0 ) {
			$invoice_name = $billing_company;
		} else {
			$invoice_name = $billing_first_name . ' ' . $billing_last_name;
		}

		$billing_email = $old_wc ? $order->billing_email : $order->get_billing_email();
		$contact_id = $this->get_id_by_email( $billing_email );
		$contact_id_only = null;

		// See if a previous contact exists
		if ( ! empty ( $contact_id ) ) {
			$contact->set_id( $contact_id );
			$contact_id_only = $contact;
		}

		// Set name
		$contact->set_name( $invoice_name );

		// Set first name
		$contact->set_first_name( $billing_first_name );

		// Set last name
		$contact->set_last_name( $billing_last_name );

		// Set email address
		$contact->set_email_address( $billing_email );

		// Set address
		$contact->set_addresses( array( $this->get_address_by_order( $order ) ) );

		// Set phone
		$billing_phone = $old_wc ? $order->billing_phone : $order->get_billing_phone();
		$contact->set_phones( array( new WC_XR_Phone( $billing_phone ) ) );

		// Return contact

		if ( ! is_null( $contact_id_only ) ) {

			$transient_key = 'wc_xero_contact_'. md5( serialize( $contact_id_only ) );
			if ( get_transient( $transient_key ) ) {
				return get_transient( $transient_key );
			}
			// Update a contact if we pulled info from a previous thing
			$contact_request_update = new WC_XR_Request_Update_Contact( $this->settings, $contact_id, $contact );
			$contact_request_update->do_request();

			set_transient( $transient_key, $contact_id_only, 31 * DAY_IN_SECONDS );
			return $contact_id_only;
		}

		return $contact;
	}

}
