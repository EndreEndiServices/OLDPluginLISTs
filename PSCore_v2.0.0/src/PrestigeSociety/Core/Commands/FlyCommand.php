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

class FlyCommand extends Command implements PluginIdentifiableCommand {

	/** @var PrestigeSocietyCore */
	protected $plugin;

	/**
	 *
	 * FlyCommand constructor.
	 *
	 * @param PrestigeSocietyCore $c
	 *
	 */
	public function __construct(PrestigeSocietyCore $c){
		parent::__construct('fly', 'Allows you to fly', '/fly', ['flight']);
		$this->plugin = $c;
	}

	/**
	 *
	 * @param CommandSender $sender
	 * @param string $commandLabel
	 * @param string[] $args
	 *
	 * @return mixed|void
	 *
	 */
	public function execute(CommandSender $sender, $commandLabel, array $args){
		if($sender instanceof Player and $sender->hasPermission('pl.fly')){
			if($sender->getLevel() == "Work") return;
			$sender->setAllowFlight($sender->getAllowFlight() ? false : true);
			$sender->sendTip(TextFormat::GREEN . ($sender->getAllowFlight() ? 'Flying turned on.' : 'Flying turned off.'));
			$sender->level->broadcastLevelSoundEvent($sender, LevelSoundEventPacket::SOUND_BLOCK_END_PORTAL_FRAME_FILL);
		}else{
			$sender->sendMessage(TextFormat::RED . "You don't have permission run this command.");
		}
	}

	/**
	 * @return PrestigeSocietyCore
	 */
	public function getPlugin(): Plugin{
		return $this->plugin;
	}
}