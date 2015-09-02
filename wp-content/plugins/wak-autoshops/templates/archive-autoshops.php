<?php

	get_header();

	$autoshops = new WAK_Autoshops();

?>

<?php $autoshops->search_form(); ?>

<div class="outer-wrapper autoshop-archive" id="main-content" style="padding-left: 15px; padding-right: 15px;">

	<div class="inner-wrapper boxed">

		<?php $autoshops->before(); ?>

		<?php $autoshops->query(); ?>

		<div class="row">

			<div class="col-md-12 col-xs-12" id="the-content">

				<div class="row">

					<?php $autoshops->display(); ?>

				</div>

			</div>

		</div>

		<?php $autoshops->after(); ?>

	</div>

</div>

<?php get_footer(); ?>