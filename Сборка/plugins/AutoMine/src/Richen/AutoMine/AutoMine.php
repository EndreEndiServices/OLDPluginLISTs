<?php

namespace Richen\AutoMine;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\math\Vector3;
use pocketmine\scheduler\CallbackTask;
use pocketmine\Server;
use pocketmine\block\Block;

use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\utils\Config;

class AutoMine extends PluginBase implements Listener
{
	private static $instance;
	
	/* @enable plugin & register events */
	public function onEnable()
	{
		if(!is_dir($this->getDataFolder())){
			@mkdir($this->getDataFolder());
		}
		$this->saveDefaultConfig("config.yml");
		$this->config = $this->getConfig("config.yml")->getAll();
		
		self::$instance = $this;
		
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new CallbackTask(array($this, "reloadMine")), 20 * 60 * 4);
	}
	
	public function getInstance(){
		return self::$instance;
	}
	
	public function reloadMine()
	{
		$config = $this->config;
		
		$minX = $config["min"]["x"]; $maxX = $config["max"]["x"];
		$minY = $config["min"]["y"]; $maxY = $config["max"]["y"];
		$minZ = $config["min"]["z"]; $maxZ = $config["max"]["z"];
		
		if($minX > $maxX) { $minX = $config["max"]["x"]; $maxX = $config["min"]["x"]; }
		if($minY > $maxY) { $minY = $config["max"]["y"]; $maxY = $config["min"]["y"]; }
		if($minZ > $maxZ) { $minZ = $config["max"]["z"]; $maxZ = $config["min"]["z"]; }
		
		for($x = $minX; $x <= $maxX; ++$x){
			for($y = $minY; $y <= $maxY; ++$y){
				for($z = $minZ; $z <= $maxZ; ++$z){
					$blocks = array(1,1,1,1,1,45,45,45,17,17,17,4,4,4,20,20,20,3,21,21,21,16,16,16,15,15,15,14,14,56,73,73,73);
					$id = $blocks[mt_rand(0, count($blocks) - 1)];
					Server::getInstance()->getDefaultLevel()->setBlock(new Vector3($x, $y, $z), Block::get($id));
					foreach(Server::getInstance()->getOnlinePlayers() as $player){
						if($player->getFloorX() == $x && $player->getFloorZ() == $z){
							$player->teleport(new Vector3(46.5, 53.5, 326.5));
						}
					}
				}
			}
		}
		Server::getInstance()->broadcastPopup("§6[§aAutoMine§6] §eАвтошахта обовлена! Скорее беги копать: §d/warp mine");
	}
}