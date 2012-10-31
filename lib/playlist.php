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

/**
 * This class manages our playlists.
 */
class OC_Media_Playlist {
    /**
     * @brief Returns the list of playlists for a specific user.
     * @param string $uid
     * @return array or false.
     */
    public static function all($uid) {
    	$statement = OCP\DB::prepare(
    		'SELECT sp.`id`,sp.`name`, sp.`created`, '
    		. 	'(SELECT COUNT(*) FROM `*PREFIX*submedia_playlists_songs` sps'
    		.	' WHERE sps.`playlist_id` = sp.`id`) as n_songs'
    		. ' FROM `*PREFIX*submedia_playlists` sp'
    		. ' WHERE userid = :user'
    	);
    	$results = $statement->execute(array(
    		':user' => $uid
    	))->fetchAll();
    	return $results;
    }

    public static function add($uid, $name, $song_ids = false){
    	OCP\DB::beginTransaction();
    	$statement = OCP\DB::prepare(
    		'INSERT INTO `*PREFIX*submedia_playlists`'
    		. ' ( `name`, `userid` ) VALUES ( :name, :userid )'
    	);
    	$result = $statement->execute(array(
    		':name' => $name,
    		':userid' => $uid
    	));
    	
    	if (!$result) {
    		return false;
    	}

    	$pid = OCP\DB::insertid('submedia_playlists');

    	if (!$song_ids)
    		return $pid;
    	else{
    		if (OC_Media_Playlist::assign($uid, $pid, $song_ids)){
    			OCP\DB::commit();
    			return $pid;
    		} else {
    			return false;
    		}
    	}
    }

    public static function update($uid, $name, $song_ids){
    	throw new Media_Playlist_Not_Allowed_Exception(':(');
    }

    public static function assign($uid, $pid, $song_ids){

    	function arrayToCommas( $in ){
			$init = '%s';
			for ($i = 0; $i < sizeof($in); $i++){
				if ($i < sizeof($in) - 1)
					$rule = ',%s';
				else
					$rule = '';
				$init = sprintf($init, $in[$i].$rule);
			}
			return $init;
		}
    	// Check if the songs do exist and are owned by the pid
    	
    	/* Does PDO allow arrays in IN statements?
    	 * so far, nope
    	 */
    	$statement = OCP\DB::prepare(
    		'SELECT COUNT(*) AS count FROM `*PREFIX*media_songs`'
    		.' WHERE `song_user` = :user AND'
    		.' `song_id` IN ('.arrayToCommas($song_ids).')'
    	);
    	$songs_exist_and_owned = $statement->execute(array(
    		':user' => $uid
    	))->fetchAll();
    	
    	if ($songs_exist_and_owned[0]['count'] != sizeof($song_ids)){
    		throw new Media_Playlist_Not_Found_Exception(':8');
    	}
    	/* AFAIK rails-style frameworks are doing HABTM 
    	 * deleting all references and adding them again.
		 */
    	$del_statement = OCP\DB::prepare(
    		'DELETE FROM `*PREFIX*submedia_playlists_songs`'
    		. 'WHERE `playlist_id` = :pid'
    	);
    	$del_statement->execute(array(':pid'=> $pid));

    	$ins_statement = OCP\DB::prepare(
    		'INSERT INTO `*PREFIX*submedia_playlists_songs`'
    		. '(`playlist_id`, `song_id`) VALUES (:pid, :sid)'
    	);
    	try {
    		foreach ($song_ids as $song){
    			$ins_statement->execute(array(
    				':pid' => $pid,
    				':sid' => $song
    			));
    		}
    	} catch (PDOException $e) {
    		return false;
    	}
    	return true;
    }
}

class Media_Playlist_Not_Allowed_Exception extends Exception {}
class Media_Playlist_Not_Found_Exception extends Exception {}