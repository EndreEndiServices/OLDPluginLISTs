<?php

namespace PrestigeSociety\Lang\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use PrestigeSociety\Core\PrestigeSocietyCore;
use PrestigeSociety\Core\Utils\RandomUtils;

class LangCommand extends Command implements PluginIdentifiableCommand {

	/** @var PrestigeSocietyCore */
	protected $core;

	public function __construct(PrestigeSocietyCore $core){
		parent::__construct("lang", "Set your language!", RandomUtils::colorMessage("&eUsage: /lang <lang/info>"), ["langs", "language"]);
		$this->core = $core;
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

		$lang = 'unknown';
		if(count($args) == 0 or count($args) > 1){
			$lang = $this->core->PrestigeSocietyLang->getLang($sender);
			switch($lang){
				case 0:
					$message = "&6[!] &cUnknown argument! &eUse: &6/lang <lang/info> &ewhere <lang> is: &6en/ro";
					$message = RandomUtils::colorMessage($message);
					$sender->sendMessage($message);

					return;
				case 1:
					$message = "&6[!] &cArgument necunoscut! &eFoloseste: &6/lang <lang/info> &eunde <lang> is: &6en/ro";
					$message = RandomUtils::colorMessage($message);
					$sender->sendMessage($message);

					return;
			}

			return;
		}
		$args[0] = strtolower($args[0]);
		switch($args[0]){
			case "list":
				$message = "&6[!] &cNow you turned on lag optimizer.";
				$message = RandomUtils::colorMessage($message);
				$sender->sendMessage($message);

				return;
			case "en":
				$lang = "English";
				$message = "&6[!] &aYou succesfully changed language to: &c" . $lang . "&a!";
				$message = RandomUtils::colorMessage($message);
				$sender->sendMessage($message);
				$this->core->PrestigeSocietyLang->setLang($sender, 0);

				return;
			case "ro":
				$lang = "Romana";
				$message = "&6[!] &aAi schimbat cu succes limba in: &c" . $lang . "&a!";
				$message = RandomUtils::colorMessage($message);
				$sender->sendMessage($message);
				$this->core->PrestigeSocietyLang->setLang($sender, 1);

				return;
			default:
				$lang = $this->core->PrestigeSocietyLang->getLang($sender);
				switch($lang){
					case 0:
						$message = "&6[!] &cUnknown argument! &eUse: &6/lang <lang/info> &ewhere <lang> is: &6en/ro";
						$message = RandomUtils::colorMessage($message);
						$sender->sendMessage($message);

						return;
					case 1:
						$message = "&6[!] &cArgument necunoscut! &eFoloseste: &6/lang <lang/info> &eunde <lang> is: &6en/ro";
						$message = RandomUtils::colorMessage($message);
						$sender->sendMessage($message);

						return;
				}

				return;
		}
	}

	/**
	 * @return Plugin
	 */
	public function getPlugin(): Plugin{
		return $this->core;
	}
}