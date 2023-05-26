<?php

namespace PrestigeSociety\Nicknames;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use PrestigeSociety\Core\PrestigeSocietyCore;
use PrestigeSociety\Core\Utils\RandomUtils;

class NickCommand extends Command implements PluginIdentifiableCommand {

	/** @var PrestigeSocietyCore */
	protected $core;

	public function __construct(PrestigeSocietyCore $core){
		parent::__construct("nick", "Set a fancy nickname!", RandomUtils::colorMessage("&eUsage: /nick <nick>"), ["nickname"]);
		$this->core = $core;
		$this->setPermission("command.nick");
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
		if(!($sender instanceof Player)) return;
		if(!$this->testPermission($sender)){
			return;
		}

		if(count($args) < 1){
			$sender->sendMessage($this->getUsage());

			return;
		}

		$nick = $args[0];

		if($nick === "reset"){
			if($this->core->getPrestigeSocietyNicks()->hasNick($sender)){
				$this->core->PrestigeSocietyNicks->resetNick($sender);
				$sender->setDisplayName($this->core->PrestigeSocietyChat()->formatDisplayName($sender));
				$sender->setNameTag($this->core->PrestigeSocietyChat()->formatDisplayName($sender));
				$message = $this->core->getMessage('nicks', 'reset_nick');
				$sender->sendMessage(RandomUtils::colorMessage($message));
			}else{
				$message = $this->core->getMessage('nicks', 'cannot_reset');
				$sender->sendMessage(RandomUtils::colorMessage($message));
			}

			return;
		}

		$this->core->PrestigeSocietyNicks->setNick($sender, RandomUtils::colorMessage($nick));
		$sender->setDisplayName($this->core->PrestigeSocietyChat()->formatDisplayName($sender));
		$sender->setNameTag($this->core->PrestigeSocietyChat()->formatDisplayName($sender));
		$message = $this->core->getMessage('nicks', 'set_nick');
		$message = str_replace("@nick", RandomUtils::colorMessage($nick), $message);
		$sender->sendMessage(RandomUtils::colorMessage($message));

		return;
	}

	/**
	 * @return Plugin
	 */
	public function getPlugin(): Plugin{
		return $this->core;
	}
}