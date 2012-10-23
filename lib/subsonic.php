<?php

/**
* ownCloud - SubMedia plugin
*
* @author Lluis Esquerda
* @copyright 2012 Blue Systems contact@blue-systems.com
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
* You should have received a copy of the GNU Lesser General Public 
* License along with this library.  If not, see <http://www.gnu.org/licenses/>.
* 
*/

/***

http://www.subsonic.org/pages/api.jsp

Param 	Required 	Default 	Comment
u		Yes 					The username.
p		Yes						The password, either in clear text or 
								hex-encoded with a "enc:" prefix.
v		Yes						The protocol version implemented by the 
								client, i.e., the version of the 
								subsonic-rest-api.xsd schema used (see below).
c		Yes						A unique string identifying the client 
								application.
f		No			xml			Request data to be returned in this format. 
								Supported values are "xml", "json" (since 1.4.0) 
								and "jsonp" (since 1.6.0). If using jsonp,
								specify name of javascript callback function 
								using a callback parameter.
Code	Description
0	A generic error.
10	Required parameter is missing.
20	Incompatible Subsonic REST protocol version. Client must upgrade.
30	Incompatible Subsonic REST protocol version. Server must upgrade.
40	Wrong username or password.
50	User is not authorized for the given operation.
60	The trial period for the Subsonic server is over. Please donate to get 
	a license key. Visit subsonic.org for details.
70	The requested data was not found.

***/

class OC_MEDIA_SUBSONIC{

	var $user = false;
	var $version = false;
	var $client = false;
	var $format = 'xml';

	public static $formatWhiteList = array(
		'xml','json','jsonp'
	);

	public function __construct($params){
		$username = (isset($params['u']))?$params['u']:false;
		$password = (isset($params['p']))?$params['p']:false;
		$version = (isset($params['v']))?$params['v']:false;
		$client = (isset($params['c']))?$params['c']:false;
		$format = (isset($params['f']))?$params['f']:'xml';
		
		if (!$username || !$password || !$version || !$client){
			throw new Exception("Required string parameter not present", 10);
		}

		if (!in_array($format, OC_MEDIA_SUBSONIC::$formatWhiteList)){
			throw new Exception("Format not allowed", 10);
		}

		if($this->checkAuth($username, $password)){
			$this->user = $username;
			$this->version = $version;
			$this->client = $client;
			$this->format = $format;
			return $this;	
		} else {
			throw new Exception("Wrong username or password", 40);
		}
	}

	private function checkAuth($user, $password){

		// Password may be clear or hex encoded (with enc: prefix)
		if (substr($password,0,4)=="enc:"){
			$password = PREG_REPLACE(
				"'([\S,\d]{2})'e","chr(hexdec('\\1'))",substr($password,4)
			);
		}
		$password = hash('sha256', $password);
		$query=OCP\DB::prepare("SELECT user_id, user_password_sha256 from *PREFIX*media_users WHERE user_id=?");
		$users=$query->execute(array($user))->fetchAll();
		if (count($users)>0){
			$auth = $users[0]['user_password_sha256'];
			return $auth == $password;
		}
		return false;
	}

	public static function getFormat($params){
		$format = (isset($params['f']))?$params['f']:'xml';
		if (!in_array($format, OC_MEDIA_SUBSONIC::$formatWhiteList)){
			$format = 'xml';
		}
		return $format;
	}
}