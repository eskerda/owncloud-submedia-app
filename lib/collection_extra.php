<?php

namespace OCA\Submedia;

class Collection_Extra {

	private $collection;

    private $uid;

    public function __construct($uid) {
        $this->uid = $uid;
        $this->collection = new \OCA\Media\Collection($this->uid);
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
        $result = $query->execute(array(':artist_id' => $id))->fetchAll();

        return $result[0]['count'] > 0;
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
        $result = $query->execute(array(':song_album' => $id))->fetchAll();

        if ($result[0]['length'] !== null) {
            return intval($result[0]['length']);
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
        $result = $query->execute(array(':song_album' => $id))->fetchAll();

        return intval($result[0]['count']);
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
        $result = $query->execute(array(':album_id' => $id))->fetchAll();

        if (count($result) > 0) {
            return $result[0];
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
        $result=$query->execute(array(':album_artist' => $id))->fetchAll();

        return intval($result[0]['count']);
    }

    /**
     * Get a list of my artists, filtering by its owner.
     * @param integer owner_id optional
     * @return array the list of artists available to me, owned or shared
     */
    public function getArtists($query = '%', $exact = false, $owner_id = null) {
        if (!$owner_id) {
            return $this->collection->getArtists($query, $exact);
        }

        if ($query != '%' && !$exact) {
            $query = "%$query%";
        } elseif ($query == '') {
            $query = '%';
        }

        $cmd = 'SELECT DISTINCT artist_name, artist_id'
            . ' FROM *PREFIX*media_artists'
            . ' INNER JOIN *PREFIX*media_songs'
            . ' ON artist_id = song_artist'
            . ' WHERE artist_name LIKE :artist_name'
            . ' AND song_user = :song_user';

        $params = array(
            ':artist_name' => $query,
            ':song_user' => $this->uid
        );

        if ($owner_id == $this->uid) {
            $cmd .= ' AND `song_path` NOT LIKE :song_path';
            $params[':song_path'] = '/Shared/%';
        } else {
            $fpaths = $this->getSharedFilePaths($owner_id);
            if (count($fpaths) > 0) {
                $songPaths = array();
                foreach ($fpaths as $fpath) {
                    $cleanPath = addslashes('/Shared' . $fpath['file_target']);
                    $songPaths[] = 'song_path LIKE "' . $cleanPath . '%"';
                }
                $cmd .= ' AND (' . implode(' OR ', $songPaths) . ')';
            } else {
                // This owner is not sharing anything with me
                return array();
            }
        }

        $cmd .= ' ORDER BY artist_name';
        $statement = \OCP\DB::prepare($cmd);
        $results = $statement->execute($params)->fetchAll();

        return $results;
    }

    /**
     * Get a list of friends from a user foo. A friend is someone that has
     * shared some nice music with foo.
     * @return array the list of uids and count of files shared with foo.
     */
    public function getFriends() {
        $friends = array();

        $statement = \OCP\DB::prepare(
            'SELECT song_path'
            . ' FROM *PREFIX*media_songs'
            . ' WHERE song_path LIKE :song_path'
            . ' AND song_user = :song_user'
        );
        $results = $statement->execute(array(
            ':song_path' => '/Shared/%',
            ':song_user' => $this->uid
        ))->fetchAll();

        if (count($results) > 0) {
            $songPaths = array();
            $dirPaths = array();
            foreach ($results as $result) {
                $songPath = substr($result['song_path'], strlen('/Shared'));
                $dirPath = addslashes(dirname($songPath));
                if (!in_array($dirPath, $dirPaths)) {
                    $dirPaths[] = $dirPath;
                }
                $songPaths[] = addslashes($songPath);
            }
            $query = 'SELECT uid_owner as uid, COUNT(*) as count'
                . ' FROM *PREFIX*share'
                . ' WHERE share_with = :share_with'
                . ' AND file_target IN ("' . implode('","', $songPaths) . '")'
                . ' OR file_target IN ("' . implode('","', $dirPaths) . '")'
                . ' GROUP BY uid_owner';
            $statement = \OCP\DB::prepare($query);
            $friends = $statement->execute(array(
                ':share_with' => $this->uid
            ))->fetchAll();
        }

        return $friends;
    }

    /**
     * Get a list of my albums, filtering by its owner.
     * @param integer owner_id optional
     * @return array the list of albums available to me, owned or shared
     */
    public function getAlbums($artist = 0, $query = '%', $exact = false, $owner_id = null) {
        if (!$owner_id) {
            return $this->collection->getAlbums($artist, $query, $exact);
        }

        if ($query != '%' && !$exact) {
            $query = "%$query%";
        }

        $cmd = 'SELECT DISTINCT album_name, album_id, album_artist'
            . ' FROM *PREFIX*media_albums'
            . ' INNER JOIN *PREFIX*media_songs'
            . ' ON album_id = song_album'
            . ' WHERE album_name LIKE :album_name'
            . ' AND song_user = :song_user';

        $params = array(
            ':album_name' => $query,
            ':song_user' => $this->uid
        );

        if ($owner_id == $this->uid) {
            $cmd .= ' AND song_path NOT LIKE :song_path';
            $params[':song_path'] = '/Shared/%';
        } else {
            $fpaths = $this->getSharedFilePaths($owner_id);
            if (count($fpaths) > 0) {
                $songPaths = array();
                foreach ($fpaths as $fpath) {
                    $cleanPath = addslashes('/Shared' . $fpath['file_target']);
                    $songPaths[] = 'song_path LIKE "' . $cleanPath . '%"';
                }
                $cmd .= ' AND (' . implode(' OR ', $songPaths) . ')';
            } else {
                // This owner is not sharing anything with me
                return array();
            }
        }

        $cmd .= ' ORDER BY album_name';
        $album_statement = \OCP\DB::prepare($cmd);
        $results = $album_statement->execute($params)->fetchAll();

        return $results;
    }

    /**
    * Get the list of songs that (optionally) match an artist and/or album and/or search string
    * @param integer artist optional
    * @param integer album optional
    * @param string search optional
    * @param string owner_id optional
    * @return array the list of songs found, owned by me or shared by owner_id
    */
    public function getSongs($artist = 0, $album = 0, $search = '', $exact = false, $owner_id = null) {
        if (!$owner_id) {
            return $this->collection->getSongs($artist, $album, $search, $exact);
        }

        $cmd = 'SELECT * FROM `*PREFIX*media_songs`'
            . ' WHERE `song_user`= :song_user';

        $params = array(':song_user' => $this->uid);

        if ($artist != 0) {
            $cmd = ' AND `song_artist` = :song_artist';
            $params[':song_artist'] = $artist;
        }
        if ($album != 0) {
            $cmd = ' AND `song_album` = :song_album';
            $params[':song_album'] = $album;
        }
        if ($search) {
            if (!$exact) {
                $search = "%$search%";
            }
            $cmd = ' AND `song_name` LIKE :song_name';
            $params[':song_name'] = $search;
        }

        if ($owner_id == $this->uid) {
            $cmd .= ' AND `song_path` NOT LIKE :song_path';
            $params[':song_path'] = '/Shared/%';
        } else {
            $fpaths = $this->getSharedFilePaths($owner_id);
            if (count($fpaths) > 0) {
                $songPaths = array();
                foreach ($fpaths as $fpath) {
                    $cleanPath = addslashes('/Shared' . $fpath['file_target'] . '%');
                    $songPaths[] = 'song_path LIKE "' . $cleanPath . '"';
                }
                $cmd .= ' AND (' . implode(' OR ', $songPaths) . ')';
            } else {
                // This owner is not sharing anything with me
                return array();
            }
        }

        $cmd .= ' ORDER BY `song_track`, `song_name`, `song_path`';
        $query = \OCP\DB::prepare($cmd);
        $results = $query->execute($params)->fetchAll();

        return $results;
    }

    private function getSharedFilePaths($owner_id) {
        $fpath_st = \OCP\DB::prepare(
            'SELECT file_target'
            . ' FROM *PREFIX*share'
            . ' WHERE share_with = :share_with'
            . ' AND uid_owner = :uid_owner'
        );

        return $fpath_st->execute(array(
            ':share_with' => $this->uid,
            ':uid_owner' => $owner_id
        ))->fetchAll();
    }
}
