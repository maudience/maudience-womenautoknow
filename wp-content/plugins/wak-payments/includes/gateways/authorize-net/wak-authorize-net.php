<?php
// No dirrect access
if ( ! defined( 'WAK_PAYMENTS_VER' ) ) exit;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_authorize_net_subscription' ) ) :
	function wak_authorize_net_subscription( $data = array(), $plan = array(), $annual = false ) {

		$prefs = wak_payments_plugin_settings();
		$now   = current_time( 'timestamp' );

		define( "AUTHORIZENET_API_LOGIN_ID",    $prefs['authorize_net_api'] );
		define( "AUTHORIZENET_TRANSACTION_KEY", $prefs['authorize_net_key'] );

		if ( $prefs['authorize_net_test'] == 1 )
			define( "AUTHORIZENET_SANDBOX", true );

		wak_load_authorize_net();

		$subscription                           = new AuthorizeNet_Subscription;
		$subscription->name                     = $plan['payment'];
		$subscription->trialOccurrences         = 0;
		$subscription->trialAmount              = 0;
		$subscription->intervalLength           = 1;
		$subscription->intervalUnit             = 'months';
		$subscription->startDate                = date( 'Y-m-d', $now );
		$subscription->totalOccurrences         = ( $annual ) ? 5 : 12;
		$subscription->amount                   = number_format( $plan['cost'], 2, '.', '' );
		$subscription->creditCardCardNumber     = $data['card'];
		$subscription->creditCardExpirationDate = $data['exp_yy'] . '-' . $data['exp_mm'];
		$subscription->creditCardCardCode       = $data['cvv'];
		$subscription->billToFirstName          = $data['first_name'];
		$subscription->billToLastName           = $data['last_name'];

		if ( $annual ) {

			$subscription->billToAddress = $data['billing-address'];
			$subscription->billToCity    = $data['billing-city'];
			$subscription->billToZip     = $data['billing-zip'];
			$subscription->billToState   = $data['billing-state'];
			$subscription->billToCountry = 'US';

		}

		$request  = new AuthorizeNetARB;
		$request->setRefId( $data['payment_id'] );

		$response = $request->createSubscription( $subscription );
		if ( $response->isOk() )
			return $response->getSubscriptionId();

		return array(
			'errors' => $response->getErrorMessage()
		);

	}
endif;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_authorize_net_charge' ) ) :
	function wak_authorize_net_charge( $data = array(), $plan = array() ) {

		$prefs = wak_payments_plugin_settings();
		$now   = current_time( 'timestamp' );

		define( "AUTHORIZENET_API_LOGIN_ID",    $prefs['authorize_net_api'] );
		define( "AUTHORIZENET_TRANSACTION_KEY", $prefs['authorize_net_key'] );

		if ( $prefs['authorize_net_test'] == 1 )
			define( "AUTHORIZENET_SANDBOX", true );

		wak_load_authorize_net();

		$sale           = new AuthorizeNetAIM;
		$sale->amount   = number_format( $plan['cost'], 2, '.', '' );
		$sale->card_num = $data['card'];
		$sale->exp_date = $data['exp_mm'] . '/' . substr( $data['exp_yy'], 2 );

		$sale->setCustomField( 'payment_id', $data['payment_id'] );

		$response = $sale->authorizeAndCapture();

		if ( $response->approved )
		    return $response->transaction_id;

		return array(
			'errors' => $response->response_reason_text
		);

	}
endif;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_authorize_net_cancel_subscription' ) ) :
	function wak_authorize_net_cancel_subscription( $subscription_id = NULL ) {

		$prefs = wak_payments_plugin_settings();
		$now   = current_time( 'timestamp' );

		define( "AUTHORIZENET_API_LOGIN_ID",    $prefs['authorize_net_api'] );
		define( "AUTHORIZENET_TRANSACTION_KEY", $prefs['authorize_net_key'] );

		if ( $prefs['authorize_net_test'] == 1 )
			define( "AUTHORIZENET_SANDBOX", true );

		wak_load_authorize_net();

		$request = new AuthorizeNetARB;

		$cancellation = $request->cancelSubscription( $subscription_id );

		if ( $cancellation->isOk() )
			return true;

		return $cancellation->response;

	}
endif;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_load_authorize_net' ) ) :
	function wak_load_authorize_net() {

		if ( class_exists( 'AuthorizeNetARB' ) ) return;

		require_once WAK_PAYMENTS_INCLUDES . 'gateways/authorize-net/lib/autoload.php';

	}
endif;

?>