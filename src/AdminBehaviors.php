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

use dcCore;

class AdminBehaviors
{
    public static function ckeditorExtraPlugins(\ArrayObject $extraPlugins, $context)
    {
        if (!My::settings()->active) {
            return;
        }

        $plugin_base_url = dcCore::app()->blog->host . dcCore::app()->blog->settings->system->public_url . '/' . basename(My::settings()->repository_path) . '/%s/plugin.js';
        $plugins = json_decode(My::settings()->plugins, true);
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
