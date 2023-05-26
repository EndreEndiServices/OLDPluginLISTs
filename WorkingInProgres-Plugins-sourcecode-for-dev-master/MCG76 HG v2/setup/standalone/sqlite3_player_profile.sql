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