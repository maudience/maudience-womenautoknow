<?php
// No dirrect access
if ( ! defined( 'WAK_MYCARS_VER' ) ) exit;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! class_exists( 'WAK_Query_Maintenance_Log' ) ) :
	class WAK_Query_Maintenance_Log {

		public $args          = array();
		public $request       = '';
		public $wheres        = '';

		public $prep          = array();
		public $results       = array();
		public $max_num_pages = 1;
		public $total_rows    = 0;

		public $categories    = array();
		public $cars          = array();

		/**
		 * Construct
		 * @version 1.0
		 */
		function __construct( $args = array() ) {

			$this->args = wp_parse_args( $args, array(
				'car_id'      => NULL,
				'user_id'     => NULL,
				'cat'         => NULL,
				'amount'      => NULL,
				'entry'       => NULL,
				'orderby'     => 'time',
				'order'       => 'DESC',
				'number'      => 10,
				'offset'      => '',
				'paged'       => NULL
			) );

			global $wpdb, $wak_mycar_log_db;

			$select = $where = $sortby = $limits = '';
			$prep = $wheres = array();

			if ( $this->args['car_id'] !== NULL ) {
				$wheres[] = 'car_id = %d';
				$prep[]   = absint( $this->args['car_id'] );
			}

			if ( $this->args['user_id'] !== NULL ) {
				$wheres[] = 'user_id = %d';
				$prep[]   = absint( $this->args['user_id'] );
			}

			if ( $this->args['cat'] !== NULL ) {
				$wheres[] = 'category = %s';
				$prep[]   = sanitize_text_field( $this->args['cat'] );
			}

			if ( $this->args['amount'] !== NULL ) {
				$wheres[] = 'amount = %f';
				$prep[]   = number_format( $this->args['amount'], 2, '.', '' );
			}

			if ( $this->args['entry'] !== NULL ) {
				$wheres[] = 'entry LIKE %s';
				$prep[]   = sanitize_text_field( $this->args['entry'] );
			}

			if ( $this->args['orderby'] == 'time-asc' ) {
				$this->args['order']   = 'ASC';
				$this->args['orderby'] = 'time';
			}
			elseif ( $this->args['orderby'] == 'time-desc' ) {
				$this->args['order']   = 'DESC';
				$this->args['orderby'] = 'time';
			}

			if ( $this->args['orderby'] !== NULL )
				$sortby = "ORDER BY " . $this->args['orderby'] . " " . $this->args['order'];

			$number = $this->args['number'];
			if ( $number < -1 )
				$number = abs( $number );

			elseif ( $number == 0 || $number == -1 )
				$number = NULL;

			// Limits
			if ( $number !== NULL ) {

				$page = 1;
				if ( $this->args['paged'] !== NULL ) {
					$page = absint( $this->args['paged'] );
					if ( ! $page )
						$page = 1;
				}

				if ( $this->args['offset'] == '' ) {
					$pgstrt = ($page - 1) * $number . ', ';
				}

				else {
					$offset = absint( $this->args['offset'] );
					$pgstrt = $offset . ', ';
				}

				$limits = 'LIMIT ' . $pgstrt . $number;
			}
			else {
				$limits = '';
			}

			// Prep return
			$select = '*';

			$found_rows = '';
			if ( $limits != '' )
				$found_rows = 'SQL_CALC_FOUND_ROWS';

			if ( empty( $wheres ) ) {
				$wheres[] = 'car_id != %d';
				$prep[]   = 0;
			}

			$this->wheres = $where = 'WHERE ' . implode( ' AND ', $wheres );

			// Run
			$this->request = $wpdb->prepare( "SELECT {$found_rows} * FROM {$wak_mycar_log_db} {$where} {$sortby} {$limits};", $prep );
			$this->prep    = $prep;
			$this->results = $wpdb->get_results( $this->request );

			if ( $limits != '' )
				$this->num_rows = $wpdb->get_var( 'SELECT FOUND_ROWS()' );
			else
				$this->num_rows = count( $this->results );

			if ( $limits != '' )
				$this->max_num_pages = ceil( $this->num_rows / $number );

			$this->total_rows = $wpdb->get_var( "SELECT COUNT( * ) FROM {$wak_mycar_log_db}" );

			if ( $this->args['user_id'] !== NULL ) {

				$this->categories = $wpdb->get_col( $wpdb->prepare( "SELECT DISTINCT category FROM {$wak_mycar_log_db} WHERE user_id = %d;", $this->args['user_id'] ) );

				$this->cars = $wpdb->get_col( $wpdb->prepare( "SELECT DISTINCT car_id FROM {$wak_mycar_log_db} WHERE user_id = %d;", $this->args['user_id'] ) );

			}

		}

		/**
		 * Has Entries
		 * @version 1.0
		 */
		public function have_entries() {

			if ( ! empty( $this->results ) ) return true;
			return false;

		}

		/**
		 * Construct
		 * @version 1.0
		 */
		public function sort_options() {

			if ( ! $this->have_entries() ) return;

?>
<div id="maintenance-log-sorting" class="form-inline">
<?php

			if ( ! empty( $this->cars ) ) {

				echo '<select name="car" class="form-control">';

				$selected_car = '';
				if ( $this->args['car_id'] !== NULL )
					$selected_car = absint( $this->args['car_id'] );

				echo '<option value=""';
				if ( $selected_car == '' ) echo ' selected="selected"';
				echo '>Show all cars</option>';

				foreach ( $this->cars as $car_id ) {

					$car = wak_get_car( $car_id );

					$name = $car->name;
					if ( $name == '' )
						$name = $car->make . ' ' . $car->model . ' ' . $car->year;

					echo '<option value="' . $car_id . '"';
					if ( $selected_car == $car_id ) echo ' selected="selected"';
					echo '>' . $name . '</option>';

				}

				echo '</select>';

			}

			if ( ! empty( $this->categories ) ) {

				echo '<select name="cat" class="form-control">';

				$selected_cat = '';
				if ( $this->args['cat'] !== NULL )
					$selected_cat = absint( $this->args['cat'] );

				echo '<option value=""';
				if ( $selected_cat == '' ) echo ' selected="selected"';
				echo '>Show all services</option>';

				$services = wak_get_log_services();
				foreach ( $this->categories as $category_id ) {

					$category = $services[ $category_id ];

					echo '<option value="' . $category_id . '"';
					if ( $selected_cat == $category_id ) echo ' selected="selected"';
					echo '>' . $category . '</option>';

				}

				echo '</select>';

			}

			$orderby = $this->args['orderby'];

			if ( $this->args['orderby'] == 'time' && $this->args['order'] == 'ASC' )
				$orderby = 'time-asc';
			
			elseif ( $this->args['orderby'] == 'time' && $this->args['order'] == 'DESC' )
				$orderby = 'time-desc';
			
			$order_options = array(
				'time-desc' => 'Newest entries first',
				'time-asc'  => 'Oldest entries first'
			);

			echo '<select name="orderby" class="form-control">';

			foreach ( $order_options as $value => $label ) {

				echo '<option value="' . $value . '"';
				if ( $selected_cat == $value ) echo ' selected="selected"';
				echo '>' . $label . '</option>';

			}

			echo '</select>';

		}

		/**
		 * Get Page Number
		 * @version 1.0
		 */
		public function get_pagenum() {

			global $paged;

			if ( $paged > 0 )
				$pagenum = absint( $paged );

			elseif ( isset( $_REQUEST['paged'] ) )
				$pagenum = absint( $_REQUEST['paged'] );

			else return 1;

			return max( 1, $pagenum );

		}

		/**
		 * Pagination
		 * @version 1.0
		 */
		public function pagination( $location = 'top', $id = '' ) {

			$output      = '';
			$total_pages = $this->max_num_pages;
			$current     = $this->get_pagenum();
			$current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . $id );

			if ( ! is_admin() )
				$current_url = str_replace( '/page/' . $current . '/', '/', $current_url );

			$current_url = remove_query_arg( array( 'hotkeys_highlight_last', 'hotkeys_highlight_first' ), $current_url );

			if ( $this->have_entries() ) {
				$total_number = count( $this->results );
				$output = '<span class="displaying-num">' . sprintf( __( 'Showing %d %s', 'mycred' ), $total_number, _n( 'entry', 'entries', $total_number, 'mycred' ) ) . '</span>';
			}

			$page_links = array();
			$pagination_class = apply_filters( 'mycred_log_paginate_class', '', $this );

			$disable_first = $disable_last = '';
			if ( $current == 1 )
				$disable_first = ' disabled';

			if ( $current == $total_pages )
				$disable_last = ' disabled';

			$page_links[] = sprintf( '<a class="%s" title="%s" href="%s">%s</a>',
				$pagination_class . 'first-page' . $disable_first,
				esc_attr__( 'Go to the first page', 'mycred' ),
				esc_url( remove_query_arg( 'paged', $current_url ) ),
				'&laquo;'
			);

			$page_links[] = sprintf( '<a class="%s" title="%s" href="%s">%s</a>',
				$pagination_class . 'prev-page' . $disable_first,
				esc_attr__( 'Go to the previous page', 'mycred' ),
				esc_url( add_query_arg( 'paged', max( 1, $current-1 ), $current_url ) ),
				'&lsaquo;'
			);

			if ( 'bottom' == $location )
				$html_current_page = $current;

			else
				$html_current_page = sprintf( '<input class="current-page" title="%s" type="text" name="paged" value="%s" size="%d" />',
					esc_attr__( 'Current page', 'mycred' ),
					$current,
					strlen( $total_pages )
				);

			$html_total_pages = sprintf( "<span class='total-pages'>%s</span>", number_format_i18n( $total_pages ) );
			$page_links[] = '<span class="paging-input">' . sprintf( _x( '%1$s of %2$s', 'mycred' ), $html_current_page, $html_total_pages ) . '</span>';

			$page_links[] = sprintf( '<a class="%s"  title="%s" href="%s">%s</a>',
				$pagination_class . 'next-page' . $disable_last,
				esc_attr__( 'Go to the next page', 'mycred' ),
				esc_url( add_query_arg( 'paged', min( $total_pages, $current+1 ), $current_url ) ),
				'&rsaquo;'
			);

			$page_links[] = sprintf( '<a class="%s" title="%s" href="%s">%s</a>',
				$pagination_class . 'last-page' . $disable_last,
				esc_attr__( 'Go to the last page', 'mycred' ),
				esc_url( add_query_arg( 'paged', $total_pages, $current_url ) ),
				'&raquo;'
			);

			$output .= "\n" . '<span class="pagination-links">' . join( "\n", $page_links ) . '</span>';

			if ( $total_pages )
				$page_class = $total_pages < 2 ? ' one-page' : '';

			else
				$page_class = ' no-pages';

			echo '<div class="tablenav-pages' . $page_class . '">' . $output . '</div>';

		}

		public function display() {

?>
<div class="container-fluid">
	<div class="row border-bottom">
		<div class="col-md-3 col-sm-3 col-xs-12">
			<strong>Date</strong>
		</div>
		<div class="col-md-3 col-sm-3 col-xs-12">
			<strong>Car</strong>
		</div>
		<div class="col-md-6 col-sm-6 col-xs-12">
			<strong>Entry</strong>
		</div>
	</div>
	<div id="wak-log-entry-list">
<?php

			if ( $this->have_entries() ) {

				$date_format = get_option( 'date_format' );
				foreach ( $this->results as $entry ) {

					$car = wak_get_car( $entry->car_id );

					$carname = $car->name;
					if ( $carname == '' )
						$carname = $car->make . ' ' . $car->model;

?>
		<div class="row border-bottom">
			<div class="col-md-3 col-sm-3 col-xs-12"><p><?php echo date( $date_format, $entry->time ); ?></p></div>
			<div class="col-md-3 col-sm-3 col-xs-12"><p><?php echo $carname . ' (' . $entry->mileage . ' mi.)'; ?></p></div>
			<div class="col-md-6 col-sm-6 col-xs-12"><p><?php echo esc_attr( $entry->entry ); ?></p></div>
		</div>
<?php

				}

			}
			else {

?>
		<div class="row">
			<div class="col-md-12 text-center no-entry">
				<p>No log entries found.</p>
			</div>
		</div>
<?php

			}

?>
	</div>
</div>
<?php

		}

	}
endif;

?>