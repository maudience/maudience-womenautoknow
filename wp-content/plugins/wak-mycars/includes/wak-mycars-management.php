<?php
// No dirrect access
if ( ! defined( 'WAK_MYCARS_VER' ) ) exit;

/**
 *
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_mycars_car_log_template' ) ) :
	function wak_mycars_car_log_template( $template ) {

		if ( isset( $_GET['view'] ) && $_GET['view'] == 'car-log' && isset( $_GET['car'] ) && is_numeric( $_GET['car'] ) )
			return WAK_MYCARS_TEMPLATES . 'view-car-log.php';

		return $template;

	}
endif;

/**
 *
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_mycars_admin_menu' ) ) :
	function wak_mycars_admin_menu() {

		$pages = array();

		$pages[] = add_menu_page(
			__( 'Cars', 'wakmycars' ),
			__( 'Cars', 'wakmycars' ),
			'moderate_comments',
			'wak-member-cars',
			'wak_mycars_overview_admin_screen',
			'dashicons-chart-bar',
			71
		);

		$pages[] = add_submenu_page(
			'wak-member-cars',
			__( 'Overview', 'wakmycars' ),
			__( 'Overview', 'wakmycars' ),
			'moderate_comments',
			'wak-member-cars',
			'wak_mycars_overview_admin_screen'
		);

		$pages[] = add_submenu_page(
			'wak-member-cars',
			__( 'All Cars', 'wakmycars' ),
			__( 'All Cars', 'wakmycars' ),
			'moderate_comments',
			'wak-member-thecars',
			'wak_mycars_cars_admin_screen'
		);

		

		$pages[] = add_submenu_page(
			'wak-member-cars',
			__( 'Maintenance Logs', 'wakmycars' ),
			__( 'Maintenance Logs', 'wakmycars' ),
			'moderate_comments',
			'wak-member-carlogs',
			'wak_mycars_carlogs_admin_screen'
		);

		foreach ( $pages as $page ) {
			add_action( 'admin_print_styles-' . $page, 'wak_mycars_admin_screen_styles' );
			add_action( 'load-' . $page,               'wak_mycars_admin_load' );
		}

	}
endif;

/**
 *
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_mycars_admin_screen_styles' ) ) :
	function wak_mycars_admin_screen_styles() {

		if ( $_GET['page'] == 'wak-member-thecars' ) {

?>
<style type="text/css">
th#name { width: auto; }
th#owner { width: 20%; }
th#make { width: 13%; }
th#model { width: 12%; }
th#year { width: 5%; }
th#vin { width: 20%; }
th#mileage { width: 10%; }
</style>
<?php

		}

		if ( $_GET['page'] == 'wak-member-carlogs' ) {

?>
<style type="text/css">
th#entry { width: auto; }
th#amount { width: 8%; }
th#service { width: 15%; }
th#mileage { width: 9%; }
th#car { width: 12%; }
th#user { width: 10%; }
th#date { width: 12%; }
</style>
<?php

		}

	}
endif;

/**
 *
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_mycars_admin_load' ) ) :
	function wak_mycars_admin_load() {

		if ( $_GET['page'] == 'wak-member-thecars' ) {

			// Handle review admin actions
			wak_process_mycars_admin_actions();

			$args = array(
				'label'   => __( 'Cars', 'wakmycars' ),
				'default' => 10,
				'option'  => 'wak_cars_per_page'
			);
			add_screen_option( 'per_page', $args );

		}

		if ( $_GET['page'] == 'wak-member-carlogs' ) {

			$args = array(
				'label'   => __( 'Log Entries', 'wakmycars' ),
				'default' => 10,
				'option'  => 'wak_carlogs_per_page'
			);
			add_screen_option( 'per_page', $args );

		}

	}
endif;

/**
 *
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_mycars_overview_admin_screen' ) ) :
	function wak_mycars_overview_admin_screen() {

		global $wpdb, $wak_mycars_db, $wak_mycar_log_db, $wak_mycar_reminder_db;

		$total_cars  = $wpdb->get_var( "SELECT COUNT( * ) FROM {$wak_mycars_db};" );
		$total_users = $wpdb->get_var( "SELECT COUNT( DISTINCT user_id ) FROM {$wak_mycars_db};" );
		$ave_cars = number_format( ( $total_cars / $total_users ), 2, '.', '' );

		$total_log   = $wpdb->get_var( "SELECT COUNT(*) FROM {$wak_mycar_log_db};" );

		$total_spent = $wpdb->get_var( "SELECT SUM( amount ) FROM {$wak_mycar_log_db};" );
		$ave_spent   = $wpdb->get_var( "SELECT AVG( amount ) FROM {$wak_mycar_log_db} GROUP BY car_id;" );

		$services = array();
		$service_ave = $wpdb->get_results( "SELECT SUM( amount ) AS total, category FROM {$wak_mycar_log_db} GROUP BY category;" );
		if ( ! empty( $service_ave ) ) {
			foreach ( $service_ave as $service ) {

				$services[ $service->category ] = $service->total;

			}
		}

?>
<div class="wrap">
	<h2><?php _e( 'Overview', 'wakmycars' ); ?></h2>
	<p>The following overview is based on the cars users have added and maintenance log entries.</p>
	<table class="wp-list-table widefat fixed striped users">
		<thead>
			<tr>
				<th>Total Registered Cars</th>
				<th>Average Car per User</th>
				<th>Total Log Entries</th>
				<th>Total Spent on Cars</th>
				<th>Average Spending</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>
					<h1><?php echo $total_cars; ?></h1>
				</td>
				<td>
					<h1><?php echo $ave_cars; ?></h1>
				</td>
				<td>
					<h1><?php echo $total_log; ?></h1>
				</td>
				<td>
					<h1>$ <?php echo number_format( $total_spent, 2, '.', '' ); ?></h1>
				</td>
				<td>
					<h1>$ <?php echo number_format( $ave_spent, 2, '.', '' ); ?></h1>
				</td>
			</tr>
		</tbody>
	</table>
	<p>&nbsp;</p>
	<h2>Services</h2>
	<p>The total amount users have spent on each service.</p>
	<table class="wp-list-table widefat fixed striped users">
		<thead>
			<tr>
				<th>Rotate / Balance Tires</th>
				<th>Wheel Alignment</th>
				<th>Brake Service</th>
				<th>Oil Change</th>
				<th>Battery</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>
					<h1>$ <?php if ( isset( $services[ 1 ] ) ) echo $services[ 1 ]; else echo '0.00'; ?></h1>
				</td>
				<td>
					<h1>$ <?php if ( isset( $services[ 2 ] ) ) echo $services[ 2 ]; else echo '0.00'; ?></h1>
				</td>
				<td>
					<h1>$ <?php if ( isset( $services[ 3 ] ) ) echo $services[ 3 ]; else echo '0.00'; ?></h1>
				</td>
				<td>
					<h1>$ <?php if ( isset( $services[ 4 ] ) ) echo $services[ 4 ]; else echo '0.00'; ?></h1>
				</td>
				<td>
					<h1>$ <?php if ( isset( $services[ 5 ] ) ) echo $services[ 5 ]; else echo '0.00'; ?></h1>
				</td>
			</tr>
		</tbody>
	</table>
	<p></p>
	<table class="wp-list-table widefat fixed striped users">
		<thead>
			<tr>
				<th>Radiator Flush & Fill</th>
				<th>Transmission Maint.</th>
				<th>Belts & Hoses</th>
				<th>Replace Tires</th>
				<th>Wiper Blades</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>
					<h1>$ <?php if ( isset( $services[ 6 ] ) ) echo $services[ 6 ]; else echo '0.00'; ?></h1>
				</td>
				<td>
					<h1>$ <?php if ( isset( $services[ 7 ] ) ) echo $services[ 7 ]; else echo '0.00'; ?></h1>
				</td>
				<td>
					<h1>$ <?php if ( isset( $services[ 8 ] ) ) echo $services[ 8 ]; else echo '0.00'; ?></h1>
				</td>
				<td>
					<h1>$ <?php if ( isset( $services[ 9 ] ) ) echo $services[ 9 ]; else echo '0.00'; ?></h1>
				</td>
				<td>
					<h1>$ <?php if ( isset( $services[ 10 ] ) ) echo $services[ 10 ]; else echo '0.00'; ?></h1>
				</td>
			</tr>
		</tbody>
	</table>
</div>
<?php

	}
endif;

/**
 *
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_mycars_cars_admin_screen' ) ) :
	function wak_mycars_cars_admin_screen() {

		if ( isset( $_GET['action'] ) && $_GET['action'] == 'edit' )
			wak_mycars_admin_screen_edit();

		else
			wak_mycars_admin_screen_list();

	}
endif;

/**
 *
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_mycars_admin_screen_list' ) ) :
	function wak_mycars_admin_screen_list() {

		$args = array();

		$number = get_user_meta( get_current_user_id(), 'wak_cars_per_page', true );
		if ( $number != '' )
			$args['number'] = absint( $number );

		if ( isset( $_GET['car_id'] ) )
			$args['car_id'] = absint( $_GET['car_id'] );

		if ( isset( $_GET['user_id'] ) )
			$args['user_id'] = absint( $_GET['user_id'] );

		if ( isset( $_GET['paged'] ) )
			$args['paged'] = absint( $_GET['paged'] );

		$cars = new WAK_Query_Cars( $args );

?>
<div class="wrap">
	<h2><?php _e( 'Registered Cars', '' ); ?></h2>
<?php

		if ( isset( $_GET['deleted'] ) && $_GET['deleted'] == 1 )
			echo '<div id="message" class="error"><p>' . ( ( isset( $_GET['multi'] ) ? sprintf( _n( 'Car was successfully deleted.', '%d Cars were successfully deleted.', $_GET['multi'], '' ), $_GET['multi'] ) : 'Car was successfully deleted.' ) ) . '</p></div>';

		elseif ( isset( $_GET['edited'] ) && $_GET['edited'] == 1 )
			echo '<div id="message" class="updated"><p>Car details saved.</p></div>';

		elseif ( isset( $_GET['edited'] ) && $_GET['edited'] == 0 )
			echo '<div id="message" class="error"><p>Failed to save car details.</p></div>';

?>
	<?php $cars->status_filter(); ?>

	<form id="car-list" method="get" action="users.php">
		<input type="hidden" name="page" value="wak-member-thecars" />
		<div class="tablenav top">

			<div class="alignleft actions bulkactions">
				<label for="bulk-action-selector-top" class="screen-reader-text">Select bulk action</label>
				<select name="action" id="bulk-action-selector-top">
					<option value="-1">Bulk Actions</option>
					<option value="delete">Delete</option>
				</select>
				<input type="submit" id="doaction" class="button action" value="Apply" />
			</div>

			<div class="tablenav-pages">
				<?php $cars->pagination(); ?>
			</div>

			<br class="clear" />

		</div>
		<table class="wp-list-table widefat fixed striped posts">
			<thead>
				<tr>
					<th scope="col" id="cb" class="manage-column column-cb check-column"><input type="checkbox" id="cb-select-all-1" /></th>
					<th scope="col" id="name" class="manage-column column-name name-column">Car Name</th>
					<th scope="col" id="owner" class="manage-column column-owner owner-column">Owner</th>
					<th scope="col" id="make" class="manage-column column-make make-column">Make</th>
					<th scope="col" id="model" class="manage-column column-model model-column">Model</th>
					<th scope="col" id="year" class="manage-column column-year year-column">Year</th>
					<th scope="col" id="vin" class="manage-column column-vin vin-column">VIN#</th>
					<th scope="col" id="mileage" class="manage-column column-mileage mileage-column">Mileage</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th scope="col" class="manage-column column-cb check-column"><input type="checkbox" id="cb-select-all-1" /></th>
					<th scope="col" class="manage-column column-name name-column">Car Name</th>
					<th scope="col" class="manage-column column-owner owner-column">Owner</th>
					<th scope="col" class="manage-column column-make make-column">Make</th>
					<th scope="col" class="manage-column column-model model-column">Model</th>
					<th scope="col" class="manage-column column-year year-column">Year</th>
					<th scope="col" class="manage-column column-vin vin-column">VIN#</th>
					<th scope="col" class="manage-column column-mileage mileage-column">Mileage</th>
				</tr>
			</tfoot>
			<tbody>
<?php

		if ( $cars->have_entries() ) {

			$date_format = get_option( 'date_format' );
			foreach ( $cars->results as $car ) {

				$owner          = get_userdata( $car->user_id );
				$edit_user_link = admin_url( 'user-edit.php?user_id=' . $car->user_id );

?>
				<tr id="<?php echo $car->car_id; ?>">
					<th scope="row" class="check-column"><input type="checkbox" id="review-<?php echo $car->car_id; ?>" name="cars[]" value="<?php echo $car->car_id; ?>" /></th>
					<td class="name-column"><?php if ( $car->name != '' ) echo esc_attr( $car->name ); else echo '-'; echo $cars->row_actions( $car ); ?></td>
					<td class="owner-column"><?php if ( isset( $owner->display_name ) ) echo '<strong><a href="' . $edit_user_link . '">' . $owner->display_name . '</a></strong>'; else echo '-'; ?></td>
					<td class="make-column"><?php if ( $car->make != '' ) echo esc_attr( $car->make ); else echo '-'; ?></td>
					<td class="model-column"><?php if ( $car->model != '' ) echo esc_attr( $car->model ); else echo '-'; ?></td>
					<td class="year-column"><?php if ( $car->year != '' ) echo esc_attr( $car->year ); else echo '-'; ?></td>
					<td class="vin-column"><?php if ( $car->VIN != '' ) echo esc_attr( $car->VIN ); else echo '-'; ?></td>
					<td class="mileage-column"><?php if ( $car->mileage != '' ) echo esc_attr( $car->mileage ) . ' mi.'; else echo '-'; ?></td>
				</tr>
<?php

			}

		}

		else {

?>
				<tr>
					<td colspan="8">No cars found.</td>
				</tr>
<?php

		}

?>
			</tbody>
		</table>
	</form>
</div>
<?php

	}
endif;

/**
 *
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_mycars_admin_screen_edit' ) ) :
	function wak_mycars_admin_screen_edit() {

		if ( ! current_user_can( 'moderate_comments' ) ) wp_die( 'You are not allowed to edit cars.' );

		$act      = sanitize_key( $_GET['action'] );
		$entry_id = absint( $_GET['car_id'] );

		$car = wak_get_car_with_log( $entry_id );

?>
<div class="wrap">
	<h2><?php _e( 'Edit Car', '' ); ?></h2>
<?php

		if ( ! isset( $car->car_id ) ) {

			echo '<p>Could not find car.</p>';

		}
		else {

			$owner = get_userdata( $car->user_id );

?>
<style type="text/css">
#carinfo .inside { margin: 0 0 0 0; padding: 0 0 0 0; }
#carinfo .inside .box-container { float: none; height: 63px; }
#carinfo .inside .car-box { float: left; width: 20%; height: 63px; }
#carinfo .inside .car-box.border { margin-right: -1px; border-right: 1px solid rgb(238, 238, 238); }
#carinfo .inside .car-box .padd { padding: 6px; }
#carinfo .inside .car-box .padd input, #carinfo .inside .car-box .padd select, #carinfo .inside .car-box .padd textarea { width: 100%; }
.add-new-car-log-entry { float: right; }
</style>
<form name="post" id="post" method="post" action="" autocomplete="off">
	<input type="hidden" name="wak_car[car_id]" value="<?php echo absint( $car->car_id ); ?>" />
	<input type="hidden" name="wak_car[last_edit]" value="<?php echo absint( $car->last_edit ); ?>" />
	<input type="hidden" name="wak_car[status]" value="<?php echo absint( $car->status ); ?>" />
	<div id="poststuff">
		<div id="post-body" class="metabox-holder columns-2">

			<div id="post-body-content" style="position: relative;">

				<div id="titlediv">
					<div id="titlewrap">
						<input type="text" name="wak_car[name]" id="title" placeholder="Car name (optional)" value="<?php echo esc_attr( $car->name ); ?>" />
					</div>
				</div>

			</div>

			<div id="postbox-container-1" style="position: relative;">
				<div id="side-sortables" class="meta-box-sortables ui-sortable">

					<div id="submitdiv" class="postbox">
						<div class="handlediv" title="Click to toggle"><br /></div>
						<h3 class="hndle ui-sortable-handle"><span>Publish</span></h3>
						<div class="inside" id="wakactions">

							<div class="submitbox" id="submitpost">
								<div id="minor-publishing">
									<div id="misc-publishing-actions">
										<div class="misc-pub-section misc-pub-post-status" style="line-height: 29px; border-bottom: 1px solid #dedede;">
											<label>Owner:</label> <strong><?php echo $owner->display_name; ?></strong><input type="text" name="wak_car[user_id]" id="user-id" value="<?php echo absint( $car->user_id ); ?>" size="8" style="float:right;" />
										</div>
										<div class="misc-pub-section misc-pub-post-by">
											<label>Last Edited:</label> <strong><?php echo date_i18n( get_option( 'date_format' ) . ' @ ' . get_option( 'time_format' ), $car->last_edit ); ?></strong>
										</div>
									</div>
								</div>
								<div id="major-publishing-actions">
									<div id="publishing-action">
										<input type="submit" name="save" class="button button-primary button-large" value="Save" />
									</div>
									<div class="clear"></div>
								</div>
							</div>

						</div>
					</div>

				</div>
			</div>

			<div id="postbox-container-2" style="position: relative;">
				<div id="normal-sortables" class="meta-box-sortables ui-sortable"></div>
				<div id="advanced-sortables" class="meta-box-sortables ui-sortable">

					<!--<div id="test" class="postbox">
						<h3 class="hndle ui-sortable-handle"><span>Test</span></h3>
						<div class="inside">
							<pre><?php print_r( $_POST ); ?></pre>
						</div>
					</div>-->

					<div id="carinfo" class="postbox">
						<h3 class="hndle ui-sortable-handle"><span>Info</span></h3>
						<div class="inside">
							
							<div class="box-container">
								<div class="car-box border">
									<div class="padd">
										<label for="wak-car-make">Make</label>
										<input type="text" name="wak_car[make]" id="wak-car-make" value="<?php echo esc_attr( $car->make ); ?>" />
									</div>
								</div>
								<div class="car-box border">
									<div class="padd">
										<label for="wak-car-model">Model</label>
										<input type="text" name="wak_car[model]" id="wak-car-model" value="<?php echo esc_attr( $car->model ); ?>" />
									</div>
								</div>
								<div class="car-box border">
									<div class="padd">
										<label for="wak-car-year">Year</label>
										<input type="text" name="wak_car[year]" id="wak-car-year" value="<?php echo esc_attr( $car->year ); ?>" />
									</div>
								</div>
								<div class="car-box border">
									<div class="padd">
										<label for="wak-car-VIN">VIN#</label>
										<input type="text" name="wak_car[VIN]" id="wak-car-VIN" value="<?php echo esc_attr( $car->VIN ); ?>" />
									</div>
								</div>
								<div class="car-box">
									<div class="padd">
										<label for="wak-car-mileage">Mileage</label>
										<input type="text" name="wak_car[mileage]" id="wak-car-mileage" value="<?php echo esc_attr( $car->mileage ); ?>" />
									</div>
								</div>
							<div class="clear"></div>
							</div>
						</div>
					</div>

					<div id="carlog" class="postbox">
						<h3 class="hndle ui-sortable-handle"><span>Log</span></h3>
						<div class="inside"><pre><?php print_r( $_POST['wak_car'] ); ?></pre>
<?php

			if ( ! empty( $car->history ) ) {

				

			}
			else {

				echo '<p>No log entries found for this car.</p>';

			}

?>
						</div>
					</div>

				</div>
			</div>

		</div>
		<div class="clear"></div>
	</div>
</form>
<div class="clear"></div>
<?php

		}

?>
</div>
<?php

	}
endif;

/**
 *
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_mycars_carlogs_admin_screen' ) ) :
	function wak_mycars_carlogs_admin_screen() {

		$args = array();

		$number = get_user_meta( get_current_user_id(), 'wak_carlogs_per_page', true );
		if ( $number != '' )
			$args['number'] = absint( $number );

		if ( isset( $_GET['car_id'] ) )
			$args['car_id'] = absint( $_GET['car_id'] );

		if ( isset( $_GET['user_id'] ) )
			$args['user_id'] = absint( $_GET['user_id'] );

		if ( isset( $_GET['paged'] ) )
			$args['paged'] = absint( $_GET['paged'] );

		$log = new WAK_Query_Maintenance_Log( $args );

?>
<div class="wrap">
	<h2><?php _e( 'Maintenance Logs', '' ); ?></h2>
	<form id="car-list" method="get" action="users.php">
		<input type="hidden" name="page" value="wak-member-carlogs" />
		<div class="tablenav top">

			<div class="alignleft actions bulkactions">
				<label for="bulk-action-selector-top" class="screen-reader-text">Select bulk action</label>
				<select name="action" id="bulk-action-selector-top">
					<option value="-1">Bulk Actions</option>
					<option value="delete">Delete</option>
				</select>
				<input type="submit" id="doaction" class="button action" value="Apply" />
			</div>

			<div class="tablenav-pages">
				<?php $log->pagination(); ?>
			</div>

			<br class="clear" />

		</div>
		<table class="wp-list-table widefat fixed striped posts">
			<thead>
				<tr>
					<th scope="col" id="cb" class="manage-column column-cb check-column"><input type="checkbox" id="cb-select-all-1" /></th>
					<th scope="col" id="date" class="manage-column column-date date-column">Date</th>
					<th scope="col" id="user" class="manage-column column-user user-column">User</th>
					<th scope="col" id="car" class="manage-column column-car car-column">Car</th>
					<th scope="col" id="mileage" class="manage-column column-mileage mileage-column">Mileage</th>
					<th scope="col" id="service" class="manage-column column-service service-column">Service</th>
					<th scope="col" id="entry" class="manage-column column-entry entry-column">Entry</th>
					<th scope="col" id="amount" class="manage-column column-amount amount-column">Amount</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th scope="col" class="manage-column column-cb check-column"><input type="checkbox" id="cb-select-all-1" /></th>
					<th scope="col" class="manage-column column-date date-column">Date</th>
					<th scope="col" class="manage-column column-user user-column">User</th>
					<th scope="col" class="manage-column column-car car-column">Car</th>
					<th scope="col" class="manage-column column-mileage mileage-column">Mileage</th>
					<th scope="col" class="manage-column column-service service-column">Service</th>
					<th scope="col" class="manage-column column-entry entry-column">Entry</th>
					<th scope="col" class="manage-column column-amount amount-column">Amount</th>
				</tr>
			</tfoot>
			<tbody>
<?php

		if ( $log->have_entries() ) {

			$date_format = get_option( 'date_format' );
			$servicelist = wak_get_log_services();

			foreach ( $log->results as $entry ) {

				$user = get_userdata( $entry->user_id );
				$car  = wak_get_car( $entry->car_id );

				$carname = '';
				if ( isset( $car->name ) )
					$carname = $car->name;
				if ( $carname == '' )
					$carname = $car->make . ' ' . $car->model . ' ' . $car->year;

?>
				<tr id="log-entry-<?php echo $entry->id; ?>">
					<th scope="row" class="check-column"><input type="checkbox" id="entry-<?php echo $entry->id; ?>" name="logentries[]" value="<?php echo $entry->id; ?>" /></th>
					<td class="date-column"><?php echo date( $date_format, $entry->time ); ?></td>
					<td class="user-column"><?php if ( isset( $user->display_name ) ) echo $user->display_name; else echo '-'; ?></td>
					<td class="car-column"><?php echo $carname; ?></td>
					<td class="mileage-column"><?php echo $entry->mileage . ' mi.'; ?></td>
					<td class="service-column"><?php if ( isset( $servicelist[ $entry->category ] ) ) echo $servicelist[ $entry->category ]; else echo '-'; ?></td>
					<td class="entry-column"><?php echo esc_attr( $entry->entry ); ?></td>
					<td class="amount-column">$ <?php echo number_format( $entry->amount, 2, '.', '' ); ?></td>
				</tr>
<?php

			}

		}
		else {

?>
				<tr>
					<td colspan="8">No log entries found</td>
				</tr>
<?php

		}

?>
			</tbody>
		</table>
	</form>
</div>
<?php

	}
endif;

/**
 *
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_mycars_user_column_headers' ) ) :
	function wak_mycars_user_column_headers( $columns ) {

		if ( array_key_exists( 'posts', $columns ) )
			unset( $columns['posts'] );

		$columns['cars'] = 'Cars';

		return $columns;

	}
endif; 

/**
 *
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_mycars_user_column_content' ) ) :
	function wak_mycars_user_column_content( $value, $column_name, $user_id ) {

		if ( $column_name == 'cars' ) {

			$count = wak_count_users_cars( $user_id );
			$url   = add_query_arg( array( 'page' => 'wak-member-cars', 'user_id' => $user_id ), admin_url( 'users.php' ) );

			if ( $count > 0 )
				return '<a href="' . $url . '">' . $count . '</a>';
			else
				return $count;
		}

	    return $value;
	}
endif;

?>