<div class="widget-liquid-right" style="width: 100% !important;">

<?php wp_nonce_field($this->options_slug, 'amm_nonce'); ?>

	<div id="widgets-right">
		<div class="sidebars-column-1">
			<div class="">
				<div id="sidebar-primary" class="widgets-sortables ui-sortable">		

					<div class="sidebar-name">
						<div class="sidebar-name-arrow"><br></div>

						<h3 style="padding-left: 0 !important;"><?php _e('Activated Menus', $this->text_domain); ?> <span class="spinner"></span></h3>
						<p class="description"><?php _e('Drag the menu to the "Disabled" sidebar to make it invisible. To rename, click on the items.', $this->text_domain); ?></p>
					</div>

					<div class="sidebar-description"></div>

					<?php $this->makeAvailableList($post->ID); ?>

				</div>
			</div>
		</div>

		<!-- Print Array Separator -->
		<input type="hidden" name="menu[amm-separator]" value="amm-separator">

		<div class="sidebars-column-2">
			<div class="widgets-holder-wrap">
				<div id="sidebar-footer" class="widgets-sortables ui-sortable">
				<div class="sidebar-name">
					<div class="sidebar-name-arrow"><br></div>
					<h3><?php _e('Disabled', $this->text_domain); ?> <span class="spinner"></span></h3>
				</div>

				<?php $this->makeDisabledList($post->ID); ?>
			</div>
		</div>
	</div>
</div>
</div>
<form action="" method="post">
	<input type="hidden" id="_wpnonce_widgets" name="_wpnonce_widgets" value="c46f4b2bb2"></form>
	<br class="clear">