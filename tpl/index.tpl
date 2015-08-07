<html>
  <head>
    <title>dcCKEditorAddons</title>
  </head>
  <body>
    <?php echo dcPage::breadcrumb(array(__('Plugins') => '',__('dcCKEditorAddons') => '')).dcPage::notices(); ?>

    <?php if ($dcckeditor_active && $is_admin):?>
    <h3 class="hidden-if-js"><?php echo __('Settings');?></h3>
    <form action="<?php echo $p_url;?>" method="post" enctype="multipart/form-data">
      <div class="fieldset">
	<h3><?php echo __('Plugin activation');?></h3>
	<p>
	  <label class="classic" for="dcckeditor_addons_active">
	    <?php echo form::checkbox('dcckeditor_addons_active', 1, $dcckeditor_addons_active);?>
	    <?php echo __('Enable dcCKEditorAddons plugin');?>
	  </label>
	</p>
      </div>

      <?php if ($dcckeditor_addons_active):?>
      <div class="fieldset">
        <h3><?php echo  __('Options');?></h3>
        <p>
	  <label for="repository" class="classic"><?php echo __('Repository path :').' ';?>
            <?php echo form::field('dcckeditor_addons_repository_path', 80, 255, $dcckeditor_addons_repository_path);?>
          </label>
	</p>
      </div>
      <?php endif;?>

      <p>
	<input type="hidden" name="p" value="dcCKEditorAddons"/>
	<?php echo $core->formNonce();?>
	<input type="submit" name="saveconfig" value="<?php echo __('Save configuration');?>" />
      </p>
    </form>
    <?php endif;?>

    <?php dcPage::helpBlock('dcCKEditorAddons');?>
  </body>
</html>
