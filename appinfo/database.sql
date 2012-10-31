DROP TABLE oc_submedia_playlists;
DROP TABLE oc_submedia_playlists_songs;

CREATE TABLE oc_submedia_playlists(
  id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT  ,
  userid TEXT(255) NOT NULL,
  name TEXT(255) DEFAULT '',
  created TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE oc_submedia_playlists_songs(
  playlist_id UNSIGNED INTEGER(4) NOT NULL,
  song_id UNSIGNED INTEGER(4) NOT NULL
);