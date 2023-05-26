<?php
/*
 *   Teleport: A TP essentials plugin
 *
 *   This program is free software: you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation, either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   This program is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace PrestigeSociety\Teleport;

use pocketmine\level\Position;
use pocketmine\permission\DefaultPermissions;
use pocketmine\permission\Permission;
use pocketmine\utils\Config;
use PrestigeSociety\Core\PrestigeSocietyCore;
use PrestigeSociety\Teleport\Commands\HomeCommand;
use PrestigeSociety\Teleport\Commands\SetSpawnCommand;
use PrestigeSociety\Teleport\Commands\TpAcceptCommand;
use PrestigeSociety\Teleport\Commands\TpaCommand;
use PrestigeSociety\Teleport\Commands\TpDenyCommand;
use PrestigeSociety\Teleport\Commands\WarpCommand;

class PrestigeSocietyTeleport {
	/**
	 *
	 * @var Config
	 *
	 */
	public $messages;
	/**
	 *
	 * @var TPAPI
	 *
	 */
	public $TP_API;
	/**
	 *
	 * @var HomeAPI
	 *
	 */
	public $HomeAPI;

	/**
	 *
	 * @var WarpAPI
	 *
	 */
	public $WarpAPI;

	/** @var PrestigeSocietyCore */
	public $plugin;

	/**
	 *
	 * PrestigeSocietyStaffMode constructor.
	 *
	 * @param PrestigeSocietyCore $core
	 *
	 */
	public function __construct(PrestigeSocietyCore $core){
		$this->plugin = $core;
	}

	/**
	 *
	 * @param $m
	 * @return string
	 *
	 */
	static public function colorMessage($m){
		return str_replace("&", "\xc2\xa7", $m);
	}

	public function init(){
		$this->plugin->saveResource('teleport_messages.yml');
		$this->messages = new Config($this->plugin->getDataFolder() . "teleport_messages.yml", Config::YAML);
		$this->TP_API = new TPAPI($this);
		$this->HomeAPI = new HomeAPI($this);
		$this->WarpAPI = new WarpAPI($this);
		$this->registerCommands();

		$this->plugin->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this->plugin);

		DefaultPermissions::registerPermission(new Permission("warp.all", "", Permission::DEFAULT_OP));

		foreach($this->getWarpAPI()->getWarps() as $warp){
			DefaultPermissions::registerPermission(new Permission("warp." . $warp, "", Permission::DEFAULT_OP));
		}
	}

	public function registerCommands(){
		$commands = [
			'home'     => new HomeCommand($this, $this->plugin),
			'warp'     => new WarpCommand($this),
			'setspawn' => new SetSpawnCommand($this),
			'tpaccept' => new TpAcceptCommand($this),
			'tpa'      => new TpaCommand($this),
			'tpdeny'   => new TpDenyCommand($this),
		];

		foreach($commands as $fallback => &$command){
			$this->plugin->getServer()->getCommandMap()->register($fallback, $command);
		}
	}

	/**
	 *
	 * @return WarpAPI
	 *
	 */
	public function getWarpAPI(): WarpAPI{
		return $this->WarpAPI;
	}

	/**
	 *
	 * @param Position $pos
	 *
	 */
	public function setSpawn(Position $pos){
		$this->plugin->getConfig()->set("spawn_x", $pos->x);
		$this->plugin->getConfig()->set("spawn_y", $pos->y);
		$this->plugin->getConfig()->set("spawn_z", $pos->z);
		$this->plugin->getConfig()->set("spawn_level", $pos->level->getName());
		$this->plugin->getConfig()->save();
		$this->plugin->getConfig()->reload();
	}

	/**
	 *
	 * @return null|Position
	 *
	 */
	public function getSpawn(){
		$x = $this->plugin->getConfig()->get("spawn_x");
		$y = $this->plugin->getConfig()->get("spawn_y");
		$z = $this->plugin->getConfig()->get("spawn_z");
		$level = $this->plugin->getServer()->getLevelByName($this->plugin->getConfig()->get("spawn_level"));
		if($level !== null){
			return new Position($x, $y, $z, $level);
		}

		return null;
	}

	/**
	 *
	 * @return Config
	 *
	 */
	public function getMessages(){
		return $this->messages;
	}

	/**
	 *
	 * @return HomeAPI
	 *
	 */
	public function getHomeAPI(): HomeAPI{
		return $this->HomeAPI;
	}

	/**
	 *
	 * @return TPAPI
	 *
	 */
	public function getTPAPI(): TPAPI{
		return $this->TP_API;
	}
}