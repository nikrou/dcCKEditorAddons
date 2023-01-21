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

class dcCKEditorAddonsBehaviors
{
    public static function ckeditorExtraPlugins(ArrayObject $extraPlugins, $context)
    {
        $self_ns = dcCore::app()->blog->settings->addNamespace('dcCKEditorAddons');
        if (!$self_ns->active) {
            return;
        }

        $plugin_base_url = dcCore::app()->blog->host . dcCore::app()->blog->settings->system->public_url . '/' . basename($self_ns->repository_path) . '/%s/plugin.js';
        $plugins = json_decode($self_ns->plugins, true);
        if (!empty($plugins)) {
            foreach ($plugins as $name => $plugin) {
                if ($plugin['activated']) {
                    $extraPlugins[] = [
                        'name' => $name,
                        'button' => !empty($plugin['button'])?$plugin['button']:$name,
                        'url' => sprintf($plugin_base_url, $plugin['path'])
                    ];
                }
            }
        }
    }
}
