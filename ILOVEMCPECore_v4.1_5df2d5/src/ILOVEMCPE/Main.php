<?php


namespace ILOVEMCPE;


use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use pocketmine\utils\TextFormat as C;

class Main extends PluginBase implements Listener {

	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->getLogger()->info(C::AQUA . "
  ____      _          ____  _____
 / ___|   _| |__   ___|  _ \| ____|
| |  | | | | '_ \ / _ \ |_) |  _|
| |__| |_| | |_) |  __/  __/| |___
 \____\__,_|_.__/ \___|_|   |_____|         
 / ___|___  _ __ ___ 
| |   / _ \| '__/ _ \
| |__| (_) | | |  __/
 \____\___/|_|  \___|

 * Developed by MateiGamingYTB  /  Little part by MCPE_Expert
		");
	}

	public function onDisable(){
		$this->getLogger()->info(TextFormat::RED . "Oprit! / Stopped!");
	}

	public function onJoin(PlayerJoinEvent $event){
		$player = $event->getPlayer();
		$name = $player->getName();
		$event->setJoinMessage(C::GRAY . "[" . C::GREEN . "+" . C::GRAY . "] " . C::WHITE . $name);
	}

	public function onQuit(PlayerQuitEvent $event){
		$player = $event->getPlayer();
		$name = $player->getName();
		$event->setQuitMessage(C::GRAY . "[" . C::DARK_RED . "-" . C::GRAY . "] " . C::WHITE . $name);
	}

	public function onCommand(CommandSender $sender, Command $command, $label, array $args): bool{
		if(strtolower($command->getName()) === 'donez'){
			$sender->sendMessage(TextFormat::RED . "----------------------------------------------");
			$sender->sendMessage(TextFormat::GOLD . "                       §7[[§o§b»§5ILOVEMCPE§b«§7]]§r ");
			$sender->sendMessage(TextFormat::BLUE . "§eVisiteaza §5forumul §nostru §9la§7: §awww§7.§5ilovemc§7.§6lolforum§7.§bnet ");
			$sender->sendMessage(TextFormat::BLUE . "§eS-au cumpara §erankuri §ede §baici§7: §aCOSMMING SOON ");
			$sender->sendMessage(TextFormat::RED . "----------------------------------------------");
			$sender->sendMessage(TextFormat::BLUE . "§4Rankuri §7& §5Preturi ");
			$sender->sendMessage(TextFormat::RED . "§6VIP§b=§52 euro ");
			$sender->sendMessage(TextFormat::GOLD . "§6VIP§6+§b=§54 euro ");
			$sender->sendMessage(TextFormat::BLUE . "§3MVP§b=§53 euro§7, §cInsa poate fi luat §l§4FREE§r §6daca omori §f5000 §ejucatori§4!! ");
			$sender->sendMessage(TextFormat::BLUE . "§3MVP§9+§b=§55 euro§7, §eAcesta nu poate fi primit la jucatori omorati si nici la §5UP§7, §5acesta de poate cumpara doar prin intermediul banilor§4:(");
			$sender->sendMessage(TextFormat::BLUE . "§5Admin§b=§56 euro ");
			$sender->sendMessage(TextFormat::BLUE . "§bModeratorb=§55 euro ");
			$sender->sendMessage(TextFormat::RED . "§aLegendary§b=§58 euro ");
			$sender->sendMessage(TextFormat::RED . "§4OP§b=§57 euro ");
			$sender->sendMessage(TextFormat::BLUE . "§2Builder§b=§58 euro ");
			$sender->sendMessage(TextFormat::BLUE . "§8[[§eUber§6VIP§8]]§b=§55 euro ");
			$sender->sendMessage(TextFormat::GOLD . "§ahelper§b=§eIntreba-ti §cOwnerul §6Principal §8[§5MateiGamingYTB§8] ");
			$sender->sendMessage(TextFormat::BLUE . "§bULTRA§b=§57 euro ");
			$sender->sendMessage(TextFormat::BLUE . "§8[[§6*§fYou§4Tuber§6*§8]]§b=§ePentru acest §5rank§a contacteaza §4Ownerul §6Principal§4!! ");
			$sender->sendMessage(TextFormat::RED . "§a§b=§5Nu se da, decat celor de incredere ");
			$sender->sendMessage(TextFormat::RED . "§cCo-Owner§b=§511 euro ");
			$sender->sendMessage(TextFormat::GOLD . "§4Owner§b=§515 euro ");
			$sender->sendMessage(TextFormat::GOLD . "§8[[§6<§3Dev§6>§8]]§b=§4NU SE DA NIMANUI§7,§6 E CEL MAI MARE RANK DE PE SERVER DECAT§4 OWNERII §6PRINCIPALI IL AU§4!! ");
			$sender->sendMessage(TextFormat::BLUE . "§aDaca doriti detalii despre rankuri §bgen§7: §6permisiuni§7, §5kituri§7, §3etc§7, §econtactati-l pe §5MateiGamingYTB §4pe Discord la §7(§9LightEnergy#0871§7) ");

			return true;
		}
	}
}
