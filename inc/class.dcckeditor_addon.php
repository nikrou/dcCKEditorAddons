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

class dcCKEditorAddon
{
    private $zip_file = '';

    public function __construct() {
    }

    public function upload($zip_file) {
        $this->zip_file = $zip_file;
    }

    public function download($url, $zip_file) {
        $this->zip_file = $zip_file;

		// Check and add default protocol if necessary
		if (!preg_match('%^http[s]?:\/\/%',$url)) {
			$url = 'http://'.$url;
		}
		// Download package
		if ($client = netHttp::initClient($url, $path)) {
			try {
				$client->setUserAgent('DotClear.org CKEditorBrowser/0.1');
				$client->useGzip(false);
				$client->setPersistReferers(false);
				$client->setOutput($this->zip_file);
				$client->get($path);
				unset($client);
			} catch (Exception $e) {
				unset($client);
				throw new Exception(__('An error occurred while downloading the file.'));
			}
		} else {
			throw new Exception(__('An error occurred while downloading the file.'));
		}
	}

    public function install() {
        global $core;

        $zip = new fileUnzip($this->zip_file);
        if ($zip->isEmpty()) {
            $zip->close();
            unlink($this->zip_file);
            throw new Exception(__('Empty plugin zip file.'));
        }

        $zip_root_dir = $zip->getRootDir();
        if (!$zip_root_dir) {
            // try to find a root anyway if all dirs start with same pattern
            $dirs = $zip->getDirsList();
            $n = 0;
            $zip_root_dir = substr($dirs[0],0,strpos($dirs[0], '/'));
            foreach ($dirs as $dir) {
                if ($zip_root_dir != substr($dirs[0],0,strpos($dirs[0], '/'))) {
                    $n++;
                }
            }
            if ($n>0) {
                $zip_root_dir = false;
            }
        }

        if ($zip_root_dir != false) {
            $target = dirname($this->zip_file);
            $destination = $target.'/'.$zip_root_dir;
			$plugin_js = $zip_root_dir.'/plugin.js';
			$has_plugin_js = $zip->hasFile($plugin_js);
        } else {
            $target = dirname($this->zip_file).'/'.preg_replace('/\.([^.]+)$/','',basename($this->zip_file));
            $destination = $target;
            $plugin_js = 'plugin.js';
			$has_plugin_js = $zip->hasFile($plugin_js);
        }

        if ($core->blog->settings->dcCKEditorAddons->check_validity) {
            if (!$has_plugin_js) {
                $zip->close();
                unlink($this->zip_file);
                throw new Exception(__('The zip file does not appear to be a valid CKEditor addon.'));
            }
        }

        if (!is_dir($destination)) {
            files::makeDir($destination, true);
		}

		$zip->unzipAll($target);
		$zip->close();
		unlink($this->zip_file);
    }
}