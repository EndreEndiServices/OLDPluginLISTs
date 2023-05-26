<?php

namespace PrestigeSociety\Economy\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat;
use PrestigeSociety\Core\PrestigeSocietyCore;
use PrestigeSociety\Core\Utils\exc;
use PrestigeSociety\Core\Utils\RandomUtils;

class SetCoinsCommand extends Command implements PluginIdentifiableCommand {

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
		parent::__construct('setcoins', "Set a players's coins", 'Usage: /setcoins <player> <amount>', ['setmoney']);
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

		if($sender->hasPermission('economy.command.setcoins') || $sender->hasPermission('economy.command.all')){
			$player = $args[0];
			if(exc::checkIsNumber($money = $args[1])){
				if(!$this->c->PrestigeSocietyEconomy->playerExists($player)){
					$message = $this->c->getMessage('economy', 'no_player');
					$sender->sendMessage(RandomUtils::colorMessage($message));

					return;
				}
				$this->c->PrestigeSocietyEconomy->setMoney($player, $money);
				$message = $this->c->getMessage('economy', 'set_money');
				$message = str_replace(['@player', '@money'], [$player, $money], $message);
				$sender->sendMessage(RandomUtils::colorMessage($message));
			}else{
				$message = $this->c->getMessage('economy', 'non_numeric');
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