<?php

namespace PrestigeSociety\Core\Utils;

use pocketmine\tile\Chest;
use pocketmine\tile\EnchantTable;
use pocketmine\tile\Furnace;
use pocketmine\tile\Sign;
use pocketmine\tile\Tile;
use PrestigeSociety\Core\PrestigeSocietyCore;

class TileUtils {
	/**
	 * @return Chest[]
	 */
	public static function getChestTiles(){
		$ct = [];
		foreach(self::getServerTiles() as $tile){
			if($tile instanceof Chest){
				$ct[] = $tile;
			}
		}

		return $ct;
	}

	/**
	 * @return Tile[]
	 */
	public static function getServerTiles(){
		$tiles = [];
		foreach(PrestigeSocietyCore::getInstance()->getServer()->getLevels() as $lvl){
			foreach($lvl->getTiles() as $l){
				$tiles[] = $l;
			}
		}

		return $tiles;
	}

	/**
	 * @return EnchantTable[]
	 */
	public static function getEnchantingTables(){
		$et = [];
		foreach(self::getServerTiles() as $tile){
			if($tile instanceof EnchantTable){
				$et[] = $tile;
			}
		}

		return $et;
	}

	/**
	 * @return Furnace[]
	 */
	public static function getFurnaces(){
		$furnaces = [];
		foreach(self::getServerTiles() as $tile){
			if($tile instanceof Furnace){
				$furnaces[] = $tile;
			}
		}

		return $furnaces;
	}

	/**
	 * @return Sign[]
	 */
	public static function getSignTiles(){
		$signs = [];
		foreach(self::getServerTiles() as $tile){
			if($tile instanceof Sign){
				$signs[] = $tile;
			}
		}

		return $signs;
	}

	/**
	 * @param Sign $tile
	 * @param string[] $text
	 * @return bool
	 */
	public static function setSignTileText(Sign $tile, array $text){
		if(count($text) < 4) return false;
		$tile->setText($text[0], $text[1], $text[2], $text[3]);

		return true;
	}
}