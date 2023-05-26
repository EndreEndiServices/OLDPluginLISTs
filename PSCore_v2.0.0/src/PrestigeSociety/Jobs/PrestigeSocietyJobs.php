<?php

namespace PrestigeSociety\Jobs;

use pocketmine\Player;
use PrestigeSociety\Core\PrestigeSocietyCore;
use PrestigeSociety\Core\Utils\RandomUtils;
use PrestigeSociety\UIForms\CustomForm;
use PrestigeSociety\UIForms\SimpleForm;

class PrestigeSocietyJobs {
	public $MENU = 50;
	/** @var \SQLite3 */
	protected $db;
	/** @var PrestigeSocietyCore */
	protected $core;

	/**
	 *
	 * PrestigeSocietyVaults constructor.
	 *
	 * @param \SQLite3 $db
	 *
	 */
	public function __construct(\SQLite3 $db, PrestigeSocietyCore $core){
		$this->db = $db;
		$this->core = $core;

		$db->exec("CREATE TABLE IF NOT EXISTS jobs (original VARCHAR, jobname VARCHAR)");
	}

	/**
	 *
	 * @param Player $player
	 *
	 * @return mixed
	 *
	 */
	public function getJob(Player $player){
		if($this->hasJob($player)){
			$query = $this->db->query("SELECT jobname FROM jobs WHERE original = '{$player->getXuid()}'");
			$fetch = $query->fetchArray(SQLITE3_ASSOC);

			return $fetch['jobname'];
		}

		return null;
	}

	/**
	 *
	 * @param Player $player
	 *
	 * @return bool
	 *
	 */
	public function hasJob(Player $player){
		$query = $this->db->query("SELECT jobname FROM jobs WHERE original = {$player->getXuid()}");
		$fetch = $query->fetchArray(SQLITE3_ASSOC);

		if(is_array($fetch) && count($fetch) != 0){
			return true;
		}

		return false;
	}

	/**
	 *
	 * @param Player $player
	 * @param        $formData
	 * @param int $formId
	 *
	 */
	public function handleFormResponse(Player $player, $formData, int $formId){
		switch($formId){
			case $this->MENU:
				switch($formData){
					case 0:
						$this->core->PrestigeSocietyJobs->setJob($player, "LumberJack");
						$this->sendReceiveJobMessage($player, "LumberJack");
						break;
					case 1:
						$this->core->PrestigeSocietyJobs->setJob($player, "Miner");
						$this->sendReceiveJobMessage($player, "Miner");
						break;
					case 2:
						$this->core->PrestigeSocietyJobs->setJob($player, "Farmer");
						$this->sendReceiveJobMessage($player, "Farmer");
						break;
					case 3:
						$this->core->PrestigeSocietyJobs->setJob($player, "CowBoy");
						$this->sendReceiveJobMessage($player, "CowBoy");
						break;
					case 4:
						$this->sendInfo($player);
						break;
					case 5:
						$this->core->PrestigeSocietyJobs->resetJob($player);
						$this->sendNonJobMessage($player);
						break;
				}
				break;
		}
	}

	/**
	 *
	 * @param Player $player
	 * @param string $job
	 *
	 */
	public function setJob(Player $player, string $job){
		if(!$this->hasJob($player)){
			$q = $this->db->prepare("INSERT INTO jobs (original, jobname) VALUES (?, ?)");
			$q->bindValue(1, $player->getXuid());
			$q->bindParam(2, $job);
			$q->execute();
		}else{
			$q = $this->db->prepare("UPDATE jobs SET jobname = ? WHERE original = '{$player->getXuid()}'");
			$q->bindParam(1, $job);
			$q->execute();
		}
	}

	public function sendReceiveJobMessage(Player $player, string $job): void{
		$player->sendMessage("§7» §3You have successfully selected Job: §b" . $job . "§3!");
	}

