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

    public static $defaultPlaylist = 'MyPlaylist';

    /**
     * @brief Returns the list of playlists for a specific user.
     * @param string $uid
     * @return array or false.
     */
    public static function all($uid) {
        $statement = OCP\DB::prepare(
            'SELECT sp.`id`,sp.`name`, sp.`created`, '
            . '(SELECT COUNT(*) FROM `*PREFIX*submedia_playlists_songs` sps'
            . ' WHERE sps.`playlist_id` = sp.`id`) as n_songs'
            . ' FROM `*PREFIX*submedia_playlists` sp'
            . ' WHERE userid = :userid'
        );
        return $statement->execute(array(':userid' => $uid))->fetchAll();
    }

    public static function add($uid, $name = '', array $song_ids = null) {
        if (!$name) {
            $name = self::$defaultPlaylist;
        }

        OCP\DB::beginTransaction();
        $statement = OCP\DB::prepare(
            'INSERT INTO `*PREFIX*submedia_playlists`'
            . ' (`userid`, `name`, `created`) VALUES (:userid, :name, :created)'
        );
        $result = $statement->execute(array(
            ':userid' => $uid,
            ':name' => $name,
            ':created' => date('Y-m-d H:i:s')
        ));
        OCP\DB::commit();

        if ($result && $song_ids) {
            $pid = OCP\DB::insertid('submedia_playlists');
            if (self::assign($uid, $pid, $song_ids)) {
                return $pid;
            }
        }
        return false;
    }

    public static function isOwner($uid, $pid) {
        $statement = OCP\DB::prepare(
            'SELECT COUNT(*) as count FROM *PREFIX*submedia_playlists'
            . ' WHERE `id` = :id AND `userid` = :userid'
        );
        $result = $statement->execute(array(
            ':id' => $pid,
            ':userid' => $uid
        ))->fetchAll();
        return $result[0]['count'] > 0;
    }

    public static function update($uid, $pid, $name = '', array $song_ids = null) {
        if (!self::isOwner($uid, $pid)) {
            throw new Media_Playlist_Not_Allowed_Exception(':(');
        }

        if ($name){
            OCP\DB::beginTransaction();
            $statement = OCP\DB::prepare(
                'UPDATE *PREFIX*submedia_playlists'
                . ' SET `name` = :name'
                . ' WHERE `id` = :id'
            );
            $result = $statement->execute(array(
                ':id' => $pid,
                ':name' => $name
            ));
            OCP\DB::commit();
        }
        if ($song_ids && self::assign($uid, $pid, $song_ids))
            return true;
        return false;
    }

    public static function delete($uid, $pid) {
        if (!self::isOwner($uid, $pid)){
            throw new Media_Playlist_Not_Allowed_Exception(':(');
        }

        // Assign empty list to clear all records for this playlist
        if (self::assign($uid, $pid, array())) {
            OCP\DB::beginTransaction();
            $statement = OCP\DB::prepare(
                'DELETE FROM *PREFIX*submedia_playlists'
                . ' WHERE `id` = :id'
            );
            $result = $statement->execute(array(':id' => $pid));
            OCP\DB::commit();
            if ($result) {
                return true;
            }
        }
        return false;
    }

    public static function find($uid, $pid) {
        if (!OC_Media_Playlist::isOwner($uid, $pid)) {
            throw new Media_Playlist_Not_Allowed_Exception(':(');
        }

        $statement = OCP\DB::prepare(
            'SELECT sp.`id`,sp.`name`, sp.`created` '
            . ' FROM `*PREFIX*submedia_playlists` sp'
            . ' WHERE id = :id AND userid = :userid'
        );
        $playlist = $statement->execute(array(
            ':id' => $pid,
            ':userid' => $uid
        ))->fetch();

        $songs = self::getSongs($pid);

        // The query is already done so no problem using code here..
        $playlist['n_songs'] = count($songs);

        /* We could enter into the song data model and extract it
         * ourselves using a join but there's already a getSong in
         * the lib_collection and so, we use it..
         */
        $playlist = array(
            'playlist' => $playlist,
            'songs' => array()
        );
        foreach ($songs as $song){
            $playlist['songs'][] = OC_Media_Collection::getSong($song);
        }
        return $playlist;
    }

    public static function getSongs($pid) {
        $statement = OCP\DB::prepare(
            'SELECT `song_id`, `playlist_id` FROM *PREFIX*submedia_playlists_songs'
            . ' WHERE `playlist_id` = :playlist_id'
        );

        $rows = $statement->execute(array(':playlist_id' => $pid))->fetchAll();
        $songs = array();
        foreach ($rows as $row){
            $songs[] = $row['song_id'];
        }
        return $songs;
    }

    public static function assign($uid, $pid, array $song_ids = null) {
        if ($song_ids) {
            $statement = OCP\DB::prepare(
                'SELECT COUNT(*) AS count FROM `*PREFIX*media_songs`'
                . ' WHERE `song_user` = :song_user'
                . ' AND `song_id` IN (' . implode(',', $song_ids) . ')'
            );
            $songs_exist_and_owned = $statement->execute(array(
                ':song_user' => $uid
            ))->fetchAll();

            if ($songs_exist_and_owned[0]['count'] != count(array_unique($song_ids))) {
                throw new Media_Playlist_Not_Found_Exception(':8');
            }

            OCP\DB::beginTransaction();
            $del_statement = OCP\DB::prepare(
                'DELETE FROM `*PREFIX*submedia_playlists_songs`'
                . 'WHERE `playlist_id` = :playlist_id'
            );
            $del_statement->execute(array(':playlist_id' => $pid));
            $ins_statement = OCP\DB::prepare(
                'INSERT INTO `*PREFIX*submedia_playlists_songs`'
                . '(`playlist_id`, `song_id`) VALUES (:playlist_id, :song_id)'
            );
            foreach ($song_ids as $song) {
                $ins_statement->execute(array(
                    ':playlist_id' => $pid,
                    ':song_id' => $song
                ));
            }
            OCP\DB::commit();
            return true;
        }
        else {
            $del_statement = OCP\DB::prepare(
                'DELETE FROM `*PREFIX*submedia_playlists_songs`'
                . 'WHERE `playlist_id` = :playlist_id'
            );
            $del_statement->execute(array(':playlist_id' => $pid));
            return true;
        }
        return false;
    }
}

class Media_Playlist_Not_Allowed_Exception extends Exception {}
class Media_Playlist_Not_Found_Exception extends Exception {}
