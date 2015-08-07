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

$version = $core->plugins->moduleInfo('dcCKEditorAddons', 'version');
if (version_compare($core->getVersion('dcCKEditorAddons'), $version,'>=')) {
    return;
}

$settings = $core->blog->settings;
$settings->addNamespace('dcCKEditorAddons');

$settings->dcCKEditorAddons->put('active', false, 'boolean', 'dcCKEditorAddons plugin activated?', false, true);

$public_path = $core->blog->public_path;
$repository_path = $public_path.'/dcckeditor_addons';

if (is_dir($repository_path)) {
    if (!is_readable($repository_path) || !is_writable($repository_path)) {
        throw new Exception(sprintf(
            __('Directory "%s" for dcCKEditorAddons plugins repository needs to allow read and write access.'),
            $repository_path
        ));
    }
} else {
    try {
        files::makeDir($repository_path);
    } catch (Exception $e) {
        throw $e;
    }
}

if (!is_file($repository_path.'/.htaccess')) {
    try {
        file_put_contents($repository_path.'/.htaccess',"Deny from all\n");
    } catch (Exception $e) {}
}

$settings->dcCKEditorAddons->put('repository_path', $repository_path, 'string', 'dcCKEditorAddons plugins directory', false, true);

$core->setVersion('dcCKEditorAddons', $version);
return true;
