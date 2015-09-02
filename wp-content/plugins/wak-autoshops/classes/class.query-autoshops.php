<?php
// No dirrect access
if ( ! defined( 'WAK_AUTOSHOPS_VER' ) ) exit;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! class_exists( 'WAK_Autoshops' ) ) :
	class WAK_Autoshops {

		public $args          = array();
		public $request       = '';
		public $wheres        = '';

		public $prep          = array();
		public $results       = array();
		public $max_num_pages = 1;
		public $total_rows    = 0;

		public $is_search     = false;
		public $is_member     = false;
		public $user          = false;
		protected $no_pledgers = false;

		/**
		 * Construct
		 */
		function __construct( $data = NULL ) {

			$args          = array();
			$accepted_args = array( 'wakshop', 'address1', 'city', 'zip', 'state', 'pledged', 'orderby', 'autoshop_id', 'owner', 'addedby', 'paged' );

			if ( $data === NULL )
				$data = $_POST;

			if ( ! empty( $data ) ) {

				foreach ( $data as $key => $value ) {

					$key = sanitize_key( $key );
					$value = sanitize_text_field( $value );

					if ( in_array( $key, $accepted_args ) )
						$args[ $key ] = $value;

				}

			}

			// No search
			if ( empty( $args ) ) {

				$args = array(
					'number'  => 30,
					'status'  => 'publish',
					'orderby' => 'highest-rated'
				);

			}
			
			// Search
			else {

				$this->is_search = true;
				if ( ! isset( $args['pledged'] ) )
					$this->no_pledgers = true;

			}

			$this->args = wp_parse_args( $args, array(
				'wakshop'     => NULL,
				'address1'    => NULL,
				'address2'    => NULL,
				'city'        => NULL,
				'zip'         => NULL,
				'state'       => NULL,
				'pledged'     => NULL,
				'premium'     => NULL,
				'autoshop_id' => NULL,
				'owner'       => NULL,
				'addedby'     => NULL,
				'orderby'     => 'highest-rated',
				'order'       => 'DESC',
				'number'      => 30,
				'offset'      => '',
				'paged'       => NULL
			) );

			if ( is_user_logged_in() ) {

				$this->is_member = true;
				$this->user      = get_userdata( get_current_user_id() );

			}

		}

		/**
		 * Query Autoshops
		 * Either shows the default post type archive results or the results
		 * of the users search.
		 * @version 1.0
		 */
		public function query() {

			global $wpdb;

			$select = $where = $sortby = $limits = '';
			$prep = $joins = $wheres = $orderbys = array();

			$wheres[] = "posts.post_type = %s";
			$prep[]   = 'autoshops';

			$wheres[] = "posts.post_status = %s";
			$prep[]   = 'publish';

			$joins[]  = "LEFT JOIN {$wpdb->postmeta} rating ON ( posts.ID = rating.post_id AND rating.meta_key = 'rating' )";

			if ( strlen( $this->args['wakshop'] ) > 0 ) {
				$wheres[] = 'posts.post_title LIKE %s';
				$prep[]   = '%' . $this->args['wakshop'] . '%';
			}

			if ( strlen( $this->args['address1'] ) > 0 ) {
				$joins[]  = "INNER JOIN {$wpdb->postmeta} ad1 ON ( posts.ID = ad1.post_id AND ad1.meta_key = 'address1' )";
				$wheres[] = 'ad1.meta_value LIKE %s';
				$prep[]   = '%' . $this->args['address1'] . '%';
			}

			if ( strlen( $this->args['address2'] ) ) {
				$joins[]  = "INNER JOIN {$wpdb->postmeta} ad2 ON ( posts.ID = ad2.post_id AND ad2.meta_key = 'address2' )";
				$wheres[] = 'ad2.meta_value LIKE %s';
				$prep[]   = '%' . $this->args['address2'] . '%';
			}

			if ( strlen( $this->args['city'] ) > 0 ) {
				$joins[]  = "INNER JOIN {$wpdb->postmeta} ci ON ( posts.ID = ci.post_id AND ci.meta_key = 'city' )";
				$wheres[] = 'ci.meta_value LIKE %s';
				$prep[]   = '%' . $this->args['city'] . '%';
			}

			if ( strlen( $this->args['zip'] ) > 0 ) {
				$joins[]  = "INNER JOIN {$wpdb->postmeta} zi ON ( posts.ID = zi.post_id AND zi.meta_key = 'zip' )";
				$wheres[] = 'zi.meta_value LIKE %s';
				$prep[]   = '%' . $this->args['zip'] . '%';
			}

			if ( strlen( $this->args['state'] ) > 0 ) {
				$joins[]  = "INNER JOIN {$wpdb->postmeta} st ON ( posts.ID = st.post_id AND st.meta_key = 'state' )";
				$wheres[] = 'st.meta_value = %s';
				$prep[]   = $this->args['state'];
			}

			if ( $this->args['pledged'] !== NULL ) {

				$joins[]  = "INNER JOIN {$wpdb->postmeta} pl ON ( posts.ID = pl.post_id AND pl.meta_key = 'pledged' )";
				$wheres[] = 'pl.meta_value = %d';
				$prep[]   = $this->args['pledged'];

				if ( $this->args['pledged'] === 1 )
					$orderbys['pl.meta_value'] = '';
				else
					$orderbys['pl.meta_value'] = 'ASC';

			}
			elseif ( $this->args['premium'] !== NULL ) {

				$joins[]  = "LEFT JOIN {$wpdb->postmeta} pr ON ( posts.ID = pr.post_id AND pr.meta_key = 'premium_until' )";
				$wheres[] = "pr.meta_value != ''";

				if ( $this->args['premium'] === 1 )
					$orderbys['pr.meta_value'] = '';
				else
					$orderbys['pr.meta_value'] = 'ASC';

			}
			else {

				$joins[]  = "LEFT JOIN {$wpdb->postmeta} pl ON ( posts.ID = pl.post_id AND pl.meta_key = 'pledged' )";
				$orderbys['pl.meta_value'] = 'DESC';

				$joins[]  = "LEFT JOIN {$wpdb->postmeta} pr ON ( posts.ID = pr.post_id AND pr.meta_key = 'premium_until' )";
				$orderbys['pr.meta_value'] = 'DESC';

			}

			if ( strlen( $this->args['autoshop_id'] ) > 0 ) {
				$wheres[] = 'posts.ID = %d';
				$prep[]   = absint( $this->args['autoshop_id'] );
			}

			if ( strlen( $this->args['owner'] ) ) {
				$wheres[] = 'posts.post_author = %d';
				$prep[]   = absint( $this->args['owner'] );
			}

			if ( strlen( $this->args['addedby'] ) > 0 ) {
				$joins[]  = "INNER JOIN {$wpdb->postmeta} adb ON ( posts.ID = adb.post_id AND adb.meta_key = 'added_by' )";
				$wheres[] = 'adb.meta_value = %d';
				$prep[]   = $this->args['addedby'];
			}

			if ( $this->args['orderby'] == 'highest-rated' ) {

				$orderbys['rating.meta_value+1'] = 'DESC';
				$orderbys['posts.post_title'] = 'ASC';

			}
			elseif ( $this->args['orderby'] == 'alphabetical' ) {

				$orderbys['posts.post_title'] = 'ASC';

			}

			$list = array();
			if ( ! empty( $orderbys ) ) {
				foreach ( $orderbys as $value => $order ) {

					if ( $order != '' )
						$value .= ' ' . $order;

					$list[] = $value;

				}
			}

			if ( ! empty( $list ) )
				$orderby = 'ORDER BY ' . implode( ', ', $list );
			else
				$orderby = 'ORDER BY posts.post_title ASC';

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

			$this->wheres = $where = 'WHERE ' . implode( ' AND ' . "\n", $wheres );

			$join = implode( ' ' . "\n", $joins );

			// Run
			$this->request = $wpdb->prepare( "
				SELECT {$found_rows} * 
				FROM {$wpdb->posts} posts 
				{$join} 
				{$where} 
				{$orderby} 
				{$limits};", $prep );

			$this->prep    = $prep;
			$this->results = $wpdb->get_results( $this->request );

			// Fillout
			// If we have no results, find any pledged autoshops in the
			// same search area.
			if ( $this->is_search && empty( $this->results ) ) {

				if ( strlen( $this->args['city'] ) > 0 && strlen( $this->args['state'] ) > 0 ) {
					$this->results = $wpdb->get_results( $wpdb->prepare( "
						SELECT * 
						FROM {$wpdb->posts} posts 
						LEFT JOIN {$wpdb->postmeta} rating ON ( posts.ID = rating.post_id AND rating.meta_key = 'rating' ) 
						INNER JOIN {$wpdb->postmeta} pl ON ( posts.ID = pl.post_id AND pl.meta_key = 'pledged' ) 
						INNER JOIN {$wpdb->postmeta} ci ON ( posts.ID = ci.post_id AND ci.meta_key = 'city' ) 
						INNER JOIN {$wpdb->postmeta} st ON ( posts.ID = st.post_id AND st.meta_key = 'state' ) 
						WHERE posts.post_type = 'autoshops' 
						AND posts.post_status = 'publish' 
						AND pl.meta_value = 1 
						AND ( ci.meta_value LIKE %s OR st.meta_value = %s ) 
						ORDER BY rating.meta_value+1 DESC LIMIT 0,4;", '%' . $this->args['city'] . '%', $this->args['state'] ) );
				}
				elseif ( strlen( $this->args['address1'] ) > 0 && strlen( $this->args['state'] ) > 0 ) {
					$this->results = $wpdb->get_results( $wpdb->prepare( "
						SELECT * 
						FROM {$wpdb->posts} posts 
						LEFT JOIN {$wpdb->postmeta} rating ON ( posts.ID = rating.post_id AND rating.meta_key = 'rating' ) 
						INNER JOIN {$wpdb->postmeta} pl ON ( posts.ID = pl.post_id AND pl.meta_key = 'pledged' ) 
						INNER JOIN {$wpdb->postmeta} ad1 ON ( posts.ID = ad1.post_id AND ad1.meta_key = 'address1' )
						INNER JOIN {$wpdb->postmeta} st ON ( posts.ID = st.post_id AND st.meta_key = 'state' ) 
						WHERE posts.post_type = 'autoshops' 
						AND posts.post_status = 'publish' 
						AND pl.meta_value = 1 
						AND ( ad1.meta_value LIKE %s OR st.meta_value = %s ) 
						ORDER BY rating.meta_value+1 DESC LIMIT 0,4;", '%' . $this->args['city'] . '%', $this->args['state'] ) );
				}
				elseif ( strlen( $this->args['wakshop'] ) > 0 && strlen( $this->args['state'] ) > 0 ) {
					$this->results = $wpdb->get_results( $wpdb->prepare( "
						SELECT * 
						FROM {$wpdb->posts} posts 
						LEFT JOIN {$wpdb->postmeta} rating ON ( posts.ID = rating.post_id AND rating.meta_key = 'rating' ) 
						INNER JOIN {$wpdb->postmeta} pl ON ( posts.ID = pl.post_id AND pl.meta_key = 'pledged' ) 
						INNER JOIN {$wpdb->postmeta} st ON ( posts.ID = st.post_id AND st.meta_key = 'state' ) 
						WHERE posts.post_type = 'autoshops' 
						AND posts.post_status = 'publish' 
						AND pl.meta_value = 1 
						AND ( posts.post_title LIKE %s OR st.meta_value = %s ) 
						ORDER BY rating.meta_value+1 DESC LIMIT 0,4;", '%' . $this->args['wakshop'] . '%', $this->args['state'] ) );
				}

			}

			if ( $limits != '' )
				$this->num_rows = $wpdb->get_var( 'SELECT FOUND_ROWS()' );
			else
				$this->num_rows = count( $this->results );

			if ( $limits != '' )
				$this->max_num_pages = ceil( $this->num_rows / $number );

			$this->total_rows = $wpdb->get_var( "SELECT COUNT( * ) FROM {$wpdb->posts} WHERE post_type = 'autoshops' AND post_status = 'publish'" );

		}

		/**
		 * Search Form
		 * Displays the autoshop search form.
		 * @version 1.0
		 */
		public function search_form() {

			$pledged = $this->args['pledged'];

?>
<div class="outer-wrapper" id="wak-autoshop-search-box">

	<div class="inner-wrapper boxed">

		<div id="locate-autoshop" class="row">
			<div id="loading-wak-autoshop-search" class="modal-backdrop" style="display:none;">
				<div class="progress-box">
					<p class="indicator"><i class="fa fa-spinner fa-spin pink"></i></p>
					<p class="result">searching auto shops ...</p>
				</div>
			</div>
			<h3>Find an Auto Shop Near You!</h3>
			<div class="row">
				<div class="col-md-12" id="search-wak-tabs">
					<ul class="nav nav-tabs" role="tablist">
						<li role="presentation" class="active"><a href="#pledged-shop-search" aria-controls="pledged-shop-search" role="tab" data-toggle="tab">Search Auto Shops</a></li>
					</ul>
					<div class="tab-content">
						<div role="tabpanel" class="tab-pane fade in active" id="pledged-shop-search">
							<form class="form-inline" id="search-wak-autoshops-form" action="<?php echo home_url( '/autoshops/' ); ?>" method="post">
								<div class="row">
									<div class="col-md-9">
										<input type="text" name="wakshop" maxlength="128" style="width:100%;" class="form-control" id="wak-locate-auto-name" value="<?php echo esc_attr( $this->args['wakshop'] ); ?>" placeholder="Search auto shop by name" />
									</div>
									<div class="col-md-3">
										<input type="submit" class="btn btn-default btn-block submit form-control" id="do-search-autoshops" value="Search" />
									</div>
								</div>
								<div class="row">
									<div class="col-md-12">
										<div class="form-group">
											<input type="text" name="address1" size="35" maxlength="128" class="form-control" id="wak-locate-auto-address" value="<?php echo esc_attr( $this->args['address1'] ); ?>" placeholder="Street" />
											<input type="text" name="city" size="25" maxlength="128" class="form-control" id="wak-locate-auto-city" value="<?php echo esc_attr( $this->args['city'] ); ?>" placeholder="City" />
											<input type="text" name="zip" size="8" maxlength="6" class="form-control" id="wak-locate-auto-zip" value="<?php echo esc_attr( $this->args['zip'] ); ?>" placeholder="Zip" />
											<?php echo wak_states_dropdown( 'state', 'wak-locate-auto-state', 'All States', $this->args['state'] ); ?>
											<input type="hidden" name="orderby" id="wak-order-auto-by" value="<?php echo esc_attr( $this->args['orderby'] ); ?>" />
										</div>
									</div>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>

		</div>

	</div>

</div>
<div class="outer-wrapper">
	<div class="inner-wrapper boxed">
		<div class="row">
			<div class="col-md-6 col-sm-6 col-xs-6 hidden-xs"><?php if ( is_user_logged_in() ) : ?><div style="padding-top:8px;"><button class="new-autoshop-button btn btn-danger form-control" data-toggle="modal" data-target="#add-new-wak-autoshop">Add Auto Shop</button></div><?php endif; ?></div>
			<div class="col-md-6 col-sm-12 col-xs-12" id="wak-shop-type-ledger">
				<div class="row">
					<div class="col-md-4 col-sm-4 col-xs-4">
						<p><img src="<?php echo plugins_url( 'assets/images/pledged-wheel.png', WAK_AUTOSHOPS ); ?>" alt="Pledged Auto Shop" />Pledged Auto Shop</p>
					</div>
					<div class="col-md-4 col-sm-4 col-xs-4">
						<p><img src="<?php echo plugins_url( 'assets/images/premium-wheel.png', WAK_AUTOSHOPS ); ?>" alt="Premium Auto Shop" />Premium Auto Shop</p>
					</div>
					<div class="col-md-4 col-sm-4 col-xs-4">
						<p><img src="<?php echo plugins_url( 'assets/images/regular-wheel.png', WAK_AUTOSHOPS ); ?>" alt="Regular Auto Shop" />Regular Auto Shop</p>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php

		}

		/**
		 * Orderby Dropdown
		 * @version 1.0
		 */
		public function wak_orderby_dropdown( $name = '', $id = '', $selected = '' ) {

			$options = array(
				'highest-rated' => 'Sort by rating',
				'alphabetical'  => 'Sort Alphabetical'
			);

			$output = '<select name="' . $name . '" id="' . $id . '" class="form-control">';
			foreach ( $options as $value => $label ) {
				$output .= '<option value="' . $value . '"';
				if ( $selected == $value ) $output .= ' selected="selected"';
				$output .= '>' . $label . '</option>';
			}
			$output .= '</select>';

			return $output;

		}

		/**
		 * Before
		 * Items to show before the auto shop results.
		 * @version 1.0
		 */
		public function before() {

			if ( $this->is_search ) {

?>
<div class="row">
	<div class="col-md-12 text-center">
		<h1>Search Results</h1>
	</div>
</div>
<?php

			}

		}

		/**
		 * Display
		 * Displays the results of either the archive results
		 * or the search results.
		 * @version 1.0
		 */
		public function display( $sort = true ) {

			if ( $sort ) {
?>
<div class="row" id="wak-sort-results">
	<div class="col-md-6 col-sm-6 col-xs-12">
		<?php if ( $this->is_search && ! empty( $this->results ) ) printf( '<p class="form-control-static" id="wak-found-rows">Found %s.</p>', sprintf( _n( '<strong>1</strong> auto shop', '<strong>%d</strong> auto shops', $this->num_rows, '' ), $this->num_rows ) ); ?>
	</div>
	<div class="col-md-6 col-sm-6 col-xs-12 text-right form-inline">
		<?php echo $this->wak_orderby_dropdown( 'orderby', 'wak-locate-auto-orderby', $this->args['orderby'] ); ?>
	</div>
</div>
<?php

			}

?>
<div class="row">
	<div class="col-md-12 col-xs-12" id="list-of-autoshops">
<?php

			if ( ! empty( $this->results ) ) {

?>
		<div class="row">
<?php

			$counter = 0;
			$states  = wak_get_states();

			foreach ( $this->results as $autoshop ) {

				$post_url = get_the_permalink( $autoshop->ID );
				$state    = get_post_meta( $autoshop->ID, 'state', true );

				if ( $counter != 0 && $counter % 2 == 0 )
					echo '<div class="row">' . "\n";

				echo '<div class="col-md-6 col-sm-6 col-xs-12">' . "\n";

				if ( autoshop_has_pledged( $autoshop->ID ) ) {
					$post_class = 'autoshops pledged';
					$address    = get_post_meta( $autoshop->ID, 'address1', true );
					$address    .= ', ' . get_post_meta( $autoshop->ID, 'city', true );
					$address    .= ', ' . get_post_meta( $autoshop->ID, 'zip', true );
					$address    .= ' ' . ( ( isset( $states[ $state ] ) ) ? $states[ $state ] : $state );
					$address    = esc_attr( $address );

					$phone = get_post_meta( $autoshop->ID, 'phone', true );
					if ( $phone != '' )
						$address    .= '<br />P: <a href="tel://' . wak_clean_phone_number( $phone ) . '">' . wak_clean_phone_number( $phone ) . '</a>';

				}
				elseif ( autoshop_is_premium( $autoshop->ID ) ) {
					$post_class = 'autoshops premium';
					$address    = get_post_meta( $autoshop->ID, 'address1', true );
					$address    .= ', ' . get_post_meta( $autoshop->ID, 'city', true );
					$address    .= ', ' . get_post_meta( $autoshop->ID, 'zip', true );
					$address    .= ' ' . ( ( isset( $states[ $state ] ) ) ? $states[ $state ] : $state );
					$address    = esc_attr( $address );

					$phone = get_post_meta( $autoshop->ID, 'phone', true );
					if ( $phone != '' )
						$address    .= '<br />P: <a href="tel://' . wak_clean_phone_number( $phone ) . '">' . wak_clean_phone_number( $phone ) . '</a>';

				}
				else {
					$post_class = 'autoshops';
					$address    = get_post_meta( $autoshop->ID, 'city', true );
					$address    .= ' ' . ( ( isset( $states[ $state ] ) ) ? $states[ $state ] : $state );
					$address    = esc_attr( $address );
				}

?>
				<div id="autoshop-<?php echo $autoshop->ID; ?>" class="<?php echo $post_class; ?>">
					<?php wak_display_autoshop_rating( $autoshop->ID ); ?>
					<h4><a href="<?php echo $post_url; ?>"><?php echo esc_attr( $autoshop->post_title ); ?></a><small><?php echo $address; ?></small></h4>
					<p class="autoshop-action-links"><?php echo get_review_actions( $autoshop->ID ); ?></p>
				</div>
<?php

				echo '</div>' . "\n";

				if ( $counter != 0 && $counter % 2 != 0 )
					echo '</div>' . "\n";

				$counter ++;

			}

			if ( $counter % 2 != 0 )
				echo '<div class="col-md-6 col-sm-6 col-xs-12 text-center"><button class="new-autoshop-button btn btn-danger form-control" data-toggle="modal" data-target="#add-new-wak-autoshop">Add Auto Shop</button></div>' . "\n";

?>
		</div>
	</div>
	<?php $this->pagination(); ?>
</div>
<?php


			}
			else {

?>
		<p class="text-center">No shops found.</p>
<?php

				if ( $this->is_member ) {
					echo '<p class="text-center"><button class="new-autoshop-button btn btn-default btn-lg" data-toggle="modal" data-target="#add-new-wak-autoshop">Add Auto Shop</button></p>';
				}
				else {
				
				}

			}

?>
	</div>
</div>
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

		public function pagination() {

			if ( empty( $this->results ) || $this->max_num_pages == 1 ) return;

			$more = $this->num_rows - $this->args['number'];

?>
<div class="row">
	<div class="col-md-12" id="load-more-autoshops">
		<form method="post" action="<?php echo home_url( '/autoshops/' ); ?>">
<?php

			foreach ( $this->args as $key => $value ) {

				if ( $value === NULL || $value == '' ) continue;
				echo '<input type="hidden" name="' . $key . '" value="' . $value . '" />';

			}

?>
			<p class="text-center"><input type="submit" class="btn btn-default" value="Load More Auto Shops" /></p>
		</form>
	</div>
</div>
<?php

		}

		/**
		 * After
		 * Items to show after the auto shop results.
		 * @version 1.0
		 */
		public function after() {



		}

	}
endif;

?>