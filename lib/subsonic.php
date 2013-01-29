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

u -> username
p -> password: clear | enc:prefix (hexencoded)
v -> version
c -> client
+f -> format: xml, json, jsonp

Error codes
===========
0   A generic error.
10  Required parameter is missing.
20  Incompatible Subsonic REST protocol version. Client must upgrade.
30  Incompatible Subsonic REST protocol version. Server must upgrade.
40  Wrong username or password.
50  User is not authorized for the given operation.
60  The trial period for the Subsonic server is over. Please donate to get
    a license key. Visit subsonic.org for details.
70  The requested data was not found.

***/

class OC_MEDIA_SUBSONIC{

    var $user = false;
    var $version = false;
    var $client = false;
    var $format = 'xml';

    public static $api_version = "1.8.0";

    protected static $data_path = 'apps/submedia/lib/data/';

    public static $formatWhiteList = array(
        'xml','json','jsonp'
    );

    public static function getFormat($params){
        $format = (isset($params['f']))?$params['f']:'xml';
        if (!in_array($format, OC_MEDIA_SUBSONIC::$formatWhiteList)){
            $format = 'xml';
        }
        return $format;
    }

    public function __construct($params){
        $username = (isset($params['u']))?$params['u']:false;
        $password = (isset($params['p']))?$params['p']:false;
        $version = (isset($params['v']))?$params['v']:false;
        $client = (isset($params['c']))?$params['c']:false;
        $format = (isset($params['f']))?$params['f']:'xml';
        $callback = (isset($params['callback']))?$params['callback']:false;
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
            if ($auth == $password){
                OC_Media_Collection::$uid=$users[0]['user_id'];
                OC_User::setUserId($users[0]['user_id']);
                return true;
            }
        }
        return false;
    }

    /**
     * @return  Returns all configured top-level music folders. Takes no
     *          extra parameters.
     */
    public function getMusicFolders(){
        /*  To day, there's no support for music folders in owncloud,
            In that sense, we just return a virtual folder with the
            username.

            In subsonic, this structures content as:
             - Podcasts
             - Music
             - ...

            In the future we could, for instance, separate shared
            music into different folders.
        */
        return array(
            'musicFolders' => array(
                    'musicFolder' => array(
                        'id' => 0,
                        'name' => $this->user,
                    )
            )
        );
    }

    /**
     * @brief Returns an indexed structure of all artists.
     * @param string optional $musicFolderId If specified, only return artists in the music folder with the given ID.
     * @param int optional $ifModifiedSince If specified, only return a result if the artist collection has changed since the given time (in milliseconds since 1 Jan 1970).
     * @return associative array
     */
    public function getIndexes($params, $version = 170){
        /** We want something similar to this mess...
        <indexes lastModified="1347639481261">
            <shortcut name="Podcast" id="920"/>
            <index name="A">
            <artist name="Antigona" id="937"/>
            <artist name="Aygan" id="926"/>
            </index>
            <index name="B">
            <artist name="bilk" id="939"/>
            <artist name="Binaerpilot" id="931"/>
            <artist name="Brad Sucks" id="935"/>
            </index>
            ...
        **/

        if ($version > 170)
            $top_root = 'artists';
        else
            $top_root = 'indexes';

        $artists = OC_Media_Collection::getArtists();

        // TODO: Find a way to get the last modified time
        $response = array(
            $top_root => array(
                'lastModified' => strval(round(microtime(true)*100)),
                'index' => array()
            )
        );

        foreach ($artists as $artist){
            $name = $artist['artist_name'];
            if (preg_match('/^[a-zA-Z]*$/',$name[0]) > 0){
                // starts with allowed char
                $letter = strtoupper($name[0]);
            } else {
                // should be grouped under #
                $letter = '#';
            }
            if (!isset($response[$top_root]['index'][$letter]))
                $response[$top_root]['index'][$letter] = array();

            $response[$top_root]['index'][$letter][] =
                self::modelArtistToSubsonic($artist, $version);
        }
        if ($this->format == 'json' || $this->format == 'jsonp'){
            $letters = $response[$top_root]['index'];
            $response[$top_root]['index'] = array();
            foreach ($letters as $letter=>$artists){
                $response[$top_root]['index'][] = array(
                    'name' => $letter,
                    'artist' => $artists
                );
            }
        }
        return $response;
    }

    function getMusicDirectory($params){
            $id = (isset($params['id']))?$params['id']:null;
            if (is_null($id)){
                throw new Exception('Required string parameter \'id\' not present', 10);
            }
            $sid = explode('_', $id);
            $response = array(
                'directory' => array(
                    'id' => $id,
                    'name' => '',
                    'child' => array()
                )
            );
            switch($sid[0]){
                case 'album':
                    $album_id = $sid[1];

                    $album = OC_Media_Collection::getAlbumName($album_id);
                    $songs = OC_Media_Collection::getSongs(0, $album_id);
                    $response['directory']['name'] = $album;

                    foreach ($songs as $song){
                        if (!isset($artist)){
                            $artist = OC_Media_Collection::getArtistName($song['song_artist']);
                        }
                        $response['directory']['child'][] = OC_MEDIA_SUBSONIC::modelSongToSubsonic($song, $artist, $album);
                    }
                    break;
                case 'artist':
                    $artist_id = $sid[1];
                    $artist = OC_Media_Collection::getArtistName($artist_id);
                    $albums = OC_Media_Collection::getAlbums($artist_id);
                    $response['directory']['name'] = $artist;

                    foreach ($albums as $album){
                        $response['directory']['child'][] = OC_MEDIA_SUBSONIC::modelAlbumToSubsonic($album, $artist);
                    }
                    break;
                default:
                    // Return all albums
                    $albums = OC_Media_Collection::getAlbums();
                    foreach($albums as $album){
                        $artist = OC_Media_Collection::getArtistName($album['album_artist']);
                        $response['directory']['child'][] = OC_MEDIA_SUBSONIC::modelAlbumToSubsonic($album, $artist);
                    }
            }

            return $response;
    }

    function stream($params){
        $id = (isset($params['id']))?$params['id']:false;
        if (!$id){
            throw new Exception('Required string parameter \'id\' not present', 10);
        }
        if ($song = OC_MEDIA_COLLECTION::getSong($id)) {
            // Find a shared music file owner and path
            if (strpos($song['song_path'], '/Shared/') === 0) {
                $statement = OCP\DB::prepare(
                    'SELECT uid_owner, file_source'
                    . ' FROM *PREFIX*share'
                    . ' WHERE share_with = :share_with'
                    . ' AND file_target = :file_target'
                    . ' LIMIT 1'
                );
                $results = $statement->execute(array(
                    ':share_with' => $song['song_user'],
                    ':file_target' => substr($song['song_path'], strlen('/Shared'))
                ))->fetchAll();
                if (count($results) > 0) {
                    $fileId = $results[0]['file_source'];
                    $userId = $results[0]['uid_owner'];
                    $statement = OCP\DB::prepare(
                        'SELECT path'
                        . ' FROM *PREFIX*fscache'
                        . ' WHERE id = :id'
                        . ' AND user = :user'
                        . ' LIMIT 1'
                    );
                    $results = $statement->execute(array(
                        ':id' => $fileId,
                        ':user' => $userId
                    ))->fetchAll();
                    if (count($results) > 0) {
                        $filePath = $results[0]['path'];
                        $song['song_user'] = $userId;
                        $song['song_path'] = substr($filePath, strlen('/' . $userId . '/files'));
                        OC_Filesystem::chroot($userId . '/files');
                    }
                }
            }
            // Send the music stream
            OC_Util::setupFS($song['song_user']);
            header('Content-type: ' . OC_Filesystem::getMimeType($song['song_path']));
            header('Content-Length: ' . $song['song_size']);
            OC_Filesystem::readfile($song['song_path']);
        }
    }

    function search ($query, $version = 2){
        switch ($version) {
            case 1:
                return self::search_1($query);
            case 2:
                return self::search_2($query);
            default:
                throw new Exception('Not implemented', 30);
        }
    }

    private function search_1 ($query){
        $artist = (isset($query['artist'])?$query['artist']:false);
        $album = (isset($query['album'])?$query['album']:false);
        $title = (isset($query['title'])?$query['title']:false);
        $any = (isset($query['any'])?$query['any']:false);
        $count = (isset($query['count'])?$query['count']:20);
        $offset = (isset($query['offset'])?$query['offset']:0);


    }

    private function search_2 ($query){
        /** Sorry for this messy function, Subsonic API search results are
         *  THAT lousy. First, it looks for artists, albums and songs that
         *  match a query.
         *  Then, for _each_ of these artists, adds the full list of albums.
         *  And then, for each of these albums, adds the full list of songs.
         */

        if (!isset($query['query']))
            $q = '';
        else
            $q = $query['query'];

        $q = trim(htmlentities($q));

        $artistCount = (isset($query['artistCount']))?intval($query['artistCount']):20;
        $artistOffset = (isset($query['artistOffset']))?intval($query['artistOffset']):0;
        $albumCount = (isset($query['albumCount']))?intval($query['albumCount']):20;
        $albumOffset = (isset($query['albumOffset']))?intval($query['albumOffset']):0;
        $songCount = (isset($query['songCount']))?intval($query['songCount']):20;
        $songOffset = (isset($query['songOffset']))?intval($query['songOffset']):0;

        $artists = OC_Media_Collection::getArtists($q);
        $albums = OC_Media_Collection::getAlbums(0, $q);
        $songs = OC_Media_Collection::getSongs(0, 0, $q);

        /** Dummy Cache for album and artists ids to name **/
        $art_ch = array();
        $alb_ch = array();

        $r = array('artist' => array(), 'album'=>array(), 'song' => array());

        foreach ($artists as $artist){
            $r['artist'][] = array(
                'name' => $artist['artist_name'],
                'id' => 'artist_'.$artist['artist_id']
            );
            $albums = array_merge($albums, OC_Media_Collection::getAlbums($artist['artist_id']));

            $art_ch[$artist['artist_id']] = $artist['artist_name'];
        }

        foreach ($albums as $album){
            if (!isset($art_ch[$album['album_artist']])){
                $art_ch[$album['album_artist']] = OC_Media_Collection::getArtistName($album['album_artist']);
            }
            $r['album'][] = OC_MEDIA_SUBSONIC::modelAlbumToSubsonic($album, $art_ch[$album['album_artist']]);
            $songs = array_merge($songs, OC_Media_Collection::getSongs(0,$album['album_id']));
            $alb_ch[$album['album_id']] = $album['album_name'];
        }

        foreach ($songs as $song){
            if (!isset($art_ch[$song['song_artist']])){
                $art_ch[$song['song_artist']] = OC_Media_Collection::getArtistName($song['song_artist']);
            }

            if (!isset($alb_ch[$song['song_album']])){
                $alb_ch[$song['song_album']] = OC_Media_Collection::getAlbumName($song['song_album']);
            }
            $r['song'][] = OC_MEDIA_SUBSONIC::modelSongToSubsonic($song, $art_ch[$song['song_artist']], $alb_ch[$song['song_album']]);
        }

        $r['artist'] = array_slice($r['artist'], $artistOffset, $artistCount);
        $r['album'] = array_slice($r['album'], $albumOffset, $albumCount);
        $r['song'] = array_slice($r['song'], $songOffset, $songCount);

        foreach ($r as $key=>$value) {
            if (count($value) == 0)
                unset($r[$key]);
        }

        if (count($r) > 0)
            return array('searchResult2'=>$r);
        else
            return array('searchResult2'=>'');
    }

    function getPlaylists(){
        $playlists = OC_Media_Playlist::all($this->user);

        $subdata = array();
        foreach($playlists as $playlist){
            $subdata[] = array(
                'id' => $playlist['id'],
                'name' => $playlist['name'],
                'owner' => $this->user,
                'songCount' => $playlist['n_songs'],
                'duration' => 0,
                'public' => false,
                'created' => $playlist['created']
            );
        }
        switch ($this->format){
            case 'json':
            case 'jsonp':
                return array(
                    'playlists' => array(
                        'playlist' => $subdata
                    )
                );
            default:
                return array(
                    'playlists' => $subdata
                );
        }
    }

    function createPlaylist($query, $query_string){
        // Yes.. Subsonic API expects &songId=1&songId=2&songId=3
        $params = $this->requestDupedParams($query_string);

        $playlist_id = (isset($query['playlistId']))?$query['playlistId']:false;
        $name = (isset($query['name']))?$query['name']:false;
        $song_ids = (isset($params['songId']))?$params['songId']:array();

        if (!$name && !$playlist_id){
            throw new Exception("Playlist ID or name must be specified.", 10);
        }

        try {
            if ($playlist_id){
                return OC_Media_Playlist::update($this->user, $playlist_id, $name, $song_ids);
            } else {
                return OC_Media_Playlist::add($this->user, $name, $song_ids);
            }
        /**
         * Here we do a little jiggling around because of Subsonic error codes
         * I am not really partidary of exceptions; in this case it seems
         * logic to use them, but bubble them around the Playlist model
         * hence, unrelating it from subsonic at all...
         */
        } catch (Media_Playlist_Not_Found_Exception $e) {
            throw new Exception('Playlist not found: '.$playlist_id, 70);
        } catch (Media_Playlist_Not_Allowed_Exception $e) {
            throw new Exception('Permission denied for playlist '.$playlist_id, 50);
        }
    }

    function deletePlaylist($params){
        $id = (isset($params['id']))?$params['id']:false;

        if (!$id){
            throw new Exception(
                'Required int parameter \'id\' is not present', 10);
        }
        try{
            OC_Media_Playlist::delete($this->user, $id);
        } catch (Media_Playlist_Not_Allowed_Exception $e){
            throw new Exception('Permission denied for playlist '.$id, 50);
        }
    }

    function getPlaylist($params){
        $id = (isset($params['id']))?$params['id']:false;
        if (!$id){
            throw new Exception(
                'Required int parameter \'id\' is not present', 10);
        }

        try{
            $playlist = OC_Media_Playlist::find($this->user, $id);
        } catch (Media_Playlist_Not_Allowed_Exception $e){
            throw new Exception('Permission denied for playlist '.$id, 50);
        }

        $response = array(
            'playlist' => array(
                'id' => $playlist['playlist']['id'],
                'songCount' => $playlist['playlist']['n_songs'],
                'created' => $playlist['playlist']['created']
            ),
            'entry' => array()
        );

        $totalTime = 0;
        foreach ($playlist['songs'] as $song){
            $artist = OC_Media_Collection::getArtistName($song['song_artist']);
            $album = OC_Media_Collection::getAlbumName($song['song_album']);
            $response['entry'][] = OC_MEDIA_SUBSONIC::modelSongToSubsonic($song, $artist, $album);
            $totalTime+=$song['song_length'];
        }

        $response['playlist']['duration'] = $totalTime;

        if ($this->format == 'json' || $this->format == 'jsonp'){
            $response['playlist']['entry'] = $response['entry'];
            unset($response['entry']);
        }
        return $response;
    }

    function outputCoverArt($params){
        $lastfm_key = "644ce3b9599de151b83f69eb1b420a1e";

        $id = (isset($params['id']))?explode('_',$params['id']):false;
        $size = (
            isset($params['size']) &&
            intval($params['size']) > 1)?intval($params['size']):200;

        if (!$id){
            throw new Exception('Required string parameter \'id\' not present', 10);
        }

        if ($size > 500)
            $size = 500;

        if (sizeof($id) > 1)
            $album_id = $id[1];
        else
            $album_id = $id[0];


        $album_name = OC_Media_Collection::getAlbumName($album_id);
        $songs = OC_Media_Collection::getSongs(0, $album_id);
        $artist_name = OC_Media_Collection::getArtistName($songs[0]['song_artist']);

        $lastFm = new OC_Media_LastFM($lastfm_key);

        $image_url = $lastFm::getCoverArt(
                html_entity_decode($artist_name, ENT_QUOTES),
                html_entity_decode($album_name, ENT_QUOTES)
            );
        if (!$image_url){
            header("HTTP/1.0 404 Not Found");
            $image_url = self::$data_path.'defaultcover.png';
        }
        $image_meta = getimagesize($image_url);
        switch($image_meta['mime']){
            case 'image/jpeg':
                $img = ImageCreateFromJPEG($image_url);
                break;
            case 'image/png':
                $img = ImageCreateFromPNG($image_url);
                break;
            default:
                throw new Exception('Internal server error', 0);
        }
        $thumb = imagecreatetruecolor($size, $size);
        imagecopyresized($thumb, $img, 0, 0, 0, 0, $size, $size, $image_meta[0], $image_meta[1]);
        header('Content-type: '.$image_meta['mime']);
        switch($image_meta['mime']){
            case 'image/jpeg':
                imagejpeg($thumb);
                break;
            case 'image/png':
                imagepng($thumb);
                break;
        }
        exit();
    }

    /**
     * @brief Returns a list of random or newest albums.
     * @param string type mandatory: the list type (random, newest).
     * @param int size optional default 10. The number of albums to return. Max 500.
     * @param int offset optional default 0. The list offset.
     * @return associative array
     */
    public function getAlbumList($params){

        function compareAlbumId($foo, $bar) {
            return $foo['album_id'] > $bar['album_id'];
        }

        $type = (isset($params['type']))?$params['type']:false;
        $size = (isset($params['size']) && $params['size'] <= 500)?$params['size']: 10;
        $offset = (isset($params['offset']))?$params['offset']:0;

        if (!$type){
            throw new Exception("Required string parameter 'type' is not present", 10);
        }

        $albums = OC_Media_Collection::getAlbums(0);

        switch($type){
            case 'newest':
                // Sort them by album_id (pseudo-date..)
                usort($albums, "compareAlbumId");
                $albums = array_slice(array_reverse($albums), $offset, $size);
                break;
            case 'random':
                shuffle($albums);
                $albums = array_slice($albums, $offset, $size);
                break;
            default:
                throw new Exception('Not implemented', 70);
        }

        $response = array();
        for ($i = 0; $i < sizeof($albums) && $i < $size; $i++){
            $response[] = OC_MEDIA_SUBSONIC::modelAlbumToSubsonic(
                $albums[$i],
                OC_Media_Collection::getArtistName($albums[$i]['album_artist'])
            );
        }

        if ($this->format == 'json' || $this->format == 'jsonp'){
            $response = array(
                'albumList' => array(
                    'album' => $response
                )
            );
        }

        return $response;
    }

    public function getArtist($params) {
        $id = (isset($params['id']))?$params['id']:false;

        if (!$id){
            throw new Exception("Required int parameter 'id' is not present", 10);
        }

        if (strpos($id, '_') !== false){
            $f_id = explode('_', $id);
            $id = $f_id[1];
        }


        if (!OC_Media_Collection_Extra::isArtist($id)){
            throw new Exception("Artist not found.", 70);
        }

        $albums = OC_Media_Collection::getAlbums($id);
        $name = OC_Media_Collection::getArtistName($id);
        $r = array(
            'artist' => array(
                'id' => 'artist_'.$id,
                'name' => $name,
                'coverArt' => 'artist_'.$id,
                'albumCount' => sizeof($albums),
                'album' => array()
            )
        );

        foreach ($albums as $album){
            $r['artist']['album'][] = self::modelAlbumToSubsonic($album, $name, 180);
        }

        if (count($r['artist']['album']) == 1) {
            if ($this->format == 'json' || $this->format == 'jsonp')
                $r['artist']['album'] = $r['artist']['album'][0];
        }

        return $r;
    }

    public function getAlbum($params) {
        $id = (isset($params['id']))?$params['id']:false;

        if (!$id){
            throw new Exception("Required int parameter 'id' is not present", 10);
        }

        if (strpos($id, '_') !== false){
            $f_id = explode('_', $id);
            $id = $f_id[1];
        }

        $album = OC_Media_Collection_Extra::getAlbum($id);

        if (!$album){
            throw new Exception("Album not found.", 70);
        }

        $songs = OC_Media_Collection::getSongs(0,$id);
        $artist = OC_Media_Collection::getArtistName($id);
        $r = array(
            'album' => self::modelAlbumToSubsonic($album, $artist, 180)
        );

        $r['album']['song'] = array();
        foreach ($songs as $song){
            $r['album']['song'][] = self::modelSongToSubsonic($song, $artist, $album['album_id']);
        }
        return $r;
    }

    private function modelAlbumToSubsonic($album, $artist, $version = 170){
        if ($version <= 170){
            return array(
                'artist' => $artist,
                //'averageRating' =>
                //'userRating' =>
                'coverArt' => 'album_'.$album['album_id'],
                'id' => 'album_'.$album['album_id'],
                'isDir' => true,
                'parent' => $album['album_artist'],
                'title' => $album['album_name'],
                //'created' =>
            );
        } else {
            return array(
                'id' => 'album_'.$album['album_id'],
                'name' => $album['album_name'],
                'artist' => $artist,
                'artistId' => 'artist_'.$album['album_artist'],
                'coverArt' => 'album_'.$album['album_id'],
                'songCount' => OC_Media_Collection_Extra::getAlbumSongCount($album['album_id']),
                'duration' => OC_Media_Collection_Extra::getAlbumLength($album['album_id'])
            );
        }
    }

    private function modelSongToSubsonic($song, $artist, $album){
        /***
         * A song has too many fields to be stupidly typing
         * it again and again..
         */

        return array(
            'id' => $song['song_id'],
            'parent' => 'album_'.$song['song_album'],
            'title' => $song['song_name'],
            'album' => $album,
            'artist' => $artist,
            'isDir' => false,
            'coverArt' => 'album_'.$song['song_album'],
            //'created' =>
            'duration' => $song['song_length'],
            'bitRate' => round($song['song_size'] / $song['song_length'] * 0.008),
            'track' => $song['song_track'],
            //'year' =>
            //'genre' =>
            'size' => $song['song_size'],
            'suffix' => 'mp3',
            'contentType' => 'audio/mpeg',
            'isVideo' => false,
            'path' => sprintf('%s/%s/%d - %s.mp3',$artist,$album,$song['song_track'],$song['song_name']),
            'albumId' => $song['song_album'],
            'artistId' => $song['song_artist'],
            'type' => 'music'
        );
    }

    private function modelArtistToSubsonic($artist, $version = 170){
        $r = array(
            'id' => 'artist_'.$artist['artist_id'],
            'name' => $artist['artist_name']
        );

        if ($version > 170) {
            $r['coverArt'] = 'artist_'.$artist['artist_id'];
            $r['albumCount'] = OC_Media_Collection_Extra::getAlbumCount($artist['artist_id']);
        }
        return $r;
    }

    private function requestDupedParams($query){
        $query  = explode('&', $query);
        $params = array();

        foreach( $query as $param )
        {
          list($name, $value) = explode('=', $param);
          $params[urldecode($name)][] = urldecode($value);
        }
        return $params;
    }
}
