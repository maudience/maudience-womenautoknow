<?php
// No dirrect access
if ( ! defined( 'WAK_RECALLS_VER' ) ) exit;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_car_has_recalls' ) ) :
	function wak_car_has_recalls( $make = '', $model = '', $year = '' ) {

		global $wpdb, $wak_recalls_db;

		$check = $wpdb->get_var( $wpdb->prepare( "
			SELECT COUNT(*) 
			FROM {$wak_recalls_db} 
			WHERE make = %s 
			AND model = %s 
			AND year = %d;", $make, $model, $year ) );

		if ( $check === NULL || $check == 0 )
			return false;

		return $check;

	}
endif;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_count_my_recalls' ) ) :
	function wak_count_my_recalls( $user_id = NULL ) {

		global $wpdb, $wak_recalls_db;

		$count = 0;
		$cars  = wak_get_my_cars( $user_id );

		if ( empty( $cars ) || $cars === false ) return 0;

		foreach ( $cars as $car ) {

			$check = $wpdb->get_var( $wpdb->prepare( "
				SELECT COUNT(*) 
				FROM {$wak_recalls_db} 
				WHERE make = %s 
				AND model = %s 
				AND year = %d;", $car->make, $car->model, $car->year ) );

			if ( $check === NULL ) $check = 0;

			$count = $count + $check;

		}

		return $count;

	}
endif;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_get_car_recalls' ) ) :
	function wak_get_car_recalls( $make = '', $model = '', $year = '' ) {

		global $wpdb, $wak_recalls_db;

		return $wpdb->get_results( $wpdb->prepare( "
			SELECT * 
			FROM {$wak_recalls_db} 
			WHERE make = %s 
			AND model = %s 
			AND year = %d;", $make, $model, $year ) );

	}
endif;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_add_recalls_author_tab' ) ) :
	function wak_add_recalls_author_tab( $tabs, $user ) {

		if ( ! $user->is_my_profile ) return $tabs;

		$active = '';
		if ( isset( $_GET['show'] ) && $_GET['show'] == 'recalls' )
			$active = 'recalls';

		$count = wak_count_my_recalls( $user->ID );
		$tabs['recalls'] = array(
			'classes' => ( ( $active == 'recalls' ) ? 'active' : '' ),
			'title'   => 'Recalls <span class="badge">' . $count . '</span>',
			'icon'    => 'fa-flash'
		);

		return $tabs;

	}
endif;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_recalls_my_tab' ) ) :
	function wak_recalls_my_tab() {

		global $wak_profile;

		$cars = wak_get_my_cars( $wak_profile->ID );

		if ( ! empty( $cars ) ) {

			$recalls = array();
			foreach ( $cars as $car ) {

				if ( ! wak_car_has_recalls( $car->make, $car->model, $car->year ) ) continue;

				$check = wak_get_car_recalls( $car->make, $car->model, $car->year );
				if ( ! empty( $check ) )
					$recalls = array_merge( $recalls, $check );

			}

			if ( ! empty( $recalls ) ) {

?>
<p class="clear clearfix"><a href="<?php echo home_url( '/resources/manufacturer-contacts/' ); ?>" class="btn btn-danger btn-block">Click here for manufacturer's contact information</a></p>
<?php

				foreach ( $recalls as $recall ) {

?>
<div class="recall-wrap">
	<ul>
		<li><strong>Issued by:</strong> <?php echo esc_attr( $recall->manufacturer_v ) ?></li>
		<li><strong>Component:</strong> <?php echo esc_attr( $recall->component ) ?></li>
		<li><strong>NHTSA Campaign Number:</strong> <?php echo esc_attr( $recall->campno ) ?></li>
	</ul>
	<h5>Summary:</h5>
	<p><?php echo nl2br( $recall->defect ); ?></p>
	<h5>Consequence if not resolved:</h5>
	<p><?php echo nl2br( $recall->consequence ); ?></p>
	<h5>Remedy:</h5>
	<p><?php echo nl2br( $recall->corrective ); ?></p>
	<h5>Notes:</h5>
	<p><?php echo nl2br( $recall->notes ); ?></p>
</div>
<?php

				}

?>
<p class="clear clearfix"><a href="<?php echo home_url( '/resources/manufacturer-contacts/' ); ?>" class="btn btn-danger btn-block">Click here for manufacturer's contact information</a></p>
<?php

			}
			else {

				echo '<p>There are currently no recalls for your registered cars.</p>';

			}

		}

		else {

			echo '<p>You do not have any cars registered.</p>';

		}

	}
endif;

?>