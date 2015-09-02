<?php get_header(); ?>

<?php global $post; $layout = wak_theme_get_layout( $post->ID ); ?>

<div class="outer-wrapper" id="main-content">

	<div class="inner-wrapper boxed">

		<?php get_template_part( 'content', 'sidebar-' . $layout ); ?>

	</div>

</div>

<?php get_footer(); ?>