<?php

get_header();

global $wp_query;

$count = 0;
$total = $wp_query->post_count;
?>
<div class="outer-wrapper" id="main-content" style="padding-top: 48px;">

	<div class="inner-wrapper boxed" id="blog-roll">

		<div id="category-list">
			<ul>
				<?php wp_list_categories( 'title_li=' ); ?>
			</ul>
			<div class="clear clearfix"></div>
		</div>

		<?php if ( have_posts() ) : ?>
			<?php while ( have_posts() ) : the_post(); $count ++; ?>

				<?php if ( $count == 1 ) { ?>

		<div class="row">
			<div class="col-md-12">
				<article id="post-<?php the_ID(); ?>" class="post">
					<div class="row">
						<div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
							<?php if ( has_post_thumbnail() ) the_post_thumbnail( 'large' ); else echo '<a href="' . get_permalink() . '"><img src="http://womenautoknow.com/wp-content/uploads/2015/06/about-audra.jpg" alt="" /></a>'; ?>
						</div>
						<div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
							<h2><a href="<?php the_permalink(); ?>" class="pink"><?php the_title(); ?></a></h2>
							<small><?php

	
	printf( '<span class="byline"><span class="author vcard"><strong class="screen-reader-text pink">%1$s </strong><a class="url fn n" href="%2$s">%3$s</a></span></span>',
		_x( 'Posted by', 'Used before post author name.', 'twentyfifteen' ),
		esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
		get_the_author()
	);

	if ( 'post' == get_post_type() ) {
		$categories_list = get_the_category_list( _x( ', ', 'Used between list items, there is a space after the comma.', 'twentyfifteen' ) );
		if ( $categories_list ) {
			printf( '<span class="cat-links"><strong class="screen-reader-text pink">%1$s </strong>%2$s</span>',
				_x( 'Categories', 'Used before category names.', 'twentyfifteen' ),
				$categories_list
			);
		}
	}

?></small>
							<div class="excerpt"><?php the_excerpt(); ?></div>
						</div>
					</div>
				</article>
			</div>
		</div>

				<?php } else { ?>

					<?php if ( $count == 2 || $count == 5 || $count == 8 ) { ?>
		<div class="row">
					<?php } ?>

			<div class="col-md-4 col-sm-12 col-xs-12">
				<article id="post-<?php the_ID(); ?>" class="post">
					<div class="row">
						<div class="col-lg-12">
							<?php if ( has_post_thumbnail() ) the_post_thumbnail( 'large' ); else echo '<a href="' . get_permalink() . '"><img src="http://womenautoknow.com/wp-content/uploads/2015/06/about-audra.jpg" alt="" /></a>'; ?>
						</div>
						<div class="col-lg-12">
							<h2><a href="<?php the_permalink(); ?>" class="pink"><?php the_title(); ?></a></h2>
							<small><?php

	printf( '<span class="byline"><span class="author vcard"><strong class="screen-reader-text pink">%1$s </strong><a class="url fn n" href="%2$s">%3$s</a></span></span>',
		_x( 'Posted by', 'Used before post author name.', 'twentyfifteen' ),
		esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
		get_the_author()
	);

	if ( 'post' == get_post_type() ) {
		$categories_list = get_the_category_list( _x( ', ', 'Used between list items, there is a space after the comma.', 'twentyfifteen' ) );
		if ( $categories_list ) {
			printf( '<span class="cat-links"><strong class="screen-reader-text pink">%1$s </strong>%2$s</span>',
				_x( 'Categories', 'Used before category names.', 'twentyfifteen' ),
				$categories_list
			);
		}
	}

?></small>
							<div class="excerpt"><?php the_excerpt(); ?></div>
						</div>
					</div>
				</article>
			</div>

					<?php if ( $count == 4 || $count == 7 || $count == 10 ) { ?>
		</div>
					<?php } ?>

				<?php } ?>


			<?php endwhile; ?>

			<?php if ( $total == 2 || $total == 5 || $total == 8 ) : ?>
			<div class="col-md-4 hidden-sm hidden-xs"></div>
			<div class="col-md-4 hidden-sm hidden-xs"></div>
		</div>
			<?php elseif ( $total == 3 || $total == 6 || $total == 9 ) : ?>
			<div class="col-md-4 hidden-sm hidden-xs"></div>
		</div>
			<?php elseif ( $total == 4 || $total == 7 ) : ?>
		</div>
			<?php endif; ?>

		<div class="row">
			<div class="col-md-12">
				<div class="navigation"><p><?php posts_nav_link(); ?></p></div>
			</div>
		</div>

		<?php else : ?>

		<div class="row">
			<div class="col-md-12">
				<p>No posts found</p>
			</div>
		</div>

		<?php endif; ?>

	</div>

</div>

<?php get_footer(); ?>