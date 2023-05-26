<?php

namespace FactionsPro;

use pocketmine\plugin\PluginBase;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\event\Listener;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\Player;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\utils\TextFormat;
use pocketmine\scheduler\PluginTask;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\utils\Config;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\math\Vector3;
use pocketmine\level\level;
use pocketmine\level\Position;
class FactionCommands {

	// ASCII Map
	CONST MAP_WIDTH = 48;
	CONST MAP_HEIGHT = 8;
	CONST MAP_HEIGHT_FULL = 17;

	CONST MAP_KEY_CHARS = "\\/#?ç¬£$%=&^ABCDEFGHJKLMNOPQRSTUVWXYZÄÖÜÆØÅ1234567890abcdeghjmnopqrsuvwxyÿzäöüæøåâêîûô";
	CONST MAP_KEY_WILDERNESS = TextFormat::GRAY . "-";
	CONST MAP_KEY_SEPARATOR = TextFormat::AQUA . "+";
	CONST MAP_KEY_OVERFLOW = TextFormat::WHITE . "-" . TextFormat::WHITE; # ::MAGIC?
	CONST MAP_OVERFLOW_MESSAGE = self::MAP_KEY_OVERFLOW . ": Prea multe Factiuni (>" . 107 . ") pe Mapa asta.";
	
	public $plugin;
	
	public function __construct(FactionMain $pg) {
		$this->plugin = $pg;
	}
	
