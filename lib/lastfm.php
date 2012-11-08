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
	public static $album_info_url = "/?method=album.getinfo&api_key=%s&artist=%s&album=%s";

	public function __construct($public_api_key){
		self::$api_key = $public_api_key;
		return $this;
	}

	public static function getAlbumInfo($artistName, $albumName){
		$url = sprintf(self::$root.self::$album_info_url,self::$api_key,urlencode(html_entity_decode($artistName)),urlencode(html_entity_decode($albumName)));
		$info = file_get_contents($url);
		if ($info == ""){
			throw new Exception("not found");
		}
		$xml = simplexml_load_string($info);
		return $info;
	}
}