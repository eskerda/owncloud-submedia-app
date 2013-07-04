<?php

namespace OCA\Submedia;

class Collection_Extra {

    private $uid;

    public function __construct($uid) {
        $this->uid = $uid;
    }

    /**
    * check if an artist does exist
    * @param int id
    * @return boolean
    */
    public function isArtist($id) {
        $query = \OCP\DB::prepare(
            'SELECT COUNT(`artist_id`) as count'
            . ' FROM `*PREFIX*media_artists`'
            . ' WHERE `artist_id` = :artist_id'
        );
        $result = $query->execute(array(':artist_id' => $id))->fetchRow();

        return $result['count'] > 0;
    }

    /**
    * Get the duration in seconds on the songs of an album
    * @param int id
    * @return int
    */
    public function getAlbumLength($id) {
        $query = \OCP\DB::prepare(
            'SELECT SUM(`song_length`) as length'
            . ' FROM `*PREFIX*media_songs`'
            . ' WHERE `song_album` = :song_album'
        );
        $result = $query->execute(array(':song_album' => $id))->fetchRow();

        if ($result['length'] !== null) {
            return intval($result['length']);
        }
        return 0;
    }

    /**
    * Get the number of songs in an album
    * @param int id
    * @return int
    */
    public function getAlbumSongCount($id) {
        $query = \OCP\DB::prepare(
            'SELECT COUNT(`song_id`) as count'
            . ' FROM `*PREFIX*media_songs`'
            . ' WHERE `song_album` = :song_album'
        );
        $result = $query->execute(array(':song_album' => $id))->fetchRow();

        return intval($result['count']);
    }

    /**
    * Get an album by an id
    * @param integer id
    * @return array the list of fields on an album
    */
    public function getAlbum($id) {
        $query = \OCP\DB::prepare(
            'SELECT `album_name`, `album_artist`, `album_id`'
            . ' FROM `*PREFIX*media_albums`'
            . ' WHERE `album_id` = :album_id'
        );
        $result = $query->execute(array(':album_id' => $id))->fetchRow();

        if ($result) {
            return $result;
        }
        return false;
    }

    /**
    * Get the number of albums from an artist
    * @param integer id
    * @return int
    */
    public function getAlbumCount($id) {
        $query = \OCP\DB::prepare(
            'SELECT COUNT(`album_id`) as count'
            . ' FROM `*PREFIX*media_albums`'
            . ' WHERE `album_artist` = :album_artist'
        );
        $result=$query->execute(array(':album_artist' => $id))->fetchRow();

        return intval($result['count']);
    }

}
