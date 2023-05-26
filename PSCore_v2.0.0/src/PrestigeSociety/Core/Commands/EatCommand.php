<?php

namespace PrestigeSociety\Core\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat;
use PrestigeSociety\Core\PrestigeSocietyCore;
use PrestigeSociety\Core\Utils\RandomUtils;

class EatCommand extends Command implements PluginIdentifiableCommand {

	/** @var PrestigeSocietyCore */
	private $c;

	/**
	 *
	 * EatCommand constructor.
	 *
	 * @param PrestigeSocietyCore $c
	 *
	 */
	public function __construct(PrestigeSocietyCore $c){
		parent::__construct("eat", "Hungry? Fill your hunger bar!", RandomUtils::colorMessage("&e/eat"), ["food", "feed"]);
		$this->c = $c;
	}

	/**
	 *
	 * @param CommandSender $sender
	 * @param string $commandLabel
	 * @param string[] $args
	 *
	 * @return mixed
	 *
	 */
	public function execute(CommandSender $sender, $commandLabel, array $args){
		if($sender instanceof Player and $sender->hasPermission("command.eat")){
			if($sender->getFood() < floatval(20)){
				$sender->setFood($sender->getMaxFood());
				$sender->sendTip(TextFormat::GREEN . "Hunger bar set full!");
				$sender->level->broadcastLevelSoundEvent($sender, LevelSoundEventPacket::SOUND_EAT);
				$sender->level->broadcastLevelSoundEvent($sender, LevelSoundEventPacket::SOUND_BURP);
			}
		}else{
			$sender->sendMessage(TextFormat::RED . "You don't have permission run this Command.");
		}

		return true;
	}

	/**
	 * @return PrestigeSocietyCore
	 */
	public function getPlugin(): Plugin{
		$this->c;
	}
}