<?php get_header(); ?>

<style type="text/css">
#the-content article {
	margin-bottom: 32px;
}
#the-content article .entry-content {
	padding: 24px 0 48px 0;
}
#the-content article .entry-header {
	font-size: 13px;
	line-height: 34px;
	color: #aaa;
	border-top: 1px solid #ddd;
}
#the-content article .entry-header > span {
	padding-right: 24px;
}
</style>
<div class="outer-wrapper" id="main-content" style="padding-top: 48px;">

	<div class="inner-wrapper boxed">

		<div class="row sidebar-right">

			<div class="col-md-8 col-xs-12" id="the-content">
				<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

					<header>
						<div class="post-title">
							<h1 class="pink"><?php the_title(); ?></h1>
						</div>
						<div class="entry-header">
<?php

	if ( 'post' == get_post_type() ) {
		printf( '<span class="byline"><span class="author vcard"><span class="screen-reader-text">%1$s </span><a class="url fn n" href="%2$s">%3$s</a></span></span>',
				_x( 'Posted by', 'Used before post author name.', 'twentyfifteen' ),
				esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
				get_the_author()
			);

		$categories_list = get_the_category_list( _x( ', ', 'Used between list items, there is a space after the comma.', 'twentyfifteen' ) );
		if ( $categories_list ) {
			printf( '<span class="cat-links"><span class="screen-reader-text">%1$s </span>%2$s</span>',
				_x( 'Categories', 'Used before category names.', 'twentyfifteen' ),
				$categories_list
			);
		}

		$tags_list = get_the_tag_list( '', _x( ', ', 'Used between list items, there is a space after the comma.', 'twentyfifteen' ) );
		if ( $tags_list ) {
			printf( '<span class="tags-links"><span class="screen-reader-text pink">%1$s </span>%2$s</span>',
				_x( 'Tags', 'Used before tag names.', 'twentyfifteen' ),
				$tags_list
			);
		}
	}

?>
						</div>
					</header>

<?php
		// Post thumbnail.
		wak_post_thumbnail();
?>

					<div class="entry-content">

						<?php the_content(); ?>

					</div>

					<footer class="entry-footer">
						
					</footer>

				</article>
<?php

			endwhile; else :

?>

				<article id="post-404" class="404">

					<h1>Page Not Found</h1>
					<p>Upps. It seems the page you were looking for no longer exists. <a href="<?php echo home_url(); ?>">Go Home</a></p>

				</article>

				<?php endif; ?>

			</div>
			<div class="col-md-4 col-xs-12" id="sidebar">

				<?php get_sidebar(); ?>

			</div>

		</div>

	</div>

</div>

<?php get_footer(); ?>