<?php get_header(); ?>

<style type="text/css">
.autoshop-opening-hours ul { margin: 0; padding: 0 12px 24px 12px; }
.autoshop-opening-hours ul li { list-style-type: none; margin: 0; padding: 0 0 0 0; font-size: 12px; line-height: 24px; }
.autoshop-opening-hours ul li span { display: inline-block; width: 30%; }
.autoshop-opening-hours ul li strong { display: inline-block; width: 20%; text-align: center; }
</style>
<div class="outer-wrapper" id="main-content">

	<div class="inner-wrapper boxed">

		<div class="row sidebar-right">

			<div class="col-md-9 col-xs-12" id="the-content">
				<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); $post_id = get_the_ID(); $owner = wak_get_autoshop_owner( $post_id ); $pledged = autoshop_has_pledged( $post_id ); ?>

				<article id="autoshop-<?php the_ID(); ?>" <?php post_class(); ?>>

					<div class="row">

						<div class="col-md-4 col-xs-12 object-thumb">

							<?php if ( has_post_thumbnail() ) : ?>
								<?php the_post_thumbnail(); ?>
							<?php else : ?>

							<div class="missing-feature-image">
								<h3 class="text-center">no image found</h3>
							</div>

								<?php if ( ! is_numeric( $owner ) ) : ?>
							<p id="missing-photo-info" class="text-left">Is this your auto shop? <a href="<?php echo home_url( '/contact-us/' ); ?>">Contact us</a> in order to claim this auto shop and upload a photo or brand.</p>
								<?php endif; ?>
							<?php endif; ?>

							<?php if ( ! $pledged ) : ?>
							<h5 class="pledged-or-notpledged">Not pledged, yet</h5>
							<?php else : ?>
							<h5 class="pledged-or-notpledged">Pledged Auto Shop</h5>
							<?php endif; ?>

							<aside class="widget light-bg">
								<h4 class="widget-title">Contact Information</h4>
								<div>

									<?php echo show_autoshop_address( $post_id, false, true ); ?>

								</div>
							</aside>

						</div>
	
						<div class="col-md-8 col-xs-12">
							<?php wak_display_autoshop_rating( $post_id ); ?>
							<header><h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2></header>
							<div class="description">
								<?php the_content(); ?>
							</div>
							<p style="padding-top:24px;"><?php echo get_review_actions( $post_id, NULL, false, 'btn-danger' ); ?></p>
						</div>

					</div>
					
					<div class="row">
						<div class="col-md-12">
							<aside class="widget">
								<h4 class="widget-title"><?php printf( '%s <small>(%d)</small>', __( 'Reviews', '' ), wak_count_autoshop_reviews( $post_id ) ); ?></h4>

								<?php wak_display_autoshop_reviews( $post_id, NULL, false ); ?>

							</aside>
						</div>
					</div>

				</article>

				<?php if ( function_exists( 'wak_update_autoshop_stats' ) ) wak_update_autoshop_stats( $post_id ); ?>

				<?php endwhile; else : ?>

				<article id="post-404" class="404">

					<h1>Page Not Found</h1>
					<p>Upps. It seems the page you were looking for no longer exists. <a href="<?php echo home_url(); ?>">Go Home</a></p>

				</article>

				<?php endif; ?>

			</div>
			<div class="col-md-3 col-xs-12" id="sidebar">

				<?php get_sidebar(); ?>

			</div>

		</div>

	</div>

</div>

<?php get_footer(); ?>