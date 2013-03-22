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
            return intval($result[0]['length']);
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

        return intval($result[0]['count']);
    }

    /**
    * Get an album by an id
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

        return intval($result[0]['count']);
    }

    /**
     * Get a list of my artists, filtering by its owner.
     * @param integer owner_id optional
     * @return array the list of artists available to me, owned or shared
     */
    public static function getArtists($owner_id = false) {
        if (!$owner_id)
            return OC_MEDIA_COLLECTION::getArtists();
        else{
            if ($owner_id == $_SESSION['user_id']){
                $statement = OCP\DB::prepare(
                    'SELECT DISTINCT artist_name, artist_id
                    FROM *PREFIX*media_artists
                    INNER JOIN *PREFIX*media_songs
                    ON artist_id = song_artist
                    WHERE artist_name LIKE :artist_name
                    AND song_path NOT LIKE :song_path
                    AND song_user = :song_user
                    ORDER BY artist_name'
                );
                    $results = $statement->execute(array(
                        'artist_name' => '%',
                        ':song_path' => '/Shared/%',
                        ':song_user' => $owner_id
                    ))->fetchAll();
                if (count($results) > 0) {
                    return $results;
                }
            } else {
                $statement = OCP\DB::prepare(
                    'SELECT file_target
                    FROM *PREFIX*share
                    WHERE share_with = :share_with
                    AND uid_owner = :uid_owner'
                );
                $results = $statement->execute(array(
                    ':share_with' => $_SESSION['user_id'],
                    ':uid_owner' => $owner_id
                ))->fetchAll();

                if (count($results) > 0) {
                    $songPaths = array();
                    foreach ($results as $result) {
                        $songPaths[] = 'song_path LIKE `/Shared'.$result['file_target'].'%`';
                    }
                    $statement = OCP\DB::prepare(
                        'SELECT DISTINCT artist_name, artist_id
                        FROM *PREFIX*media_artists
                        INNER JOIN *PREFIX*media_songs
                        ON artist_id = song_artist
                        WHERE artist_name LIKE :artist_name
                        AND '.implode(" OR ", $songPaths).'
                        AND song_user = :song_user
                        ORDER BY artist_name'
                    );
                    $results = $statement->execute(array(
                        'artist_name' => '%',
                        ':song_user' => $_SESSION['user_id']
                    ))->fetchAll();

                    if (count($results) > 0) {
                        return $results;
                    }
                }
                return array();
            }
        }
    }

    /**
     * Get a list of friends from a user foo. A friend is someone that has
     * shared some nice music with foo.
     * @param integer user_id optional
     * @return array the list of uids and count of files shared with foo.
     */
    public static function getFriends($user_id = false) {
        if (!$user_id)
            $user_id = $_SESSION['user_id'];

        $friends = array();

        $statement = OCP\DB::prepare(
            'SELECT song_path
            FROM *PREFIX*media_songs
            WHERE song_path LIKE :song_path
            AND song_user = :song_user'
        );

        $results = $statement->execute(array(
            ':song_path' => '/Shared/%',
            ':song_user' => $user_id
        ))->fetchAll();

        if (count($results) > 0) {
            $songPaths = array();
            foreach ($results as $result) {
                $songPath = substr($result['song_path'], strlen('/Shared'));
                $dirPath = dirname($songPath);
                if (!in_array($dirPath, $dirPaths))
                    $dirPaths[] = dirname($songPath);
                $songPaths[] = $songPath;
            }
            $saneSongPaths = '(`'.implode("`,`", $songPaths).'`)';
            $saneDirPaths = '(`'.implode("`,`", $dirPaths).'`)';

            $statement = OCP\DB::prepare(
                'SELECT uid_owner as uid, COUNT(*) as count
                FROM *PREFIX*share
                WHERE share_with = :share_with
                AND file_target IN '.$saneSongPaths.'
                OR file_target IN '.$saneDirPaths.'
                GROUP BY uid_owner'
            );

            $friends = $statement->execute(array(
                ':share_with' => $user_id
            ))->fetchAll();
        }
        return $friends;
    }

    /**
     * Get a list of my albums, filtering by its owner.
     * @param integer owner_id optional
     * @return array the list of albums available to me, owned or shared
     */
    public static function getAlbums($owner_id = false) {
        if (!$owner_id)
            return OC_MEDIA_COLLECTION::getArtists();
        else{
            if ($owner_id == $_SESSION['user_id']){
                $statement = OCP\DB::prepare(
                    'SELECT DISTINCT album_name, album_id
                    FROM *PREFIX*media_albums
                    INNER JOIN *PREFIX*media_songs
                    ON album_id = song_album
                    WHERE album_name LIKE :album_name
                    AND song_path NOT LIKE :song_path
                    AND song_user = :song_user
                    ORDER BY album_name'
                );
                    $results = $statement->execute(array(
                        ':album_name' => '%',
                        ':song_path' => '/Shared/%',
                        ':song_user' => $owner_id
                    ))->fetchAll();
                if (count($results) > 0) {
                    return $results;
                }
            } else {
                $statement = OCP\DB::prepare(
                    'SELECT file_target
                    FROM *PREFIX*share
                    WHERE share_with = :share_with
                    AND uid_owner = :uid_owner'
                );
                $results = $statement->execute(array(
                    ':share_with' => $_SESSION['user_id'],
                    ':uid_owner' => $owner_id
                ))->fetchAll();

                if (count($results) > 0) {
                    $songPaths = array();
                    foreach ($results as $result) {
                        $songPaths[] = 'song_path LIKE `/Shared'.$result['file_target'].'%`';
                    }
                    $statement = OCP\DB::prepare(
                        'SELECT DISTINCT album_name, album_id
                        FROM *PREFIX*media_albums
                        INNER JOIN *PREFIX*media_songs
                        ON album_id = song_album
                        WHERE album_name LIKE :album_name
                        AND '.implode(" OR ", $songPaths).'
                        AND song_user = :song_user
                        ORDER BY album_name'
                    );
                    $results = $statement->execute(array(
                        ':album_name' => '%',
                        ':song_user' => $_SESSION['user_id']
                    ))->fetchAll();

                    if (count($results) > 0) {
                        return $results;
                    }
                }
                return array();
            }
        }
    }

    private function getSharedFilePaths($owner_id){
        $uid = $_SESSION['user_id'];

        $fpath_st = OCP\DB::prepare(
            'SELECT file_target
            FROM *PREFIX*share
            WHERE share_with = :share_with
            AND uid_owner = :uid_owner');

        return $fpath_st->execute(array(
            ':share_with' => $uid,
            ':uid_owner' => $owner_id
        ))->fetchAll();
    }
}
