<div class="outer-wrapper" id="website-footer">

	<div class="inner-wrapper boxed">
		<footer>
			<div class="row">
				<div class="col-md-4 col-sm-4 col-xs-12">
					<div class="widget">
						<h4 class="widget-title">Contact Women Auto Know</h4>
						<div>
							<img class="medium" src="<?php echo esc_url( get_template_directory_uri() . '/images/large-logo-full.png' ); ?>" alt="" />

							<?php wak_theme_contact_details(); ?>
						</div>
					</div>
				</div>
				<div class="col-md-4 col-sm-4 col-xs-12">
				<?php wak_theme_twitter_feed(); ?>
				</div>
				<div class="col-md-4 col-sm-4 col-xs-12">
					<div class="widget">
						<h4 class="widget-title">Helpful Links</h4>
<?php

	// Primary navigation menu.
	wp_nav_menu( array(
		'menu_class'     => 'nav-menu',
		'theme_location' => 'footer',
	) );

?>
					</div>
				</div>
			</div>
		</footer>
	</div>

</div>

<div class="outer-wrapper" id="legal">

	<div class="inner-wrapper boxed">
		<div id="website-cc">Copyright &copy; <?php echo date( 'Y', current_time( 'timestamp' ) ); ?> Women Auto Know. All rights reserved.</div>
	</div>

	<?php wp_footer(); ?>

<script type="text/javascript">
(function($) {

	$( '#wak-visitors-nav a' ).click(function(){
		var menuel = $( '#menu-top-menu-visitors' );
		if ( menuel.hasClass( 'open' ) )
			menuel.hide().removeClass( 'open' );
		else
			menuel.show().addClass( 'open' );
	});

})(jQuery);
</script>
</div>
</body>
</html>