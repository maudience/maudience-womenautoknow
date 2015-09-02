		<div class="row sidebar-left">

			<div class="col-md-4 col-xs-12" id="sidebar">

				<?php get_sidebar(); ?>

			</div>

			<div class="col-md-8 col-xs-12" id="the-content">
				<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

					<?php the_content(); ?>

				</article>

				<?php endwhile; else : ?>

				<article id="post-404" class="404">

					<h1>Page Not Found</h1>
					<p>Upps. It seems the page you were looking for no longer exists. <a href="<?php echo home_url(); ?>">Go Home</a></p>

				</article>

				<?php endif; ?>

			</div>

		</div>