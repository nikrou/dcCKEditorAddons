<html>
  <head>
    <title>dcCKEditorAddons</title>
    <?php echo dcPage::jsPageTabs($default_tab);?>
    <?php echo dcPage::cssLoad('index.php?pf=dcCKEditorAddons/css/admin.css');?>
    <?php echo dcPage::jsLoad('index.php?pf=dcCKEditorAddons/js/admin.js');?>
    <script type="text/javascript">
      //<![CDATA[
      var dcckeditor_addons_confirm_delete = [];
      dcckeditor_addons_confirm_delete['addons'] = "<?php echo __('Are you sure you want to delete selected addons (%s)?');?>";
      dcckeditor_addons_confirm_delete['addon'] = "<?php echo __('Are you sure you want to delete selected addon?');?>";
      //]]>
    </script>
  </head>
  <body>
    <?php echo dcPage::breadcrumb(array(__('Plugins') => '',__('dcCKEditorAddons') => '')).dcPage::notices(); ?>

    <?php if ($dcckeditor_active && $is_admin):?>
    <div class="multi-part" id="settings" title="<?php echo __('Settings');?>">
      <h3 class="hidden-if-js"><?php echo __('Settings');?></h3>
      <form action="<?php echo  dcCore::app()->admin->getPageURL();?>" method="post" enctype="multipart/form-data">
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
	    <label class="classic" for="dcckeditor_addons_check_validity">
	      <?php echo form::checkbox('dcckeditor_addons_check_validity', 1, $dcckeditor_addons_check_validity);?>
	      <?php echo __('Check if zip file is a valid CKEditor addon?');?>
	    </label>
	  </p>
	  <p class="form-note">
	    <?php echo __('If test for a valid CKEditor addon failed and you can not add the addon, then uncheck that option or unzip the CKEditor addon manually');?>
	  </p>
          <p>
	    <label for="repository" class="classic"><?php echo __('Repository path :').' ';?>
              <?php echo form::field('dcckeditor_addons_repository_path', 80, 255, $dcckeditor_addons_repository_path);?>
            </label>
	  </p>
	</div>
	<?php endif;?>

	<p>
	  <input type="hidden" name="p" value="dcCKEditorAddons"/>
	  <?php echo dcCore::app()->formNonce();?>
	  <input type="submit" name="saveconfig" value="<?php echo __('Save configuration');?>" />
	</p>
      </form>
    </div>

    <?php if ($dcckeditor_addons_active):?>
    <div class="multi-part" id="plugins" title="<?php echo __('Plugins');?>">
      <h3 class="hidden-if-js"><?php echo __('Plugins');?></h3>
      <p class="top-add">
	<a class="button add" href="<?php echo  dcCore::app()->admin->getPageURL();?>#add-plugin"><?php echo __('Add a plugin');?></a>
      </p>
      <?php if (!empty($plugins)):?>
      <form method="post" action="<?php echo  dcCore::app()->admin->getPageURL();?>#plugins" enctype="multipart/form-data" name="plugins-list" id="plugins-form">
	<div class="table-outer ckeditor-addons">
	  <table>
	    <thead>
	      <th><?php echo __('Name');?></th>
	      <th><?php echo __('Button');?></th>
	      <th><?php echo __('Dependencies');?></th>
	      <th><?php echo __('Activated?');?></th>
	    </thead>
	    <tbody>
	      <?php foreach ($plugins as $plugin_name => $plugin):?>
	      <tr>
		<td>
		  <label class="classic">
		    <input type="checkbox" name="plugins[]" value="<?php echo $plugin_name;?>">
		    <?php echo $plugin_name;?>
		  </label>
		</td>
		<td>
		  <?php echo form::field(array('buttons['.$plugin['name'].']'),80,255,$plugin['button']);?>
		</td>
		<td>
		  <?php if (!empty($plugin['dependencies'])):?>
		  <?php echo $plugin['dependencies'];?>
		  <?php endif;?>
		</td>
		<td>
		  <?php echo $img_plugin_status[$plugin['activated']];?>
		</td>
	      </tr>
	      <?php endforeach;?>
	    </tbody>
	  </table>
	  <div class="two-cols">
	    <p class="col checkboxes-helpers"></p>
	    <p class="col right">
	      <label for="action" class="classic"><?php echo __('Selected addons action:');?></label>
	      <?php echo form::combo('action', $plugins_actions_combo);?>
	      <?php echo dcCore::app()->formNonce();?>
	      <input type="submit" value="<?php echo __('ok');?>" />
	    </p>
	  </div>
	</div>
      </form>
      <?php endif;?>
    </div>

    <div class="multi-part" id="add-plugin" title="<?php echo __('Add a plugin');?>">
      <p><?php echo __('You can install plugins by uploading or downloading zip files.');?></p>
      <div class="fieldset">
	<form method="post" action="<?php echo dcCore::app()->admin->getPageURL();?>#plugins" enctype="multipart/form-data" name="upload-plugin">
	  <h4><?php echo __('Upload a zip file');?></h4>
	  <p class="field">
	    <label for="plugin_file" class="classic required">
	      <abbr title="<?php echo __('Required field');?>">*</abbr> <?php echo __('Zip file path:');?>
	    </label>
	    <input type="file" name="plugin_file" id="plugin_file">
	  </p>
	  <p class="field">
	    <label for="passwd1" class="classic required">
	      <abbr title="<?php echo __('Required field');?>">*</abbr> <?php echo __('Your password:');?>
	    </label>
	    <input type="password" name="your_pwd" id="passwd1" value="">
	  </p>
	  <p>
	    <input type="submit" name="upload_plugin" value="<?php echo __('Upload');?>"/>
	    <?php echo dcCore::app()->formNonce();?>
	  </p>
	</form>
      </div>
      <div class="fieldset">
	<form method="post" action="<?php echo dcCore::app()->admin->getPageURL();?>#plugins" enctype="multipart/form-data" name="download-plugin">
	  <h4><?php echo __('Download a zip file');?></h4>
	  <p class="field">
	    <label for="plugin_url" class="classic required">
	      <abbr title="<?php echo __('Required field');?>">*</abbr> <?php echo __('Zip file URL:');?>
	    </label>
	    <input type="text" name="plugin_url" id="plugin_url" value="">
	  </p>
	  <p class="field">
	    <label for="passwd2" class="classic required">
	      <abbr title="<?php echo __('Required field');?>">*</abbr> <?php echo __('Your password:');?>
	    </label>
	    <input type="password" name="your_pwd" id="passwd2" value="">
	  <p>
	    <input type="submit" name="fetch_plugin" value="<?php echo __('Download');?>"/>
	    <?php echo dcCore::app()->formNonce();?>
	  </p>
	</form>
      </div>
    </div>
    <?php endif;?>

    <?php endif;?>

    <?php dcPage::helpBlock('dcCKEditorAddons');?>
  </body>
</html>
