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

declare(strict_types=1);

namespace Dotclear\Plugin\dcCKEditorAddons;

use Dotclear\Core\Backend\Notices;
use Dotclear\Core\Backend\Page;
use Dotclear\Core\Process;
use Dotclear\Helper\File\Files;
use Dotclear\Helper\Html\Html;
use dcCore;
use form;

class Manage extends Process
{
    private static $active_tab = 'addons';

    private static FormModel $formModel;

    public static function init(): bool
    {
        if (!self::status(My::checkContext(My::MANAGE))) {
            return false;
        }

        return self::status(true);
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        self::$formModel = new FormModel();
        self::$formModel->setIsAdmin(dcCore::app()->auth->isSuperAdmin());
        self::$formModel->setActive(My::settings()->active);
        self::$formModel->setCheckValidity(My::settings()->check_validity);
        self::$formModel->setRepositoryPath(My::settings()->repository_path);
        self::$formModel->setPlugins(json_decode(My::settings()->plugins, true) ?? []);

        $plugins = [];
        $name_pattern = "`CKEDITOR\.plugins\.add\(\s*'([^']*)'`";
        $button_pattern = "`addButton\(\s*'([^']*)'`";
        $require_pattern = "`requires\s*:\s*'([^']*)'`";

        $fmt_img = '<img alt="%1$s" title="%1$s" src="images/%2$s" />';
        $img_plugin_status = [];
        $img_plugin_status[true] = sprintf($fmt_img, __('Activated'), 'check-on.png');
        $img_plugin_status[false] = sprintf($fmt_img, __('Deactivated'), 'check-off.png');

        foreach (glob(My::settings()->repository_path . '/*/plugin.js') as $plugin_js) {
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
                if (!empty(self::$formModel->getPlugins()[$plugin['name']])) {
                    $plugin['activated'] = self::$formModel->getPlugins()[$plugin['name']]['activated'];
                    if (!empty(self::$formModel->getPlugins()[$plugin['name']]['button'])) {
                        $plugin['button'] = self::$formModel->getPlugins()[$plugin['name']]['button'];
                    }
                }
                $plugins[$plugin['name']] = $plugin;
            }
        }
        self::$formModel->setPlugins($plugins);

        if ((!empty($_POST['upload_plugin']) && !empty($_FILES['plugin_file'])) || (!empty($_POST['fetch_plugin']) && !empty($_POST['plugin_url']))) {
            if (empty($_POST['your_pwd']) || !dcCore::app()->auth->checkPassword($_POST['your_pwd'])) {
                Notices::addErrorNotice(__('Password verification failed'));
            }

            if (!empty($_POST['upload_plugin'])) {
                try {
                    if (!move_uploaded_file($_FILES['plugin_file']['tmp_name'], self::$formModel->getRepositoryPath() . '/' . $_FILES['plugin_file']['name'])) {
                        throw new \Exception(__('Unable to move uploaded file.'));
                    }

                    $ckeditor_addon = new Addon();
                    $ckeditor_addon->upload(self::$formModel->getRepositoryPath() . '/' . $_FILES['plugin_file']['name']);
                    $ckeditor_addon->install();

                    Notices::addSuccessNotice(__('Addon has been uploaded.'));
                } catch (\Exception $e) {
                    Notices::addErrorNotice($e->getMessage());
                }
            } else {
                $url = urldecode($_POST['plugin_url']);
                $dest = self::$formModel->getRepositoryPath() . '/' . basename($url);
                try {
                    $ckeditor_addon = new Addon();
                    $ckeditor_addon->download($url, $dest);
                    $ckeditor_addon->install();

                    Notices::addSuccessNotice(__('Addon has been downloaded.'));
                } catch (\Exception $e) {
                    Notices::addErrorNotice($e->getMessage());
                }
            }

            My::redirect();
        } elseif (!empty($_POST['action']) && My::settings()->active && !empty($_POST['plugins'])) {
            if ($_POST['action'] === 'activate' || $_POST['action'] === 'deactivate') {
                foreach ($_POST['plugins'] as $plugin_name) {
                    $plugins[$plugin_name]['activated'] = $_POST['action'] === 'activate';
                }

                if (!empty($_POST['buttons'])) {
                    foreach ($_POST['buttons'] as $plugin_name => $button_name) {
                        $plugins[$plugin_name]['button'] = $button_name;
                    }
                }

                self::$formModel->setPlugins($plugins);
                My::settings()->put('plugins', json_encode($plugins), 'string');
                if ($_POST['action'] === 'activate') {
                    $verb = 'activated';
                } else {
                    $verb = 'deactivated';
                }
                Notices::addSuccessNotice(
                    sprintf(
                        __('Selected addon has been ' . $verb . '.', 'Selected (%d) addons have been ' . $verb . '.', count($_POST['plugins'])),
                        count($_POST['plugins'])
                    )
                );
                My::redirect();
            } elseif ($_POST['action'] === 'delete') {
                try {
                    foreach ($_POST['plugins'] as $plugin_name) {
                        if (!isset(self::$formModel->getPlugins()[$plugin_name])) {
                            continue;
                        }

                        if (!Files::deltree(self::$formModel->getRepositoryPath() . '/' . self::$formModel->getPlugins()[$plugin_name]['path'])) {
                            throw new \Exception(sprintf(__('Cannot remove addon "%s" files'), $plugin_name));
                        }
                        unset($plugins[$plugin_name]);
                    }
                    self::$formModel->setPlugins($plugins);
                    My::settings()->put('plugins', json_encode($plugins), 'string');
                    Notices::addSuccessNotice(
                        sprintf(
                            __('Selected addon has been deleted.', 'Selected (%d) addons have been deleted.', count($_POST['plugins'])),
                            count($_POST['plugins'])
                        )
                    );
                    My::redirect();
                } catch (\Exception $e) {
                    Notices::addErrorNotice($e->getMessage());
                }
                My::redirect();
            }
        }

