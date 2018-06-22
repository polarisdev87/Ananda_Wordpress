<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class WC_XR_Line_Item {

	/**
	 * @var string
	 */
	private $description = '';

	/**
	 * @var string
	 */
	private $account_code = '';

	/**
	 * @var string
	 */
	private $item_code = '';

	/**
	 * @var float
	 */
	private $unit_amount = 0;

	/**
	 * @var int
	 */
	private $quantity = 0;

	/**
	 * @var float
	 */
	private $line_amount = null;

	/**
	 * @var float
	 */
	private $tax_amount = 0;

	/**
	 * @var array
	 */
	private $tax_rate = array();

	/**
	 * @var float
	 */
	private $discount_rate = 0;

	/**
	 * @var WC_XR_Settings
	 */
	private $settings;

	/**
	 * WC_XR_Line_Item constructor.
	 *
	 * @param WC_XR_Settings $settings
	 */
	public function __construct( WC_XR_Settings $settings ) {
		$this->settings = $settings;
	}

	/**
	 * @return string
	 */
	public function get_description() {
		return apply_filters( 'woocommerce_xero_line_item_description', $this->description, $this );
	}

	/**
	 * @param string $description
	 */
	public function set_description( $description ) {
		$this->description = htmlspecialchars( $description );
	}

	/**
	 * @return string
	 */
	public function get_account_code() {
		return apply_filters( 'woocommerce_xero_line_item_account_code', $this->account_code, $this );
	}

	/**
	 * @param string $account_code
	 */
	public function set_account_code( $account_code ) {
		$this->account_code = $account_code;
	}

	/**
	 * @return string
	 */
	public function get_item_code() {
		return apply_filters( 'woocommerce_xero_line_item_item_code', $this->item_code, $this );
	}

	/**
	 * @param string $item_code
	 */
	public function set_item_code( $item_code ) {
		$this->item_code = $item_code;
	}

	/**
	 * @return float
	 */
	public function get_unit_amount() {
		return apply_filters( 'woocommerce_xero_line_item_unit_amount', $this->unit_amount, $this );
	}

	/**
	 * @param float $unit_amount
	 */
	public function set_unit_amount( $unit_amount ) {
		$this->unit_amount = round( floatval( $unit_amount ), 4 );
	}

	/**
	 * @return int
	 */
	public function get_quantity() {
		return apply_filters( 'woocommerce_xero_line_item_quantity', $this->quantity, $this );
	}

	/**
	 * @param int $quantity
	 */
	public function set_quantity( $quantity ) {
		$this->quantity = intval( $quantity );
	}

	/**
	 * @return float
	 */
	public function get_line_amount() {
		return apply_filters( 'woocommerce_xero_line_item_line_amount', $this->line_amount, $this );
	}

	/**
	 * @param float $line_amount
	 */
	public function set_line_amount( $line_amount ) {
		$this->line_amount = round( floatval( $line_amount ), 2 );
	}

	/**
	 * @return float
	 */
	public function get_tax_amount() {
		return apply_filters( 'woocommerce_xero_line_item_tax_amount', $this->tax_amount, $this );
	}

	/**
	 * @param float $tax_amount
	 */
	public function set_tax_amount( $tax_amount ) {
		$this->tax_amount = round( floatval( $tax_amount ), 2 );
	}

	/**
	 * @return array
	 */
	public function get_tax_rate() {
		return apply_filters( 'woocommerce_xero_line_item_tax_rate', $this->tax_rate, $this );
	}

	/**
	 * @param array $tax_rate
	 */
	public function set_tax_rate( $tax_rate ) {
		$this->tax_rate = $tax_rate;
	}

	/**
	 * @return float
	 */
	public function get_discount_rate() {
		return apply_filters( 'woocommerce_xero_line_item_discount_rate', $this->discount_rate, $this );
	}

	/**
	 * @param float $discount_rate
	 */
	public function set_discount_rate( $discount_rate ) {
		$this->discount_rate = round( floatval( $discount_rate ), 2 );
	}

	/**
	 * Creates a new tax type in the XERO system if one doesn't exist
	 * otherwise it passes the existing one
	 *
	 * @since 1.6.11
	 * @version 1.7.7
	 *
	 * @return string	The tax type for the line item
	 */
	public function get_tax_type() {

		// Create the logger to capture our interactions with Xero and the merchant's tax settings
		$line_item_description = $this->get_description();
		$logger = new WC_XR_Logger( $this->settings );
		$logger->write( "Getting tax type for line item ($line_item_description)" );

		// Is this item tax exempt? Tax exempt Xero tax types vary by country
		if ( $this->get_tax_amount() <= 0 ) {
			$tax_type = $this->get_tax_exempt_type_for_base_country();
			$logger->write( " - Item has zero tax. Returning tax (exempt) type ($tax_type)" );
			return $tax_type;
		}

		// OK, at this point we're going to have to consult Xero
		// to figure out the tax type.
		// Let's see if we have already fetched tax rates recently
		$xero_tax_rates = array();
		$transient_key = 'wc_xero_tax_rates';
		$transient_value = get_transient( $transient_key );
		if ( is_array( $transient_value ) ) {
			$xero_tax_rates = $transient_value;
		}

		// If we don't have tax rates, time to fetch them
		if ( empty( $xero_tax_rates ) ) {
			$logger->write( " - Found no tax rates in transient... fetching from Xero" );

			$tax_rates_request = new WC_XR_Request_Tax_Rate( $this->settings );
			$tax_rates_request->do_request();
			$xml_response = $tax_rates_request->get_response_body_xml();

			if ( empty ( $xml_response->TaxRates->TaxRate ) ) {
				$logger->write( " - Error - unable to retrieve tax rates from Xero" );
				$logger->write( " - Returning (default) tax type (OUTPUT)" );
				return 'OUTPUT';
			} else {
				// Prepare the rates for caching
				$logger->write( " - Successfully retrieved tax rates from Xero" );
				foreach ( $xml_response->TaxRates->children() as $key => $value ) {
					$name_to_add = $value->Name->__toString();
					$tax_type_to_add = $value->TaxType->__toString();
					$report_tax_type_to_add = isset( $value->ReportTaxType ) ? $value->ReportTaxType->__toString() : '';
					$effective_rate_to_add = floatval( $value->EffectiveRate->__toString() );
					$rate_status = $value->Status->__toString();

					if ( 'ACTIVE' === $rate_status ) {
						$logger->write( " - Caching Name ($name_to_add), TaxType ($tax_type_to_add), ReportTaxType ($report_tax_type_to_add), EffectiveRate ($effective_rate_to_add)" );
						$xero_tax_rates[] = array(
							'name' => $name_to_add,
							'tax_type' => $tax_type_to_add,
							'report_tax_type' => $report_tax_type_to_add,
							'effective_rate' => $effective_rate_to_add
						);
					} else {
						$logger->write( " - Skipping Name ($name_to_add), TaxType ($tax_type_to_add), ReportTaxType ($report_tax_type_to_add), EffectiveRate ($effective_rate_to_add), Status ($rate_status)" );
					}
				}

				set_transient( $transient_key, $xero_tax_rates, 1 * HOUR_IN_SECONDS );
			}
		}

		// Iterate over the tax rates looking for our rate (e.g. 10) and label/name (e.g. "GST")
		$tax_rate = $this->get_tax_rate();
		$rate_to_find = floatval( $tax_rate[ 'rate' ] );

		// It is possible the Tax Name (label) in WooCommerce > Settings > Tax Rates is empty.
		// If so, then choose a base location appropriate default
		if ( empty( $tax_rate['label'] ) ) {
			$base_country = WC()->countries->get_base_country();
			switch( $base_country ) {
				case 'AU':
				case 'NZ':
					$tax_rate['label'] = 'GST';
					break;
				case 'GB':
					$tax_rate['label'] = 'VAT';
					break;
				default:
					$tax_rate['label'] = 'Tax';
			}
			$logger->write( " - Rate ($rate_to_find) has an empty Tax Name. Will use label ({$tax_rate['label']}) by default." );
		}

		// Add the rate to the label to make it unique
		$tax_rate['label'] .= ' ' . sprintf( '(%.2F%%)', $rate_to_find );

		$logger->write( " - Searching rates for label ({$tax_rate['label']}) and rate ($rate_to_find)" );
		$tax_type = self::get_tax_type_for_label_and_rate( $xero_tax_rates, $tax_rate['label'], $rate_to_find );
		if ( ! empty( $tax_type) ) {
			$logger->write( " - Found and returning tax type ($tax_type)" );
			return $tax_type;
		}

		$logger->write( " - Could not find a cached tax type for that label and rate. Attempting to add new one to Xero." );

		// If no tax rate was found, ask Xero to add one for us

		// First, see if we need a ReportTaxType
		$report_tax_type = $this->get_report_tax_type_for_base_country();
		if ( ! empty( $report_tax_type ) ) {
			$tax_rate['report_tax_type'] = $report_tax_type;
			$logger->write( " - Setting ReportTaxType to ($report_tax_type)" );
		}

		$tax_type_create_request = new WC_XR_Request_Create_Tax_Rate( $this->settings, $tax_rate );
		$tax_type_create_request->do_request();
		$xml_response = $tax_type_create_request->get_response_body_xml();

		if ( ! empty( $xml_response->TaxRates->TaxRate->TaxType ) ) {
			$tax_type = $xml_response->TaxRates->TaxRate->TaxType->__toString();

			// Delete our transient so the next fetch will store the new rate and type
			delete_transient( $transient_key );

			// Return the type to the caller
			$logger->write( " - Successfully added tax rate to Xero" );
			$logger->write( " - Returning tax type ($tax_type)" );
			return $tax_type;
		}

		// Log the error and return an empty string
		$logger->write( " - Error - unable to add rate to Xero  - Returning empty tax type ()" );
		$logger->write( print_r( $xml_response, true ) );
		return '';
	}

	/**
	 * Returns an appropriate tax type for tax-exempt line items based on the country, options setting
	 * and whether this is a shipping line item. For tax exempt items, Australia requires a tax type of
	 * EXEMPTOUTPUT for income items or of EXEMPTEXPENSES for expense items.  Since merchants may
	 * elect to treat shipping as an expense or as income, we need to take that into account too.
	 *
	 * NONE:			Appropriate for tax exempt items for all countries except AU
	 *
	 * EXEMPTOUTPUT: 	Line item would be output taxed (income, sometimes shipping),
	 * 					except this particular line item is exempt of tax (AU only)
	 * EXEMPTEXPENSES:	Line item would be input taxed (expense, typically services like shipping),
	 * 					except this particular line item is exempt of tax (AU only)
	 *
	 * @since 1.7.7
	 * @version 1.7.7
	 *
	 * @return string	NONE | EXEMPTOUTPUT | EXEMPTEXPENSES
	 */
	protected function get_tax_exempt_type_for_base_country() {
		$tax_rate = $this->get_tax_rate();
		$is_shipping_line_item = array_key_exists( 'is_shipping_line_item', $tax_rate ) && $tax_rate['is_shipping_line_item'];
		$base_country = WC()->countries->get_base_country();

		$tax_exempt_type = 'NONE';
		if ( 'AU' === $base_country ) {
			$tax_exempt_type = 'EXEMPTOUTPUT';
			if ( $is_shipping_line_item ) {
				$treat_shipping_as = $this->settings->get_option( 'treat_shipping_as' );
				$tax_exempt_type = ( 'income' === $treat_shipping_as ) ? 'EXEMPTOUTPUT' : 'EXEMPTEXPENSES';
			}
		}
		return $tax_exempt_type;
	}

	/**
	 * Returns an appropriate report tax type (if any) for the line item for the country. Since
	 * merchants may elect to treat shipping as an expense or as income, we need to take that into account too.
	 *
	 * Only AU, NZ and GB have report tax types
	 *
	 * OUTPUT: 		Line item's report tax type should be income (and therefore output taxed)
	 * 				Output taxes are ad valorem tax charged on the selling price of taxable items
	 * 				Note: Shipping (esp flat rate) is treated as income by some merchants
	 * INPUT:		Line item's report tax type should be expense (and therefore input taxed)
	 * 				Input taxes are taxes charged on services (e.g. shipping) which a business consumes/uses in its operations
	 *
	 * @since 1.7.7
	 * @version 1.7.7
	 *
	 * @return string	(empty) | OUTPUT | INPUT
	 */
	protected function get_report_tax_type_for_base_country() {
		$tax_rate = $this->get_tax_rate();
		$is_shipping_line_item = array_key_exists( 'is_shipping_line_item', $tax_rate ) && $tax_rate['is_shipping_line_item'];
		$base_country = WC()->countries->get_base_country();

		$report_tax_type = '';
		if ( in_array( $base_country, array( 'AU', 'NZ', 'GB' ) ) ) {
			$report_tax_type = 'OUTPUT';
			if ( $is_shipping_line_item ) {
				$treat_shipping_as = $this->settings->get_option( 'treat_shipping_as' );
				$report_tax_type = ( 'income' === $treat_shipping_as ) ? 'OUTPUT' : 'INPUT';
			}
		}

		return $report_tax_type;
	}

	/**
	 * Search an array of (active) tax rates from Xero for the one that matches the given label and rate in WooCommerce
	 *
	 * @param array $tax_rates		An array of tax rates retrieved from Xero
	 * @param array $label_to_find	The name of the rate to find (i.e. the Tax Name from WooCommerce > Settings > Tax > Standard Rates)
	 * @param array $rate_to_find	The rate (in percent) to find
	 *
	 * @return string The xero tax type found (e.g. "OUTPUT") or an empty string if not found
	 */
	protected static function get_tax_type_for_label_and_rate( $tax_rates, $label_to_find, $rate_to_find ) {
		$tax_type = '';
		foreach ( $tax_rates as $tax_rate ) {
			if ( strcasecmp( $tax_rate[ 'name' ], $label_to_find ) == 0 ) {
				if ( abs( $rate_to_find - $tax_rate[ 'effective_rate' ] ) <= 0.0001 ) {
					$tax_type = $tax_rate[ 'tax_type' ];
					break;
				}
			}
		}

		return $tax_type;
	}

	/**
	 * Format the line item to XML and return the XML string
	 *
	 * @return string
	 */
	public function to_xml() {
		$xml = '<LineItem>';

		// Description
		if ( '' !== $this->get_description() ) {
			$xml .= '<Description>' . $this->get_description() . '</Description>';
		}

		// Account code
		if ( '' !== $this->get_account_code() ) {
			$xml .= '<AccountCode>' . $this->get_account_code() . '</AccountCode>';
		}

		// Check if there's an item code
		if ( '' !== $this->get_item_code() ) {
			$xml .= '<ItemCode>' . htmlspecialchars( $this->get_item_code(), ENT_XML1, 'UTF-8' ) . '</ItemCode>';
		}

		$xml .= '<UnitAmount>' . $this->get_unit_amount() . '</UnitAmount>';

		// Quantity
		$xml .= '<Quantity>' . $this->get_quantity() . '</Quantity>';

		// Tax Amount
		$tax_type = $this->get_tax_type();
		if ( ! empty( $tax_type ) ) {
			$xml .= '<TaxType>' . $tax_type . '</TaxType>';
		}
		$xml .= '<TaxAmount>' . $this->get_tax_amount() . '</TaxAmount>';

		// Discount?
		$discount_rate = $this->get_discount_rate();
		if ( 0.001 < abs( $discount_rate ) ) {
			$xml .= '<DiscountRate>' . $discount_rate . '</DiscountRate>';
		}

		$xml .= '</LineItem>';

		return $xml;
	}
}
