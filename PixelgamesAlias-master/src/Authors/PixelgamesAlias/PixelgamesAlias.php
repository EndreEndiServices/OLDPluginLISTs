<?php

namespace Authors\PixelgamesAlias;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\Player;
use pocketmine\IPlayer;
use pocketmine\OfflinePlayer;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

class PixelgamesAlias extends PluginBase implements Listener {
    
    public function onLoad() {
        $this->getLogger()->info("Laden...");
    }
    
    public function onEnable() {
        $this->getLogger()->info("Aktiviert");
        
        $config = new Config($this->getDataFolder()."config.yml", CONFIG::YAML, array(

			"CID/IP" => "CID",

			));

        $this->getServer()->getPluginManager()->registerEvents($this, $this);

		if(!is_dir($this->getDataFolder()."players/lastip")){

			@mkdir($this->getDataFolder()."players/lastip", 0777, true);

		}

		if(!is_dir($this->getDataFolder()."players/ip")){

			@mkdir($this->getDataFolder()."players/ip", 0777, true);

		}

		if(!is_dir($this->getDataFolder()."players/cid")){

			@mkdir($this->getDataFolder()."players/cid", 0777, true);

		}

		if(!is_dir($this->getDataFolder()."players/lastcid")){

			@mkdir($this->getDataFolder()."players/lastcid", 0777, true);

		}

    }

	public function onDisable() {
            $this->getLogger()->info("Deaktiviert");
        }

	public function onJoin(PlayerJoinEvent $event){

		$name = $event->getPlayer()->getDisplayName();

		$ip = $event->getPlayer()->getAddress();

		$cid = $event->getPlayer()->getClientId();

		if(is_file($this->getDataFolder()."players/lastcid/".$name[0]."/".$name.".yml")){

			unlink($this->getDataFolder()."players/lastcid/".$name[0]."/".$name.".yml");

			$name = $event->getPlayer()->getDisplayName();

			$cid = $event->getPlayer()->getClientId();

			@mkdir($this->getDataFolder()."players/lastcid/".$name[0]."", 0777, true);

			$lastcid = new Config($this->getDataFolder()."players/lastcid/".$name[0]."/".$name.".yml", CONFIG::YAML, array(

				"lastcid" => "".$cid."",

			));

			$lastcid->save();

			$cidfile = new Config($this->getDataFolder()."players/cid/".$cid.".txt", CONFIG::ENUM);

			$cidfile->set($name);

			$cidfile->save();

		}else{

			$name = $event->getPlayer()->getDisplayName();

			$cid = $event->getPlayer()->getClientId();

			@mkdir($this->getDataFolder()."players/lastcid/".$name[0]."", 0777, true);

			$lastcid = new Config($this->getDataFolder()."players/lastcid/".$name[0]."/".$name.".yml", CONFIG::YAML, array(

				"lastcid" => "".$cid."",

				));

			$lastcid->save();

			$cidfile = new Config($this->getDataFolder()."players/cid/".$cid.".txt", CONFIG::ENUM);

			$cidfile->set($name);

			$cidfile->save();

		}

		if(is_file($this->getDataFolder()."players/lastip/".$name[0]."/".$name.".yml")){

			unlink($this->getDataFolder()."players/lastip/".$name[0]."/".$name.".yml");

			$name = $event->getPlayer()->getDisplayName();

			$ip = $event->getPlayer()->getAddress();

			@mkdir($this->getDataFolder()."players/lastip/".$name[0]."", 0777, true);

			$lastip = new Config($this->getDataFolder()."players/lastip/".$name[0]."/".$name.".yml", CONFIG::YAML, array(

				"lastip" => "".$ip."",

			));

			$lastip->save();

			@mkdir($this->getDataFolder()."players/ip/".$ip[0]."", 0777, true);

			$ipfile = new Config($this->getDataFolder()."players/ip/".$ip[0]."/".$ip.".txt", CONFIG::ENUM);

			$ipfile->set($name);

			$ipfile->save();

		}else{

			$name = $event->getPlayer()->getDisplayName();

			$ip = $event->getPlayer()->getAddress();

			@mkdir($this->getDataFolder()."players/lastip/".$name[0]."", 0777, true);

			$lastip = new Config($this->getDataFolder()."players/lastip/".$name[0]."/".$name.".yml", CONFIG::YAML, array(

				"lastip" => "".$ip."",

			));

			$lastip->save();

			@mkdir($this->getDataFolder()."players/ip/".$ip[0]."", 0777, true);

			$ipfile = new Config($this->getDataFolder()."players/ip/".$ip[0]."/".$ip.".txt", CONFIG::ENUM);

			$ipfile->set($name);

			$ipfile->save();

		}

	}

