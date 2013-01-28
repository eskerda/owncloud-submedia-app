<?php

class OC_MEDIA_COLLECTION_EXTRA{

    /**
    * check if an artist does exist
    * @param int id
    * @return boolean
    */
    public static function isArtist($id) {
        $query=OCP\DB::prepare(
            'SELECT COUNT(`artist_id`) as count FROM `*PREFIX*media_artists` '
            . 'WHERE `artist_id` = :id'
        );
        $result=$query->execute(array(':id' => $id))->fetchAll();
        return $result[0]['count'] > 0;
    }

    /**
    * Get the duration in seconds on the songs of an album
    * @param int id
    * @return int
    */
    public static function getAlbumLength($id) {
        $query=OCP\DB::prepare(
            'SELECT SUM(`song_length`) as length FROM `*PREFIX*media_songs` '
            . 'WHERE `song_album` = :id'
        );
        $result=$query->execute(array(':id' => $id))->fetchAll();

        if ($result[0]['length'] !== NULL)
            return $result[0]['length'];
        else
            return 0;
    }

    /**
    * Get the number of songs in an album
    * @param int id
    * @return int
    */
    public static function getAlbumSongCount($id) {
        $query=OCP\DB::prepare(
            'SELECT COUNT(`song_id`) as count FROM `*PREFIX*media_songs` '
            . 'WHERE `song_album` = :id'
        );
        $result=$query->execute(array(':id' => $id))->fetchAll();

        return $result[0]['count'];
    }

    /**
    * Get an album that an id
    * @param integer id
    * @return array the list of fields on an album
    */
    public static function getAlbum($id) {
        $query=OCP\DB::prepare(
            'SELECT `album_name`, `album_artist`, `album_id`
            FROM `*PREFIX*media_albums`
            WHERE `album_id` = :id'
        );
        $result=$query->execute(array(':id' => $id))->fetchAll();
        if (count($result) > 0)
            return $result[0];
        else
            return false;
    }

    /**
    * Get the number of albums from an artist
    * @param integer id
    * @return int
    */
    public static function getAlbumCount($id) {
        $query=OCP\DB::prepare(
            'SELECT COUNT(`album_id`) as count FROM `*PREFIX*media_albums` '
            . 'WHERE `album_artist` = :id'
        );
        $result=$query->execute(array(':id' => $id))->fetchAll();

        return $result[0]['count'];
    }
}
