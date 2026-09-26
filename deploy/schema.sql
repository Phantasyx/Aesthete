-- Tables used by the PHP match server.
-- Names assume AESTHETE_TABLE_PREFIX=aesthete_
-- This schema is reconstructed from the queries in lib/Steampunk.
-- It is the shape a new database needs. It is not an original dump.

CREATE TABLE aesthete_user (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(255) NOT NULL DEFAULT '',
  name VARCHAR(255) NOT NULL,
  password CHAR(64) NULL,
  salt VARCHAR(32) NULL,
  guest CHAR(1) NOT NULL DEFAULT 'u'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE aesthete_validator (
  id INT AUTO_INCREMENT PRIMARY KEY,
  userid INT NOT NULL,
  validator VARCHAR(32) NOT NULL,
  date DATETIME NOT NULL,
  KEY userid (userid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE aesthete_game (
  id INT AUTO_INCREMENT PRIMARY KEY,
  gamename VARCHAR(255) NOT NULL,
  p1 INT NOT NULL,
  p2 INT NULL,
  size INT NOT NULL,
  turn INT NOT NULL,
  winner INT NULL,
  json MEDIUMTEXT NULL,
  time DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
