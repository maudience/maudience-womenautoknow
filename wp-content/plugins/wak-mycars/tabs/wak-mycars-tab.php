<?php
// No dirrect access
if ( ! defined( 'WAK_MYCARS_VER' ) ) exit;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_mycars_author_tab' ) ) :
	function wak_mycars_author_tab() {

		global $wak_profile;

		$states      = wak_get_states();
		$wak_profile = ( get_query_var( 'author_name' ) ) ? get_user_by( 'slug', get_query_var( 'author_name' ) ) : get_userdata( get_query_var( 'author' ) );

		$user_id     = $wak_profile->ID;
		$cars        = wak_get_my_cars( $user_id );
		$profile_url = wak_theme_get_profile_url( $user_id );

?>
<div class="inline-row">
	<div id="wak-my-cars">
		<header><h1 class="pink"><?php echo $wak_profile->display_name; ?></h1></header>
		<div class="widget"><h4 class="widget-title">My Cars<button type="button" data-backdrop="static" class="btn btn-danger pull-right" data-toggle="modal" data-target="#wak-add-car">Add New Car</button></h4></div>

<?php

		if ( ! empty( $cars ) ) {

			foreach ( $cars as $car ) {

				$total = wak_spent_on_car( $car->car_id );

				$zero = '';
				if ( $total == 0.00 )
					$zero = 'zero';

?>

		<div class="row" id="my-car<?php echo $car->car_id; ?>">
			<div class="col-md-6 col-sm-6 col-xs-12">
				<div id="car-<?php echo $car->car_id; ?>" class="cars type-cars status-publish hentry">
					<h4>
						<?php if ( $car->name != '' ) echo $car->name; else echo '-'; ?>
						<?php wak_show_car_details( $car ); ?>
					</h4>
					<p class="mycar-action-links"><a href="<?php echo add_query_arg( array( 'show' => 'log', 'car' => $car->car_id ), $profile_url ); ?>" title="View this cars maintenance log" class="btn btn-default btn-xs">View Log</a> <a href="#" data-backdrop="static" data-toggle="modal" data-target="#wak-edit-car" data-car="<?php echo $car->car_id; ?>" class="btn btn-danger btn-xs">Edit Car</a></p>
				</div>
			</div>
			<div class="col-md-6 col-sm-6 col-xs-12">
				<div class="spent-on-car text-right">
					<h1 class="<?php echo $zero; ?>" data-toggle="tooltip" data-placement="top" title="Total amount spent on this car.">$ <?php echo $total; ?></h1>
				</div>
			</div>
		</div>

<?php

			}

		}
		else {

?>

		<div class="row">
			<div class="col-md-12">
				<p>You have no cars registered yet.</p>
			</div>
		</div>

<?php

		}

?>

	</div>
</div>
<div class="modal fade" id="wak-add-car">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title pink"><i class="fa fa-car"></i>Add New Car</h4>
			</div>
			<div class="modal-body">
				<h1 class="text-center pink"><i class="fa fa-spinner fa-spin"></i></h1>
				<p class="text-center">loading form ...</p>
			</div>
		</div>
	</div>
</div>
<div class="modal fade" id="wak-edit-car">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title pink"><i class="fa fa-pencil-square-o"></i>Edit Car</h4>
			</div>
			<div class="modal-body">
				<h1 class="text-center pink"><i class="fa fa-spinner fa-spin"></i></h1>
				<p class="text-center">loading car details ...</p>
			</div>
		</div>
	</div>
</div>
<div class="modal fade" id="wak-view-car-log">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title pink"><i class="fa fa-list"></i>View Car Log</h4>
			</div>
			<div class="modal-body">
				<h1 class="text-center pink"><i class="fa fa-spinner fa-spin"></i></h1>
				<p class="text-center">loading car log ...</p>
			</div>
		</div>
	</div>
</div>
<?php

	}
endif;

?>