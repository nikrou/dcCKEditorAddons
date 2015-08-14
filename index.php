<?php
// +-----------------------------------------------------------------------+
// | dcCKEditorAddons - a plugin for Dotclear                              |
// +-----------------------------------------------------------------------+
// | Copyright(C) 2015 Nicolas Roudaire             http://www.nikrou.net  |
// +-----------------------------------------------------------------------+
// | This program is free software; you can redistribute it and/or modify  |
// | it under the terms of the GNU General Public License version 2 as     |
// | published by the Free Software Foundation                             |
// |                                                                       |
// | This program is distributed in the hope that it will be useful, but   |
// | WITHOUT ANY WARRANTY; without even the implied warranty of            |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU      |
// | General Public License for more details.                              |
// |                                                                       |
// | You should have received a copy of the GNU General Public License     |
// | along with this program; if not, write to the Free Software           |
// | Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,            |
// | MA 02110-1301 USA.                                                    |
// +-----------------------------------------------------------------------+

if (!defined('DC_CONTEXT_ADMIN')) { return; }

$is_admin = $core->auth->isSuperAdmin();

$dcckeditor_active = $core->blog->settings->dcckeditor->active;

$core->blog->settings->addNameSpace('dcCKEditorAddons');
$dcckeditor_addons_active = $core->blog->settings->dcCKEditorAddons->active;
$dcckeditor_addons_was_actived = $dcckeditor_addons_active;
$dcckeditor_addons_check_validity = $core->blog->settings->dcCKEditorAddons->check_validity;
$dcckeditor_addons_repository_path = $core->blog->settings->dcCKEditorAddons->repository_path;
$dcckeditor_addons_plugins = json_decode($core->blog->settings->dcCKEditorAddons->plugins, true);

$plugins = array();
$name_pattern = "`CKEDITOR\.plugins\.add\(\s*'([^']*)'`";
$button_pattern = "`addButton\(\s*'([^']*)'`";
$require_pattern = "`requires\s*:\s*'([^']*)'`";

foreach ($dirs = glob($dcckeditor_addons_repository_path.'/*/plugin.js') as $plugin_js) {
    $name = '';
    $plugin = array('name' => '', 'button' => '', 'activated' => false);
    $plugin_js_content = file_get_contents($plugin_js);
    if (preg_match($name_pattern, $plugin_js_content, $matches)) {
        $plugin['name'] = $matches[1];
    }
    if (preg_match($button_pattern, $plugin_js_content, $matches)) {
        $plugin['button'] = $matches[1];
    }
    if (preg_match($require_pattern, $plugin_js_content, $matches)) {
        $plugin['dependencies'] = $matches[1];
    }
    if (!empty($plugin['name'])) {
        if (!empty($dcckeditor_addons_plugins[$plugin['name']])) {
            foreach ($dcckeditor_addons_plugins[$plugin['name']] as $key => $value) {
                if (!empty($value)) {
                    $plugin[$key] = $value;
                }
            }
        }
        $plugins[$plugin['name']] = $plugin;
    }
}

$default_tab = 'settings';

if (!$dcckeditor_active) {
    dcPage::addErrorNotice(__('You must enable dcCKEditor plugin to use that plugin.'));
} else {
    if (!empty($_POST['saveconfig'])) {
        try {
            $dcckeditor_addons_active = (empty($_POST['dcckeditor_addons_active']))?false:true;
            $core->blog->settings->dcCKEditorAddons->put('active', $dcckeditor_addons_active, 'boolean');

            // change other settings only if they were in html page
            if ($dcckeditor_addons_was_actived) {
                $dcckeditor_addons_check_validity = (empty($_POST['dcckeditor_addons_check_validity']))?false:true;
                $core->blog->settings->dcCKEditorAddons->put('check_validity', $dcckeditor_addons_check_validity, 'boolean');

                if (empty($_POST['dcckeditor_addons_repository_path']) || trim($_POST['dcckeditor_addons_repository_path']) == '') {
                    $tmp_repository = $core->blog->public_path.'/dcckeditor_addons';
                } else {
                    $tmp_repository = trim($_POST['dcckeditor_addons_repository_path']);
                }

                if (is_dir($tmp_repository) && is_writable($tmp_repository)) {
                    $core->blog->settings->related->put('repository_path', $tmp_repository);
                    $repository = $tmp_repository;
                } else {
                    try {
                        files::makeDir($tmp_repository);
                        $core->blog->settings->dcCKEditorAddons->put('repository_path', $tmp_repository);
                        $repository = $tmp_repository;
                    } catch (Exception $e) {
                        throw new Exception(sprintf(
                            __('Directory "%s" for dcCKEditorAddons plugins repository needs to allow read and write access.'),
                            $tmp_repository
                        ));
                    }
                }
            }
            dcPage::addSuccessNotice(__('The configuration has been updated.'));
            http::redirect($p_url);
        } catch(Exception $e) {
            $core->error->add($e->getMessage());
        }
    } elseif ((!empty($_POST['upload_plugin']) && !empty($_FILES['plugin_file']))
              || (!empty($_POST['fetch_plugin']) && !empty($_POST['plugin_url']))) {

        if (empty($_POST['your_pwd']) || !$core->auth->checkPassword($core->auth->crypt($_POST['your_pwd']))) {
            dcPage::addErrorNotice(__('Password verification failed'));
        }

        if (!empty($_POST['upload_plugin'])) {
            try {
                if (!move_uploaded_file($_FILES['plugin_file']['tmp_name'], $dcckeditor_addons_repository_path.'/'.$_FILES['plugin_file']['name'])) {
                    throw new Exception(__('Unable to move uploaded file.'));
                }

                $ckeditor_addon = new dcCKEditorAddon();
                $ckeditor_addon->upload($dcckeditor_addons_repository_path.'/'.$_FILES['plugin_file']['name']);
                $ckeditor_addon->install();

                dcPage::addSuccessNotice(__('Addon has been uploaded.'));
            } catch (Exception $e) {
                dcPage::addErrorNotice($e->getMessage());
            }
        } else {
            $url = urldecode($_POST['plugin_url']);
            $dest = $dcckeditor_addons_repository_path.'/'.basename($url);
            try {
                $ckeditor_addon = new dcCKEditorAddon();
                $ckeditor_addon->download($url, $dest);
                $ckeditor_addon->install();

                dcPage::addSuccessNotice(__('Addon has been downloaded.'));
            } catch (Exception $e) {
                dcPage::addErrorNotice($e->getMessage());
            }
        }

        http::redirect($p_url.'#plugins');
    } elseif (!empty($_POST['activate_plugins']) && $dcckeditor_addons_was_actived) {
        if (!empty($_POST['plugins'])) {
            foreach ($_POST['plugins'] as $plugin) {
                $plugins[$plugin]['activated'] = true;
            }
        }
        if (!empty($_POST['buttons'])) {
            foreach ($_POST['buttons'] as $plugin => $button) {
                $plugins[$plugin]['button'] = $button;
            }
        }
        $core->blog->settings->dcCKEditorAddons->put('plugins', json_encode($plugins), 'string');
        dcPage::addSuccessNotice(__('The configuration has been updated.'));
        http::redirect($p_url);
    }
}

include(__DIR__.'/tpl/index.tpl');
