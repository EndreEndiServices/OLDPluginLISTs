<?php

namespace GametypeStatues;

use GametypeStatues\entity\GametypeStatue;
use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\Enum;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use pocketmine\math\Vector3;
use Data\NpcData;

/**
 * Base class to create Gametype statues on server enable
 */
class GametypeStatues {
	/**@var GameTypeStatues*/
	static private $instance;
	/**@var bool*/
	private $isEnable;
	/**@var array*/
	private $statues = array();
	//protected
	private function __construct() {}
	private function __clone() {}
	private function __wakeup() {}
	
	static public function getInstance() {
		if (is_null(self::$instance)) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	public function enable($pluginName) {
		$this->isEnable = true;
		$this->placeStatues(NpcData::$NpcInfo[$pluginName]);
		$this->loadChunkSection();
	}
	
	/**
	 * Set statues on positions and save its additional data
	 * 
	 * @param array $npcInfo
	 */
	private function placeStatues($npcInfo) {
		if(isset($npcInfo["games"])){
			foreach ($npcInfo["games"] as $edata) {
				$repeat = round(((strlen($edata["title"])) / 2), 0);
				if ($repeat <= 0 or $repeat == false) {
					$repeat = 0;
				}
				$targetChunk = Server::getInstance()->getDefaultLevel()->getChunk(
					$edata["position"][0] >> 4, $edata["position"][2] >> 4, true
				);
				$statue = new GametypeStatue($targetChunk, new Compound("", [
					"Pos" => new Enum("Pos", [
						new DoubleTag("", $edata["position"][0]),
						new DoubleTag("", $edata["position"][1]),
						new DoubleTag("", $edata["position"][2])
					]),
					"Rotation" => new Enum("Rotation", [
						new FloatTag("", $edata["rotation"]),
						new FloatTag("", 0),
					]),
					"Motion" => new Enum("Motion", [
						new DoubleTag("", 0),
						new DoubleTag("", 0),
						new DoubleTag("", 0)
					]),
					"Inventory" => new Enum("Inventory", []),
					"NameTag" => new StringTag("NameTag", TextFormat::GOLD . TextFormat::BOLD . $edata["title"] . TextFormat::RESET . "\n" . TextFormat::BLUE . str_repeat(" ", $repeat))
				]));

				$statue->NPCId = $edata["id"];
				$statue->text = $edata["text"];
				$statue->type = "game";
				$statue->getInventory()->setItem(0, Item::get($edata["holding"]));
				$statue->getInventory()->setHeldItemSlot(0);
				$statue->setSkin(file_get_contents(__DIR__."/../Data/skins/".$edata["skinFile"]), $edata["isSlim"]);
				$statue->setDataProperty(Entity::DATA_NO_AI, Entity::DATA_TYPE_BYTE, 1);
				//$entity->setNameTag($edata["title"]);
				$statue->setLevel(Server::getInstance()->getDefaultLevel());
				$statue->spawnToAll();
				//$this->getServer()->getDefaultLevel()->addEntity($entity);

				$this->statues[] = $statue;
				$sign = Server::getInstance()->getDefaultLevel()->getTile(new Vector3($edata["position"][0] - 0.5, $edata["position"][1] - 2, $edata["position"][2] - 2.5));
				$sign->setText("", TextFormat::WHITE.$edata["signText"][0], TextFormat::WHITE.$edata["signText"][1], "");
			}
		}
		if(isset($npcInfo["kits"])){
			foreach ($npcInfo["kits"] as $edata) {
				$repeat = round(((strlen($edata["title"])) / 2), 0);
				if ($repeat <= 0 or $repeat == false) {
					$repeat = 0;
				}
				$targetChunk = Server::getInstance()->getDefaultLevel()->getChunk(
					$edata["position"][0] >> 4, $edata["position"][2] >> 4, true
				);
				$statue = new GametypeStatue($targetChunk, new Compound("", [
					"Pos" => new Enum("Pos", [
						new DoubleTag("", $edata["position"][0]),
						new DoubleTag("", $edata["position"][1]),
						new DoubleTag("", $edata["position"][2])
					]),
					"Rotation" => new Enum("Rotation", [
						new FloatTag("", $edata["rotation"]),
						new FloatTag("", 0),
					]),
					"Motion" => new Enum("Motion", [
						new DoubleTag("", 0),
						new DoubleTag("", 0),
						new DoubleTag("", 0)
					]),
					"Inventory" => new Enum("Inventory", []),
					"NameTag" => new StringTag("NameTag", TextFormat::GOLD . TextFormat::BOLD . $edata["title"] . TextFormat::RESET . "\n" . TextFormat::BLUE . str_repeat(" ", $repeat))
				]));

				$statue->NPCId = $edata["id"];
				$statue->type = "kit";
				$statue->getInventory()->setItem(0, Item::get($edata["holding"]));
				$statue->getInventory()->setHeldItemSlot(0);
				if(!empty($edata["armor"])){
					foreach($edata["armor"] as $slot => $item){
						$statue->getInventory()->setArmorItem($slot, Item::get($item));
					}
				}
				$statue->setSkin(file_get_contents(__DIR__."/../Data/skins/".$edata["skinFile"]), $edata["isSlim"]);
				$statue->setDataProperty(Entity::DATA_NO_AI, Entity::DATA_TYPE_BYTE, 1);
				$statue->setLevel(Server::getInstance()->getDefaultLevel());
				$statue->spawnToAll();
				$this->statues[] = $statue;
			}
		}
	}

	/**
	 * Freeze chunks near statues
	 */
	private function loadChunkSection() {
		$chunks = Server::getInstance()->getDefaultLevel()->getProvider()->getLoadedChunks();
		foreach ($chunks as $chunk) {
			$chunk->allowUnload = false;
		}
    }
}
