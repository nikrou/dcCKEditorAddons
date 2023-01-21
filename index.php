<?php
/*
 *  -- BEGIN LICENSE BLOCK ----------------------------------
 *
 *  This file is part of dcCKEditorAddons, a plugin for DotClear2.
 *
 *  Licensed under the GPL version 2.0 license.
 *  See LICENSE file or
 *  http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 *
 *  -- END LICENSE BLOCK ------------------------------------
 */

$_dc_plugin = $plugin;

$is_admin = dcCore::app()->auth->isSuperAdmin();

$dcckeditor_active = dcCore::app()->blog->settings->dcckeditor->active;

dcCore::app()->blog->settings->addNameSpace('dcCKEditorAddons');
$dcckeditor_addons_active = dcCore::app()->blog->settings->dcCKEditorAddons->active;
$dcckeditor_addons_was_actived = $dcckeditor_addons_active;
$dcckeditor_addons_check_validity = dcCore::app()->blog->settings->dcCKEditorAddons->check_validity;
$dcckeditor_addons_repository_path = dcCore::app()->blog->settings->dcCKEditorAddons->repository_path;
$dcckeditor_addons_plugins = json_decode(dcCore::app()->blog->settings->dcCKEditorAddons->plugins, true);

$plugins = [];
$name_pattern = "`CKEDITOR\.plugins\.add\(\s*'([^']*)'`";
$button_pattern = "`addButton\(\s*'([^']*)'`";
$require_pattern = "`requires\s*:\s*'([^']*)'`";

$plugins_actions_combo = [
    __('Activate') => 'activate',
    __('Deactivate') => 'deactivate',
    __('Delete') => 'delete',
];

$fmt_img = '<img alt="%1$s" title="%1$s" src="images/%2$s" />';
$img_plugin_status = [];
$img_plugin_status[true] = sprintf($fmt_img, __('Activated'), 'check-on.png');
$img_plugin_status[false] = sprintf($fmt_img, __('Deactivated'), 'check-off.png');

