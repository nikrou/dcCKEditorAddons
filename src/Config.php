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
use Dotclear\Core\Process;
use Dotclear\Helper\File\Files;
use dcCore;
use form;

class Config extends Process
{
    private static FormModel $formModel;

    public static function init(): bool
    {
        return self::status(My::checkContext(My::CONFIG));
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        try {
            $already_active = My::settings()->active;

            self::$formModel = new FormModel();
            self::$formModel->setIsAdmin(dcCore::app()->auth->isSuperAdmin());
            self::$formModel->setActive(My::settings()->active);
            self::$formModel->setCheckValidity(My::settings()->check_validity);
            self::$formModel->setRepositoryPath(My::settings()->repository_path);
            self::$formModel->setPlugins(json_decode(My::settings()->plugins, true) ?? []);

            if (empty($_POST['save'])) {
                return true;
            }

            self::$formModel->setActive(!empty($_POST['dcckeditor_addons_active']));
            My::settings()->put('active', self::$formModel->isActive(), 'boolean');

            // change other settings only if they were in html page
            if ($already_active) {
                self::$formModel->setCheckValidity(!empty($_POST['dcckeditor_addons_check_validity']));
                My::settings()->put('check_validity', self::$formModel->getCheckValidity(), 'boolean');

                if (empty($_POST['dcckeditor_addons_repository_path']) || trim($_POST['dcckeditor_addons_repository_path']) === '') {
                    $tmp_repository = dcCore::app()->blog->public_path . '/dcckeditor_addons';
                } else {
                    $tmp_repository = trim($_POST['dcckeditor_addons_repository_path']);
                }

                if (is_dir($tmp_repository) && is_writable($tmp_repository)) {
                    self::$formModel->setRepositoryPath($tmp_repository);
                    My::settings()->put('repository_path', $tmp_repository);
                } else {
                    try {
                        files::makeDir($tmp_repository);
                        self::$formModel->setRepositoryPath($tmp_repository);
                        My::settings()->put('repository_path', $tmp_repository);
                    } catch (\Exception $e) {
                        throw new \Exception(sprintf(
                            __('Directory "%s" for dcCKEditorAddons plugins repository needs to allow read and write access.'),
                            $tmp_repository
                        ));
                    }
                }
            }
            Notices::addSuccessNotice(__('The configuration has been updated.'));
            dcCore::app()->admin->url->redirect('admin.plugins', [
                'module' => My::id(),
                'conf' => '1'
            ]);
        } catch(\Exception $e) {
            dcCore::app()->error->add($e->getMessage());
        }

        return true;
    }

    public static function render(): void
    {
        if (!self::status()) {
            return;
        }

        if (dcCore::app()->auth->isSuperAdmin()) {
            echo '<div class="fieldset">';
            echo '<h3>', __('Plugin activation'), '</h3>';
            echo '<p>';
            echo '<label class="classic" for="dcckeditor_addons_active">';
            echo form::checkbox('dcckeditor_addons_active', 1, self::$formModel->isActive());
            echo __('Enable dcCKEditorAddons plugin');
            echo '</label>';
            echo '</p>';
            echo '</div>';

            if (self::$formModel->isActive()) {
                echo '<div class="fieldset">';
                echo '<h3>', __('Options'), '</h3>';
                echo '<p>';
                echo '<label class="classic" for="dcckeditor_addons_check_validity">';
                echo form::checkbox('dcckeditor_addons_check_validity', 1, self::$formModel->getCheckValidity());
                echo __('Check if zip file is a valid CKEditor addon?');
                echo '</label>';
                echo '</p>';
                echo '<p class="form-note">';
                echo __('If test for a valid CKEditor addon failed and you can not add the addon, then uncheck that option or unzip the CKEditor addon manually');
                echo '</p>';
                echo '<p>';
                echo '<label for="repository" class="classic">', __('Repository path :'), ' ';
                echo form::field('dcckeditor_addons_repository_path', 80, 255, self::$formModel->getRepositoryPath());
                echo '</label>';
                echo '</p>';
                echo '</div>';
            }
        }
    }
}
