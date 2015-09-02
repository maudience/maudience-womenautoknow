<?php get_header(); ?>

<div class="outer-wrapper autoshop-archive" id="main-content">

	<div class="inner-wrapper boxed">

		<?php get_template_part( 'content', 'autoshop-locator' ); ?>

		<?php wak_search_autoshops(); ?>

		<div class="row">

			<div class="col-md-12 col-xs-12" id="the-content">

				<div class="row">

					<?php $counter = 0; if ( have_posts() ) : while ( have_posts() ) : the_post(); $post_id = get_the_ID(); ?>

					<?php if ( $counter != 0 && $counter % 2 == 0 ) : ?>

					<div class="row">

					<?php endif; ?>

					<div class="col-md-6 col-sm-6 col-xs-12">

						<?php
						
						if ( autoshop_has_pledged( $post_id ) )
							get_template_part( 'autoshop', 'pledged' );
						
						elseif ( autoshop_is_premium( $post_id ) )
							get_template_part( 'autoshop', 'premium' );
						
						else
							get_template_part( 'autoshop', 'default' );

						?>

					</div>

					<?php if ( $counter != 0 && $counter % 2 != 0 ) : ?>

					</div>

					<?php endif; ?>

						

				<?php $counter ++; endwhile; ?>

					<?php if ( $counter % 2 != 0 ) : ?>

					<div class="col-md-6 col-sm-6 col-xs-12">
					</div>

					<?php endif; ?>

				</div>

				<div class="row">
					<div class="col-md-12 col-xs-12">
<?php

				// Previous/next page navigation.
				the_posts_pagination( array(
					'prev_text'          => 'Previous page',
					'next_text'          => 'Next page',
					'before_page_number' => '<span class="meta-nav screen-reader-text">Page </span>',
				) );

?>

					</div>
				</div>

				<?php else : ?>

				<div class="row">
					<div class="col-md-12 col-xs-12">
						<p>No shops found.</p>
					</div>
				</div>

				<?php endif; wak_reset_query(); ?>

			</div>

		</div>

	</div>

</div>

<?php get_footer(); ?>