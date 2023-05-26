BEGIN TRANSACTION;
CREATE TABLE player_story (
  pname TEXT not null,
  level TEXT not null,
  map TEXT not null,
  type INTEGER,
  rank INTEGER,
  rating INTEGER,
  wins INTEGER,  
  loss INTEGER,  
  points INTEGER,  
  date TEXT,  
  status TEXT,
  note TEXT,
  lupt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (pname, level,map)
);
INSERT INTO `player_story` VALUES('pe76',1,'1_Survival_Swamp',0,0,0,4,1,85,'2015-07-26 00:37:03','new','win','2015-07-26 00:37:03');
INSERT INTO `player_story` VALUES('hgdemo',1,'1_Survival_Swamp',0,0,0,2,1,6,'2015-07-26 00:37:06','new','loss','2015-07-26 00:37:06');
INSERT INTO `player_story` VALUES('hgdemo',1,'1_Survival_Breeze',0,0,0,7,3,47,'2015-07-26 00:44:42','new','loss','2015-07-26 00:44:42');
INSERT INTO `player_story` VALUES('pe76',1,'1_Survival_Breeze',0,0,0,5,3,82,'2015-07-26 00:44:44','new','win','2015-07-26 00:44:44');
INSERT INTO `player_story` VALUES('mini',1,'1_Survival_Land',0,0,0,0,2,0,'2015-07-26 00:50:49','new','loss','2015-07-26 00:50:49');
INSERT INTO `player_story` VALUES('hgdemo',1,'1_Survival_Land',0,0,0,0,2,0,'2015-07-26 00:53:45','new','loss','2015-07-26 00:53:45');
INSERT INTO `player_story` VALUES('pe76',1,'1_Survival_Land',0,0,0,2,0,8,'2015-07-26 00:53:48','new','win','2015-07-26 00:53:48');
INSERT INTO `player_story` VALUES('mini',1,'1_Survival_Breeze',0,0,0,2,5,12,'2015-07-26 01:04:57','new','loss','2015-07-26 01:04:57');
INSERT INTO `player_story` VALUES('hgdemo',2,'2_Catching_Fire_1',0,0,0,0,2,0,'2015-07-26 01:09:50','new','loss','2015-07-26 01:09:50');
INSERT INTO `player_story` VALUES('pe76',2,'2_Catching_Fire_1',0,0,0,1,3,0,'2015-07-26 01:10:43','new','loss','2015-07-26 01:10:43');
INSERT INTO `player_story` VALUES('mini',2,'2_Catching_Fire_1',0,0,0,3,1,24,'2015-07-26 01:10:48','new','win','2015-07-26 01:10:48');
INSERT INTO `player_story` VALUES('pe76',2,'2_Catching_Fire_2',0,0,0,1,3,51,'2015-07-26 01:13:58','new','win','2015-07-26 01:13:58');
INSERT INTO `player_story` VALUES('mini',2,'2_Catching_Fire_2',0,0,0,1,2,8,'2015-07-26 01:15:19','new','loss','2015-07-26 01:15:19');
INSERT INTO `player_story` VALUES('hgdemo',2,'2_Catching_Fire_2',0,0,0,4,1,37,'2015-07-26 01:15:24','new','loss','2015-07-26 01:15:24');
INSERT INTO `player_story` VALUES('mini',3,'3_MockingJay_Airport',0,0,0,2,1,15,'2015-07-26 01:19:02','new','win','2015-07-26 01:19:02');
INSERT INTO `player_story` VALUES('hgdemo',3,'3_MockingJay_Airport',0,0,0,1,1,18,'2015-07-26 01:19:06','new','loss','2015-07-26 01:19:06');
INSERT INTO `player_story` VALUES('hgdemo',3,'3_MockingJay_Highway',0,0,0,3,3,34,'2015-07-26 01:41:19','new','loss','2015-07-26 01:41:19');
INSERT INTO `player_story` VALUES('mini',3,'3_MockingJay_Highway',0,0,0,3,5,36,'2015-07-26 01:41:21','new','loss','2015-07-26 01:41:21');
INSERT INTO `player_story` VALUES('hgdemo',4,'4_VIP_Barcos',0,0,0,4,8,8,'2015-07-26 01:44:41','new','loss','2015-07-26 01:44:41');
INSERT INTO `player_story` VALUES('mini',4,'4_VIP_Barcos',0,0,0,3,4,9,'2015-07-26 01:44:46','new','loss','2015-07-26 01:44:46');
INSERT INTO `player_story` VALUES('mini',4,'4_VIP_Ultimate',0,0,0,1,1,0,'2015-07-26 02:18:21','new','win','2015-07-26 02:18:21');
INSERT INTO `player_story` VALUES('hgdemo',4,'4_VIP_Ultimate',0,0,0,2,2,30,'2015-07-26 02:18:24','new','loss','2015-07-26 02:18:24');
INSERT INTO `player_story` VALUES('pe76',4,'4_VIP_Barcos',0,0,0,4,9,8,'2015-07-26 01:44:41','new','loss','2015-07-26 01:44:41');
INSERT INTO `player_story` VALUES('pe76',3,'3_MockingJay_Highway',0,0,0,4,2,38,'2015-07-26 01:41:21','new','win','2015-07-26 01:41:21');
INSERT INTO `player_story` VALUES('pe76',4,'4_VIP_Ultimate',0,0,0,1,2,0,'2015-07-26 08:14:39','new','loss','2015-07-26 08:14:39');
INSERT INTO `player_story` VALUES('pe76',3,'3_MockingJay_Airport',0,0,0,0,2,0,'2015-07-27 07:51:42','new','loss','2015-07-27 07:51:42');
INSERT INTO `player_story` VALUES('mc76',1,'1_Survival_Breeze',0,0,0,5,0,0,'2015-07-28 05:10:47','new','win','2015-07-28 05:10:47');
INSERT INTO `player_story` VALUES('mc76',1,'1_Survival_Swamp',0,0,0,2,0,0,'2015-07-28 05:22:53','new','win','2015-07-28 05:22:53');
INSERT INTO `player_story` VALUES('bob',1,'1_Survival_Breeze',0,0,0,1,2,0,'2015-07-28 07:37:23','new','loss','2015-07-28 07:37:23');
INSERT INTO `player_story` VALUES('mc76',2,'2_Catching_Fire_2',0,0,0,0,1,0,'2015-07-28 07:38:02','new','loss','2015-07-28 07:38:02');
INSERT INTO `player_story` VALUES('bob',1,'1_Survival_Swamp',0,0,0,0,2,0,'2015-07-28 07:48:29','new','loss','2015-07-28 07:48:29');
INSERT INTO `player_story` VALUES('mini',1,'1_Survival_Swamp',0,0,0,3,4,10,'2015-07-28 07:48:31','new','loss','2015-07-28 07:48:31');
INSERT INTO `player_story` VALUES('bob',2,'2_Catching_Fire_2',0,0,0,1,1,4,'2015-07-30 02:43:40','new','win','2015-07-30 02:43:40');
INSERT INTO `player_story` VALUES('bob',1,'1_Survival_Land',0,0,0,1,0,13,'2015-07-30 03:16:17','new','win','2015-07-30 03:16:17');
INSERT INTO `player_story` VALUES('bob',2,'2_Catching_Fire_1',0,0,0,1,0,4,'2015-07-30 03:30:07','new','win','2015-07-30 03:30:07');
;
COMMIT;