	public function onCommand(CommandSender $sender, Command $command, $label, array $args) {
		if($sender instanceof Player) {
			$player = $sender->getPlayer()->getName();
			if(strtolower($command->getName('f'))) {
				if(empty($args)) {
					$sender->sendMessage($this->plugin->formatMessage(" §7Foloseste: §a/f help §5Pentru a vedea lista de comenzi"));
					return true;
				}
				if(count($args == 2)) {
					
					///////////////////////////////// WAR /////////////////////////////////
					
					if($args[0] == "war") {
						if(!isset($args[1])) {
							$sender->sendMessage($this->plugin->formatMessage("§7Foloseste: §a/f war§7 <numele_factiuni:tp>"));
							return true;
						}
						if(strtolower($args[1]) == "tp") {
							foreach($this->plugin->wars as $r => $f) {
								$fac = $this->plugin->getPlayerFaction($player);
								if($r == $fac) {
									$x = mt_rand(0, $this->plugin->getNumberOfPlayers($fac) - 1);
									$tper = $this->plugin->war_players[$f][$x];
									$sender->teleport($this->plugin->getServer()->getPlayerByName($tper));
									return;
								}
								if($f == $fac) {
									$x = mt_rand(0, $this->plugin->getNumberOfPlayers($fac) - 1);
									$tper = $this->plugin->war_players[$r][$x];
									$sender->teleport($this->plugin->getServer()->getPlayer($tper));
									return;
								}
							}
							$sender->sendMessage("§5Trebuie sa fii intr-un razboi pentru a face acest lucru!");
							return true;
						}
						if(!(ctype_alnum($args[1]))) {
							$sender->sendMessage($this->plugin->formatMessage("§5Ai posibilitatea sa utilizati numai litere si cifre!"));
							return true;
						}
						if(!$this->plugin->factionExists($args[1])) {
							$sender->sendMessage($this->plugin->formatMessage("§5Factiunea nu exista"));
							return true;
						}
						if(!$this->plugin->isInFaction($sender->getName())) {
							$sender->sendMessage($this->plugin->formatMessage("§5Trebuie sa fii intr-o fractiune pentru a face acest lucru!"));
							return true;
						}
						if(!$this->plugin->isLeader($player)){
							$sender->sendMessage($this->plugin->formatMessage("§5Doar liderii factiuni pot începe un război!"));
							return true;
						} 
						if(!$this->plugin->areEnemies($this->plugin->getPlayerFaction($player),$args[1])){
                            $sender->sendMessage($this->plugin->formatMessage("§5Factiunea lui nu este inamica cu $args[1]!"));
                            return true;
                        } else {
							$factionName = $args[1];
							$sFaction = $this->plugin->getPlayerFaction($player);
							foreach($this->plugin->war_req as $r => $f) {
								if($r == $args[1] && $f == $sFaction) {
									foreach($this->plugin->getServer()->getOnlinePlayers() as $p) {
										$task = new FactionWar($this->plugin, $r);
										$handler = $this->plugin->getServer()->getScheduler()->scheduleDelayedTask($task, 20 * 60 * 2);
										$task->setHandler($handler);
										$p->sendMessage("§5Razboiul dinttre $factionName si $sFaction a inceput");
										if($this->plugin->getPlayerFaction($p->getName()) == $sFaction) {
											$this->plugin->war_players[$sFaction][] = $p->getName();
										}
										if($this->plugin->getPlayerFaction($p->getName()) == $factionName) {
											$this->plugin->war_players[$factionName][] = $p->getName();
										}
									}
									$this->plugin->wars[$factionName] = $sFaction;
									unset($this->plugin->war_req[strtolower($args[1])]);
									return true;
								}
							}
							$this->plugin->war_req[$sFaction] = $factionName;
							foreach($this->plugin->getServer()->getOnlinePlayers() as $p) {
								if($this->plugin->getPlayerFaction($p->getName()) == $factionName) {
									if($this->plugin->getLeader($factionName) == $p->getName()) {
										$p->sendMessage("§Pe5ntru a incepemu un razboi §a 'Foloseste /f war $sFaction' §5incepe!");
										$sender->sendMessage("§7Razboi determinat!");
										return true;
									}
								}
							}
							$sender->sendMessage("§5Liderul factiunii este offline.");
							return true;
						}
					}
						
					/////////////////////////////// CREATE ///////////////////////////////
					
					if($args[0] == "create") {
						if(!isset($args[1])) {
							$sender->sendMessage($this->plugin->formatMessage("§5Foloseste: §a/f create <numele_factiunii> §5Pentru a crea o factiune"));
							return true;
						}
						if(!(ctype_alnum($args[1]))) {
							$sender->sendMessage($this->plugin->formatMessage("§7Poti folosi numai litere si cifre!"));
							return true;
						}
						if($this->plugin->isNameBanned($args[1])) {
							$sender->sendMessage($this->plugin->formatMessage("§5Acest nume este interzis!"));
							return true;
						}
						if($this->plugin->factionExists($args[1])) {
							$sender->sendMessage($this->plugin->formatMessage("§5Acest nume este deja aexistent in sistem!"));
							return true;
						}
						if(strlen($args[1]) > $this->plugin->prefs->get("MaxFactionNameLength")) {
							$sender->sendMessage($this->plugin->formatMessage("§5Numele este prea lung!"));
							return true;
						}
						if($this->plugin->isInFaction($sender->getName())) {
							$sender->sendMessage($this->plugin->formatMessage("§5Trebuie sa iesi din factiunea curenta"));
							return true;
						} else {
							$factionName = $args[1];
							$rank = "Leader";
							$stmt = $this->plugin->db->prepare("Introducerea sau inlocuire in master (player, faction, rank) VALUES (:player, :faction, :rank);");
							$stmt->bindValue(":player", $player);
							$stmt->bindValue(":faction", $factionName);
							$stmt->bindValue(":rank", $rank);
							$result = $stmt->execute();
                            $this->plugin->updateAllies($factionName);
                            $this->plugin->setFactionPower($factionName, $this->plugin->prefs->get("TheDefaultPowerEveryFactionStartsWith"));
							$this->plugin->updateTag($sender->getName());
							$this->plugin->setBalance($factionName, $this->plugin->prefs->get("defaultFactionBalance", 0));
							$sender->sendMessage($this->plugin->formatMessage("§5Factiune creata, succes!", true));
							var_dump($this->plugin->db->query("Selecteaza * din balanta;")->fetchArray(SQLITE3_ASSOC));
							return true;
						}
					}
					
					/////////////////////////////// INVITE ///////////////////////////////
					
					if($args[0] == "invite") {
						if(!isset($args[1])) {
							$sender->sendMessage($this->plugin->formatMessage("§5Foloseste: §a/f invite <jucator> §5Pentru a invita"));
							return true;
						}
						if($this->plugin->isFactionFull($this->plugin->getPlayerFaction($player)) ) {
							$sender->sendMessage($this->plugin->formatMessage("§5Factiune plina."));
							return true;
						}
						$invited = $this->plugin->getServer()->getPlayerExact($args[1]);
                        if(!($invited instanceof Player)) {
							$sender->sendMessage($this->plugin->formatMessage("§5Jucator offline!"));
							return true;
						}
						if($this->plugin->isInFaction($invited) == true) {
							$sender->sendMessage($this->plugin->formatMessage("§5Jucatorul este deja intr-o alta factiune"));
							return true;
						}
						if($this->plugin->prefs->get("OnlyLeadersAndOfficersCanInvite")) {
                            if(!($this->plugin->isOfficer($player) || $this->plugin->isLeader($player))){
							    $sender->sendMessage($this->plugin->formatMessage("§5Doar liderul!"));
							    return true;
                            } 
						}
                        if($invited->getName() == $player){
                            
				            $sender->sendMessage($this->plugin->formatMessage("§5Nu te poți invita in propria factiune "));
                            return true;
                        }
						
				        $factionName = $this->plugin->getPlayerFaction($player);
				        $invitedName = $invited->getName();
				        $rank = "Member";
								
				        $stmt = $this->plugin->db->prepare("Introducerea sau inlocuirea de confirmare in (player, faction, invitedby, timestamp) VALUES (:player, :faction, :invitedby, :timestamp);");
				        $stmt->bindValue(":player", $invitedName);
				        $stmt->bindValue(":faction", $factionName);
				        $stmt->bindValue(":invitedby", $sender->getName());
				        $stmt->bindValue(":timestamp", time());
				        $result = $stmt->execute();
				        $sender->sendMessage($this->plugin->formatMessage("§a $invitedName §5Confirmat!", true));
				        $invited->sendMessage($this->plugin->formatMessage("§7Voce Esta convidado por §a $factionName. §7Foloseste §a/f accept §5pentru a intra in factiune§7,sau §a/f deny §5pentru a anula!", true));
						
					}
					
					/////////////////////////////// LEADER ///////////////////////////////
					
					if($args[0] == "leader") {
						if(!isset($args[1])) {
							$sender->sendMessage($this->plugin->formatMessage("§5Foloseste: /f leader <jucator> §5Pentru a da liderul altuia"));
							return true;
						}
						if(!$this->plugin->isInFaction($sender->getName())) {
							$sender->sendMessage($this->plugin->formatMessage("§5Trebuie acest lucru intr-o fractiune "));
                            return true;
						}
						if(!$this->plugin->isLeader($player)) {
							$sender->sendMessage($this->plugin->formatMessage("§Numai lideri au acces"));
                            return true;
						}
						if($this->plugin->getPlayerFaction($player) != $this->plugin->getPlayerFaction($args[1])) {
							$sender->sendMessage($this->plugin->formatMessage("§Adauga un colegiu mai intai!"));
                            return true;
						}		
						if(!($this->plugin->getServer()->getPlayerExact($args[1]) instanceof Player)) {
							$sender->sendMessage($this->plugin->formatMessage("§5Jucator offline!"));
                            return true;
						}
                        if($args[1] == $sender->getName()){
                            
				            $sender->sendMessage($this->plugin->formatMessage("§5Nu puteți transfera avantajul de a se"));
                            return true;
                        }
				        $factionName = $this->plugin->getPlayerFaction($player);
	
				        $stmt = $this->plugin->db->prepare("Introducere sau inlocuirea master (player, faction, rank) VALUES (:player, :faction, :rank);");
				        $stmt->bindValue(":player", $player);
						$stmt->bindValue(":faction", $factionName);
						$stmt->bindValue(":rank", "Member");
						$result = $stmt->execute();
	
						$stmt = $this->plugin->db->prepare("Introducere sau inlocuirea master (player, faction, rank) VALUES (:player, :faction, :rank);");
						$stmt->bindValue(":player", $args[1]);
						$stmt->bindValue(":faction", $factionName);
						$stmt->bindValue(":rank", "Leader");
				        $result = $stmt->execute();
	
	
						$sender->sendMessage($this->plugin->formatMessage("§Nu le mai da Lider!", true));
						$this->plugin->getServer()->getPlayerExact($args[1])->sendMessage($this->plugin->formatMessage("§5Acum, Tu ești liderul factiuni §a $factionName!",  true));
						$this->plugin->updateTag($sender->getName());
						$this->plugin->updateTag($this->plugin->getServer()->getPlayerExact($args[1])->getName());
				    }
					
					/////////////////////////////// PROMOTE ///////////////////////////////
					
					if($args[0] == "promote") {
						if(!isset($args[1])) {
							$sender->sendMessage($this->plugin->formatMessage("§5Foloseste: §a/f promote <jucator> §5Pentru a promova un jucator"));
							return true;
						}
						if(!$this->plugin->isInFaction($sender->getName())) {
							$sender->sendMessage($this->plugin->formatMessage("§5Trebuie să vă acest într-un facțiune!"));
							return true;
						}
						if(!$this->plugin->isLeader($player)) {
							$sender->sendMessage($this->plugin->formatMessage("§5Numai lider"));
							return true;
						}
						if($this->plugin->getPlayerFaction($player) != $this->plugin->getPlayerFaction($args[1])) {
							$sender->sendMessage($this->plugin->formatMessage("§5Jucatorul nu este in factiunea ta"));
							return true;
						}
                        if($args[1] == $sender->getName()){
                            $sender->sendMessage($this->plugin->formatMessage("§5Nu poti să faci singur."));
							return true;
                        }
                        
						if($this->plugin->isOfficer($args[1])) {
							$sender->sendMessage($this->plugin->formatMessage("§5Jucatorul a fost promovat"));
							return true;
						}
						$factionName = $this->plugin->getPlayerFaction($player);
						$stmt = $this->plugin->db->prepare("Introducere siinlocuire in master (player, faction, rank) VALUES (:player, :faction, :rank);");
						$stmt->bindValue(":player", $args[1]);
						$stmt->bindValue(":faction", $factionName);
						$stmt->bindValue(":rank", "Officer");
						$result = $stmt->execute();
						$player = $this->plugin->getServer()->getPlayerExact($args[1]);
						$sender->sendMessage($this->plugin->formatMessage("$args[1] §5Promovat", true));
                        
						if($player instanceof Player) {
						    $player->sendMessage($this->plugin->formatMessage("§5Ai fost promovat in factiunea §a $factionName!", true));
                            $this->plugin->updateTag($this->plugin->getServer()->getPlayerExact($args[1])->getName());
                            return true;
                        }
					}
					
					/////////////////////////////// DEMOTE ///////////////////////////////
					
					if($args[0] == "demote") {
						if(!isset($args[1])) {
							$sender->sendMessage($this->plugin->formatMessage("§5Foloseste: §a/f demote <jcator> §5Pentru a demonta"));
							return true;
						}
						if($this->plugin->isInFaction($sender->getName()) == false) {
							$sender->sendMessage($this->plugin->formatMessage("§5Ar trebui să aceasta într-un facțiune!"));
							return true;
						}
						if($this->plugin->isLeader($player) == false) {
							$sender->sendMessage($this->plugin->formatMessage("§5Numai liderul"));
							return true;
						}
						if($this->plugin->getPlayerFaction($player) != $this->plugin->getPlayerFaction($args[1])) {
							$sender->sendMessage($this->plugin->formatMessage("§5Jucatorul nu este in factiune!"));
							return true;
						}
						
                        if($args[1] == $sender->getName()){
                            $sender->sendMessage($this->plugin->formatMessage("§5Nu poti face asta!"));
							return true;
                        }
                        if(!$this->plugin->isOfficer($args[1])) {
							$sender->sendMessage($this->plugin->formatMessage("§5Jucatorul este deja in factiune"));
							return true;
						}
						$factionName = $this->plugin->getPlayerFaction($player);
						$stmt = $this->plugin->db->prepare("Introducere si inlocuirre in master (player, faction, rank) VALUES (:player, :faction, :rank);");
						$stmt->bindValue(":player", $args[1]);
						$stmt->bindValue(":faction", $factionName);
						$stmt->bindValue(":rank", "Member");
						$result = $stmt->execute();
						$player = $this->plugin->getServer()->getPlayerExact($args[1]);
						$sender->sendMessage($this->plugin->formatMessage("$args[1] §5Ai fost retrogradat!", true));
						if($player instanceof Player) {
						    $player->sendMessage($this->plugin->formatMessage("§5Jucatorul a fost retrogradat din $factionName!", true));
						    $this->plugin->updateTag($this->plugin->getServer()->getPlayerExact($args[1])->getName());
                            return true;
                        }
					}
					
					/////////////////////////////// KICK ///////////////////////////////
					
					if($args[0] == "kick") {
						if(!isset($args[1])) {
							$sender->sendMessage($this->plugin->formatMessage("§5Foloseste: §a/f kick <jucator> §5Pentru a da afara un jucator din factiune"));
							return true;
						}
						if($this->plugin->isInFaction($sender->getName()) == false) {
							$sender->sendMessage($this->plugin->formatMessage("§5Trebuie sa va acest intr-un facțiune!"));
							return true;
						}
						if($this->plugin->isLeader($player) == false) {
							$sender->sendMessage($this->plugin->formatMessage("§5Numai lideri"));
							return true;
						}
						if($this->plugin->getPlayerFaction($player) != $this->plugin->getPlayerFaction($args[1])) {
							$sender->sendMessage($this->plugin->formatMessage("§Jucatorul nu este in factiunea ta!"));
							return true;
						}
                        if($args[1] == $sender->getName()){
                            $sender->sendMessage($this->plugin->formatMessage("§5Nu poti sa eliminati"));
							return true;
                        }
						$kicked = $this->plugin->getServer()->getPlayerExact($args[1]);
						$factionName = $this->plugin->getPlayerFaction($player);
						$this->plugin->db->query("Sterge din master unde e jucatorul='$args[1]';");
						$sender->sendMessage($this->plugin->formatMessage("§5ai condus $args[1]!", true));
                        $this->plugin->subtractFactionPower($factionName,$this->plugin->prefs->get("PowerGainedPerPlayerInFaction"));
						
						if($kicked instanceof Player) {
			                $kicked->sendMessage($this->plugin->formatMessage("§7Voce foi expulso da $factionName!",true));
							$this->plugin->updateTag($this->plugin->getServer()->getPlayerExact($args[1])->getName());
							return true;
						}
					}
					
					/////////////////////////////// INFO ///////////////////////////////
					
					if(strtolower($args[0]) == 'info') {
						if(isset($args[1])) {
							if( !(ctype_alnum($args[1])) | !($this->plugin->factionExists($args[1]))) {
								$sender->sendMessage($this->plugin->formatMessage("§Factiunea nu exista!"));
							    $sender->sendMessage($this->plugin->formatMessage("§5Revizuire nume."));
								return true;
							}
							$faction = $args[1];
							$result = $this->plugin->db->query("Selecteaza * de la motd unde factiune='$faction';");
							$array = $result->fetchArray(SQLITE3_ASSOC);
                            $power = $this->plugin->getFactionPower($faction);
							$message = $array["message"];
							$leader = $this->plugin->getLeader($faction);
							$numPlayers = $this->plugin->getNumberOfPlayers($faction);
							$sender->sendMessage(TextFormat::GOLD . TextFormat::ITALIC . "-------INFORMACAO-------ME".TextFormat::RESET);
							$sender->sendMessage(TextFormat::GOLD . TextFormat::ITALIC . "|[Faction]| : " . TextFormat::GREEN . "$faction".TextFormat::RESET);
							$sender->sendMessage(TextFormat::GOLD . TextFormat::ITALIC . "|(LIDER)| : " . TextFormat::YELLOW . "$leader".TextFormat::RESET);
							$sender->sendMessage(TextFormat::GOLD . TextFormat::ITALIC . "|^JOGADORES^| : " . TextFormat::LIGHT_PURPLE . "$numPlayers".TextFormat::RESET);
							$sender->sendMessage(TextFormat::GOLD . TextFormat::ITALIC . "|&FORÇA&| : " . TextFormat::RED . "$power" . " STR".TextFormat::RESET);
							$sender->sendMessage(TextFormat::GOLD . TextFormat::ITALIC . "|*DESCRIÇÃO*| : " . TextFormat::AQUA . TextFormat::UNDERLINE . "$message".TextFormat::RESET);
							$sender->sendMessage(TextFormat::GOLD . TextFormat::ITALIC . "-------INFORMAÇÃO------".TextFormat::RESET);
						} else {
                            if(!$this->plugin->isInFaction($player)){
                                $sender->sendMessage($this->plugin->formatMessage("§5Trebuie sa va acest ntr-un factiiune!"));
                                return true;
                            }
							$faction = $this->plugin->getPlayerFaction(($sender->getName()));
							$result = $this->plugin->db->query("Selecteaza * FROM motd WHERE faction='$faction';");
							$array = $result->fetchArray(SQLITE3_ASSOC);
                            $power = $this->plugin->getFactionPower($faction);
							$message = $array["message"];
							$leader = $this->plugin->getLeader($faction);
							$numPlayers = $this->plugin->getNumberOfPlayers($faction);
							$sender->sendMessage(TextFormat::GOLD . TextFormat::ITALIC . "-------INFORMATII-------".TextFormat::RESET);
							$sender->sendMessage(TextFormat::GOLD . TextFormat::ITALIC . "|[Factiune]| : " . TextFormat::GREEN . "$faction".TextFormat::RESET);
							$sender->sendMessage(TextFormat::GOLD . TextFormat::ITALIC . "|(Lider)| : " . TextFormat::YELLOW . "$leader".TextFormat::RESET);
							$sender->sendMessage(TextFormat::GOLD . TextFormat::ITALIC . "|^Jucatori^| : " . TextFormat::LIGHT_PURPLE . "$numPlayers".TextFormat::RESET);
							$sender->sendMessage(TextFormat::GOLD . TextFormat::ITALIC . "|&Putere&| : " . TextFormat::RED . "$power" . " STR".TextFormat::RESET);
							$sender->sendMessage(TextFormat::GOLD . TextFormat::ITALIC . "|*Descriere*| : " . TextFormat::AQUA . TextFormat::UNDERLINE . "$message".TextFormat::RESET);
							$sender->sendMessage(TextFormat::GOLD . TextFormat::ITALIC . "-------INFORMATII-------".TextFormat::RESET);
						}
					}
					if(strtolower($args[0]) == "help") {
						if(!isset($args[1]) || $args[1] == 1) {
							$sender->sendMessage(TextFormat::GOLD . "§o§l§5»»ILOVEMCPE«« §7--=§aAjutor 1-7§7=--" . TextFormat::RED . "\n\n§2/f about Detalii despre pluginul\n§2/f accept -§7Accepta cererea de intrare intr-o factiune\n§2/f overclaim -§7\n§2/f claim- §7Domina un teren\n§2/f create <nume> -§7Creaza o factiune\n§2/f del -§7Stergeti factiunea\n§2/f demote <jucator> -§7Retrogradeaza un jucator\n§2/f deny -§7Declinati o cerere de la o factiune ");
							return true;
						}
						if($args[1] == 2) {
							$sender->sendMessage(TextFormat::GOLD . "§o§l§5»»ILOVEMCPE«« §7--=§aAjutor 2-7§7=--" . TextFormat::RED . "\n\n§2/f home -§7Pentru a te duce acasa\n§2/f help <pagina> -§7Pentru paginile de ajutor\n§2/f who <factiune> -§7\n§2/f info <factiune> -§7Pentru informatiile factoiuni\n§2/f invite <jucator> -§7Pentru a invita un jucator in factiune\n§2/f kick <jucator> -§7Pentru a da afara un jucator\n§2/f leader <jucator> -§7Pentru a da liderul altuia\n§2/f leave -§7Pentru a iesii din factiunea curenta");
							return true;
						} 
                        if($args[1] == 3) {
							$sender->sendMessage(TextFormat::GOLD . "§o§l§5»»ILOVEMCPE«« §7--=§aHAjutor 3-7§7=--" . TextFormat::RED . "\n\n§2/f sethome -§7Seteaza locul pentru \n§2/f unclaim -§7Pentru a scoate scoate Protectia\n§2/f unsethome -§7Pentru a scoata casa...\n§2/f ourmembers -§7Membri factiuni tale\n§2/f ourofficers -§7Ofiterii factiuni\n§2/f ourleader -§7Liderul  factiunii\n§2/f allies -§7Aliatii factiuni tale");
							return true;
						} 
                        if($args[1] == 4) {
                            $sender->sendMessage(TextFormat::GOLD . "§o§l§5»»ILOVEMCPE«« §7--=§aAjutor 4-7§7=--" . TextFormat::RED . "\n\n§2/f desc -§7Vezi descrierea factiunii\n§2/f promote <jucator> -§7Promoveaza un membru al factiunii\n§2/f ally <factiune> -§7Faceti alianta cu alte factiuni\n§2/f unally <factiune> -§7SAnuleaza o alianta cu o factiune\n§2/f allyok -§7Accepta o alianta cu o factiune\n§2/f allyno -§7Refuza o cerere de alianta\n§2/f allies <faction> -§7Vezi cu ce factiuni sunteti aliati");
							return true;
                        } 
                        if($args[1] == 5){
                            $sender->sendMessage(TextFormat::GOLD . "§o§l§5»»ILOVEMCPE«« §7--=§aAjutor 5-7§7=--" . TextFormat::RED . "\n\n§2/f membersof <factiune> -§7Vezi membri unei factiuni\n§2/f officersof <factiune> -§7Vezi ofiterii unei factiuni\n§2/f leaderof <factiune> -§7Vezi liderul unei factiuni\n§2/f say <mesaj> -§7Trimite mesaj privat membrilor din factiune\n§2/f pf <jucator> -§7Informatii jucator\n§2/f top [Bani|Putere] -§7Top 10 factiuni puternice");
							return true;
                        }
                        if($args[1] == 6) {
                        	$sender->sendMessage(TextFormat::GOLD . "§o§l§5»»ILOVEMCPE«« §7--=§aAjutor 6-7§7=--\n§7Pentru OP" . TextFormat::RED . "\n\n§2/f forceunclaim <factiune> -§7Sterge claim-urile unei factiuni - OWNERI\n§2/f forcedelete <factiune> -§7Sterge o factiune - OWNERI\n§2/f addstrto <factiune> -§7 [Putere] [Pozitiv + Negativ -] - OWNERI\n§2/f Mapa -§7Ver Info Sobre Terras\n§2/f Banco -§7[Ver Money da sua fac]");
							return true;
                        }
					}
				}
				if(count($args == 1)) {
					
					/////////////////////////////// CLAIM ///////////////////////////////
					
					if(strtolower($args[0]) == 'claim') {
						if(!$this->plugin->isInFaction($player)) {
							$sender->sendMessage($this->plugin->formatMessage("§7Voce Precisa esta em uma facção."));
							return true;
						}
						if(!$this->plugin->isLeader($player)) {
							$sender->sendMessage($this->plugin->formatMessage("§7Apenas Liders."));
							return true;
						}
                        
						if($this->plugin->inOwnPlot($sender)) {
							$sender->sendMessage($this->plugin->formatMessage("§7Sua fac Claimo essa área."));
							return true;
						}
						$faction = $this->plugin->getPlayerFaction($sender->getPlayer()->getName());
                        if($this->plugin->getNumberOfPlayers($faction) < $this->plugin->prefs->get("PlayersNeededInFactionToClaimAPlot")){
                           
                           $needed_players =  $this->plugin->prefs->get("PlayersNeededInFactionToClaimAPlot") - 
                                               $this->plugin->getNumberOfPlayers($faction);
                           $sender->sendMessage($this->plugin->formatMessage("§7Você Precisa de §a$needed_players §7em Sua facção"));
				           return true;
                        }
                        if($this->plugin->getFactionPower($faction) < $this->plugin->prefs->get("PowerNeededToClaimAPlot")){
                            $needed_power = $this->plugin->prefs->get("PowerNeededToClaimAPlot");
                            $faction_power = $this->plugin->getFactionPower($faction);
							$sender->sendMessage($this->plugin->formatMessage("§7Sua facção não tem Power Suficiente."));
							$sender->sendMessage($this->plugin->formatMessage("§7Precisa de §a$needed_power .§7Tem§a $faction_power STR."));
                            return true;
                        }
						
                        $x = floor($sender->getX());
						$y = floor($sender->getY());
						$z = floor($sender->getZ());
						if($this->plugin->drawPlot($sender, $faction, $x, $y, $z, $sender->getPlayer()->getLevel(), $this->plugin->prefs->get("PlotSize")) == false) {
                            
							return true;
						}
                        
						$sender->sendMessage($this->plugin->formatMessage("§7Pegando coordinates...", true));
                        $plot_size = $this->plugin->prefs->get("PlotSize");
                        $faction_power = $this->plugin->getFactionPower($faction);
						$sender->sendMessage($this->plugin->formatMessage("§7Area claimada.", true));
					
					}
                    if(strtolower($args[0]) == 'plotinfo'){
                        $x = floor($sender->getX());
						$y = floor($sender->getY());
						$z = floor($sender->getZ());
                        if(!$this->plugin->isInPlot($sender)){
                            $sender->sendMessage($this->plugin->formatMessage("§7Area Livre. Use: §a/f claim", true));
							return true;
                        }
                        
                        $fac = $this->plugin->factionFromPoint($x,$z);
                        $power = $this->plugin->getFactionPower($fac);
                        $sender->sendMessage($this->plugin->formatMessage("§7Area Ja possui dono§a $fac Power $power "));
                    }
                    if(strtolower($args[0]) == 'forcedelete') {
                        if(!isset($args[1])){
                            $sender->sendMessage($this->plugin->formatMessage("§7Use: §a/f forcedelete <faction>"));
                            return true;
                        }
                        if(!$this->plugin->factionExists($args[1])) {
							$sender->sendMessage($this->plugin->formatMessage("§7Facção Não Existe."));
                            return true;
						}
                        if(!($sender->isOp())) {
							$sender->sendMessage($this->plugin->formatMessage("§7Apenas OP."));
                            return true;
						}
						$this->plugin->db->query("DELETE FROM master WHERE faction='$args[1]';");
						$this->plugin->db->query("DELETE FROM plots WHERE faction='$args[1]';");
				        $this->plugin->db->query("DELETE FROM allies WHERE faction1='$args[1]';");
				        $this->plugin->db->query("DELETE FROM allies WHERE faction2='$args[1]';");
                        $this->plugin->db->query("DELETE FROM strength WHERE faction='$args[1]';");
						$this->plugin->db->query("DELETE FROM motd WHERE faction='$args[1]';");
				        $this->plugin->db->query("DELETE FROM home WHERE faction='$args[1]';");
				        $sender->sendMessage($this->plugin->formatMessage("§7Facção Deletada", true));
                    }
                    if(strtolower($args[0]) == 'addstrto') {
                        if(!isset($args[1]) or !isset($args[2])){
                            $sender->sendMessage($this->plugin->formatMessage("§7Use: §a/f addstrto <faction> <STR>"));
                            return true;
                        }
                        if(!$this->plugin->factionExists($args[1])) {
							$sender->sendMessage($this->plugin->formatMessage("§7Facção Não existe."));
                            return true;
						}
                        if(!($sender->isOp())) {
							$sender->sendMessage($this->plugin->formatMessage("§7Apenas OP."));
                            return true;
						}
                        $this->plugin->addFactionPower($args[1],$args[2]);
				        $sender->sendMessage($this->plugin->formatMessage("§7Adicionado §a$args[2] STR a $args[1]", true));
                    }
                    if(strtolower($args[0]) == 'pf'){
                        if(!isset($args[1])){
                            $sender->sendMessage($this->plugin->formatMessage("§7Use: §a/f pf <player>"));
                            return true;
                        }
                        if(!$this->plugin->isInFaction($args[1])) {
							$sender->sendMessage($this->plugin->formatMessage("§7Jogador Não Possui Facção."));
							$sender->sendMessage($this->plugin->formatMessage("§7Jogador Não existe/n§7Olhe Bem o Nome."));
                            return true;
						}
                        $faction = $this->plugin->getPlayerFaction($args[1]);
                        $sender->sendMessage($this->plugin->formatMessage("-$args[1] is in $faction-",true));
                        
                    }
                    
                    if(strtolower($args[0]) == 'overclaim') {
						if(!$this->plugin->isInFaction($player)) {
							$sender->sendMessage($this->plugin->formatMessage("§7Voce Não possui Facção"));
							return true;
						}
						if(!$this->plugin->isLeader($player)) {
							$sender->sendMessage($this->plugin->formatMessage("§7Apenas Lider."));
							return true;
						}
                        $faction = $this->plugin->getPlayerFaction($player);
						if($this->plugin->getNumberOfPlayers($faction) < $this->plugin->prefs->get("PlayersNeededInFactionToClaimAPlot")){
                           
                           $needed_players =  $this->plugin->prefs->get("PlayersNeededInFactionToClaimAPlot") - 
                                               $this->plugin->getNumberOfPlayers($faction);
                           $sender->sendMessage($this->plugin->formatMessage("§7Voce precisa de §a$needed_players §7Em sua facção para dominar Area"));
				           return true;
                        }
                        if($this->plugin->getFactionPower($faction) < $this->plugin->prefs->get("PowerNeededToClaimAPlot")){
                            $needed_power = $this->plugin->prefs->get("PowerNeededToClaimAPlot");
                            $faction_power = $this->plugin->getFactionPower($faction);
							$sender->sendMessage($this->plugin->formatMessage("Your faction doesn't have enough STR to claim a land."));
							$sender->sendMessage($this->plugin->formatMessage("§7Boce precisa de §a$needed_power Power\n§7Voce so tem §a$faction_power Power."));
                            return true;
                        }
						$sender->sendMessage($this->plugin->formatMessage("§7Pegando coordinates...", true));
						$x = floor($sender->getX());
						$y = floor($sender->getY());
						$z = floor($sender->getZ());
                        if($this->plugin->prefs->get("EnableOverClaim")){
                            if($this->plugin->isInPlot($sender)){
                                $faction_victim = $this->plugin->factionFromPoint($x,$z);
                                $faction_victim_power = $this->plugin->getFactionPower($faction_victim);
                                $faction_ours = $this->plugin->getPlayerFaction($player);
                                $faction_ours_power = $this->plugin->getFactionPower($faction_ours);
                                if($this->plugin->inOwnPlot($sender)){
                                    $sender->sendMessage($this->plugin->formatMessage("§7Area Dominada :)."));
                                    return true;
                                } else {
                                    if($faction_ours_power < $faction_victim_power){
                                        $sender->sendMessage($this->plugin->formatMessage("§7Sua Facção esta mais fraca que a Dona da Area"));
                                        return true;
                                    } else {
                                        $this->plugin->db->query("DELETE FROM plots WHERE faction='$faction_ours';");
                                        $this->plugin->db->query("DELETE FROM plots WHERE faction='$faction_victim';");
                                        $arm = (($this->plugin->prefs->get("PlotSize")) - 1) / 2;
                                        $this->plugin->newPlot($faction_ours,$x+$arm,$z+$arm,$x-$arm,$z-$arm);
						                $sender->sendMessage($this->plugin->formatMessage("§7Sua area foi dominada §a  $faction_victim.", true));
                                        return true;
                                    }
                                    
                                }
                            } else {
                                $sender->sendMessage($this->plugin->formatMessage("§7Precisa de facção"));
                                return true;
                            }
                        } else {
                            $sender->sendMessage($this->plugin->formatMessage("Overclaiming is disabled."));
                            return true;
                        }
                        
					}
                    
					
					/////////////////////////////// UNCLAIM ///////////////////////////////
					
					if(strtolower($args[0]) == "unclaim") {
                        if(!$this->plugin->isInFaction($sender->getName())) {
							$sender->sendMessage($this->plugin->formatMessage("§7Você Não possui facção."));
							return true;
						}
						if(!$this->plugin->isLeader($sender->getName())) {
							$sender->sendMessage($this->plugin->formatMessage("§7Apenas Lider."));
							return true;
						}
						$faction = $this->plugin->getPlayerFaction($sender->getName());
						$this->plugin->db->query("DELETE FROM plots WHERE faction='$faction';");
						$sender->sendMessage($this->plugin->formatMessage("§7Terra Desclaimada.", true));
					}
					
					/////////////////////////////// DESCRIPTION ///////////////////////////////
					
					if(strtolower($args[0]) == "desc") {
						if($this->plugin->isInFaction($sender->getName()) == false) {
							$sender->sendMessage($this->plugin->formatMessage("§7Você Não possui facção!"));
							return true;
						}
						if($this->plugin->isLeader($player) == false) {
							$sender->sendMessage($this->plugin->formatMessage("§7Apenas Lider"));
							return true;
						}
						$sender->sendMessage($this->plugin->formatMessage("§7Digite a sua §a{msg} §7So voce ira vela :)", true));
						$stmt = $this->plugin->db->prepare("INSERT OR REPLACE INTO motdrcv (player, timestamp) VALUES (:player, :timestamp);");
						$stmt->bindValue(":player", $sender->getName());
						$stmt->bindValue(":timestamp", time());
						$result = $stmt->execute();
					}

					/////////////////////////////// TOP, also by @PrimusLV //////////////////////////

					if(strtolower($args[0]) === "top") {
						$sortBy = isset($args[1]) ? $args[1] : "dinheiro";
						switch ($sortBy) {
							case 'dinheiro':
								$this->plugin->sendListOfTop10RichestFactionsTo($sender);
								break;
							case "poder":
								$this->plugin->sendListOfTop10FactionsTo($sender);
							default:
								$sender->sendMessage($this->plugin->formatMessage("§7TOPs:."));
								break;
						}
						return true;
					}
					
					/////////////////////////////// ACCEPT ///////////////////////////////
					
					if(strtolower($args[0]) == "accept") {
						$player = $sender->getName();
						$lowercaseName = ($player);
						$result = $this->plugin->db->query("SELECT * FROM confirm WHERE player='$lowercaseName';");
						$array = $result->fetchArray(SQLITE3_ASSOC);
						if(empty($array) == true) {
							$sender->sendMessage($this->plugin->formatMessage("§7Você Não possui pedido!"));
							return true;
						}
						$invitedTime = $array["timestamp"];
						$currentTime = time();
						if(($currentTime - $invitedTime) <= 60) { //This should be configurable
							$faction = $array["faction"];
							$stmt = $this->plugin->db->prepare("INSERT OR REPLACE INTO master (player, faction, rank) VALUES (:player, :faction, :rank);");
							$stmt->bindValue(":player", ($player));
							$stmt->bindValue(":faction", $faction);
							$stmt->bindValue(":rank", "Member");
							$result = $stmt->execute();
							$this->plugin->db->query("DELETE FROM confirm WHERE player='$lowercaseName';");
							$sender->sendMessage($this->plugin->formatMessage("§7Voce entrou na§a $faction!", true));
                            $this->plugin->addFactionPower($faction,$this->plugin->prefs->get("PowerGainedPerPlayerInFaction"));
							$this->plugin->getServer()->getPlayerExact($array["invitedby"])->sendMessage($this->plugin->formatMessage("§a $player §7entrou na sua facção!", true));
							$this->plugin->updateTag($sender->getName());
						} else {
							$sender->sendMessage($this->plugin->formatMessage("§7Tempo esgotado"));
							$this->plugin->db->query("DELETE * FROM confirm WHERE player='$player';");
						}
					}
					
					/////////////////////////////// DENY ///////////////////////////////
					
					if(strtolower($args[0]) == "deny") {
						$player = $sender->getName();
						$lowercaseName = ($player);
						$result = $this->plugin->db->query("SELECT * FROM confirm WHERE player='$lowercaseName';");
						$array = $result->fetchArray(SQLITE3_ASSOC);
						if(empty($array) == true) {
							$sender->sendMessage($this->plugin->formatMessage("§7Voce Não possui pedidos"));
							return true;
						}
						$invitedTime = $array["timestamp"];
						$currentTime = time();
						if( ($currentTime - $invitedTime) <= 60 ) { //This should be configurable
							$this->plugin->db->query("DELETE FROM confirm WHERE player='$lowercaseName';");
							$sender->sendMessage($this->plugin->formatMessage("Invite declined!", true));
							$this->plugin->getServer()->getPlayerExact($array["invitedby"])->sendMessage($this->plugin->formatMessage("§a $player §7Negou Convite!"));
						} else {
							$sender->sendMessage($this->plugin->formatMessage("§7Tempo acabou!"));
							$this->plugin->db->query("DELETE * FROM confirm WHERE player='$lowercaseName';");
						}
					}
					
					/////////////////////////////// DELETE ///////////////////////////////
					
					if(strtolower($args[0]) == "del") {
						if($this->plugin->isInFaction($player) == true) {
							if($this->plugin->isLeader($player)) {
								$faction = $this->plugin->getPlayerFaction($player);
                                $this->plugin->db->query("DELETE FROM plots WHERE faction='$faction';");
								$this->plugin->db->query("DELETE FROM master WHERE faction='$faction';");
								$this->plugin->db->query("DELETE FROM allies WHERE faction1='$faction';");
								$this->plugin->db->query("DELETE FROM allies WHERE faction2='$faction';");
								$this->plugin->db->query("DELETE FROM strength WHERE faction='$faction';");
								$this->plugin->db->query("DELETE FROM motd WHERE faction='$faction';");
								$this->plugin->db->query("DELETE FROM home WHERE faction='$faction';");
								$sender->sendMessage($this->plugin->formatMessage("Faction successfully disbanded and the faction plot was unclaimed!", true));
								$this->plugin->updateTag($sender->getName());
							} else {
								$sender->sendMessage($this->plugin->formatMessage("§7Somente Lider!"));
							}
						} else {
							$sender->sendMessage($this->plugin->formatMessage("§7Voce Não possui facção"));
						}
					}
					
					/////////////////////////////// LEAVE ///////////////////////////////
					
					if(strtolower($args[0] == "leave")) {
						if($this->plugin->isLeader($player) == false) {
							$remove = $sender->getPlayer()->getNameTag();
							$faction = $this->plugin->getPlayerFaction($player);
							$name = $sender->getName();
							$this->plugin->db->query("DELETE FROM master WHERE player='$name';");
							$sender->sendMessage($this->plugin->formatMessage("§7Você saiu da§a $faction", true));
                            
                            $this->plugin->subtractFactionPower($faction,$this->plugin->prefs->get("PowerGainedPerPlayerInFaction"));
							$this->plugin->updateTag($sender->getName());
						} else {
							$sender->sendMessage($this->plugin->formatMessage("§7Primeiro de a liderança"));
						}
					}
					
					/////////////////////////////// SETHOME ///////////////////////////////

					if(strtolower($args[0] == "sethome")) {
						if(!$this->plugin->isInFaction($player)) {
							$sender->sendMessage($this->plugin->formatMessage("§7Voce Não possui facção"));
							return true;
						}
						if(!$this->plugin->isLeader($player)) {
							$sender->sendMessage($this->plugin->formatMessage("§7Apenas lider."));
							return true;
						}
						$factionName = $this->plugin->getPlayerFaction($sender->getName());
						$stmt = $this->plugin->db->prepare("INSERT OR REPLACE INTO home (faction, x, y, z) VALUES (:faction, :x, :y, :z);");
						$stmt->bindValue(":faction", $factionName);
						$stmt->bindValue(":x", $sender->getX());
						$stmt->bindValue(":y", $sender->getY());
						$stmt->bindValue(":z", $sender->getZ());
						$result = $stmt->execute();
						$sender->sendMessage($this->plugin->formatMessage("Home set!", true));
					}

					/////////////////////////////// UNSETHOME ///////////////////////////////

					if(strtolower($args[0] == "unsethome")) {
						if(!$this->plugin->isInFaction($player)) {
							$sender->sendMessage($this->plugin->formatMessage("§7Voce Não possui facção."));
							return true;
						}
						if(!$this->plugin->isLeader($player)) {
							$sender->sendMessage($this->plugin->formatMessage("§7Apenas Lider."));
							return true;
						}
						$faction = $this->plugin->getPlayerFaction($sender->getName());
						$this->plugin->db->query("DELETE FROM home WHERE faction = '$faction';");
						$sender->sendMessage($this->plugin->formatMessage("§7Home retirada!", true));
					}

					/////////////////////////////// HOME ///////////////////////////////

					if(strtolower($args[0] == "home")) {
						if(!$this->plugin->isInFaction($player)) {
							$sender->sendMessage($this->plugin->formatMessage("§7Voce Não possui facção."));
							return true;
						}
						$faction = $this->plugin->getPlayerFaction($sender->getName());
						$result = $this->plugin->db->query("SELECT * FROM home WHERE faction = '$faction';");
						$array = $result->fetchArray(SQLITE3_ASSOC);
						if(!empty($array)){
							$sender->getPlayer()->teleport(new Position($array['x'], $array['y'], $array['z'], $this->plugin->getServer()->getLevelByName("Factions")));
							$sender->sendMessage($this->plugin->formatMessage("§7Teleportado home.", true));
						} 
						else{
							$sender->sendMessage($this->plugin->formatMessage("§7Home Não setada."));
						}
					}
                    
                    /////////////////////////////// MEMBERS/OFFICERS/LEADER AND THEIR STATUSES ///////////////////////////////
                    if(strtolower($args[0] == "ourmembers")){
                        if(!$this->plugin->isInFaction($player)) {
							$sender->sendMessage($this->plugin->formatMessage("§7Voce Não possui facção."));
                            return true;
						}
                        $this->plugin->getPlayersInFactionByRank($sender,$this->plugin->getPlayerFaction($player),"Member");
                       
                    }
                    if(strtolower($args[0] == "membersof")){
                        if(!isset($args[1])){
                            $sender->sendMessage($this->plugin->formatMessage("§7Use: §a/f membersof <faction>"));
                            return true;
                        }
                        if(!$this->plugin->factionExists($args[1])) {
							$sender->sendMessage($this->plugin->formatMessage("§7Facção Não existe"));
                            return true;
                        }
                        $this->plugin->getPlayersInFactionByRank($sender,$args[1],"Member");
                       
                    }
                    if(strtolower($args[0] == "ourofficers")){
                        if(!$this->plugin->isInFaction($player)) {
							$sender->sendMessage($this->plugin->formatMessage("§7Voce Não possui facção."));
                            return true;
						}
                        $this->plugin->getPlayersInFactionByRank($sender,$this->plugin->getPlayerFaction($player),"Officer");
                    }
                    if(strtolower($args[0] == "officersof")){
                        if(!isset($args[1])){
                            $sender->sendMessage($this->plugin->formatMessage("§7Use: §a/f officersof <faction>"));
                            return true;
                        }
                        if(!$this->plugin->factionExists($args[1])) {
							$sender->sendMessage($this->plugin->formatMessage("§7Facção Não existe."));
                            return true;
                        }
                        $this->plugin->getPlayersInFactionByRank($sender,$args[1],"Officer");
                       
                    }
                    if(strtolower($args[0] == "ourleader")){
                        if(!$this->plugin->isInFaction($player)) {
							$sender->sendMessage($this->plugin->formatMessage("§7Voce Não possui facção."));
                            return true;
						}
                        $this->plugin->getPlayersInFactionByRank($sender,$this->plugin->getPlayerFaction($player),"Leader");
                    }
                    if(strtolower($args[0] == "leaderof")){
                        if(!isset($args[1])){
                            $sender->sendMessage($this->plugin->formatMessage("§7Use: §a/f leaderof <faction>"));
                            return true;
                        }
                        if(!$this->plugin->factionExists($args[1])) {
							$sender->sendMessage($this->plugin->formatMessage("§7Facção Não existe."));
                            return true;
                        }
                        $this->plugin->getPlayersInFactionByRank($sender,$args[1],"Leader");
                       
                    }
                    if(strtolower($args[0] == "say")){
                        if(!isset($args[1])){
                            $sender->sendMessage($this->plugin->formatMessage("§7Use: §/f say <mensagem>"));
                            return true;
                        }
                        if(!($this->plugin->isInFaction($player))){
                            
                            $sender->sendMessage($this->plugin->formatMessage("§7Voce Não possui facção"));
                            return true;
                        }
                        $r = count($args);
                        $row = array();
                        $rank = "";
                        $f = $this->plugin->getPlayerFaction($player);
                        
                        if($this->plugin->isOfficer($player)){
                            $rank = "*";
                        } else if($this->plugin->isLeader($player)){
                            $rank = "**";
                        }
                        $message = "-> ";
                        for($i=0;$i<$r-1;$i=$i+1){
                            $message = $message.$args[$i+1]." "; 
                        }
                        $result = $this->plugin->db->query("SELECT * FROM master WHERE faction='$f';");
                        for($i=0;$resultArr = $result->fetchArray(SQLITE3_ASSOC);$i=$i+1){
                            $row[$i]['player'] = $resultArr['player'];
                            $p = $this->plugin->getServer()->getPlayerExact($row[$i]['player']);
                            if($p instanceof Player){
                                $p->sendMessage(TextFormat::ITALIC.TextFormat::RED."<FM>".TextFormat::AQUA." <$rank$f> ".TextFormat::GREEN."<$player> ".": ".TextFormat::RESET);
                                $p->sendMessage(TextFormat::ITALIC.TextFormat::DARK_AQUA.$message.TextFormat::RESET);
                                
                            }
                        } 
                            
                    }
                    
                  
                    ////////////////////////////// ALLY SYSTEM ////////////////////////////////
					if(strtolower($args[0] == "enemy")){
                        if(!isset($args[1])){
                            $sender->sendMessage($this->plugin->formatMessage("§7Use: §a/f enemywith <faction>"));
                            return true;
                        }
                        if(!$this->plugin->isInFaction($player)) {
							$sender->sendMessage($this->plugin->formatMessage("§7Voce Não possui facção."));
                            return true;
						}
                        if(!$this->plugin->isLeader($player)) {
							$sender->sendMessage($this->plugin->formatMessage("Apenas Lider."));
                            return true;
						}
                        if(!$this->plugin->factionExists($args[1])) {
							$sender->sendMessage($this->plugin->formatMessage("§7Facção Não existe"));
                            return true;
						}
                        if($this->plugin->getPlayerFaction($player) == $args[1]){
                            $sender->sendMessage($this->plugin->formatMessage("§7Facção Não é aliada"));
                            return true;
                        }
                        if($this->plugin->areAllies($this->plugin->getPlayerFaction($player),$args[1])){
                            $sender->sendMessage($this->plugin->formatMessage("§7Sua Facção é aliada com§a $args[1]!"));
                            return true;
                        }
                        $fac = $this->plugin->getPlayerFaction($player);
						$leader = $this->plugin->getServer()->getPlayerExact($this->plugin->getLeader($args[1]));
                        
                        if(!($leader instanceof Player)){
                            $sender->sendMessage($this->plugin->formatMessage("§7Lider dessa facção esta offLine."));
                            return true;
                        }
                        $this->plugin->setEnemies($fac, $args[1]);
                        $sender->sendMessage($this->plugin->formatMessage("§7Inimigo§a  $args[1]!",true));
                        $leader->sendMessage($this->plugin->formatMessage("§7Lider da§a $fac §7é Inimigo de sua facção.",true));
                        
                    }
                    if(strtolower($args[0] == "ally")){
                        if(!isset($args[1])){
                            $sender->sendMessage($this->plugin->formatMessage("Use: §a/f allywith <faction>"));
                            return true;
                        }
                        if(!$this->plugin->isInFaction($player)) {
							$sender->sendMessage($this->plugin->formatMessage("§7Voce Não possui facção."));
                            return true;
						}
                        if(!$this->plugin->isLeader($player)) {
							$sender->sendMessage($this->plugin->formatMessage("§7Apenas Lider."));
                            return true;
						}
                        if(!$this->plugin->factionExists($args[1])) {
							$sender->sendMessage($this->plugin->formatMessage("§7faccção Não existe"));
                            return true;
						}
                        if($this->plugin->getPlayerFaction($player) == $args[1]){
                            $sender->sendMessage($this->plugin->formatMessage("§7Sua facção não pode aliar-se"));
                            return true;
                        }
                        if($this->plugin->areAllies($this->plugin->getPlayerFaction($player),$args[1])){
                            $sender->sendMessage($this->plugin->formatMessage("§7Sua Facção ja é aliada com§a $args[1]!"));
                            return true;
                        }
                        $fac = $this->plugin->getPlayerFaction($player);
						$leader = $this->plugin->getServer()->getPlayerExact($this->plugin->getLeader($args[1]));
                        $this->plugin->updateAllies($fac);
                        $this->plugin->updateAllies($args[1]);
                        
                        if(!($leader instanceof Player)){
                            $sender->sendMessage($this->plugin->formatMessage("§7Lider esta offline."));
                            return true;
                        }
                        if($this->plugin->getAlliesCount($args[1])>=$this->plugin->getAlliesLimit()){
                           $sender->sendMessage($this->plugin->formatMessage("§7Essa facção ja possui o maximo de alianças.",false));
                           return true;
                        }
                        if($this->plugin->getAlliesCount($fac)>=$this->plugin->getAlliesLimit()){
                           $sender->sendMessage($this->plugin->formatMessage("§7Sua Facção ja esta no maximo de alianças.",false));
                           return true;
                        }
                        $stmt = $this->plugin->db->prepare("INSERT OR REPLACE INTO alliance (player, faction, requestedby, timestamp) VALUES (:player, :faction, :requestedby, :timestamp);");
				        $stmt->bindValue(":player", $leader->getName());
				        $stmt->bindValue(":faction", $args[1]);
				        $stmt->bindValue(":requestedby", $sender->getName());
				        $stmt->bindValue(":timestamp", time());
				        $result = $stmt->execute();
                        $sender->sendMessage($this->plugin->formatMessage("§7Pedido de alianca§a $args[1].",true));
                        $leader->sendMessage($this->plugin->formatMessage("§7O Lider da§a $fac §7Quer aliar-se a voce \n§7Use§a /f allyok §7Para aceitar§a /f allyno §7Para cancar",true));
                        
                    }
                    if(strtolower($args[0] == "unally")){
                        if(!isset($args[1])){
                            $sender->sendMessage($this->plugin->formatMessage("Use:§a /f unally <faction>"));
                            return true;
                        }
                        if(!$this->plugin->isInFaction($player)) {
							$sender->sendMessage($this->plugin->formatMessage("§7Voce Não possui facção."));
                            return true;
						}
                        if(!$this->plugin->isLeader($player)) {
							$sender->sendMessage($this->plugin->formatMessage("§7Apenas Lider."));
                            return true;
						}
                        if(!$this->plugin->factionExists($args[1])) {
							$sender->sendMessage($this->plugin->formatMessage("§7Facção Não existe."));
                            return true;
						}
                        if($this->plugin->getPlayerFaction($player) == $args[1]){
                            $sender->sendMessage($this->plugin->formatMessage("§7Voce Não pode fazer isso."));
                            return true;
                        }
                        if(!$this->plugin->areAllies($this->plugin->getPlayerFaction($player),$args[1])){
                            $sender->sendMessage($this->plugin->formatMessage("§7Sua Facção nao é aliada de§a $args[1]!"));
                            return true;
                        }
                        
                        $fac = $this->plugin->getPlayerFaction($player);        
						$leader= $this->plugin->getServer()->getPlayerExact($this->plugin->getLeader($args[1]));
                        $this->plugin->deleteAllies($fac,$args[1]);
                        $this->plugin->deleteAllies($args[1],$fac);
                        $this->plugin->subtractFactionPower($fac,$this->plugin->prefs->get("PowerGainedPerAlly"));
                        $this->plugin->subtractFactionPower($args[1],$this->plugin->prefs->get("PowerGainedPerAlly"));
                        $this->plugin->updateAllies($fac);
                        $this->plugin->updateAllies($args[1]);
                        $sender->sendMessage($this->plugin->formatMessage("§7Sua facção§a $fac §7Nao é aliada de§a $args[1]!",true));
                        if($leader instanceof Player){
                            $leader->sendMessage($this->plugin->formatMessage("§7O lider da§a $fac §7 $args[1]!",false));
                        }
                        
                        
                    }
                    if(strtolower($args[0] == "forceunclaim")){
                        if(!isset($args[1])){
                            $sender->sendMessage($this->plugin->formatMessage("Use: §a/f forceunclaim <faction>"));
                            return true;
                        }
                        if(!$this->plugin->factionExists($args[1])) {
							$sender->sendMessage($this->plugin->formatMessage("§7Facção Não existe."));
                            return true;
						}
                        if(!($sender->isOp())) {
							$sender->sendMessage($this->plugin->formatMessage("§7Apenas OP."));
                            return true;
						}
				        $sender->sendMessage($this->plugin->formatMessage("Successfully unclaimed the unwanted plot of $args[1]!"));
                        $this->plugin->db->query("DELETE FROM plots WHERE faction='$args[1]';");
                        
                    }
                    
                    if(strtolower($args[0] == "allies")){
                        if(!isset($args[1])){
                            if(!$this->plugin->isInFaction($player)) {
							    $sender->sendMessage($this->plugin->formatMessage("§7Voce Não possui facção."));
                                return true;
						    }
                            
                            $this->plugin->updateAllies($this->plugin->getPlayerFaction($player));
                            $this->plugin->getAllAllies($sender,$this->plugin->getPlayerFaction($player));
                        } else {
                            if(!$this->plugin->factionExists($args[1])) {
							    $sender->sendMessage($this->plugin->formatMessage("§7Facção Não existe."));
                                return true;
						    }
                            $this->plugin->updateAllies($args[1]);
                            $this->plugin->getAllAllies($sender,$args[1]);
                            
                        }
                        
                    }
                    if(strtolower($args[0] == "allyok")){
                        if(!$this->plugin->isInFaction($player)) {
							$sender->sendMessage($this->plugin->formatMessage("§7Voce Não possui facção."));
                            return true;
						}
                        if(!$this->plugin->isLeader($player)) {
							$sender->sendMessage($this->plugin->formatMessage("§7Apenas Lider."));
                            return true;
						}
						$lowercaseName = ($player);
						$result = $this->plugin->db->query("SELECT * FROM alliance WHERE player='$lowercaseName';");
						$array = $result->fetchArray(SQLITE3_ASSOC);
						if(empty($array) == true) {
							$sender->sendMessage($this->plugin->formatMessage("§7Sua facção não foi solicitada a se aliar com quaisquer facções!"));
							return true;
						}
						$allyTime = $array["timestamp"];
						$currentTime = time();
						if(($currentTime - $allyTime) <= 60) { //This should be configurable
                            $requested_fac = $this->plugin->getPlayerFaction($array["requestedby"]);
                            $sender_fac = $this->plugin->getPlayerFaction($player);
							$this->plugin->setAllies($requested_fac,$sender_fac);
							$this->plugin->setAllies($sender_fac,$requested_fac);
                            $this->plugin->addFactionPower($sender_fac,$this->plugin->prefs->get("PowerGainedPerAlly"));
                            $this->plugin->addFactionPower($requested_fac,$this->plugin->prefs->get("PowerGainedPerAlly"));
							$this->plugin->db->query("DELETE FROM alliance WHERE player='$lowercaseName';");
                            $this->plugin->updateAllies($requested_fac);
                            $this->plugin->updateAllies($sender_fac);
							$sender->sendMessage($this->plugin->formatMessage("§7Sua Facção é aliada agora com§a $requested_fac!", true));
							$this->plugin->getServer()->getPlayerExact($array["requestedby"])->sendMessage($this->plugin->formatMessage("$player da $sender_fac Aceito aliança!", true));
                            
                            
						} else {
							$sender->sendMessage($this->plugin->formatMessage("§7Tempo acabou!"));
							$this->plugin->db->query("DELETE * FROM alliance WHERE player='$lowercaseName';");
						}
                        
                    }
                    if(strtolower($args[0]) == "allyno") {
                        if(!$this->plugin->isInFaction($player)) {
							$sender->sendMessage($this->plugin->formatMessage("§7Voce Não possui facção."));
                            return true;
						}
                        if(!$this->plugin->isLeader($player)) {
							$sender->sendMessage($this->plugin->formatMessage("§7Apenas Lider."));
                            return true;
						}
						$lowercaseName = ($player);
						$result = $this->plugin->db->query("SELECT * FROM alliance WHERE player='$lowercaseName';");
						$array = $result->fetchArray(SQLITE3_ASSOC);
						if(empty($array) == true) {
							$sender->sendMessage($this->plugin->formatMessage("§7Sua facção não possui pedido!"));
							return true;
						}
						$allyTime = $array["timestamp"];
						$currentTime = time();
						if( ($currentTime - $allyTime) <= 60 ) { //This should be configurable
                            $requested_fac = $this->plugin->getPlayerFaction($array["requestedby"]);
                            $sender_fac = $this->plugin->getPlayerFaction($player);
							$this->plugin->db->query("DELETE FROM alliance WHERE player='$lowercaseName';");
							$sender->sendMessage($this->plugin->formatMessage("§7Sua Facção cancelou.", true));
							$this->plugin->getServer()->getPlayerExact($array["requestedby"])->sendMessage($this->plugin->formatMessage("$player da $sender_fac negou pedido!"));
                            
						} else {
							$sender->sendMessage($this->plugin->formatMessage("§7Tempo acabou!"));
							$this->plugin->db->query("DELETE * FROM alliance WHERE player='$lowercaseName';");
						}
					}
                           
                    
					/////////////////////////////// ABOUT ///////////////////////////////
					
					if(strtolower($args[0] == 'about')) {
						$sender->sendMessage(TextFormat::GREEN . "[ORIGINAL] FactionsPro v1.4.0 by " . TextFormat::BOLD . "Tethered_");
						$sender->sendMessage(TextFormat::GOLD . "[MODDED-DEV] FactionsPro v1.8.0 by " . TextFormat::BOLD . "@PrimusLV");
						$sender->sendMessage(TextFormat::GOLD . "[MODDED-DEV] Factions Pro v1.10.0 by " . TextFormat::BOLD . "@zMarcos360");
					}
					////////////////////////////// CHAT ////////////////////////////////
					if(strtolower($args[0]) == "chat" or strtolower($args[0]) == "c"){
						if($this->plugin->isInFaction($player)){
							if(isset($this->plugin->factionChatActive[$player])){
								unset($this->plugin->factionChatActive[$player]);
								$sender->sendMessage($this->plugin->formatMessage("§7Chat desativado!", false));
								return true;
							}
							else{
								$this->plugin->factionChatActive[$player] = 1;
								$sender->sendMessage($this->plugin->formatMessage("§7Chat ativado!", false));
								return true;
							}
						}
						else{
							$sender->sendMessage($this->plugin->formatMessage("§7Voce Não possui facção"));
							return true;
						}
					}
					/////////////////////////////// MAP, map by Primus (no compass) ////////////////////////////////
					// Coupon for compass: G1wEmEde0mp455

					if(strtolower($args[0] == "mapa")) {
						$map = $this->getMap($sender, self::MAP_WIDTH, self::MAP_HEIGHT, $sender->getYaw(), $this->plugin->prefs->get("PlotSize"));
						foreach($map as $line) {
							$sender->sendMessage($line);
						}
						return true;
					}

					////////////////////////////// BALANCE, by primus ;) ///////////////////////////////////////

					if(strtolower($args[0]) === "bal" or strtolower($args[0]) === "banco") {
						if(!$this->plugin->isInFaction($player)) {
							$sender->sendMessage($this->plugin->formatMessage("§7Voce Não possui facção!", false));
							return true;
						}
						$faction = $this->plugin->getPlayerFaction($player);
						$balance = $this->plugin->getBalance($faction);
						$sender->sendMessage($this->plugin->formatMessage("§7Money da facção: " . TextFormat::GOLD . "$".$balance));
						return true;
					}
					if(strtolower($args[0]) === "saca" or strtolower($args[0]) === "wd") {
						if(($e = $this->plugin->getEconomy()) == null) {
							$sender->sendMessage($this->plugin->formatMessage("§7Ação Não permitida", true));
							return true;
						}
						if(!isset($args[1])) {
							$sender->sendMessage($this->plugin->formatMessage("Use: §a/f saca <amount>"));
							return true;
						}
						if(!is_numeric($args[1])) {
							$sender->sendMessage($this->plugin->formatMessage("Amount must be numeric value", false));
							return true;
						}
						if(!$this->plugin->isInFaction($player)) {
							$sender->sendMessage($this->plugin->formatMessage("§7Voce Não possui facção!", false));
							return true;
						}
						if(!$this->plugin->isLeader($player)) {
							$sender->sendMessage($this->plugin->formatMessage("§7Apenas o líder pode retirar da conta bancária facção!", false));
							return true;
						}
						$faction = $this->plugin->getPlayerFaction($sender->getName());
						if( (($fM = $this->plugin->getBalance($faction)) - ($args[1]) ) < 0 ) {
							$sender->sendMessage($this->plugin->formatMessage("§7Sua Facção não possui money!", false));
							return true;
						}
						$this->plugin->takeFromBalance($faction, $args[1]);
						$e->addMoney($sender, $args[1], false, "faction bank account");
						$sender->sendMessage($this->plugin->formatMessage("$".$args[1]." §7concedido a partir de facção", true));
						return true;
					}
					if(strtolower($args[0]) === "depositar") {
						if(($e = $this->plugin->getEconomy()) === null) {
							$sender->sendMessage($this->plugin->formatMessage("§7Ação Não permitida", true));
							return true;
						}
						if(!isset($args[1])) {
							$sender->sendMessage($this->plugin->formatMessage("Use: §a/f Depositar <amount>"));
							return true;
						}
						if(!is_numeric($args[1])) {
							$sender->sendMessage($this->plugin->formatMessage("§7Apenas numeros", false));
							return true;
						}
						if(!$this->plugin->isInFaction($player)) {
							$sender->sendMessage($this->plugin->formatMessage("§7Voce Não é Lider", false));
							return true;
						}
						if( ( ($e->myMoney($sender)) - ($args[1]) ) < 0 ) {
							$sender->sendMessage($this->plugin->formatMessage("§7Insuficiente money!", false));
							return true;
						}
						$faction = $this->plugin->getPlayerFaction($sender->getName());
						if($e->reduceMoney($sender, $args[1], false, "faction bank account") === \onebone\economyapi\EconomyAPI::RET_SUCCESS) {
							$this->plugin->addToBalance($faction, $args[1]);
							$sender->sendMessage($this->plugin->formatMessage("$".$args[1]." Depositado"));
						}
						return true;
					}
					if(strtolower($args[0]) == "allychat" or strtolower($args[0]) == "ac"){
						if($this->plugin->isInFaction($player)){
							if(isset($this->plugin->allyChatActive[$player])){
								unset($this->plugin->allyChatActive[$player]);
								$sender->sendMessage($this->plugin->formatMessage("§7Chat da aliança desativado!", false));
								return true;
							}
							else{
								$this->plugin->allyChatActive[$player] = 1;
								$sender->sendMessage($this->plugin->formatMessage("§7Chat da aliança ativado!", false));
								return true;
							}
						}
						else{
							$sender->sendMessage($this->plugin->formatMessage("§7Voce Não possui facção"));
							return true;
						}
					}
				}
			}
		} else {
			$this->plugin->getServer()->getLogger()->info($this->plugin->formatMessage("Please run command in game"));
		}
	}

