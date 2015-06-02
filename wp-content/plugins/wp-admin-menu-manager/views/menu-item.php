<?php //var_dump($menu); ?>
<div id="" class="widget">
  <div class="widget-top">
    <div class="widget-title-action">
      
      <?php if (isset($menu['separator']) && !$menu['separator']) : ?>
        <!-- Show more Icon, for non-separator buttons -->
        <a class="widget-action hide-if-no-js" href="#available-widgets"></a>
      <?php endif; ?>
      
    </div>

    <div class="widget-title">
      <h4 style="<?php echo ($menu['separator']) ? 'background-color: #fefefe !important;' : ''; ?>">
        <span data-wpamm-icon="<?php echo $menu[6]; ?>" class="dashicons <?php echo ($menu['icon']) ? $menu['icon'] : $menu[6]; ?>" style=""></span>
        <span class="wp-admin-menu-title"><?php echo ($menu['rename']) ? $menu['rename'].' ('.$menu[0].')' : $menu[0]; ?></span>
      </h4>
    </div>
  </div>

  <?php if (!$menu['separator']) : ?>
  <div class="widget-inside" style="padding: 15px;">
      
      <div class="widget-content">
        <p>
          <label for="<?php echo $menu[0]; ?>-rename">
          <span class="wpamm-title"><?php _e('Rename', $this->text_domain); ?></span>
          <input class="widefat" id="<?php echo $menu[0]; ?>-rename" name="menu[menu-<?php echo $newOrder; ?>][rename]" type="text" value="<?php echo $menu['rename']; ?>">
          </label>
        </p>
        
        <!--
        Change Icons
        -->
        <p>
          <label for="<?php echo $menu[0]; ?>-icon">
          <span class="wpamm-title"><?php _e('Icon', $this->text_domain); ?></span>
          
          <input class="widefat" id="<?php echo $menu[0]; ?>-icon" name="menu[menu-<?php echo $newOrder; ?>][icon]" type="text" value="<?php echo $menu['icon']; ?>">
          <input type="button" data-target="#<?php echo $menu[0]; ?>-icon" class="button dashicons-picker" value="<?php _e('Choose Icon', $this->text_domain); ?>" />
            
          </label>
        </p>

        <?php if ($this->hasItemSubmenus($menu[2])) : ?>
        <p>
          <span class="wpamm-title"><?php _e('Submenus', $this->text_domain); ?></span><br>
          <?php _e('Edit name, link and position of submenus.', $this->text_domain); ?>

          <div class="wpamm-submenu-sortable">
            <?php $this->getItemSubmenus($menu[2]); ?>
          </div>

          <!-- <div class="wpamm-add-more">
            <div class="alignleft">
              <a class="wpamm-button wpadmin-add-new-submenu" href="#"><?php _e('Add new submenu', $this->text_domain); ?></a>
            </div>
          </div> -->
        </p>
      <?php endif; ?>

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
          <a class="widget-control-close wpamm-close" href="#close"><?php _e('Close Item', $this->text_domain); ?></a>
        </div>

        <!--
        <div class="alignright">
          <input type="submit" name="savewidget" id="widget-search-2-savewidget" class="button button-primary widget-control-save right" value="<?php _e('Save', $this->text_domain); ?>"><span class="spinner"></span>
        </div>
        -->
        <br class="clear">
      </div>
  </div>

<!-- Is SEPARATOR, show options -->
<?php elseif (false) : ?>

  <div class="widget-inside" style="padding: 15px;">
      <div class="widget-content">
        
        <p>
          <span class="wpamm-title"><?php _e('Separator options', $this->text_domain); ?></span><br>
          <?php _e('You can duplicate or delete them.', $this->text_domain); ?>
        </p>
        
        <p class="separator-action-button">
          <button class="wpamm-separator-duplicate button button-primary"><?php _e('Duplicate', $this->text_domain); ?></button>
          <button class="wpamm-separator-delete button"><?php _e('Delete', $this->text_domain); ?></button>
        </p>
        
        <div class="widget-control-actions">
          <div class="alignleft">
            <a class="widget-control-close wpamm-close" href="#close"><?php _e('Close Item', $this->text_domain); ?></a>
          </div>

          <!--
          <div class="alignright">
            <input type="submit" name="savewidget" id="widget-search-2-savewidget" class="button button-primary widget-control-save right" value="<?php _e('Save', $this->text_domain); ?>"><span class="spinner"></span>
          </div>
          -->
          <br class="clear">
        </div>
        
      </div>
  </div>

<!-- End IF separator -->
<?php endif; ?>

<input class="order-carrier" type="hidden" name="menu[menu-<?php echo $newOrder; ?>][order]" value="<?php echo $menuID; ?>">
<input class="id-carrier" type="hidden" name="menu[menu-<?php echo $newOrder; ?>][id]" value="<?php echo $menu[2]; ?>">

</div>