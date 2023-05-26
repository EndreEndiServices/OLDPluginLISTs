<?php

namespace MCFTWARS;

use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use pocketmine\command\PluginCommand;
use pocketmine\command\Command;
use pocketmine\utils\Utils;
use pocketmine\level\Position;
use MCFTWARS\task\WarStartTask;

class MCFTWARS extends PluginBase implements Listener {
	private $m_version = 1, $db_version = 1, $plugin_version;
	public $messages, $warDB, $config, $itemlist;
	private $newversion = false;
	public $war;
	public $eventlistener;
	public function onEnable() {
		return $this->disablePlugin();
		@mkdir ( $this->getDataFolder () );
		$this->messages = $this->Loadmessage ();
		$this->warDB = $this->Loadplugindata ( "warDB.json" );
		$this->LoadConfig();
		$this->LoadItemlist();
		$this->registerCommand ( $this->get ( "command" ), "mcftwars.command.allow", $this->get ( "command-description" ), $this->get ( "command-help" ) );
		$this->getServer ()->getPluginManager ()->registerEvents ( $this, $this );
		$this->eventlistener = new EventListener ( $this );
		$this->war = new war ( $this );
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new WarStartTask($this), $this->config["war-minute"]*20*60 + $this->config["rest-second"]*20);
	}
	public function disablePlugin() {
		$this->getServer ()->getLogger ()->error ( "알수없는 오류가 발생해 플러그인을 비활성화 합니다." );
		$this->getServer ()->getPluginManager ()->disablePlugin ( $this );
	}
	public function registerCommand($name, $permission, $description = "", $usage = "") {
		$commandMap = $this->getServer ()->getCommandMap ();
		$command = new PluginCommand ( $name, $this );
		$command->setDescription ( $description );
		$command->setPermission ( $permission );
		$command->setUsage ( $usage );
		$commandMap->register ( $name, $command );
	}
	public function onDisable() {
		$this->save ( "warDB.json", $this->warDB );
	}
	public function Loadmessage() {
		$this->saveResource ( "messages.yml" );
		$this->UpdateMessage ( "messages.yml" );
		return (new Config ( $this->getDataFolder () . "messages.yml", Config::YAML ))->getAll ();
	}
	public function UpdateMessage($ymlfile) {
		$yml = (new Config ( $this->getDataFolder () . "messages.yml", Config::YAML ))->getAll ();
		if (! isset ( $yml ["m_version"] )) {
			$this->saveResource ( $ymlfile, true );
		} else if ($yml ["m_version"] < $this->m_version) {
			$this->saveResource ( $ymlfile, true );
		}
	}
	/**
	 *
	 * @param string $dbname        	
	 * @param string $save
	 *        	true 로 설정시 resource 폴더에서 플러그인 데이터폴더로 불러옴.
	 */
	public function Loadplugindata($dbname, $save = false) {
		if ($save == true) {
			$this->saveResource ( $dbname );
			$this->UpdateDB ( $dbname );
		}
		return (new Config ( $this->getDataFolder () . $dbname, Config::JSON ))->getAll ();
	}
	public function UpdateDB($dbname) {
		$db = (new Config ( $this->getDataFolder () . $dbname, Config::JSON ))->getAll ();
		if (! isset ( $db ["db_version"] )) {
			$this->saveResource ( $dbname, true );
		} else if ($db ["db_version"] < $this->db_version) {
			$this->saveResource ( $dbname, true );
		}
	}
	public function save($dbname, $var) {
		$save = new Config ( $this->getDataFolder () . $dbname, Config::JSON );
		$save->setAll ( $var );
		$save->save ();
	}
	public function get($text) {
		return $this->messages [$this->messages ["default-language"] . "-" . $text];
	}
	public function alert(CommandSender $sender, $message = "", $prefix = NULL) {
		if ($prefix == NULL) {
			$prefix = $this->get ( "default-prefix" );
		}
		$sender->sendMessage ( TextFormat::RED . $prefix . " $message" );
	}
	public function message(CommandSender $sender, $message = "", $prefix = NULL) {
		if ($prefix == NULL) {
			$prefix = $this->get ( "default-prefix" );
		}
		$sender->sendMessage ( TextFormat::DARK_AQUA . $prefix . " $message" );
	}
	public function NoticeVersionLicense() {
		$this->getLogger ()->alert ( "이 플러그인은 maru-EULA 라이센스를 사용합니다." );
		$this->getLogger ()->alert ( "이 플러그인 사용시 라이센스에 동의하는것으로 간주합니다." );
		$this->getLogger ()->alert ( "라이센스: https://github.com/wsj7178/PMMP-plugins/blob/master/LICENSE.md" );
		$this->plugin_version = $this->getDescription ()->getVersion ();
		$version = json_decode ( Utils::getURL ( "https://raw.githubusercontent.com/wsj7178/PMMP-plugins/master/version.json" ), true );
		if ($this->plugin_version < $version ["MCFTWARS"]) {
			$this->getLogger ()->notice ( "플러그인의 새로운 버전이 존재합니다. 플러그인을 최신 버전으로 업데이트 해주세요!" );
			$this->getLogger ()->notice ( "현재버전: " . $this->plugin_version . ", 최신버전: " . $version ["MCFTWARS"] );
			$this->newversion = true;
		}
	}
	public function LoadConfig() {
		$this->saveResource ( "config.yml" );
		$this->config = (new Config ( $this->getDataFolder () . "config.yml", Config::YAML ))->getAll ();
	}
	public function LoadItemlist() {
		$this->saveResource("itemlist.yml");
		$this->itemlist = (new Config($this->getDataFolder()."itemlist.yml", Config::YAML))->getAll();
	}
	// ============================================================================
	public function onCommand(CommandSender $sender, Command $command, $label, Array $args) {
		if (strtolower ( $command ) == $this->get ( "command" )) { // TODO <- 빈칸에 명령어
			if (! isset ( $args [0] )) {
				if ($sender->isOp ()) {
					$this->alert ( $sender, $this->get ( "command-ophelp" ) );
				} else {
					$this->alert ( $sender, $this->get ( "command-help" ) );
				}
				return true;
			}
			switch (strtolower ( $args [0] )) {
				case $this->get ( "command-participation" ) :
					if(!$this->war->isPlay()) {
						$this->alert($sender, $this->get("not-resume-war"));
						break;
					}
					if($this->war->getSoldier($sender) != null) {
						$this->alert($sender, $this->get("already-participate"));
						break;
					}
					$this->war->participate ( $sender );
					$this->message( $sender, str_replace ( "%team%", $this->war->getSoldier ( $sender )->getTeam ()->getTeamName (), $this->get ( "success-participate" ) ) );
					break;
				case $this->get ( "command-spawn" ) :
					if (! $sender->isOp ()) {
						$this->alert ( $sender, $this->get ( "dont-have-permission" ) );
						break;
					}
					if (! isset ( $args [1] )) {
						$this->alert ( $sender, $this->get ( "spawn-help" ) );
						break;
					}
					$pos = new Position ( $sender->getX (), $sender->getY (), $sender->getZ (), $sender->getLevel () );
					switch (strtolower ( $args [1] )) {
						case $this->get ( "command-red" ) :
							$this->war->redteam->setSpawnPoint ( $pos );
							$this->message ( $sender, str_replace ( "%team%", $args [1], $this->get ( "success-setspawn" ) ) );
							break;
						case $this->get ( "command-blue" ) :
							$this->war->blueteam->setSpawnPoint ( $pos );
							$this->message ( $sender, str_replace ( "%team%", $args [1], $this->get ( "success-setspawn" ) ) );
							break;
						case $this->get("command-lobby") :
							$this->war->setLobby($pos);
							$this->message($sender, $this->get("success-setlobby"));
							break;
						default :
							$this->alert ( $sender, $this->get ( "spawn-help" ) );
					}
					break;
				case $this->get ( "command-leave" ) :
					if ($this->war->leaveWar ( $sender )) {
						$this->message ( $sender, $this->get ( "leave-from-war" ) );
					} else {
						$this->alert ( $sender, $this->get ( "you-dont-participate" ) );
					}
					break;
				default :
					if ($sender->isOp ()) {
						$this->alert ( $sender, $this->get ( "command-ophelp" ) );
					} else {
						$this->alert ( $sender, $this->get ( "command-help" ) );
					}
					break;
			}
		}
		return true;
	}
}