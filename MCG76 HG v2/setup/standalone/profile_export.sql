BEGIN TRANSACTION;
CREATE TABLE player_profile (
  pname TEXT PRIMARY KEY,
  password TEXT,
  balance INTEGER,
  rank INTEGER,
  wins INTEGER,  
  loss INTEGER,  
  rating INTEGER,
  vip TEXT,
  home_x INTEGER,
  home_y INTEGER,
  home_z INTEGER,
  status TEXT,
  kit    TEXT DEFAULT NULL,
  lupt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
INSERT INTO `player_profile` VALUES('hgdemo','hgdemo',227,0,23,23,'','false',0,0,0,'free_kit','','2015-07-26 00:37:06');
INSERT INTO `player_profile` VALUES('pe76','pe76',121,0,19,22,'','false',0,0,0,'new','','2015-07-26 00:44:44');
INSERT INTO `player_profile` VALUES('mini','mini',169,0,18,24,'','false',0,0,0,'free_kit','','2015-07-26 01:04:57');
INSERT INTO `player_profile` VALUES('mc76','mc76',21,0,7,1,'','false',0,0,0,'new','','2015-07-28 05:10:47');
INSERT INTO `player_profile` VALUES('bob','bob',16,0,4,5,'','false',0,0,0,'new','','2015-07-28 07:37:23');
;
COMMIT;
