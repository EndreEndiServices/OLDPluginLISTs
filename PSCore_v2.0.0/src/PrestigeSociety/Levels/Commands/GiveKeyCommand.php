<?php

namespace PrestigeSociety\Levels\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat;
use PrestigeSociety\Core\PrestigeSocietyCore;
use PrestigeSociety\Core\Utils\RandomUtils;

class GiveKeyCommand extends Command implements PluginIdentifiableCommand {

	/** @var PrestigeSocietyCore */
	protected $plugin;

	/**
	 *
	 * SetDeathsCommand constructor.
	 *
	 * @param PrestigeSocietyCore $c
	 *
	 */
	public function __construct(PrestigeSocietyCore $c){
		parent::__construct('givekey', 'Allows you to give a player crate keys', 'Usage: /givekey <player> <level> [amount]', []);
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
		if($sender->hasPermission('pl.givekey')){
			if(count($args) < 2){
				$sender->sendMessage(TextFormat::GREEN . $this->getUsage());

				return;
			}

			$level = intval($args[1]);
			$amount = 1;

			if(!is_numeric($level) or !is_int($level)){
				$sender->sendMessage(RandomUtils::colorMessage('&cThe level must be an integer.'));

				return;
			}

			if($level < 1 or $level > 7){
				$sender->sendMessage(RandomUtils::colorMessage('&cThe level cannot be less than 1 or greater than 7.'));

				return;
			}

			if(isset($args[2])){
				$amount = intval($args[2]);
			}

			if(!is_numeric($amount) or !is_int($amount)){
				$sender->sendMessage(RandomUtils::colorMessage('&cThe amount must be an integer.'));

				return;
			}

			if($amount < 1){
				$sender->sendMessage(RandomUtils::colorMessage('&cThe amount cannot be less than 0.'));

				return;
			}

			$name = $args[0];

			if(($player = $this->plugin->getServer()->getPlayer($name)) instanceof Player){
				$this->plugin->PrestigeSocietyLevels()->getSafeCrate($player, intval($level), intval($amount));
				$sender->sendMessage(RandomUtils::colorMessage(str_replace(['@player', '@level', '@amount'], [$player->getName(), $level, $amount], $this->plugin->getMessage('levels', 'give_key'))));
			}else{
				$sender->sendMessage(RandomUtils::colorMessage($this->plugin->getMessage('levels', 'player_offline')));
			}
		}
	}

	/**
	 * @return PrestigeSocietyCore
	 */
	public function getPlugin(): Plugin{
		return $this->plugin;
	}
}