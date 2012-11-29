<?php

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

class OC_Media_LastFM{
	public static $api_key = "";
	public static $root = "http://ws.audioscrobbler.com/2.0/";
	public static $album_info_url = "?method=album.getinfo&api_key=%s&artist=%s&album=%s";

	public function __construct($public_api_key){
		self::$api_key = $public_api_key;
		return $this;
	}

	public static function getAlbumInfo($artistName, $albumName){
		$url = sprintf(self::$root.self::$album_info_url,
			self::$api_key,
			urlencode($artistName),
			urlencode($albumName)
		);

		$info = file_get_contents($url);
		
		if ($info == ""){
			throw new Exception("not found");
		}
		$xml = simplexml_load_string($info);
		return $info;
	}

	public static function getCoverArt($artistName, $albumName){
		$simpleFileCache = new Simple_File_Cache();

		$file = $simpleFileCache::getFilePath($artistName.$albumName);

		if ($file)
			return $file;

		if ($simpleFileCache::isBlackListed($artistName.$albumName))
			return false;

		try{
			$album_info = self::getAlbumInfo($artistName, $albumName);
			$xml = simplexml_load_string($album_info);
        	$image_url = (string)$xml->album->image[3];
        } catch (Exception $e){
        	$image_url = false;
        }
        if (!$image_url){
        	$simpleFileCache::blackList($artistName.$albumName);
        	return false;
        } else {
        	return $simpleFileCache::putFile($artistName.$albumName, $image_url);
        }
	}
}

class Simple_File_Cache{
	protected static $cache = '/oc_submedia/cache/';
	protected static $blacklist = '/oc_submedia/cache/blacklisted/';

	public function __construct($tmp_path = false){
		if (!$tmp_path)
			$tmp_path = sys_get_temp_dir();
		
		self::$cache = $tmp_path.self::$cache;
		self::$blacklist = $tmp_path.self::$blacklist;

		if (!is_dir(self::$cache))
			mkdir(self::$cache, 0777, true);

		if (!is_dir(self::$blacklist))
			mkdir(self::$blacklist, 0777, true);

		return $this;
	}

	public static function getFilePath($key){
		if (file_exists(self::$cache.md5($key)))
			return self::$cache.md5($key);
		else
			return false;
	}

	public static function putFile($key, $path){
		if (copy($path, self::$cache.md5($key))){
			return self::getFilePath($key);
		} else {
			return false;
		}
	}

	public static function blackList($key){
		return touch(self::$blacklist.md5($key));
	}

	public static function isBlackListed($key){
		return file_exists(self::$blacklist.md5($key));
	}
}