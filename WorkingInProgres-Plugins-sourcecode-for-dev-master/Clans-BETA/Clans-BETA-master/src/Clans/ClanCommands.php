<?php

namespace Clans;

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
use Clans\Clan;
use Clans\utils\ClanInvite;
use Clans\utils\Clans\utils;
use Clans\utils\Home;
use pocketmine\level\Position;

class ClanCommands {
	
	public $plugin;
	
	public function __construct(Main $pg) {
		$this->plugin = $pg;
	}
	
	public function onCommand(CommandSender $sender, Command $command, $label, array $args) {
		if($sender instanceof Player) {
			
			$ses = $this->plugin->getSession($sender->getPlayer()); //new
			
			$player = $sender->getPlayer()->getName();
			if(strtolower($command->getName('c'))) {
				if(empty($args)) {
					$sender->sendMessage($this->plugin->formatMessage("Please use /c help for a list of commands"));
					return true;
				}
				if(count($args == 2)) {
					
					/////////////////////////////// CREATE ///////////////////////////////
					
					if($args[0] == "create") {
						if($this->plugin->commands->get("/c create") == false) {
							$sender->sendMessage($this->plugin->formatMessage("This command has been disabled, please ask Admin if you think this is a mistake."));
							return true;
						}
						if(!isset($args[1])) {
							$sender->sendMessage($this->plugin->formatMessage("Usage: /c create <clan name>"));
							return true;
						}
						if(!(ctype_alnum($args[1]))) {
							$sender->sendMessage($this->plugin->formatMessage("You may only use letters and numbers!"));
							return true;
						}
						if($this->plugin->isNameBanned($args[1])) {
							$sender->sendMessage($this->plugin->formatMessage("This name is not allowed."));
							return true;
						}
						if($this->plugin->clanExists($args[1]) == true ) {
							$sender->sendMessage($this->plugin->formatMessage("clan already exists"));
							return true;
						}
						if(strlen($args[1]) > $this->plugin->prefs->get("Maximum clan Name Length")) {
							$sender->sendMessage($this->plugin->formatMessage("This name is too long. Please try again!"));
							return true;
						}
						if($ses->inFaction()) {
							$sender->sendMessage($this->plugin->formatMessage("You must leave this clan first"));
							return true;
						} else {
							$factionName = $args[1];
							$f = new Clan($this->plugin, $args[1], $sender->getPlayer()); //TODO: Split into two lines
							$ses->updateFaction();
							$sender->sendMessage($this->plugin->formatMessage("Clan successfully created!", true));
							return true;
						}
					}
					
					/////////////////////////////// INVITE ///////////////////////////////
					
					if($args[0] == "invite") {
						if($this->plugin->commands->get("/c invite") == false) {
							$sender->sendMessage($this->plugin->formatMessage("This command has been disabled, please ask Admin if you think this is a mistake."));
							return true;
						}
						if(!isset($args[1])) {
							$sender->sendMessage($this->plugin->formatMessage("Usage: /c invite <player>"));
							return true;
						}
						if($this->plugin->prefs->get("OnlyLeadersAndOfficersCanInvite") && !($ses->isLeader())) {
							$sender->sendMessage($this->plugin->formatMessage("You are not allowed to invite."));
							return true;
						}
						if($ses->getFaction()->isFull()) {
							$sender->sendMessage($this->plugin->formatMessage("Clan is full. Please kick players to make room."));
							return true;
						}
						$invited = $this->plugin->getServer()->getPlayer($args[1]);
						if(!$invited instanceof Player) {
							$sender->sendMessage($this->plugin->formatMessage("Player not online!"));
							return true;
						}
						if($this->plugin->getSession($invited)->inFaction()) {
							$sender->sendMessage($this->plugin->formatMessage("Player is currently in a clan"));
							return true;
						}
						$invite = new FactionInvite($this->plugin->getSession($invited), $ses);
						$sender->sendMessage($this->plugin->formatMessage($invited->getName() . " has been invited!", true));
						$invited->sendMessage($this->plugin->formatMessage("You have been invited to " . $ses->getClan()->getName() . ". Type '/c accept' or '/c deny' into chat to accept or deny!", true));
					}
					
					/////////////////////////////// LEADER ///////////////////////////////
					
					if($args[0] == "leader") {
						if($this->plugin->commands->get("/c leader") == false) {
							$sender->sendMessage($this->plugin->formatMessage("This command has been disabled, please ask Admin if you think this is a mistake."));
							return true;
						}
						if(!isset($args[1])) {
							$sender->sendMessage($this->plugin->formatMessage("Usage: /c leader <player>"));
							return true;
						}
						if(!$ses->inFaction()) {
							$sender->sendMessage($this->plugin->formatMessage("You must be in a clan to use this!"));
							return true;
						}
						if(!$ses->isLeader()) {
							$sender->sendMessage($this->plugin->formatMessage("You must be leader to use this"));
							return true;
						}
						if($ses->getFaction() != $this->plugin->getSession($this->plugin->getServer()->getPlayer($args[1]))->getFaction()) {
							$sender->sendMessage($this->plugin->formatMessage("Player is not in your clan"));
							return true;
						}		
						if(!$this->plugin->getServer()->getPlayerExact($args[1]) instanceOf Player) {
							$sender->sendMessage($this->plugin->formatMessage("Player not online!"));
							return true;
						}
						$newLeader = $this->plugin->getSession($this->plugin->getServer()->getPlayer($args[1]));
						
						$ses->getFaction()->setRank($sender->getPlayer(), "Member");
						$ses->getFaction()->setRank($newLeader->getPlayer(), "Leader"); //TODO: make setRank availble through session rather than clan?
	
						$sender->sendMessage($this->plugin->formatMessage("You are no longer leader!", true));
						$newLeader->getPlayer()->sendMessage($this->plugin->formatMessage("You are now leader of " . $ses->getClan()->getName() . "!",  true));
						$ses->updateTag();
						$newLeader->updateTag();
						}
					
					/////////////////////////////// PROMOTE ///////////////////////////////
					
					if($args[0] == "promote") {
						if($this->plugin->commands->get("/c promote") == false) {
							$sender->sendMessage($this->plugin->formatMessage("This command has been disabled, please ask Admin if you think this is a mistake."));
							return true;
						}
						if(!isset($args[1])) {
							$sender->sendMessage($this->plugin->formatMessage("Usage: /c promote <player>"));
							return true;
						}
						if(!$ses->inFaction()) {
							$sender->sendMessage($this->plugin->formatMessage("You must be in a clan to use this!"));
							return true;
						}
						if(!$ses->isLeader()) {
							$sender->sendMessage($this->plugin->formatMessage("You must be leader to use this"));
							return true;
						}
						if(!$ses->getFaction()->hasPlayer_string($args[1])) {
							$sender->sendMessage($this->plugin->formatMessage("Player is not in this clan!"));
							return true;
						}
						$demoted = $this->plugin->getSessionFromName($args[1]);
						if(strcmp($ses->getClan()->getRank_string($args[1]),"Officer") == 0) {
							$sender->sendMessage($this->plugin->formatMessage("Player is already Officer"));
							return true;
						}
						$ses->getClan()->setRank_string($args[1], "Officer");
						$sender->sendMessage($this->plugin->formatMessage("" . $args[1] . " has been promoted to Officer.", true));
						if(!$promoted == false) { $demoted->getPlayer()->sendMessage($this->plugin->formatMessage("You were Promoted to Officer.", true));
							$promoted->updateRank();
							$promoted->updateTag();
						}
					}
					
					/////////////////////////////// DEMOTE ///////////////////////////////
					
					if($args[0] == "demote") {
						if($this->plugin->commands->get("/c demote") == false) {
							$sender->sendMessage($this->plugin->formatMessage("This command has been disabled, please ask Admin if you think this is a mistake."));
							return true;
						}
						if(!isset($args[1])) {
							$sender->sendMessage($this->plugin->formatMessage("Usage: /c demote <player>"));
							return true;
						}
						if(!$ses->inFaction()) {
							$sender->sendMessage($this->plugin->formatMessage("You must be in a clan to use this!"));
							return true;
						}
						if(!$ses->isLeader()) {
							$sender->sendMessage($this->plugin->formatMessage("You must be leader to use this"));
							return true;
						}
						if(!$ses->getClan()->hasPlayer_string($args[1])) {
							$sender->sendMessage($this->plugin->formatMessage("Player is not in this clan!"));
							return true;
						}
						$demoted = $this->plugin->getSessionFromName($args[1]);
						if(strcmp($ses->getFaction()->getRank_string($args[1]),"Officer") != 0) {
							$sender->sendMessage($this->plugin->formatMessage("Player is already Member"));
							return true;
						}
						$ses->getFaction()->setRank_string($args[1], "Member");
						$sender->sendMessage($this->plugin->formatMessage("" . $args[1] . " has been demoted to Member.", true));
						if(!$demoted == false) { $demoted->getPlayer()->sendMessage($this->plugin->formatMessage("You were demoted to Member.", true));
							$demoted->updateRank();
							$demoted->updateTag();
						}
					}
					
					/////////////////////////////// KICK ///////////////////////////////
					//TODO: what if kicked is offline??
					if($args[0] == "kick") {
						if($this->plugin->commands->get("/c kick") == false) {
							$sender->sendMessage($this->plugin->formatMessage("This command has been disabled, please ask Admin if you think this is a mistake."));
							return true;
						}
						if(!isset($args[1])) {
							$sender->sendMessage($this->plugin->formatMessage("Usage: /c kick <player>"));
							return true;
						}
						if(!$ses->inClan()) {
							$sender->sendMessage($this->plugin->formatMessage("You must be in a clan to use this!"));
							return true;
						}
						if(!$ses->isLeader()) {
							$sender->sendMessage($this->plugin->formatMessage("You must be leader to use this"));
							return true;
						}
						if(!$ses->getClan()->hasPlayer_string($args[1])) {
							$sender->sendMessage($this->plugin->formatMessage("Player is not in this clan!"));
							return true;
						}
						if(strtolower($ses->getPlayer()->getName()) == strtolower($args[1])) {
							$sender->sendMessage($this->plugin->formatMessage("You may not kick yourself. Use /c leave or /c del instead."));
							return true;
						}
						
						$ses->getFaction()->removePlayer_string($args[1]);
						
						$sender->sendMessage($this->plugin->formatMessage("You successfully kicked $args[1]!", true));
						$players[] = $this->plugin->getServer()->getOnlinePlayers();
						if($this->plugin->getServer()->getPlayer($args[1]) instanceof Player) {
							$factionName = $ses->getFaction()->getName();
							$this->plugin->getServer()->getPlayer($args[1])->sendMessage($this->plugin->formatMessage("You have been kicked from \n $clanName.", true));
							$this->plugin->getSession($this->plugin->getServer()->getPlayer($args[1]))->updateClan();							
							$this->plugin->getSession($this->plugin->getServer()->getPlayer($args[1]))->updateTag();
							return true;
						}
					}
					
					/////////////////////////////// DESC ///////////////////////////////
					
					if(strtolower($args[0]) == "desc") {
						if($this->plugin->commands->get("/c desc") == false) {
							$sender->sendMessage($this->plugin->formatMessage("This command has been disabled, please ask Admin if you think this is a mistake."));
							return true;
						}
						if(!$ses->inFaction()) {
							$sender->sendMessage($this->plugin->formatMessage("You must be in a clan to use this!"));
							return true;
						}
						if(!$ses->isLeader()) {
							$sender->sendMessage($this->plugin->formatMessage("You must be leader to use this"));
							return true;
						}
						$desc = "";
						for($i = 0; $i < count($args) - 1; $i++)
						{
							if($i == 0) { $desc = $args[$i + 1]; } else { $desc = $desc . " " . $args[$i + 1]; }
						}
						$ses->getFaction()->setDescription($desc);
						$sender->sendMessage(TextFormat::GREEN . "Updated description to: " . TextFormat::WHITE . $desc);
					}
					
					/////////////////////////////// INFO ///////////////////////////////
					
					if(strtolower($args[0]) == 'info') {
						if($this->plugin->commands->get("/c info") == false) {
							$sender->sendMessage($this->plugin->formatMessage("This command has been disabled, please ask Admin if you think this is a mistake."));
							return true;
						}
						if(isset($args[1])) {
							if( !(ctype_alnum($args[1])) | !($this->plugin->factionExists($args[1]))) {
								$sender->sendMessage($this->plugin->formatMessage("Clan does not exist"));
								return true;
							}
							$faction = $this->plugin->getClan($args[1])->getName();
							$leader = $this->plugin->getClan($args[1])->getLeader();
							$numPlayers = $this->plugin->getClan($args[1])->getNumberMembers();
							$sender->sendMessage(TextFormat::BOLD . TextFormat::BLUE . "------[ " . TextFormat::GOLD . $faction . TextFormat::BLUE . " ]------");
							$sender->sendMessage(TextFormat::BOLD . TextFormat::BLUE . "Description: " . TextFormat::RESET . TextFormat::WHITE . "$desc");
							$sender->sendMessage(TextFormat::BOLD . TextFormat::BLUE . "Leader: " . TextFormat::RESET . TextFormat::WHITE . "$leader");
							$sender->sendMessage(TextFormat::BOLD . TextFormat::BLUE . "# of Players: " . TextFormat::RESET . TextFormat::WHITE . "$numPlayers");
						} else {
							$faction = $ses->getClan()->getName();
							$desc = $ses->getClan()->getDescription();
							$leader = $ses->getClan()->getLeader();
							$numPlayers = $ses->getClan()->getNumberMembers();
							$sender->sendMessage(TextFormat::BOLD . TextFormat::BLUE . "------[ " . TextFormat::GOLD . $faction . TextFormat::BLUE . " ]------");
							$sender->sendMessage(TextFormat::BOLD . TextFormat::BLUE . "Description: " . TextFormat::RESET . TextFormat::WHITE . "$desc");
							$sender->sendMessage(TextFormat::BOLD . TextFormat::BLUE . "Leader: " . TextFormat::RESET . TextFormat::WHITE . "$leader");
							$sender->sendMessage(TextFormat::BOLD . TextFormat::BLUE . "# of Players: " . TextFormat::RESET . TextFormat::WHITE . "$numPlayers");
						}
					}
						
						
					/////////////////////////////// HOME ///////////////////////////////
					
					if(strtolower($args[0] == "home")) {
						if($this->plugin->commands->get("/c home") == false) {
							$sender->sendMessage($this->plugin->formatMessage("This command has been disabled, please ask Admin if you think this is a mistake."));
							return true;
						}
						if(!$ses->inClan()) {
							$sender->sendMessage($this->plugin->formatMessage("You must be in a clan to do this."));
						}
						if(isset($args[1]) && strtolower($args[1] == "set")) {
							if(!$ses->isLeader()) {
								$sender->sendMessage($this->plugin->formatMessage("You must be leader to set home."));
								return true;
							}
							$ses->getClan()->setHome($sender->getPlayer()->getPosition());
							$sender->sendMessage($this->plugin->formatMessage("Home updated!", true));
							return true;
						}
						if(isset($args[1]) && strtolower($args[1] == "unset")) {
							if(!$ses->isLeader()) {
								$sender->sendMessage($this->plugin->formatMessage("You must be leader to set home."));
								return true;
							}
							$ses->getClan()->unsetHome();
							$sender->sendMessage($this->plugin->formatMessage("Home has been unset.", true));
							return true;
						}
						if($ses->getClan()->hasHome()) {
							$sender->getPlayer()->teleport($ses->getFaction()->getHome());
							$sender->sendMessage($this->plugin->formatMessage("Teleported home.", true));
							return true;
						} else {
							$sender->sendMessage($this->plugin->formatMessage("Home is not set."));
						}
					}
					
					if(strtolower($args[0]) == "help") {
						if(!isset($args[1]) || $args[1] == 1) {
							$sender->sendMessage(TextFormat::BLUE . "FactionsPro Help Page 1 of 2" . TextFormat::RED . 
									"\n/c about
									\n/c accept
									\n/c create <name>
									\n/c del
									\n/c demote <player>
									\n/c deny
									\n/c desc <description>");
							return true;
						}
						if($args[1] == 2) {
							$sender->sendMessage(TextFormat::BLUE . "FactionsPro Help Page 2 of 2" . TextFormat::RED . 
									"\n/c home <set/unset>
									\n/c help <page>
									\n/c info <faction>
									\n/c invite <player>
									\n/c kick <player>
									\n/c leader <player>
									\n/c leave
									\n/c promote <player>");
							return true;
						}
					}
				}
				if(count($args == 1)) {
					
					/////////////////////////////// ACCEPT ///////////////////////////////
					
					if(strtolower($args[0]) == "accept") {
						if(!$ses->hasInvite())
						{
							$sender->sendMessage($this->plugin->formatMessage("You have not been invited to any clans!"));
							return true;
						}
						if($ses->getInvite()->getTimeout() <= time())
						{
							$sender->sendMessage($this->plugin->formatMessage("Invite has timed out!"));
							$ses->deregisterInvite();
							return true;
						}
						$ses->joinFaction($ses->getInvite()->getFaction());
						$sender->sendMessage($this->plugin->formatMessage("You successfully joined " . $ses->getClan()->getName() . "!", true));
						if($ses->getInvite()->getInvitedby() instanceof Player) 
						{
							$ses->getInvite()->getInvitedby()->sendMessage($this->plugin->formatMessage($sender->getPlayer()->getName() . " joined the clan!", true));
						}
						$ses->updateTag();
						$ses->deregisterInvite();
					}
					
					/////////////////////////////// DENY ///////////////////////////////
					
					if(strtolower($args[0]) == "deny") {
					if(!$ses->hasInvite())
						{
							$sender->sendMessage($this->plugin->formatMessage("You have not been invited to any clans!"));
							return true;
						}
						if($ses->getInvite()->getTimeout() <= time())
						{
							$sender->sendMessage($this->plugin->formatMessage("Invite has timed out!"));
							$ses->deregisterInvite();
							return true;
						}
						$sender->sendMessage($this->plugin->formatMessage("Invite declined."));
						if($ses->getInvite()->getInvitedby() instanceof Player) 
						{
							$ses->getInvite()->getInvitedby()->sendMessage($this->plugin->formatMessage($sender->getPlayer()->getName() . " declined your invite."));
						}
						$ses->deregisterInvite();
					}
					
					/////////////////////////////// DELETE ///////////////////////////////
					
					if(strtolower($args[0]) == "del") {
						if($this->plugin->commands->get("/c delete") == false) {
							$sender->sendMessage($this->plugin->formatMessage("This command has been disabled, please ask Admin if you think this is a mistake."));
							return true;
						}
						if(!$ses->inClan()) {
							$sender->sendMessage($this->plugin->formatMessage("You are not in a clan!"));
						}
						if(!$ses->isLeader()) {
							$sender->sendMessage($this->plugin->formatMessage("You are not leader!"));
						}
						$ses->getClan()->delete();
						$sender->sendMessage($this->plugin->formatMessage("Clan successfully disbanded!", true));
						$ses->updateTag();
					}
					
					/////////////////////////////// LEAVE ///////////////////////////////
					
					if(strtolower($args[0] == "leave")) {
						if($this->plugin->commands->get("/c leave") == false) {
							$sender->sendMessage($this->plugin->formatMessage("This command has been disabled, please ask Admin if you think this is a mistake."));
							return true;
						}
						if(!$ses->isLeader()) {
							$faction = $ses->getClan()->getName();
							$ses->leaveClan();
							$sender->sendMessage($this->plugin->formatMessage("You successfully left $clan", true));
							$ses->updateTag();
						} else {
							$sender->sendMessage($this->plugin->formatMessage("You must delete or give\nleadership first!"));
						}
					}
					
					/////////////////////////////// ABOUT ///////////////////////////////
					
					if(strtolower($args[0] == 'about')) {
						$sender->sendMessage(TextFormat::BLUE . "Clans v1.5b3 by " . TextFormat::BOLD . "Edwardthedog2" . TextFormat::RESET . TextFormat::BLUE . "Profile: " . TextFormat::ITALIC . "https://forums.pocketmine.net/members/edwardallington.21838/");
					}
				}
		} else {
			$this->plugin->getServer()->getLogger()->info($this->plugin->formatMessage("Please run command in game"));
		}
	}
}
}
