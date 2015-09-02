<?php if ( is_author() ) : ?>

	<?php if ( is_active_sidebar( 'profile' ) ) : ?>

	<?php dynamic_sidebar( 'profile' ); ?>

	<?php endif; ?>

<?php elseif ( is_post_type_archive( 'autoshops' ) || is_singular( array( 'autoshops' ) ) ) : ?>

	<?php if ( is_active_sidebar( 'autoshop' ) ) : ?>

	<?php dynamic_sidebar( 'autoshop' ); ?>

	<?php endif; ?>

<?php elseif ( is_home() && ! is_front_page() ) : ?>

	<?php if ( is_active_sidebar( 'blog' ) ) : ?>

	<?php dynamic_sidebar( 'blog' ); ?>

	<?php endif; ?>

<?php elseif ( is_tree( 33 ) ) : ?>

	<?php if ( is_active_sidebar( 'resources' ) ) : ?>

	<?php dynamic_sidebar( 'resources' ); ?>

	<?php endif; ?>

<?php elseif ( is_singular() ) : ?>

	<?php if ( is_active_sidebar( 'posts' ) ) : ?>

	<?php dynamic_sidebar( 'posts' ); ?>

	<?php endif; ?>

<?php else : ?>

	<?php if ( is_active_sidebar( 'sidebar' ) ) : ?>

	<?php dynamic_sidebar( 'sidebar' ); ?>

	<?php endif; ?>

<?php endif; ?>