<?php

namespace PrestigeSociety\Signs;

use pocketmine\tile\Sign;
use pocketmine\utils\TextFormat;
use PrestigeSociety\Core\PrestigeSocietyCore;
use PrestigeSociety\Core\Utils\exc;
use PrestigeSociety\Core\Utils\ServerUtils;
use PrestigeSociety\Core\Utils\TileUtils;

class PrestigeSocietySigns {

	/**
	 *
	 * @API
	 *
	 * @param Sign $t
	 *
	 * @return bool
	 *
	 */
	public function isGarbageSign(Sign $t){
		foreach($this->getAllGarbageSignTiles() as $gb){
			if($t !== $gb) continue;

			return true;
		}

		return false;
	}

	/**
	 *
	 * @API
	 *
	 * @return Sign[]
	 *
	 */
	public function getAllGarbageSignTiles(){
		$signs = [];
		$text = PrestigeSocietyCore::getInstance()->getConfig()->getAll()["signs"]["garbage_sign"];
		foreach(TileUtils::getSignTiles() as $t){
			if(TextFormat::clean($t->getText()[0], true) === exc::clearColors($text[0]) and
				TextFormat::clean($t->getText()[1], true) === exc::clearColors($text[1]) and
				TextFormat::clean($t->getText()[2], true) === exc::clearColors($text[2]) and
				TextFormat::clean($t->getText()[3], true) === exc::clearColors($text[3])){
				array_push($signs, $t);
			}
		}

		return $signs;
	}

	/**
	 *
	 * @API
	 *
	 * @param Sign $t
	 *
	 * @return bool
	 *
	 */
	public function isWorldSign(Sign $t){
		foreach($this->getAllWorldSignTiles() as $wd){
			if($t !== $wd) continue;

			return true;
		}

		return false;
	}

	/**
	 *
	 * @API
	 *
	 * @return Sign[]
	 *
	 */
	public function getAllWorldSignTiles(){
		$signs = [];
		$text = PrestigeSocietyCore::getInstance()->getConfig()->getAll()["signs"]["world_sign"];

		foreach(TileUtils::getSignTiles() as $t){

			if(TextFormat::clean($t->getText()[0], true) === exc::clearColors($text[0]) and
				TextFormat::clean($t->getText()[1], true) !== null and
				TextFormat::clean($t->getText()[2], true) === exc::clearColors($text[2]) and
				TextFormat::clean($t->getText()[3], true) === exc::clearColors($text[3])){
				foreach(ServerUtils::getLevels() as $lvl){
					if(TextFormat::clean($t->getText()[1], true) === $lvl->getName()){
						array_push($signs, $t);
					}
				}
			}

		}

		return $signs;
	}
}