	public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{

		switch($command->getName()){

			case "alias":

				if(!isset($args[0])){

					$sender->sendMessage("§c[PGAlias] Benutzung: /alias <Benutzername>");
                                        $sender->sendMessage("§6[PGAlias] Benutzung: /aliasinfo");
                                        $sender->sendMessage("§6[PGAlias] Benutzung: /aliashelp");
					return true;

				}

				$config = new Config($this->getDataFolder()."config.yml", CONFIG::YAML);

				$switch = $config->get("CID/IP");

				if($switch == "CID"){

					$name = strtolower($args[0]);

					$player = $this->getServer()->getPlayer($name);

					if($player instanceOf Player){

						$cid = $player->getPlayer()->getClientId();

						$file = new Config($this->getDataFolder()."players/cid/".$cid.".txt");

						$names = $file->getAll(true);

						$names = implode(', ', $names);

						$sender->sendMessage(TextFormat::BLUE."[PGAlias] CID-Alias für ".$name."...");

						$sender->sendMessage(TextFormat::BLUE."[PGAlias] §o".$names."");

						return true;

					}else{

						if(!is_file($this->getDataFolder()."players/lastcid/".$name[0]."/".$name.".yml")){

							$sender->sendMessage(TextFormat::RED."[PGAlias] Fehler: Spieler offline oder keine CID-Daten für diesen Spieler gespeichert");

							return true;

						}else{

							$lastcid = new Config($this->getDataFolder()."players/lastcid/".$name[0]."/".$name.".yml");

							$cid = $lastcid->get("lastcid");

							$file = new Config($this->getDataFolder()."players/cid/".$cid.".txt");

							$names = $file->getAll(true);

							if($names == null){

								$sender->sendMessage(TextFormat::RED."[PGAlias] Fehler: Spieler offline oder keine LastCID-Daten für diesen Spieler gespeichert");

								return true;

							}else{

								$names = implode(', ', $names);

								$sender->sendMessage(TextFormat::BLUE."[PGAlias] LastCID-Alias für ".$name."...");

								$sender->sendMessage(TextFormat::BLUE."[PGAlias] §o".$names."");

								return true;

							}

						}

					}

				}elseif($switch == "IP"){

					$name = strtolower($args[0]);

					$player = $this->getServer()->getPlayer($name);

					if($player instanceOf Player){

						$ip = $player->getPlayer()->getAddress();

						$file = new Config($this->getDataFolder()."players/ip/".$ip[0]."/".$ip.".txt");

						$names = $file->getAll(true);

						$names = implode(', ', $names);

						$sender->sendMessage(TextFormat::BLUE."[PGAlias] IP-Alias für ".$name."...");

						$sender->sendMessage(TextFormat::BLUE."[PGAlias] §o".$names."");

						return true;

					}else{

						if(!is_file($this->getDataFolder()."players/lastip/".$name[0]."/".$name.".yml")){

							$sender->sendMessage(TextFormat::RED."[PGAlias] Fehler: Spieler offline oder keine IP-Daten für diesen Spieler gespeichert");

							return true;

						}else{

							$lastip = new Config($this->getDataFolder()."players/lastip/".$name[0]."/".$name.".yml");

							$ip = $lastip->get("lastip");

							$file = new Config($this->getDataFolder()."players/ip/".$ip[0]."/".$ip.".txt");

							$names = $file->getAll(true);

							if($names == null){

								$sender->sendMessage(TextFormat::RED."[PGAlias] Fehler: Spieler offline oder keine LastIP-Daten für diesen Spieler gespeichert");

								return true;

							}else{

							$names = implode(', ', $names);

							$sender->sendMessage(TextFormat::BLUE."[PGAlias] LastIP-Alias für ".$name."...");

							$sender->sendMessage(TextFormat::BLUE."[PGAlias] §o".$names."");

							return true;

							}

						}

					}

				}else{

					$sender->sendMessage(TextFormat::DARK_RED."[PGAlias] Fehler: Die Config ist fehlerhaft eingestellt!");

					return true;

				}

				return true;
                                
                        case "aliasinfo":
                                if (!isset ($args [0])) {      
                                    $sender->sendMessage("§e--------------------------------------");
                                    $sender->sendMessage("§ePlugin von ZacHack, iStrafeNubzHDyt");
                                    $sender->sendMessage("§bName: PixelgamesAlias");
                                    $sender->sendMessage("§bOriginal: Alias");
                                    $sender->sendMessage("§bVersion: 2.4#");
                                    $sender->sendMessage("§bFür PocketMine-API: 3.0.0-ALPHA11");
                                    $sender->sendMessage("§6Permissions: pgalias, pgalias.command.alias, pgalias.command.setalias, pgalias.command.aliasip, pgalias.command.aliascid, pgalias.command.checkalias, pgalias.command.aliasinfo, pgalias.command.aliashelp");
                                    $sender->sendMessage("§eSpeziell für PIXELGAMES entwickelt");
                                    $sender->sendMessage("§e--------------------------------------");
                                    return true;  
                                }
                                
                        case "aliashelp":
                                if (!isset($args [0])) {
                                    $sender->sendMessage("§9---§aAlias-Plugin§9---");
                                    $sender->sendMessage("§a/alias <Benutzername> §b-> Anzeige aller Benutzernamen, die ein Spieler verwendet hat (CID/IP)");
                                    $sender->sendMessage("§a/setalias <cid|ip> §b-> Verändert die Einstellung von Alias");
                                    $sender->sendMessage("§a/aliasip <Benutzername> §b-> Benutzt IP-Daten");
                                    $sender->sendMessage("§a/aliascid <Benutzername> §b-> Benutzt CID-Daten");
                                    $sender->sendMessage("§a/checkalias §b-> Zeigt die Einstellung von Alias an");
                                    $sender->sendMessage("§6/aliashelp §b-> Zeigt dieses Hilfemenü an");
                                    $sender->sendMessage("§6/aliasinfo §b-> Zeigt Details über das Plugin");
                                    return true;
                                    
                                }

			case "setalias":

				if(!isset($args[0])){

					$sender->sendMessage(TextFormat::RED."[PGAlias] Benutzung: ".$command->getUsage()."");

					return true;

				}

				$args[0] = strtoupper($args[0]);

				$config = new Config($this->getDataFolder()."config.yml", CONFIG::YAML);

				unlink($this->getDataFolder()."config.yml");

				$config = new Config($this->getDataFolder()."config.yml", CONFIG::YAML, array(

				"CID/IP" => "".$args[0]."",

				));

				$sender->sendMessage(TextFormat::GREEN."[PGAlias] Du hast die Einstellung für Alias erfolgreich auf §b".$args[0]." §agesetzt!");

				return true;

			case "aliasip":

				if(!isset($args[0])){

					$sender->sendMessage(TextFormat::RED."[PGAlias] Benutzung: /aliasip <Benutzername>");

					return true;

				}

				$name = strtolower($args[0]);

				$player = $this->getServer()->getPlayer($name);

				if($player instanceOf Player){

					$ip = $player->getPlayer()->getAddress();

					$file = new Config($this->getDataFolder()."players/ip/".$ip[0]."/".$ip.".txt");

					$names = $file->getAll(true);

					$names = implode(', ', $names);

					$sender->sendMessage(TextFormat::BLUE."[PGAlias] IP-Alias für ".$name."...");

					$sender->sendMessage(TextFormat::BLUE."[PGAlias] §o".$names."");

					return true;

				}else{

					if(!is_file($this->getDataFolder()."players/lastip/".$name[0]."/".$name.".yml")){

						$sender->sendMessage(TextFormat::RED."[PGAlias] Fehler: Spieler offline oder keine IP-Daten für diesen Spieler gespeichert");

						return true;

					}else{

						$lastip = new Config($this->getDataFolder()."players/lastip/".$name[0]."/".$name.".yml");

						$ip = $lastip->get("lastip");

						$file = new Config($this->getDataFolder()."players/ip/".$ip[0]."/".$ip.".txt");

						$names = $file->getAll(true);

						if($names == null){

							$sender->sendMessage(TextFormat::RED."[PGAlias] Fehler: Spieler offline oder keine LastIP-Daten für diesen Spieler gespeichert");

							return true;

						}else{

						$names = implode(', ', $names);

						$sender->sendMessage(TextFormat::BLUE."[PGAlias] LastIP-Alias für ".$name."...");

						$sender->sendMessage(TextFormat::BLUE."[PGAlias] §o".$names."");

						return true;

						}

					}

				}

				return true;

			case "aliascid":

				if(!isset($args[0])){

					$sender->sendMessage(TextFormat::RED."[PGAlias] Benutzung: /aliascid <Benutzername>");

					return true;

				}

				$name = strtolower($args[0]);

				$player = $this->getServer()->getPlayer($name);

				if($player instanceOf Player){

					$cid = $player->getPlayer()->getClientId();

					$file = new Config($this->getDataFolder()."players/cid/".$cid.".txt");

					$names = $file->getAll(true);

					$names = implode(', ', $names);

					$sender->sendMessage(TextFormat::BLUE."[PGAlias] CID-Alias für ".$name."...");

					$sender->sendMessage(TextFormat::BLUE."[PGAlias] §o".$names."");

					return true;

				}else{

					if(!is_file($this->getDataFolder()."players/lastcid/".$name[0]."/".$name.".yml")){

						$sender->sendMessage(TextFormat::RED."[PGAlias] Fehler: Spieler offline oder keine CID-Daten für diesen Spieler gespeichert");

						return true;

					}else{

						$lastcid = new Config($this->getDataFolder()."players/lastcid/".$name[0]."/".$name.".yml");

						$cid = $lastcid->get("lastcid");

						$file = new Config($this->getDataFolder()."players/cid/".$cid.".txt");

						$names = $file->getAll(true);

						if($names == null){

							$sender->sendMessage(TextFormat::RED."[PGAlias] Fehler: Spieler offline oder keine LastCID-Daten für diesen Spieler gespeichert");

							return true;

						}else{

							$names = implode(', ', $names);

							$sender->sendMessage(TextFormat::BLUE."[PGAlias] LastCID-Alias für ".$name."...");

							$sender->sendMessage(TextFormat::BLUE."[PGAlias] §o".$names."");

							return true;

						}

					}

				}

				return true;

			case "checkalias":

				$config = new Config($this->getDataFolder()."config.yml");

				$setting = $config->get("CID/IP");

				$sender->sendMessage(TextFormat::GREEN."[PGAlias] Die Einstellung für Alias steht auf §b".$setting."");

				return true;

		}

	}

}
    
        
    
