<?php
// No dirrect access
if ( ! defined( 'WAK_MYCARS_VER' ) ) exit;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_mycars_profile_log_tab' ) ) :
	function wak_mycars_profile_log_tab() {

		global $wak_profile;

		$args = array( 'user_id' => $wak_profile->ID );

		if ( isset( $_GET['car'] ) && $_GET['car'] )
			$args['car_id'] = absint( $_GET['car'] );

		if ( isset( $_GET['cat'] ) && $_GET['cat'] )
			$args['cat'] = sanitize_key( $_GET['cat'] );

		if ( isset( $_GET['orderby'] ) && $_GET['orderby'] )
			$args['orderby'] = sanitize_key( $_GET['orderby'] );

		$log = new WAK_Query_Maintenance_Log( $args );

		$cars = wak_get_my_cars( $wak_profile->ID );

?>
<div class="inline-row">
<?php
		$car_count = $total = 0;
		if ( ! empty( $cars ) ) {

?>
	<div class="widget">
		<h4 class="widget-title">Car Expenditures</h4>
	</div>
	<div class="row">
<?php

			$car_total = count( $cars );
			foreach ( $cars as $car ) {

				$car_count++;

				if ( $car_count != 1 && $car_count % 2 != 0 )
					echo '<div class="row">';

				$car_name = $car->name;
				if ( $car_name == '' )
					$car_name = $car->make . ' ' . $car->model . ' ' . $car->year;

				$total = wak_spent_on_car( $car->car_id );

?>
		<div class="col-md-6 col-sm-6 col-xs-6">
			<div><?php echo $car_name; ?></div>
			<h4>$ <?php echo $total; ?></h4>
		</div>
<?php

				if ( $car_count % 2 == 0 && $car_count != $car_total )
					echo '</div><!-- Boom: $car_count = ' . $car_count . ' -->';

			}

			if ( $car_count % 2 != 0 )
				echo '<div class="col-md-6 col-sm-6 col-xs-6"></div>';

?>
	</div>
	<div id="full-car-log-wrap">
		<div class="widget">
			<h4 class="widget-title">My Maintenance Log <a href="<?php echo home_url( '/maintenance-log/' ); ?>" class="btn btn-danger pull-right">Help</a></h4>
		</div>

		<div id="maintenance-log-body">

			<?php $log->display(); ?>

		</div>
	</div>
<?php

		}

?>
</div>
<?php

	}
endif;

?>