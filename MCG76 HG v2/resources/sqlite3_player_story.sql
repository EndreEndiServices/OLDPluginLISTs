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