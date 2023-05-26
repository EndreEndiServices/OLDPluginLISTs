CREATE TABLE IF NOT EXISTS users(
	nickname TEXT PRIMARY KEY NOT NULL,
	password TEXT NOT NULL,
	ipReg TEXT NOT NULL,
	ipLast TEXT default null
);