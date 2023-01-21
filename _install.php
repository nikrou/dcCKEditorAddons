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

$version = dcCore::app()->plugins->moduleInfo('dcCKEditorAddons', 'version');
if (version_compare(dcCore::app()->getVersion('dcCKEditorAddons'), $version, '>=')) {
    return;
}

$settings = dcCore::app()->blog->settings;
$settings->addNamespace('dcCKEditorAddons');

$settings->dcCKEditorAddons->put('active', false, 'boolean', 'dcCKEditorAddons plugin activated?', false, true);
$settings->dcCKEditorAddons->put('check_validity', true, 'boolean', 'Check if zip file is a valid CKEditor addon?', false, true);
$settings->dcCKEditorAddons->put('plugins', '{}', 'string', 'dcCKEditorAddons activated plugins', false, true);

$public_path = dcCore::app()->blog->public_path;
$repository_path = $public_path . '/dcckeditor_addons';

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

$settings->dcCKEditorAddons->put('repository_path', $repository_path, 'string', 'dcCKEditorAddons plugins directory', false, true);

dcCore::app()->setVersion('dcCKEditorAddons', $version);
return true;
