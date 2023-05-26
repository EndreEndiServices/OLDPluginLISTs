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
namespace CRCore\events;
use pocketmine\level\Position;
use pocketmine\permission\DefaultPermissions;
use pocketmine\permission\Permission;
use pocketmine\utils\Config;
use CRCore\core\loader;
use CRCore\core\api\HomeAPI;
use CRCore\core\api\TPAPI;
use CRCore\commands\HomeCommand;
use CRCore\commands\TpaCommand;
use CRCore\commands\TpDenyCommand;
use CRCore\commands\TpAcceptCommand;

class TeleportListener{
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


        /** @var PrestigeSocietyCore */
        public $plugin;

        /**
         *
         * PrestigeSocietyStaffMode constructor.
         *
         * @param PrestigeSocietyCore $core
         *
         */
        public function __construct(Loader $main){
                $this->plugin = $main;
        }

        public function init(){
                $this->plugin->saveResource('teleport_messages.yml');
                $this->messages = new Config($this->plugin->getDataFolder() . "teleport_messages.yml", Config::YAML);
                $this->TP_API = new TPAPI($this);
                $this->HomeAPI = new HomeAPI($this);
                $this->registerCommands();{
                }
        }

        public function registerCommands(){
                $commands = [
                    'home' => new HomeCommand($this),
                    'tpaccept' => new TpAcceptCommand($this),
                    'tpa' => new TpaCommand($this),
                    'tpdeny' => new TpDenyCommand($this)
                ];

                foreach($commands as $fallback => &$command){
                        $this->plugin->getServer()->getCommandMap()->register($fallback, $command);
                }
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
         * @param $m
         * @return string
         *
         */
        static public function colorMessage($m){
                return str_replace("&", "\xc2\xa7", $m);
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