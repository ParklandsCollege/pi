<div class="widget">
  <div class="widget-top">
    <div class="widget-title-action">
      <a class="widget-action hide-if-no-js" href="#available-widgets"></a> 
      <a class="widget-control-edit hide-if-js" href="/wordpress/wp-admin/widgets.php?editwidget=search-2&amp;sidebar=sidebar-primary&amp;key=0">
        <span class="edit">Edit</span>
        <span class="add">Add</span>
        <span class="screen-reader-text"></span>
      </a>
    </div>

    <div class="widget-title <?php echo ($submenu['hide'] == 'on') ? 'submenu-hidden' : ''; ?>">
      <h4 style="no-text-indent">
        <span class="wp-admin-submenu-title"><?php echo ($submenu['rename'] == '') ? $submenu[0] : "{$submenu['rename']} ({$submenu[0]})"; ?></span>
      </h4>
    </div>
  </div>

    <div class="widget-inside" style="padding: 15px;">
      
      <div class="widget-content">
        <p>
          <label for="<?php echo $submenu[1]; ?>-rename">
          <span class="wpamm-title"><?php _e('Rename', $this->text_domain); ?></span>
          <br><?php _e('Change the name of this submenu item.', $this->text_domain); ?>
          <input class="widefat" id="<?php echo $submenu[1]; ?>-rename" name="submenu[<?php echo $id; ?>][<?php echo $newOrder; ?>][rename]" type="text" value="<?php echo $submenu['rename']; ?>">
          </label>

          <label for="<?php echo $submenu[1]; ?>-link">
          <span class="wpamm-title"><?php _e('Custom Link', $this->text_domain); ?></span>
          <br><?php _e('Set a custom link for the submenu item.', $this->text_domain); ?>
          <input class="widefat" id="<?php echo $submenu[1]; ?>-link" name="submenu[<?php echo $id; ?>][<?php echo $newOrder; ?>][link]" type="text" value="<?php echo $submenu['link']; ?>">
          </label>

          <label for="<?php echo $submenu[1]; ?>-hide">
          <input class="" id="<?php echo $submenu[1]; ?>-hide" name="submenu[<?php echo $id; ?>][<?php echo $newOrder; ?>][hide]" type="checkbox" <?php echo ($submenu['hide'] == 'on') ? 'checked' : ''; ?>>
          <span class="wpamm-title"><?php _e('Hide Submenu?', $this->text_domain); ?></span>
          </label>
        </p>
      </div>

      <!--
      <input type="hidden" name="id_base" class="id_base" value="search">
      <input type="hidden" name="widget-width" class="widget-width" value="250">
      <input type="hidden" name="widget-height" class="widget-height" value="200">
      <input type="hidden" name="widget_number" class="widget_number" value="2">
      <input type="hidden" name="multi_number" class="multi_number" value="">
      <input type="hidden" name="add_new" class="add_new" value="">
      -->

      <div class="widget-control-actions">
        <div class="alignleft">
          <a class="widget-control-close wpamm-close" href="#close">Close Item</a>
        </div>

        <!--
        <div class="alignright">
          <input type="submit" name="savewidget" id="widget-search-2-savewidget" class="button button-primary widget-control-save right" value="Save"><span class="spinner"></span>
        </div>
        -->
        <br class="clear">
      </div>
  </div>

<input type="hidden" name="submenu[<?php echo $id; ?>][<?php echo $newOrder; ?>][order]" value="<?php echo $order; ?>">
<input type="hidden" name="submenu[<?php echo $id; ?>][<?php echo $newOrder; ?>][0]" value="<?php echo $submenu[0]; ?>">
<input type="hidden" name="submenu[<?php echo $id; ?>][<?php echo $newOrder; ?>][1]" value="<?php echo $submenu[1]; ?>">
<input type="hidden" name="submenu[<?php echo $id; ?>][<?php echo $newOrder; ?>][2]" value="<?php echo $submenu[2]; ?>">

<!-- <input type="hidden" name="submenu[<?php echo $id; ?>][<?php echo $newOrder; ?>][id]" value="edit.php"> -->

</div>