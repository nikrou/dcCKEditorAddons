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
$dcckeditor_addons_repository_path = $core->blog->settings->dcCKEditorAddons->repository_path;

if (!$dcckeditor_active) {
    dcPage::addErrorNotice(__('You must enable dcCKEditor plugin to use that plugin.'));
} else {
    if (!empty($_POST['saveconfig'])) {
        try {
            $dcckeditor_addons_active = (empty($_POST['dcckeditor_addons_active']))?false:true;
            $core->blog->settings->dcCKEditorAddons->put('active', $dcckeditor_addons_active, 'boolean');

            // change other settings only if they were in html page
            if ($dcckeditor_addons_was_actived) {
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
    }
}

include(dirname(__FILE__).'/tpl/index.tpl');
