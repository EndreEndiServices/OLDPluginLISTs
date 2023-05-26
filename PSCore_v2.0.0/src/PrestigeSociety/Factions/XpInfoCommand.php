<?php

namespace PrestigeSociety\Factions;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use PrestigeSociety\Core\PrestigeSocietyCore;
use PrestigeSociety\Core\Utils\RandomUtils;
use PrestigeSociety\UIForms\CustomForm;

class XpInfoCommand extends Command implements PluginIdentifiableCommand {

	/** @var PrestigeSocietyCore */
	protected $plugin;

	/**na
	 *
	 * StatsCommand constructor.
	 *
	 * @param PrestigeSocietyCore $c
	 *
	 */
	public function __construct(PrestigeSocietyCore $c){
		parent::__construct('xpinfo', 'About XP System', '/xpinfo', ['xpi', 'expinfo']);
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
		if($sender instanceof Player){
			$lang = $this->plugin->PrestigeSocietyLang->getLang($sender);
			$message = 'unknown';
			switch($lang){
				case 0:
					$message = "§e§lXP Informations§r\n\n§7The virtual XP is displayed in chat.\nTo level up\nyou need experience §8[§d/stats§8]§7.\n§7There are multiple ways to earn experience:\n§7every 5 minutes you earn 10-20 experiente,\nUsing the following jobs §dMiner§7, §dFarmer§7, §dLumberJack§7 you get\n between 1-3 experience, and using\n the job §dCowBoy§7 you get between \n150 and 200 experience per bucket.\n\n§e§oGold Members are getting §225 percent §emore XP§r\n§o§eVIP Members are getting §235 percent §emore XP§r";
					break;
				case 1:
					$message = "§e§lXP Informations§r\n\n§7XP-ul virtual pe server este afisat in chat.\nPentru a putea face Level UP\naveti nevoie de experienta §8[§d/stats§8]§7.\n§7Sunt mai multe moduri in care puteti acumula experienta:\n§7la fiecare 5 minute primiti intre 10 si 20 experienta,\nla joburile §dMiner§7, §dFarmer§7, §dLumberJack§7 primiti\n o suma de experienta intre 1 si 3, iar la\n jobul §dCowBoy§7 primiti intre\n150 si 200 experienta pe galeata.\n\n§e§oMembrii Gold au cu §225 la suta §emai mult XP§r\n§o§eMembrii VIP au cu §235 la suta §emai mult XP§r";
					break;
			}
			$message = RandomUtils::colorMessage($message);
			$ui = new CustomForm();
			$ui->setId(100);
			$ui->setTitle(RandomUtils::colorMessage("&e&k|&r&5&lXP Info&r&k&e|"));
			$ui->setLabel($message);
			$ui->send($sender);

			return;
		}
	}

	/**
	 *
	 * @return PrestigeSocietyCore
	 *
	 */
	public function getPlugin(): Plugin{
		return $this->plugin;
	}
}