		public function getMap(Player $observer, int $width, int $height, int $inDegrees, int $size = 16) { // No compass
		$to = (int)sqrt($size);
		$centerPs = new Vector3($observer->x >> $to, 0, $observer->z >> $to);

		$map = [];

		$centerFaction = $this->plugin->factionFromPoint($observer->getFloorX(), $observer->getFloorZ());
		$centerFaction = $centerFaction ? $centerFaction : "Wilderness";

		$head = TextFormat::GREEN . " (" . $centerPs->getX() . "," . $centerPs->getZ() . ") " . $centerFaction . " " . TextFormat::WHITE;
		$head = TextFormat::GOLD . str_repeat("_", (($width - strlen($head)) / 2)) . ".[" . $head . TextFormat::GOLD . "]." . str_repeat("_", (($width - strlen($head)) / 2));

		$map[] = $head;

		$halfWidth = $width / 2;
		$halfHeight = $height / 2;
		$width = $halfWidth * 2 + 1;
		$height = $halfHeight * 2 + 1;

		$topLeftPs = new Vector3($centerPs->x + -$halfWidth, 0, $centerPs->z + -$halfHeight);

		// Get the compass
		$asciiCompass = self::getASCIICompass($inDegrees, TextFormat::RED, TextFormat::GOLD);

		// Make room for the list of names
		$height--;

		/** @var string[] $fList */
		$fList = array();
		$chrIdx = 0;
		$overflown = false;
		$chars = self::MAP_KEY_CHARS;

		// For each row
		for ($dz = 0; $dz < $height; $dz++) {
			// Draw and add that row
			$row = "";
			for ($dx = 0; $dx < $width; $dx++) {
				if ($dx == $halfWidth && $dz == $halfHeight) {
					$row .= (self::MAP_KEY_SEPARATOR);
					continue;
				}

				if (!$overflown && $chrIdx >= strlen(self::MAP_KEY_CHARS)) $overflown = true;
				$herePs = $topLeftPs->add($dx, 0, $dz);
				$hereFaction = $this->plugin->factionFromPoint($herePs->x << $to, $herePs->z << $to);
				$contains = in_array($hereFaction, $fList, true);
				if ($hereFaction === NULL) {
					$row .= self::MAP_KEY_WILDERNESS;
				} elseif (!$contains && $overflown) {
					$row .= self::MAP_KEY_OVERFLOW;
				} else {
					if (!$contains) $fList[$chars{$chrIdx++}] = $hereFaction;
					$fchar = array_search($hereFaction, $fList);
					$row .= $this->getColorForTo($observer, $hereFaction) . $fchar;
				}
			}

			$line = $row; // ... ---------------

			// Add the compass
			if ($dz == 0) $line = $asciiCompass[0] . "" . substr($row, 3 * strlen(self::MAP_KEY_SEPARATOR));
			if ($dz == 1) $line = $asciiCompass[1] . "" . substr($row, 3 * strlen(self::MAP_KEY_SEPARATOR));
			if ($dz == 2) $line = $asciiCompass[2] . "" . substr($row, 3 * strlen(self::MAP_KEY_SEPARATOR));

			$map[] = $line;
		}
		$fRow = "";
		foreach ($fList as $char => $faction) {
			$fRow .= $this->getColorForTo($observer, $faction) . $char . ": " . $faction . " ";
		}
		if ($overflown) $fRow .= self::MAP_OVERFLOW_MESSAGE;
		$fRow = trim($fRow);
		$map[] = $fRow;

		return $map;
	}

