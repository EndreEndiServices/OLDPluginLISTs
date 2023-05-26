<?php

namespace minecombat;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

class Main extends PluginBase implements Listener{

	/**
	 * @var Config
	 */
	private $dataFile;

	/**
	 * @var bool
	 */
	private $isTeamGame, $isStarted;

	/**
	 * @var string[][]
	 */
	private $teamList;

	public function onLoad(){
		$this->getLogger()->info(TextFormat::WHITE . "MineCombat 로딩 완료.");
	}

	public function onEnable(){
		$this->isStarted = false;

		@mkdir($this->getDataFolder());

		$this->dataFile = new Config($this->getDataFolder()."data.yml", Config::YAML);

		$this->getServer()->getPluginManager()->registerEvents($this, $this);

		$this->getLogger()->info(TextFormat::DARK_GREEN . "MineCombat 활성화 완료");
    }

	public function onCommand(CommandSender $sender, Command $command, $label, array $args){
		switch($command->getName()){
			case "mc":
				$sub = array_shift($args);
				switch($sub){
					case "start":
						$this->isStarted = true;
						// TODO
						break;
					case "stop":
						 // TODO
						break;
					default:
					$sender->sendMessage("Usage: ".$command->getUsage());
				}
				return true;
			default:
				return false;
		}
		return true;
	}

	public function onPlayerJoin(PlayerJoinEvent $event){

	}

	public function onPlayerSpawn(PlayerRespawnEvent $event){

	}

	public function onPlayerHit(EntityDamageByEntityEvent $event){
		$damager = $event->getDamager();
		$entity = $event->getEntity();
		if($damager instanceof Player and $entity instanceof Player){
			if($this->isColleague($damager->getName(), $entity->getName())){
				$event->setCancelled(true);
			}
		}
	}

	public function isColleague($player1, $player2){
		return $this->isTeamGame and (isset($this->teamList[0][$player1]) and isset($this->teamList[0][$player2])) or (isset($this->teamList[1][$player1]) and isset($this->teamList[1][$player2]));
	}
}