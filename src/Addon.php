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

use Dotclear\Helper\File\Files;
use Dotclear\Helper\File\Zip\Unzip;
use Dotclear\Helper\Network\HttpClient;

class Addon
{
    private $zip_file = '';

    public function upload($zip_file)
    {
        $this->zip_file = $zip_file;
    }

    public function download($url, $zip_file)
    {
        $this->zip_file = $zip_file;

        // Check and add default protocol if necessary
        if (!preg_match('%^http[s]?:\/\/%', $url)) {
            $url = 'https://' . $url;
        }
        // Download package
        if ($client = HttpClient::initClient($url, $path)) {
            try {
                $client->setUserAgent('DotClear.org CKEditorBrowser/0.1');
                $client->useGzip(false);
                $client->setPersistReferers(false);
                $client->setOutput($this->zip_file);
                $client->get($path);
                unset($client);
            } catch (\Exception $e) {
                unset($client);
                throw new \Exception(__('An error occurred while downloading the file.'));
            }
        } else {
            throw new \Exception(__('An error occurred while downloading the file.'));
        }
    }

    public function install()
    {
        $zip = new Unzip($this->zip_file);
        if ($zip->isEmpty()) {
            $zip->close();
            unlink($this->zip_file);
            throw new \Exception(__('Empty plugin zip file.'));
        }

        $zip_root_dir = $zip->getRootDir();
        if (!$zip_root_dir) {
            // try to find a root anyway if all dirs start with same pattern
            $dirs = $zip->getDirsList();
            $n = 0;
            $zip_root_dir = substr($dirs[0], 0, strpos($dirs[0], '/'));
            foreach ($dirs as $dir) {
                if ($zip_root_dir != substr($dirs[0], 0, strpos($dirs[0], '/'))) {
                    $n++;
                }
            }
            if ($n > 0) {
                $zip_root_dir = false;
            }
        }

        if ($zip_root_dir !== false) {
            $target = dirname($this->zip_file);
            $destination = $target . '/' . $zip_root_dir;
            $plugin_js = $zip_root_dir . '/plugin.js';
            $has_plugin_js = $zip->hasFile($plugin_js);
        } else {
            $target = dirname($this->zip_file) . '/' . preg_replace('/\.([^.]+)$/', '', basename($this->zip_file));
            $destination = $target;
            $plugin_js = 'plugin.js';
            $has_plugin_js = $zip->hasFile($plugin_js);
        }

        if (My::settings()->check_validity) {
            if (!$has_plugin_js) {
                $zip->close();
                unlink($this->zip_file);
                throw new \Exception(__('The zip file does not appear to be a valid CKEditor addon.'));
            }
        }

        if (!is_dir($destination)) {
            Files::makeDir($destination, true);
        }

        $zip->unzipAll($target);
        $zip->close();
        unlink($this->zip_file);
    }
}
