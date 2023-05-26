<?php

namespace MXSB\GreenWix\island;

use MXSB\GreenWix\Main;
use pocketmine\math\Vector3;
use pocketmine\item\Item;
use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\tile\Tile;
use pocketmine\block\Block;


class Island {

    public function __construct($data){
    	$this->ownerName = $data["owner"];
		$this->members = unserialize($data["members"]);
		$this->locked = $data["locked"];
		//$this->chunkNum = $data["home"]; soon
		$this->islandX = $data["islandX"];
		$this->islandZ = $data["islandZ"];
		$this->point = unserialize($data["point"]);
		$this->spawn = unserialize($data["spawn"]);
    }
    public function isLocked(){
    	if($this->locked == "true"){
    		return true;
    	} else {
    		return false;
    	}
    }
    public function setSpawn($vector){
    	$this->spawn = [
    		0 => $vector->getFloorX(),
    		1 => $vector->getFloorY(),
    		2 => $vector->getFloorZ()
    	];
    }
    public function getSpawn(){
    	var_dump($this->spawn);
    	return $this->spawn;
    }
    public function setPoint($vector){
        $this->point = [
            0 => $vector->getFloorX(),
            1 => $vector->getFloorY(),
            2 => $vector->getFloorZ()
        ];
    }
    public function getPoint(){
        return $this->point;
    }
}