        return true;
    }

    public static function render(): void
    {
        if (!self::status()) {
            return;
        }

        $fmt_img = '<img alt="%1$s" title="%1$s" src="images/%2$s" />';
        $img_plugin_status = [];
        $img_plugin_status[true] = sprintf($fmt_img, __('Activated'), 'check-on.png');
        $img_plugin_status[false] = sprintf($fmt_img, __('Deactivated'), 'check-off.png');

        $plugins_actions_combo = [
            __('Activate') => 'activate',
            __('Deactivate') => 'deactivate',
            __('Delete') => 'delete',
        ];

        Page::openModule(
            __('dcCKEditorAddons'),
            Page::jsPageTabs(self::$active_tab)
        );

        echo Page::breadcrumb([Html::escapeHTML(dcCore::app()->blog->name) => '',
            '<a href="' . My::manageUrl() . '">' . __('dcCKEditorAddons') . '</a>' => ''
        ]);

        echo Notices::getNotices();

        echo '<div class="multi-part" id="plugins" title="', __('Extensions'), '">';
        echo '<h3 class="hidden-if-js">',__('Plugins'), '</h3>';
        echo '<p class="top-add">';
        echo '<a class="button add" href="', My::manageUrl(), '#add-plugin">', __('Add a plugin'), '</a>';
        echo '</p>';

        if (count(self::$formModel->getPlugins()) > 0) {
            echo '<form method="post" action="', My::manageUrl(), '#plugins" enctype="multipart/form-data" name="plugins-list" id="plugins-form">';
            echo '<div class="table-outer ckeditor-addons" style="margin-top: 2em">';
            echo '<table>';
            echo '<thead>';
            echo '<th>', __('Name'), '</th>';
            echo '<th>', __('Button'), '</th>';
            echo '<th>', __('Dependencies'), '</th>';
            echo '<th>', __('Activated?'), '</th>';
            echo '</thead>';
            echo '<tbody>';
            foreach (self::$formModel->getPlugins() as $plugin_name => $plugin) {
                echo '<tr>';
                echo '<td>';
                echo '<label class="classic">';
                echo '<input type="checkbox" name="plugins[]" value="', $plugin_name, '">';
                echo $plugin_name;
                echo '</label>';
                echo '</td>';
                echo '<td>';
                echo form::field(['buttons[' . $plugin['name'] . ']'], 80, 255, $plugin['button']);
                echo '</td>';
                echo '<td>';
                if (!empty($plugin['dependencies'])) {
                    echo $plugin['dependencies'];
                }
                echo '</td>';
                echo '<td>';
                echo $img_plugin_status[$plugin['activated']];
                echo '</td>';
                echo '</tr>';
            }
            echo '</tbody>';
            echo '</table>';
            echo '<div class="two-cols">';
            echo '<p class="col checkboxes-helpers"></p>';
            echo '<p class="col right">';
            echo '<label for="action" class="classic">', __('Selected addons action:'), '</label>';
            echo form::combo('action', $plugins_actions_combo);
            echo dcCore::app()->formNonce();
            echo '<input type="submit" value="', __('ok'), '" />';
            echo '</p>';
            echo '</div>';
            echo '</div>';
            echo '</form>';
        }

        echo '</div>';

        echo '<div class="multi-part" id="add-plugin" title="', __('Add a plugin'), '">';
        echo '<p>', __('You can install plugins by uploading or downloading zip files.'), '</p>';

        echo '<div class="fieldset">';
        echo '<form method="post" action="', My::manageUrl(), '#plugins" enctype="multipart/form-data" name="upload-plugin">';
        echo '<h4>', __('Upload a zip file'), '</h4>';
        echo '<p class="field">';
        echo '<label for="plugin_file" class="classic required">';
        echo '<abbr title="', __('Required field'), '">*</abbr> ', __('Zip file path:');
        echo '</label>';
        echo '<input type="file" name="plugin_file" id="plugin_file">';
        echo '</p>';
        echo '<p class="field">';
        echo '<label for="passwd1" class="classic required">';
        echo '<abbr title="', __('Required field'), '">*</abbr> ', __('Your password:');
        echo '</label>';
        echo '<input type="password" name="your_pwd" id="passwd1" value="">';
        echo '</p>';
        echo '<p>';
        echo '<input type="submit" name="upload_plugin" value="', __('Upload'), '"/>';
        echo dcCore::app()->formNonce();
        echo '</p>';
        echo '</form>';
        echo '</div>';

        echo '<div class="fieldset">';
        echo '<form method="post" action="', My::manageUrl(), '#plugins" enctype="multipart/form-data" name="download-plugin">';
        echo '<h4>', __('Download a zip file'), '</h4>';
        echo '<p class="field">';
        echo '<label for="plugin_url" class="classic required">';
        echo '<abbr title="', __('Required field'), '">*</abbr> ', __('Zip file URL:');
        echo '</label>';
        echo '<input type="text" name="plugin_url" id="plugin_url" value="">';
        echo '</p>';
        echo '<p class="field">';
        echo '<label for="passwd2" class="classic required">';
        echo '<abbr title="', __('Required field'), '">*</abbr> ', __('Your password:');
        echo '</label>';
        echo '<input type="password" name="your_pwd" id="passwd2" value="">';
        echo '<p>';
        echo '<input type="submit" name="fetch_plugin" value="', __('Download'), '"/>';
        echo dcCore::app()->formNonce();
        echo '</p>';
        echo '</form>';
        echo '</div>';

        echo '</div>';

        Page::helpBlock(My::id());
        Page::closeModule();
    }
}
