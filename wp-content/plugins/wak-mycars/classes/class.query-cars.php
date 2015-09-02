<?php
// No dirrect access
if ( ! defined( 'WAK_MYCARS_VER' ) ) exit;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! class_exists( 'WAK_Query_Cars' ) ) :
	class WAK_Query_Cars {

		public $args          = array();
		public $request       = '';
		public $wheres        = '';

		public $prep          = array();
		public $results       = array();
		public $max_num_pages = 1;
		public $total_rows    = 0;

		/**
		 * Construct
		 * @version 1.0
		 */
		function __construct( $args = array() ) {

			$this->args = wp_parse_args( $args, array(
				'car_id'      => NULL,
				'user_id'     => NULL,
				'last_edit'   => NULL,
				'model'       => NULL,
				'make'        => NULL,
				'year'        => NULL,
				'VIN'         => NULL,
				'mileage'     => NULL,
				'orderby'     => 'last_edit',
				'order'       => 'DESC',
				'number'      => 10,
				'offset'      => '',
				'paged'       => NULL
			) );

			global $wpdb, $wak_mycars_db;

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

			if ( $this->args['last_edit'] !== NULL ) {
				
			}

			if ( $this->args['model'] !== NULL ) {
				$wheres[] = 'model = %s';
				$prep[]   = sanitize_text_field( $this->args['model'] );
			}

			if ( $this->args['make'] !== NULL ) {
				$wheres[] = 'make = %s';
				$prep[]   = sanitize_text_field( $this->args['make'] );
			}

			if ( $this->args['year'] !== NULL ) {
				$wheres[] = 'year = %d';
				$prep[]   = absint( $this->args['year'] );
			}

			if ( $this->args['VIN'] !== NULL ) {
				$wheres[] = 'VIN = %s';
				$prep[]   = sanitize_text_field( $this->args['VIN'] );
			}

			if ( $this->args['mileage'] !== NULL ) {
				$wheres[] = 'mileage = %d';
				$prep[]   = absint( $this->args['mileage'] );
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
			$this->request = $wpdb->prepare( "SELECT {$found_rows} * FROM {$wak_mycars_db} {$where} {$sortby} {$limits};", $prep );
			$this->prep    = $prep;
			$this->results = $wpdb->get_results( $this->request );

			if ( $limits != '' )
				$this->num_rows = $wpdb->get_var( 'SELECT FOUND_ROWS()' );
			else
				$this->num_rows = count( $this->results );

			if ( $limits != '' )
				$this->max_num_pages = ceil( $this->num_rows / $number );

			$this->total_rows = $wpdb->get_var( "SELECT COUNT( * ) FROM {$wak_mycars_db}" );

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
		public function status_filter() {

			$base = add_query_arg( array( 'page' => $_GET['page'] ), admin_url( 'admin.php' ) );

?>
<ul class="subsubsub">
	<li><a href="<?php echo esc_url( $base ); ?>"<?php if ( ! isset( $_GET['status'] ) || $_GET['status'] == '-1' ) echo ' class="current"'; ?>>All <span class="count">(<?php echo number_format_i18n( $this->total_rows ); ?>)</span></a></li>
</ul>
<?php

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

		/**
		 * Row Actions
		 * @version 1.0
		 */
		public function row_actions( $entry ) {

			$actions = array();
			$base    = add_query_arg( array( 'page' => 'wak-member-thecars' ), admin_url( 'admin.php' ) );

			$actions['edit'] = '<a href="' . esc_url( add_query_arg( array( 'action' => 'edit', 'car_id' => $entry->car_id ), $base ) ) . '">Edit Car</a>';
			$actions['delete'] = '<a href="' . esc_url( add_query_arg( array( 'action' => 'delete', 'car_id' => $entry->car_id ), $base ) ) . '">Delete</a>';

			$output = '';
			$counter = 0;
			$count = count( $actions );
			foreach ( $actions as $id => $link ) {

				$end = ' | ';
				if ( $counter+1 == $count )
					$end = '';

				$output .= '<span class="' . $id . '">' . $link . $end . '</span>';
				$counter ++;

			}

			return '<div class="row-actions">' . $output . '</div>';

		}

	}
endif;

?>