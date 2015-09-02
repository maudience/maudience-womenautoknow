<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width">
	<link rel="profile" href="http://gmpg.org/xfn/11">
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">

	<link rel="apple-touch-icon" sizes="57x57" href="<?php home_url(); ?>/favicons/apple-touch-icon-57x57.png?ver=1.0">
	<link rel="apple-touch-icon" sizes="60x60" href="<?php home_url(); ?>/favicons/apple-touch-icon-60x60.png?ver=1.0">
	<link rel="apple-touch-icon" sizes="72x72" href="<?php home_url(); ?>/favicons/apple-touch-icon-72x72.png?ver=1.0">
	<link rel="apple-touch-icon" sizes="76x76" href="<?php home_url(); ?>/favicons/apple-touch-icon-76x76.png?ver=1.0">
	<link rel="apple-touch-icon" sizes="114x114" href="<?php home_url(); ?>/favicons/apple-touch-icon-114x114.png?ver=1.0">
	<link rel="apple-touch-icon" sizes="120x120" href="<?php home_url(); ?>/favicons/apple-touch-icon-120x120.png?ver=1.0">
	<link rel="apple-touch-icon" sizes="144x144" href="<?php home_url(); ?>/favicons/apple-touch-icon-144x144.png?ver=1.0">
	<link rel="apple-touch-icon" sizes="152x152" href="<?php home_url(); ?>/favicons/apple-touch-icon-152x152.png?ver=1.0">
	<link rel="apple-touch-icon" sizes="180x180" href="<?php home_url(); ?>/favicons/apple-touch-icon-180x180.png?ver=1.0">
	<link rel="icon" type="image/png" href="<?php home_url(); ?>/favicons/favicon-32x32.png?ver=1.0" sizes="32x32">
	<link rel="icon" type="image/png" href="<?php home_url(); ?>/favicons/android-chrome-192x192.png?ver=1.0" sizes="192x192">
	<link rel="icon" type="image/png" href="<?php home_url(); ?>/favicons/favicon-96x96.png?ver=1.0" sizes="96x96">
	<link rel="icon" type="image/png" href="<?php home_url(); ?>/favicons/favicon-16x16.png?ver=1.0" sizes="16x16">
	<link rel="manifest" href="<?php home_url(); ?>/favicons/manifest.json?ver=1.0">
	<link rel="shortcut icon" href="<?php home_url(); ?>/favicons/favicon.ico?ver=1.0">
	<meta name="apple-mobile-web-app-title" content="WomenAutoKnow">
	<meta name="application-name" content="WomenAutoKnow">
	<meta name="msapplication-TileColor" content="#f1f1f1">
	<meta name="msapplication-TileImage" content="<?php home_url(); ?>/favicons/mstile-144x144.png?ver=1.0">
	<meta name="theme-color" content="#ffffff">

	<!--[if lt IE 9]>
	<script src="<?php echo esc_url( get_template_directory_uri() ); ?>/js/html5.js"></script>
	<![endif]-->
	<script>(function(){document.documentElement.className='js'})();</script>

	<?php wp_head(); ?>
	<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">

</head>

<body <?php body_class(); ?>>

<div class="outer-wrapper" id="website-header">

	<div class="inner-wrapper boxed">

		<header>

			<div class="row">
			
				<div class="col-md-2 col-sm-2 col-xs-4">
					<a id="logo" href="<?php echo home_url(); ?>"><img src="<?php echo esc_url( get_template_directory_uri() . '/images/logo.png' ); ?>" alt="" /></a>
				</div>
				<div class="col-md-10 col-sm-10 col-xs-8">
					<div id="top-row">
						<ul>
							<li>Autoshops: <strong><?php echo wak_theme_autoshops_count(); ?></strong></li>
							<li>Pledged Drivers: <strong><?php echo wak_theme_driver_count(); ?></strong></li>
						</ul>
					</div>
					<?php wak_theme_top_navigation(); ?>
				</div>

			</div>

		</header>

	</div>

</div>

<?php //get_template_part( 'menu', ( is_user_logged_in() ) ? 'member' : 'visitors' ); ?>