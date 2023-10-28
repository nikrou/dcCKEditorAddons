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

$this->registerModule(
    "dcCKEditorAddons", // Name
    "Add CKEditor plugins easily to your blog", // Description
    "Nicolas Roudaire", // Author
    '1.1.0', // Version
    [
        'permissions' => dcCore::app()->auth->makePermissions([dcAuth::PERMISSION_CONTENT_ADMIN, initPages::PERMISSION_PAGES]),
        'type' => 'plugin',
        'dc_min' => '2.27',
        'requires' => [['core', '2.27']],
        'repository' => 'https://github.com/nikrou/dcCKEditorAddons',
        'support' => 'https://forum.dotclear.org/viewtopic.php?id=48338',
        'details' => 'https://plugins.dotaddict.org/dc2/details/dcCKEditorAddons'
    ]
);
