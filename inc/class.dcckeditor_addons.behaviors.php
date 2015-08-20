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

class dcCKEditorAddonsBehaviors
{
    public static function ckeditorExtraPlugins(ArrayObject $extraPlugins, $context) {
        global $core;

        $self_ns = $core->blog->settings->addNamespace('dcCKEditorAddons');
        if (!$self_ns->active) {
            return;
        }

        $plugin_base_url = $core->blog->host.$core->blog->settings->system->public_url.'/'.basename($self_ns->repository_path).'/%s/plugin.js';
        $plugins = json_decode($self_ns->plugins, true);
        if (!empty($plugins)) {
            foreach ($plugins as $name => $plugin) {
                if ($plugin['activated']) {
                    $extraPlugins[] = array(
                        'name' => $name,
                        'button' => !empty($plugin['button'])?$plugin['button']:$name,
                        'url' => sprintf($plugin_base_url, $name)
                    );
                }
            }
        }
    }
}