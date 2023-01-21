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

dcCore::app()->menu[dcAdmin::MENU_PLUGINS]->addItem(
    'dcCKEditorAddons',
    dcCore::app()->adminurl->get('admin.plugin.dcCKEditorAddons'),
    dcPage::getPF('dcCKEditorAddons/imgs/icon.png'),
    preg_match('/' . preg_quote(dcCore::app()->adminurl->get('admin.plugin.dcCKEditorAddons')) . '(&.*)?$/', $_SERVER['REQUEST_URI']),
    dcCore::app()->auth->check(dcCore::app()->auth->makePermissions([dcAuth::PERMISSION_ADMIN, dcAuth::PERMISSION_CONTENT_ADMIN]), dcCore::app()->blog->id)
);

dcCore::app()->addBehavior('ckeditorExtraPlugins', ['dcCKEditorAddonsBehaviors', 'ckeditorExtraPlugins']);
