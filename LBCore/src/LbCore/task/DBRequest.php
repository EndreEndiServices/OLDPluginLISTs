<?php

namespace LbCore\task;

use mysqli;

/**
 * Work with base db requests to VIP kits
 */
class DBRequest {
	const DB_SERVER = "accessory.lbsg.net";
	const DB_NAME = "ingamekits";
	const DB_USER = "ingamekits";
	const DB_PASS = "jdyhu7c7olaP3";
	
	/**
	 * Get kit data by player name
	 * 
	 * @param string $username
	 * @return array|boolean
	 */
	static public function getBHJunk(string $username) {	
		$connect = new mysqli(self::DB_SERVER, self::DB_NAME, self::DB_PASS, self::DB_NAME);
		if (!$connect->connect_errno) {
			$query = "SELECT * FROM bh_vip_save_data WHERE username='{$username}';";
			if ($stmt = $connect->prepare($query)) {
				if ($stmt->execute()) {
					$queryRes = $stmt->get_result();
					while ($row = $queryRes->fetch_assoc()) {
						$connect->close();
						return $row["save_data"];
					}
				} else {
					var_dump("BH VIP JUNK GET: problem");
				}
			} else {
				var_dump("BH VIP JUNK GET: query is bad");
			}
			$connect->close();
		} else {
			var_dump("BH VIP JUNK CONNECT: error");
		}
		
		return false;
	}
	
	/**
	 * Set kit data by player name
	 * 
	 * @param string $username
	 * @param string $saveData
	 * @return array
	 */
	static public function setBHJunk(string $username, $saveData) {
		$res = false;
		
		if (is_array($saveData)) {
			$saveData = implode(', ', $saveData);
		}
		
		$connect = new mysqli(self::DB_SERVER, self::DB_NAME, self::DB_PASS, self::DB_NAME);
		
		if (!$connect->connect_errno) {
			$query = "SELECT * FROM bh_vip_save_data WHERE username='{$username}'";
			if ($stmt = $connect->prepare($query)) {
				if($stmt->execute()){
					$queryRes = $stmt->get_result();
					if ($queryRes->num_rows > 0) {
						$query = "UPDATE bh_vip_save_data SET save_data='{$saveData}' WHERE username='{$username}';";						
					} else {
						$query = "INSERT INTO bh_vip_save_data (username, save_data) VALUES ('{$username}', '{$saveData}');";
					}
					if ($stmt = $connect->prepare($query)) {
						$res = $stmt->execute();
					}
				}
			} else {
				var_dump("BH VIP JUNK SET: problem");
			}
			$connect->close();
		} else {
			var_dump("BH VIP JUNK CONNECT: error");
		}
		
		return $res;
	}
}