	public function getColorForTo(Player $player, $faction) {
		if($this->plugin->getPlayerFaction($player->getName()) === $faction) {
			return TextFormat::GREEN;
		}
		return TextFormat::LIGHT_PURPLE;
	}

	const N = 'N';
    const NE = '/';
    const E = 'E';
    const SE = '\\';
    const S = 'S';
    const SW = '/';
    const W = 'W';
    const NW = '\\';

    public static function getASCIICompass($degrees, $colorActive, $colorDefault) : array
    {
        $ret = [];
        $point = self::getCompassPointForDirection($degrees);

        $row = "";
        $row .= ($point === self::NW ? $colorActive : $colorDefault) . self::NW;
        $row .= ($point === self::N ? $colorActive : $colorDefault) . self::N;
        $row .= ($point === self::NE ? $colorActive : $colorDefault) . self::NE;
        $ret[] = $row;

        $row = "";
        $row .= ($point === self::W ? $colorActive : $colorDefault) . self::W;
        $row .= $colorDefault . "+";
        $row .= ($point === self::E ? $colorActive : $colorDefault) . self::E;
        $ret[] = $row;

        $row = "";
        $row .= ($point === self::SW ? $colorActive : $colorDefault) . self::SW;
        $row .= ($point === self::S ? $colorActive : $colorDefault) . self::S;
        $row .= ($point === self::SE ? $colorActive : $colorDefault) . self::SE;
        $ret[] = $row;

        return $ret;
    }

    public static function getCompassPointForDirection($degrees)
    {
        $degrees = ($degrees - 180) % 360;
        if ($degrees < 0)
            $degrees += 360;

        if (0 <= $degrees && $degrees < 22.5)
            return self::N;
        elseif (22.5 <= $degrees && $degrees < 67.5)
            return self::NE;
        elseif (67.5 <= $degrees && $degrees < 112.5)
            return self::E;
        elseif (112.5 <= $degrees && $degrees < 157.5)
            return self::SE;
        elseif (157.5 <= $degrees && $degrees < 202.5)
            return self::S;
        elseif (202.5 <= $degrees && $degrees < 247.5)
            return self::SW;
        elseif (247.5 <= $degrees && $degrees < 292.5)
            return self::W;
        elseif (292.5 <= $degrees && $degrees < 337.5)
            return self::NW;
        elseif (337.5 <= $degrees && $degrees < 360.0)
            return self::N;
        else
            return null;
    }

}
