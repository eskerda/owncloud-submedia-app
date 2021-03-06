<?php

namespace OCA\Submedia;

/**
 * ownCloud - Media Playlists
 *
 * @author Lluís Esquerda
 * @copyright 2012 Blue-Systems contact@blue-systems.com
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

class LastFM {

    public $api_key = '';
    public $root = 'http://ws.audioscrobbler.com/2.0/';
    public $album_info_url = '?method=album.getinfo&api_key=%s&artist=%s&album=%s';

    public function __construct($public_api_key) {
        $this->api_key = $public_api_key;
    }

    public function getAlbumInfo($artistName, $albumName) {
        $url = sprintf(
            $this->root . $this->album_info_url,
            $this->api_key,
            urlencode($artistName),
            urlencode($albumName)
        );

        $info = file_get_contents($url);

        if ($info == "") {
            throw new \Exception('not found');
        }
        //$xml = simplexml_load_string($info);
        return $info;
    }

    public function getCoverArt($artistName, $albumName) {
        $simpleFileCache = new Simple_File_Cache();

        $file = $simpleFileCache->getFilePath($artistName . $albumName);

        if ($file) {
            return $file;
        }

        if ($simpleFileCache->isBlackListed($artistName . $albumName)) {
            return false;
        }

        try {
            $album_info = $this->getAlbumInfo($artistName, $albumName);
            $xml = simplexml_load_string($album_info);
            $image_url = (string)$xml->album->image[3];
        } catch (\Exception $e) {
            $image_url = false;
        }
        if (!$image_url) {
            $simpleFileCache->blackList($artistName . $albumName);
            return false;
        }
        return $simpleFileCache->putFile($artistName . $albumName, $image_url);
    }
}

class Simple_File_Cache {

    protected $cache = '/oc_submedia/cache/';
    protected $blacklist = '/oc_submedia/cache/blacklisted/';

    public function __construct($tmp_path = null) {
        if (!$tmp_path) {
            $tmp_path = sys_get_temp_dir();
        }

        $this->cache = $tmp_path . $this->cache;
        $this->blacklist = $tmp_path . $this->blacklist;

        if (!is_dir($this->cache)) {
            mkdir($this->cache, 0777, true);
        }
        if (!is_dir($this->blacklist)) {
            mkdir($this->blacklist, 0777, true);
        }
    }

    public function getFilePath($key) {
        if (is_file($this->cache . md5($key))) {
            return $this->cache . md5($key);
        }
        return false;
    }

    public function putFile($key, $path) {
        if (copy($path, $this->cache . md5($key))) {
            return $this->getFilePath($key);
        }
        return false;
    }

    public function blackList($key) {
        return touch($this->blacklist . md5($key));
    }

    public function isBlackListed($key) {
        return is_file($this->blacklist . md5($key));
    }
}
