<?php

namespace mcg76\skywars;

use pocketmine\math\Vector3 as Vector3;
use pocketmine\level\Position;
use pocketmine\Server;
use pocketmine\Player;
use pocketmine\utils\Config;
use pocketmine\block\Chest;
use pocketmine\item\Item;

/**
 * MCG76 SkyBlockPlayer
 *
 * Copyright (C) 2014 minecraftgenius76
 * YouTube Channel: http://www.youtube.com/user/minecraftgenius76
 *
 * @author minecraftgenius76@gmail.com
 *
 */

class SkyWarsPlayer {
	public $pgin;
	public $name;
	public $chestLocation;
	public $spawnLocation;
	public $chestFilled;
	public $status;
	public $levelname;
	
	public function __construct(SkyBlockPlugIn $pg, $name){
		$this->pgin=$pg;		
		$this->name = $name;
	}

	public function save(){				
		$path = $this->pgin->getDataFolder() . "players/";		
		$data = new Config($path . "$this->name.yml", Config::YAML);
		//$this->plotLevelName = $this->p1->getLevel()->getName();
		//this should not happen		
		
// 		$this->log($path);
// 		var_dump($data);
		if ($this->chestFilled==null) {
			$this->chestFilled = "no";
		}
		$data->set("name", $this->name);		
		$data->set("levelname", $this->levelname);
		if ($this->spawnLocation!=null) {
			$data->set("spawnX", $this->spawnLocation->x);
			$data->set("spawnY", $this->spawnLocation->y);
			$data->set("spawnZ", $this->spawnLocation->z);
		}
		if ($this->chestLocation!=null) {
			$data->set("chestX", $this->chestLocation->x);
			$data->set("chestY", $this->chestLocation->y);
			$data->set("chestZ", $this->chestLocation->z);
			//$data->set("chestLocation", $this->chestLocation);
		}
		$data->set("chestFilled", $this->chestFilled);
		$data->set("status", $this->status);
		
		$data->save();
	}
	
	public function delete(){
		$path = $this->pgin->getDataFolder() . "players/";		
		$name = $this->name;
		@unlink($path . "$name.yml");
	}
		
	public function getRandomChestItems(Player $player, $block) {
		$tile = $player->getLevel()->getTile ( new Vector3 ( $block->x, $block->y, $block->z ) );
		// $new = [];
		// for($i = 0; $i < 27; $i++){
		// $new[] = array($tile->getItem($i)->getID(),$tile->getItem($i)->count,$tile->getItem($i)->getDamage());
		// }
		// var_dump($tile);		
		// if($tile!=null && ($tile instanceof Chest)) {
		if ($tile!=null) {
			$inv = $tile->getRealInventory ();
			$inv->setItem ( 1, Item::ICE, 0, 2);				
			$inv->setItem ( 2, Item::LAVA,0,1);
			$inv->setItem ( 3, Item::BUCKET,0,1);
			//$inv->setItem ( 2, Item::WOODEN_PICKAXE,0,1);
			$inv->setItem ( 2, Item::SIGN,0,2);
		}
	}
	
	public function randomItems() {
		$i = rand ( 0, 30 );
		if ($i == 0) {
			return new Item ( Item::BOW, 0, 2 );
		}
		if ($i == 1) {
			return new Item ( Item::ARROW, 0, 64 );
		}
		if ($i == 2) {
			return new Item ( Item::APPLE, 0, 5 );
		}		
		if ($i == 3) {
			return new Item ( Item::BREAD, 0, 5 );
		}
		if ($i == 4) {
			return new Item ( Item::BED, 0, 1 );
		}
		if ($i == 5) {
			return new Item ( Item::DIAMOND_CHESTPLATE, 0, 1 );
		}
		if ($i == 6) {
			return new Item ( Item::DIAMOND_BOOTS, 0, 1 );
		}
		if ($i == 7) {
			return new Item ( Item::DIAMOND_LEGGINGS, 0, 1 );
		}
		if ($i == 8) {
			return new Item ( Item::DIAMOND_HELMET, 0, 1 );
		}
		if ($i == 9) {
			return new Item ( Item::DIAMOND_SWORD, 0, 1 );
		}
		if ($i == 10) {
			return new Item ( Item::DIAMOND_AXE, 0, 1 );
		}
		if ($i == 11) {
			return new Item ( Item::DIAMOND_PICKAXE, 0, 1 );
		}
		if ($i == 12) {
			return new Item ( Item::IRON_BOOTS, 0, 1 );
		}
		if ($i == 13) {
			return new Item ( Item::IRON_CHESTPLATE, 0, 1 );
		}
		if ($i == 14) {
			return new Item ( Item::IRON_HELMET, 0, 1 );
		}
		if ($i == 15) {
			return new Item ( Item::IRON_LEGGINGS, 0, 1 );
		}
		if ($i == 16) {
			return new Item ( Item::IRON_SWORD, 0, 1 );
		}
		if ($i == 17) {
			return new Item ( Item::IRON_PICKAXE, 0, 1 );
		}
		return new Item ( Item::AIR);
	}
	
	public function between($l, $m, $r){
		$lm = abs($l - $m);
		$rm = abs($r - $m);
		$lrm = $lm + $rm;
		$lr = abs($l - $r);
		//Server::getInstance()->broadcastMessage("lrm:".$lrm." lr:".$lr);
		if ($lrm <= $lr) {
			return 1;
		}
		return 0;
	}
	
	private function log($msg) {
		$this->pgin->getLogger ()->info ( $msg );
	}
	
}