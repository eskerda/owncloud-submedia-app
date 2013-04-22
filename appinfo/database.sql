DROP TABLE IF EXISTS oc_submedia_playlists;
DROP TABLE IF EXISTS oc_submedia_playlists_songs;

CREATE TABLE oc_submedia_playlists(
  /* MySQL */
  id INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,
  /* PostgreSQL */
  /*id SERIAL PRIMARY KEY,*/
  /* SQLite3 */
  /*id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,*/
  userid VARCHAR(64) NOT NULL,
  name VARCHAR(255) DEFAULT '',
  created VARCHAR(255) NOT NULL
);

CREATE TABLE oc_submedia_playlists_songs(
  playlist_id INTEGER(4) NOT NULL,
  song_id INTEGER(4) NOT NULL
);
