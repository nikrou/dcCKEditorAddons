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

Clearbricks::lib()->autoload(
    [
        'dcCKEditorAddon' => __DIR__ . '/inc/class.dcckeditor_addon.php',
        'dcCKEditorAddonsBehaviors' => __DIR__ . '/inc/class.dcckeditor_addons.behaviors.php'
    ]
);
