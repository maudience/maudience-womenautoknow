<div id="locate-autoshop" class="row">
	<div id="loading-wak-autoshop-search" class="modal-backdrop" style="display:none;">
		<div class="progress-box">
			<p class="indicator"><i class="fa fa-spinner fa-spin pink"></i></p>
			<p class="result">searching auto shops ...</p>
		</div>
	</div>
	<form class="form-inline" id="search-wak-autoshops-form" action="" method="post">
		<div class="col-md-9 col-xs-12">
			<div class="row">
				<div class="col-md-6">
					<div class="form-group">
						<input type="text" name="name" size="35" maxlength="128" class="form-control" id="wak-locate-auto-name" value="" placeholder="Auto Shop Name" />
					</div>
				</div>
				<div class="col-md-6">
					<div class="form-group">
						<div class="checkbox">
							<label for="wak-locate-auto-pledged"><input type="checkbox" name="pledged" checked="checked" id="wak-locate-auto-pledged" value="1" /> Pledged autoshops only.</label>
						</div>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-12">
					<div class="form-group">
						<input type="text" name="address1" size="35" maxlength="128" class="form-control" id="wak-locate-auto-address" value="" placeholder="Address" />
						<input type="text" name="zip" size="8" maxlength="6" class="form-control" id="wak-locate-auto-zip" value="" placeholder="Zip" />
						<?php echo wak_states_dropdown( 'state', 'wak-locate-auto-state', 'Select State', '' ); ?>
						<?php echo wak_orderby_dropdown( 'orderby', 'wak-locate-auto-orderby', ( ( isset( $_GET['orderby'] ) ? $_GET['orderby'] : 'highest-rating' ) ) ); ?>
					</div>
				</div>
			</div>
		</div>
		<div class="col-md-3 col-xs-12">
			<div class="row">
				<div class="col-md-12">
					<input type="submit" class="btn btn-danger btn-block form-control" id="do-search-autoshops" value="Search Auto Shops" />
				</div>
			</div>
			<div class="row">
				<div class="col-md-12">
					<?php if ( is_user_logged_in() ) : ?><button id="wak-add-new-autoshop-button" class="btn btn-default btn-block form-control">Add Auto Shop</button><?php endif; ?>
				</div>
			</div>
		</div>
		<div class="clear clearfix"></div>
	</form>
</div>