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

class PayCommand extends Command implements PluginIdentifiableCommand {

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
		parent::__construct('pay', 'Pay a player from your coins!', 'Usage: /pay <player> <amount>', ['paycoins']);
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

		if(count($args) > 2){
			$sender->sendMessage(TextFormat::GREEN . $this->getUsage());

			return;
		}
		$player = $args[0];

		if(exc::checkIsNumber($money = $args[1])){
			if(!$this->c->PrestigeSocietyEconomy->playerExists($player)){
				$message = $this->c->getMessage('economy', 'no_player');
				$sender->sendMessage(RandomUtils::colorMessage($message));

				return;
			}

			if($this->c->PrestigeSocietyEconomy->getMoney($sender) < $money){
				$message = $this->c->getMessage('economy', 'insufficient_to_pay');
				$sender->sendMessage(RandomUtils::colorMessage($message));

				return;
			}

			$this->c->PrestigeSocietyEconomy->subtractMoney($sender, $money);
			$this->c->PrestigeSocietyEconomy->addMoney($player, $money);
			$message = $this->c->getMessage('economy', 'payed_money');
			$message = str_replace(['@player', '@money'], [$player, $money], $message);
			$sender->sendMessage(RandomUtils::colorMessage($message));

		}else{
			$message = $this->c->getMessage('economy', 'non_numeric');
			$sender->sendMessage(RandomUtils::colorMessage($message));
		}

		return;
	}

	/**
	 * @return Plugin
	 */
	public function getPlugin(): Plugin{
		return $this->c;
	}
}