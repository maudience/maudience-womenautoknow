<?php

	if ( ! isset( $_GET['car'] ) || ! is_user_logged_in() ) exit;

	$log = wak_get_cars_log( $_GET['car'] );
	$car = wak_get_car( $_GET['car'] );

	if ( ! isset( $car->user_id ) || $car->user_id != get_current_user_id() ) {

		wp_die( 'Please login to view your cars log.' );

	}

	$date_format = get_option( 'date_format' );
	$categories  = wak_get_log_categories();

	$details = array();

	if ( $car->make != '' )
		$details[] = $car->make;

	if ( $car->model != '' )
		$details[] = $car->model;

	if ( $car->year != '' )
		$details[] = $car->year;

	if ( $car->name != '' )
		$details[] = '( ' . $car->name . ' )';

	$details = implode( ' ', $details );

	$nothing = __( 'There are no log entries for this car.', '' );
	if ( $car->name != '' )
		$nothing = sprintf( __( '%s does not have any log entries yet.', '' ), $car->name );

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width">
	<link rel="profile" href="http://gmpg.org/xfn/11">
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
	<!--[if lt IE 9]>
	<script src="<?php echo esc_url( get_template_directory_uri() ); ?>/js/html5.js"></script>
	<![endif]-->
	<script>(function(){document.documentElement.className='js'})();</script>

	<?php wp_head(); ?>
	<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">

	<style type="text/css">
		#print-header { margin-bottom: 2em; }
		#log-header { margin-bottom: 1em; border-bottom: 1px solid #dedede; }
		#print-header button { margin-left: 24px; }
		.row .nothing { text-align: center; padding: 2em 0; }
		.row.alt { background-color: white; }
		.row > div > p { margin-bottom: 0; padding: 6px 0; }
		@media print {
		  a[href]:after {
		    content: none !important;
		  }
		}
	</style>

</head>

<body <?php body_class(); ?> onload="window.print()">

	<div class="container">

		<div class="row" id="print-header">
			<div class="col-md-3 col-sm-3 col-xs-3">
				<a id="logo" href="<?php echo home_url(); ?>"><img src="<?php echo esc_url( get_template_directory_uri() . '/images/logo.png' ); ?>" alt="" /></a>
			</div>
			<div class="col-md-9 col-sm-9 col-xs-9">
				<h1 class="text-right pink"><?php _e( 'My Vehicle Log', '' ); ?></h1>
				<h3 class="text-right"><?php echo esc_attr( $details ); ?></h3>
				<div><button type="button" onclick="window.close();" class="btn btn-default pull-right hidden-print">Close Window</button> <button type="button" class="btn btn-danger pull-right hidden-print" onclick="javascript:window.print();">Print</button></div>
			</div>
		</div>

		<div class="row" id="log-header">
			<div class="col-md-3 col-sm-3 col-xs-3">
				<p><strong>Date</strong></p>
			</div>
			<div class="col-md-3 col-sm-3 col-xs-3">
				<p><strong>Category</strong></p>
			</div>
			<div class="col-md-6 col-sm-6 col-xs-6">
				<p><strong>Entry</strong></p>
			</div>
		</div>

		<?php if ( ! empty( $log ) ) { $count = 0; foreach ( $log as $entry ) : $count ++; ?>

		<div class="row<?php if ( $count % 2 == 0 ) echo ' alt'; ?>">
			<div class="col-md-3 col-sm-3 col-xs-3">
				<p><?php echo date( $date_format, $entry->time ); ?></p>
			</div>
			<div class="col-md-3 col-sm-3 col-xs-3">
				<p><?php echo ( ( array_key_exists( $entry->detail, $categories ) ) ? $categories[ $entry->detail ] : '-' ); ?></p>
			</div>
			<div class="col-md-6 col-sm-6 col-xs-6">
				<p><?php echo esc_attr( $entry->entry ); ?></p>
			</div>
		</div>

		<?php endforeach; } else { ?>

		<div class="row">
			<div class="col-md-12 col-sm-12 col-xs-12">

				<p class="nothing"><?php echo $nothing; ?></p>

			</div>
		</div>

		<?php } ?>

	</div>

</body>
</html>