	public function sendInfo(Player $player){
		$ui = new CustomForm();
		$ui->setId(51);
		$ui->setTitle(RandomUtils::colorMessage("&e&k|&r&5&lInfo Jobs&r&k&e|"));
		$lang = $this->core->PrestigeSocietyLang->getLang($player);
		$message = 'unknown';
		switch($lang){
			case 0:
				$message = "Hello!\n" .
					"            Welcome to Info Jobs!\n\n" .
					"§c» §aLumberJack:\n" .
					"§eWith that job you can get money if you cut wood, any type of wood.\n" .
					"§dThe sum received per block: §77.5$\n\n" .
					"§c» §aMiner: \n" .
					"§eIf you select this job, you need to break Cobblestone or any kind of ore to earn money.\n" .
					"§dThe sum received per block: §75$\n\n" .
					"§c» §aFarmer: \n" .
					"§eYou can earn money with this job if you cultivate seeds, any kind of seeds.\n" .
					"§dThe sum received per block: §72.5$\n\n" .
					"§c» §aCowBoy: \n" .
					"§eThis is a really fun job.\n" .
					"§eYou need to go to the mine which is located near the barn\n" .
					"§eAnd break 16x ore 32x End Stone Brick to earn 1x or 2x Bucket(s).\n" .
					"§eWith the Bucket(s) you need to go to the barn and hit the cow to earn a Bucket Milk\n" .
					"§eAfter that, you need to go to the second floor and give the Bucket MIlk to the NPC.\n" .
					"§dThe sum received per Milk Bucked: §7250$\n\n";
				break;
			case 1:
				$message = "            Bun venit la Jobs Info!\n\n" .
					"§c» §aLumberJack:\n" .
					"§eDaca selectezi acest job, pentru a face banii trebuie sa spargi lemn (orice lemn).\n" .
					"§dBanii primiti pe block: §77.5$\n\n" .
					"§c» §aMiner: \n" .
					"§eDaca selectezi acest job, pentru a face banii trebuie sa spargi cobblestone sau ore-uri.\n" .
					"§dBanii primiti pe block: §75$\n\n" .
					"§c» §aFarmer: \n" .
					"§eDaca selectezi acest job, pentru a face banii trebuie sa colectezi seminte (dupa ce sunt facute).\n" .
					"§dBanii primiti pe samanta: §72.5$\n\n" .
					"§c» §aCowBoy: \n" .
					"§eAcest job este unul distractiv.\n" .
					"§eTrebuie sa te duci la mina de langa hambar\n" .
					"§eSi sa spargi 16x End Stone Bricks sau 32x End stone bricks pentru a primi 1x sau 2x Bucket(s).\n" .
					"§eCu Bucket(s) te duci la hambar si lovesti vaca ca sa primesti galeata cu lapte.\n" .
					"§eDupa aceea te duci la etaju 2 din hambar si apesi cu galeata pe NPC.\n" .
					"§dBanii primiti pe galeata cu lapte: §7250$\n\n";
				break;
		}
		$content = RandomUtils::colorMessage($message);
		$ui->setLabel($content);
		$ui->send($player);

		return true;
	}

	/**
	 *
	 * @param Player $player
	 *
	 * @return bool
	 *
	 */
	public function resetJob(Player $player){
		if($this->hasJob($player)){
			$this->db->querySingle("DELETE FROM jobs WHERE original = '{$player->getXuid()}'");

			return true;
		}

		return false;
	}

	public function sendNonJobMessage(Player $player): void{
		$player->sendMessage("§7» §3From now, you don't have One Job §3!");
	}

	public function showMenu(Player $player){
		$ui = new SimpleForm();
		$ui->setId($this->MENU);
		$ui->setTitle(RandomUtils::colorMessage("&e&k|&r&5&lJobs&r&k&e|"));
		$content = "";
		$lang = $this->core->PrestigeSocietyLang->getLang($player);
		$message = 'unknown';
		switch($lang){
			case 0:
				$message = "                       Hello!\n" .
					"           Welcome to Job Category!\n" .
					"    Select what you want to do from now.";
				break;
			case 1:
				$message = "                       Salut!\n" .
					"        Ai ajuns la categoria de Joburi!\n" .
					"  Selecteaza ce vrei sa faci in continuare.";
				break;
		}
		$content .= RandomUtils::colorMessage($message);
		$ui->setContent($content);
		$ui->setButton(RandomUtils::colorMessage(
			"LumberJack"
		), "https://i.imgur.com/uWmtrax.png");
		$ui->setButton(RandomUtils::colorMessage(
			"Miner"
		), "https://i.imgur.com/XFCYdCz.png");
		$ui->setButton(RandomUtils::colorMessage(
			"Farmer"
		), "https://i.imgur.com/otMDlEU.png");
		$ui->setButton(RandomUtils::colorMessage(
			"CowBoy"
		), "https://i.imgur.com/otMDlEU.png");
		$ui->setButton(RandomUtils::colorMessage(
			"Info"
		), "https://i.imgur.com/nujWKR3.png");
		$ui->setButton(RandomUtils::colorMessage(
			"Without Job"
		), "https://i.imgur.com/YXBNPBc.png");
		$ui->send($player);

		return true;
	}
}