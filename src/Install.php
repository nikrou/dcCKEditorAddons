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

use Dotclear\Core\Process;
use Dotclear\Helper\File\Files;
use dcCore;

class Install extends Process
{
    public static function init(): bool
    {
        return self::status(My::checkContext(My::INSTALL));
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        My::settings()->put('active', false, 'boolean', 'dcCKEditorAddons plugin activated?', false, true);
        My::settings()->put('check_validity', true, 'boolean', 'Check if zip file is a valid CKEditor addon?', false, true);
        My::settings()->put('plugins', '{}', 'string', 'dcCKEditorAddons activated plugins', false, true);

        $public_path = dcCore::app()->blog->public_path;
        $repository_path = $public_path . '/dcckeditor_addons';

        if (dcCore::app()->getVersion(My::id()) == null) {
            if (is_dir($repository_path)) {
                if (!is_readable($repository_path) || !is_writable($repository_path)) {
                    throw new \Exception(sprintf(
                        __('Directory "%s" for dcCKEditorAddons plugins repository needs to allow read and write access.'),
                        $repository_path
                    ));
                }
            } else {
                try {
                    Files::makeDir($repository_path);
                } catch (\Exception $e) {
                    throw $e;
                }
            }
        }

        My::settings()->put('repository_path', $repository_path, 'string', 'dcCKEditorAddons plugins directory', false, true);

        return true;
    }
}