foreach ($dirs = glob($dcckeditor_addons_repository_path . '/*/plugin.js') as $plugin_js) {
    $plugin = ['name' => '', 'button' => '', 'path' => '', 'activated' => false];
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
    $plugin['path'] = basename(dirname($plugin_js));
    if (!empty($plugin['name'])) {
        if (!empty($dcckeditor_addons_plugins[$plugin['name']])) {
            $plugin['activated'] = $dcckeditor_addons_plugins[$plugin['name']]['activated'];
            if (!empty($dcckeditor_addons_plugins[$plugin['name']]['button'])) {
                $plugin['button'] = $dcckeditor_addons_plugins[$plugin['name']]['button'];
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
            dcCore::app()->blog->settings->dcCKEditorAddons->put('active', $dcckeditor_addons_active, 'boolean');

            // change other settings only if they were in html page
            if ($dcckeditor_addons_was_actived) {
                $dcckeditor_addons_check_validity = (empty($_POST['dcckeditor_addons_check_validity']))?false:true;
                dcCore::app()->blog->settings->dcCKEditorAddons->put('check_validity', $dcckeditor_addons_check_validity, 'boolean');

                if (empty($_POST['dcckeditor_addons_repository_path']) || trim($_POST['dcckeditor_addons_repository_path']) == '') {
                    $tmp_repository = dcCore::app()->blog->public_path . '/dcckeditor_addons';
                } else {
                    $tmp_repository = trim($_POST['dcckeditor_addons_repository_path']);
                }

                if (is_dir($tmp_repository) && is_writable($tmp_repository)) {
                    dcCore::app()->blog->settings->dcCKEditorAddons->put('repository_path', $tmp_repository);
                    $repository = $tmp_repository;
                } else {
                    try {
                        files::makeDir($tmp_repository);
                        dcCore::app()->blog->settings->dcCKEditorAddons->put('repository_path', $tmp_repository);
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
            http::redirect(dcCore::app()->admin->getPageURL());
        } catch(Exception $e) {
            dcCore::app()->error->add($e->getMessage());
        }
    } elseif ((!empty($_POST['upload_plugin']) && !empty($_FILES['plugin_file'])) || (!empty($_POST['fetch_plugin']) && !empty($_POST['plugin_url']))) {
        if (empty($_POST['your_pwd']) || !dcCore::app()->auth->checkPassword($_POST['your_pwd'])) {
            dcPage::addErrorNotice(__('Password verification failed'));
        }

        if (!empty($_POST['upload_plugin'])) {
            try {
                if (!move_uploaded_file($_FILES['plugin_file']['tmp_name'], $dcckeditor_addons_repository_path . '/' . $_FILES['plugin_file']['name'])) {
                    throw new Exception(__('Unable to move uploaded file.'));
                }

                $ckeditor_addon = new dcCKEditorAddon();
                $ckeditor_addon->upload($dcckeditor_addons_repository_path . '/' . $_FILES['plugin_file']['name']);
                $ckeditor_addon->install();

                dcPage::addSuccessNotice(__('Addon has been uploaded.'));
            } catch (Exception $e) {
                dcPage::addErrorNotice($e->getMessage());
            }
        } else {
            $url = urldecode($_POST['plugin_url']);
            $dest = $dcckeditor_addons_repository_path . '/' . basename($url);
            try {
                $ckeditor_addon = new dcCKEditorAddon();
                $ckeditor_addon->download($url, $dest);
                $ckeditor_addon->install();

                dcPage::addSuccessNotice(__('Addon has been downloaded.'));
            } catch (Exception $e) {
                dcPage::addErrorNotice($e->getMessage());
            }
        }

        http::redirect(dcCore::app()->admin->getPageURL() . '#plugins');
    } elseif (!empty($_POST['action']) && $dcckeditor_addons_was_actived && !empty($_POST['plugins'])) {
        if ($_POST['action'] === 'activate' || $_POST['action'] === 'deactivate') {
            foreach ($_POST['plugins'] as $plugin_name) {
                $plugins[$plugin_name]['activated'] = $_POST['action'] == 'activate';
            }

            if (!empty($_POST['buttons'])) {
                foreach ($_POST['buttons'] as $plugin_name => $button_name) {
                    $plugins[$plugin_name]['button'] = $button_name;
                }
            }

            dcCore::app()->blog->settings->dcCKEditorAddons->put('plugins', json_encode($plugins), 'string');
            if ($_POST['action'] == 'activate') {
                $verb = 'activated';
            } else {
                $verb = 'deactivated';
            }
            dcPage::addSuccessNotice(
                sprintf(
                    __('Selected addon has been ' . $verb . '.', 'Selected (%d) addons have been ' . $verb . '.', count($_POST['plugins'])),
                    count($_POST['plugins'])
                )
            );
            http::redirect(dcCore::app()->admin->getPageURL());
        } elseif ($_POST['action'] === 'delete') {
            try {
                foreach ($_POST['plugins'] as $plugin_name) {
                    if (!isset($dcckeditor_addons_plugins[$plugin_name])) {
                        continue;
                    }

                    if (!files::deltree($dcckeditor_addons_repository_path . '/' . $dcckeditor_addons_plugins[$plugin_name]['path'])) {
                        throw new Exception(sprintf(__('Cannot remove addon "%s" files'), $plugin_name));
                    }
                    unset($plugins[$plugin_name]);
                }
                dcPage::addSuccessNotice(
                    sprintf(
                        __('Selected addon has been deleted.', 'Selected (%d) addons have been deleted.', count($_POST['plugins'])),
                        count($_POST['plugins'])
                    )
                );
                dcCore::app()->blog->settings->dcCKEditorAddons->put('plugins', json_encode($plugins), 'string');
            } catch (Exception $e) {
                dcPage::addErrorNotice($e->getMessage());
            }
            http::redirect(dcCore::app()->admin->getPageURL());
        }
    }
}

include(__DIR__ . '/tpl/index.tpl');

$plugin = $_dc_plugin;
