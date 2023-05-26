<?php

namespace PrestigeSociety\Chat\Broadcaster;

use pocketmine\scheduler\PluginTask;
use PrestigeSociety\Core\PrestigeSocietyCore;
use PrestigeSociety\Core\Utils\RandomUtils;

class BroadcasterTask extends PluginTask {
	/** @var PrestigeSocietyCore */
	private $core;
	/** @var int */
	private $msgNumber = 0;

	/**
	 *
	 * BroadcasterTask constructor.
	 *
	 * @param PrestigeSocietyCore $c
	 *
	 */
	public function __construct(PrestigeSocietyCore $c){
		parent::__construct($c);
		$this->core = $c;
	}

	/**
	 *
	 * @param int $currentTick
	 *
	 */
	public function onRun(int $currentTick){
		$ro = [
			'\n&5>&7------------- &dCHPE &l&5» &r&dFactions &7-------------&5<&r\n\n &7Daca doresti sa achizitionezi credite folosind metoda PayPal acceseaza &5store.chpe.us\n &7Daca doresti sa achizitionezi credite prin alta metoda contacteaza-l pe &5MRN#1358 &7pe discord\n &7Pentru mai multe informatii foloseste &5/shop &7-> &5info\n\n&5>&7------------- &dCHPE &l&5» &r&dFactions &7-------------&5<&r\n',
			'\n&5>&7------------- &dCHPE &l&5» &r&dFactions &7-------------&5<&r\n\n &7Voteaza-ne serverul zilnic pentru a castiga premii &5vote.chpe.us\n &7Te asteptam in grupul de discord pentru a afla toate informatiile in timp real &5discord.chpe.us\n &7Iti multumim ca te joci pe &5play.chpe.us\n\n&5>&7------------- &dCHPE &l&5» &r&dFactions &7-------------&5<&r\n',
			'\n&5>&7------------- &dCHPE &l&5» &r&dFactions &7-------------&5<&r\n\n &7Limita intr-o factiune este de 5 jucatori. &7Mareste aceasta limita din &5Premium Shop\n &7Vrei sa ajungi cool pe server si sa se uite toti jucatorii la tine?\n &7Atunci cumpara &lLSD acces &r&7si &lRainbow Nickname&r&7 din &5Premium Shop\n\n&5>&7------------- &dCHPE &l&5» &r&dFactions &7-------------&5<&r\n',
			'\n&5>&7------------- &dCHPE &l&5» &r&dFactions &7-------------&5<&r\n\n&7 Munceste pentru a face bani folosind comanda &5/jobs\n &7Vizioneaza toate warpurile de pe server folodin comanda &5/warp list\n &7Pentru a seta un home foloseste comanda &5/home set\n\n&5>&7------------- &dCHPE &l&5» &r&dFactions &7-------------&5<&r',
		];
		$eng = [
			'\n&5>&7------------- &dCHPE &l&5» &r&dFactions &7-------------&5<&r\n\n &7If you want to purchase credits using Paypal acces our store: &5store.chpe.us\n &7If you want to purchase credits using other methods of payment contact &5MRN#1358 &7on discord\n &7For more informations use &5/shop &7-> &5info\n\n&5>&7------------- &dCHPE &l&5» &r&dFactions &7-------------&5<&r\n',
			'\n&5>&7------------- &dCHPE &l&5» &r&dFactions &7-------------&5<&r\n\n &7Vote our server daily to win awesome prizes &5vote.chpe.us\n &7Join our discord to get all the new info about our server! &5discord.chpe.us\n &7Thank you for playing on &5play.chpe.us\n\n&5>&7------------- &dCHPE &l&5» &r&dFactions &7-------------&5<&r\n',
			'\n&5>&7------------- &dCHPE &l&5» &r&dFactions &7-------------&5<&r\n\n &7Player limit in a factions is 5 members. &7You can buy faction slots from &5Premium Shop\n &7You want to be cool and be in the attention of players?!\n &7Then buy &lLSD acces &r&7and &lRainbow Nickname&r&7 from &5Premium Shop\n\n&5>&7------------- &dCHPE &l&5» &r&dFactions &7-------------&5<&r\n',
			'\n&5>&7------------- &dCHPE &l&5» &r&dFactions &7-------------&5<&r\n\n&7 You can earn money by working &5/jobs\n &7Check all the warps on the server using &5/warp list\n &7If u want to set a home just use &5/home set\n\n&5>&7------------- &dCHPE &l&5» &r&dFactions &7-------------&5<&r',
		];
		try{
			foreach($this->core->getServer()->getOnlinePlayers() as $player){
				$lang = $this->core->PrestigeSocietyLang->getLang($player);
				$message = 'unknown';
				switch($lang){
					case 0:
						$message = $eng;
						break;
					case 1:
						$message = $ro;
						break;
				}
				$msg = RandomUtils::colorMessage($message[$this->msgNumber]);
				$msg = RandomUtils::broadcasterTextReplacer($msg);
				$msg = str_replace("\\n", "\n", $msg);
				$player->sendMessage($msg);
			}
			++$this->msgNumber;
			if($this->msgNumber == count($this->core->getMessages()["broadcaster_messages"])){
				$this->msgNumber = 0;
			}
		}catch(\Exception $e){
			$this->core->PocketLegionLogger->addLogger(
				RandomUtils::textOptions("Restarter error (Line: " . $e->getLine() . ", File: " . $e->getFile() . ", Date: " . date("jS of F, Y") . ""));
		}
	}
}