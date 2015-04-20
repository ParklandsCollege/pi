<?php
class Image_Upload extends WP_Customize_Control {
	public function render_content() {
	?>
		<span class="customize-control-title">
			<?php echo esc_html( $this->label ); ?>
		</span>
		<div class="sleek-image-upload-field">
			<img class="sleek-image-upload-preview" src="<?php echo $this->value();?>">
			<input type="hidden" value="<?php echo $this->value(); ?>" <?php $this->link(); ?>>
			<a class="button button-primary js-sleek-image-upload-button">
				<!-- if image exists add remove btn -->
				<?php if($this->value()): ?>
					Change Image
				<?php else: ?>
					Upload Image
				<?php endif; ?>
			</a>
			<a class="remove js-bg-image-remove">Remove</a>
		</div>
	<?php
	}
}
?>