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

$_menu['Plugins']->addItem(
    'dcCKEditorAddons',
    $core->adminurl->get('admin.plugin.dcCKEditorAddons'),
    dcPage::getPF('dcCKEditorAddons/imgs/icon.png'),
    preg_match('/'.preg_quote($core->adminurl->get('admin.plugin.dcCKEditorAddons')).'(&.*)?$/', $_SERVER['REQUEST_URI']),
    $core->auth->check('admin,contentadmin', $core->blog->id)
);

$core->addBehavior('ckeditorExtraPlugins', array('dcCKEditorAddonsBehaviors', 'ckeditorExtraPlugins'));
