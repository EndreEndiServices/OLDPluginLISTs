<?php

namespace PrestigeSociety\Credits\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat;
use PrestigeSociety\Core\PrestigeSocietyCore;
use PrestigeSociety\Core\Utils\exc;
use PrestigeSociety\Core\Utils\RandomUtils;

class SetCreditsCommand extends Command implements PluginIdentifiableCommand {

	/** @var PrestigeSocietyCore */
	protected $c;

	/**
	 *
	 * RepairCommand constructor.
	 *
	 * @param PrestigeSocietyCore $c
	 *
	 */
	public function __construct(PrestigeSocietyCore $c){
		parent::__construct('setcredits', "Set a players's coins", 'Usage: /setcredits <player> <amount>', ['setcredits']);
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
	public function execute(CommandSender $sender, string $commandLabel, array $args){

		if(count($args) < 2){
			$sender->sendMessage(TextFormat::GREEN . $this->getUsage());

			return;
		}

		if($sender->hasPermission('credits.command.setcredits') || $sender->hasPermission('credits.command.all')){
			$player = $args[0];
			if(exc::checkIsNumber($credits = $args[1])){
				if(!$this->c->PrestigeSocietyCredits->playerExists($player)){
					$message = $this->c->getMessage('credits', 'no_player');
					$sender->sendMessage(RandomUtils::colorMessage($message));

					return;
				}
				$this->c->PrestigeSocietyCredits->setCredits($player, $credits);
				$message = $this->c->getMessage('credits', 'set_credits');
				$message = str_replace(['@player', '@coins'], [$player, $credits], $message);
				$sender->sendMessage(RandomUtils::colorMessage($message));
			}else{
				$message = $this->c->getMessage('credits', 'non_numeric');
				$sender->sendMessage(RandomUtils::colorMessage($message));
			}
		}else{
			$sender->sendMessage(TextFormat::RED . "You don't have permission run this Command.");
		}
	}

	/**
	 * @return Plugin
	 */
	public function getPlugin(): Plugin{
		return $this->c;
	}
}