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
namespace CRCore\commands;
use pocketmine\block\Block;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use CRCore\events\TeleportListener;
class TpAcceptCommand extends Command implements PluginIdentifiableCommand{
        /**
         * @var TeleportListener
         */
        protected $base;

        /**
         * TpaAcceptCommand constructor.
         * @param TeleportListener $base
         */
        public function __construct(TeleportListener $base){
                $this->base = $base;
                parent::__construct("tpaccept", "accept a tp request from someone", "/tpaccept", ["tp-accept"]);
        }

        /**
         *
         * @param CommandSender $sender
         * @param string        $commandLabel
         * @param string[]      $args
         *
         * @return mixed|void
         *
         * @throws \InvalidStateException
         *
         */
        public function execute(CommandSender $sender, $commandLabel, array $args){
                if(!$sender instanceof Player){
                        $sender->sendMessage("Please run this command in-game.");
                        return;
                }
                if($this->base->getTPAPI()->hasRequest($sender)){

                        $acre = $this->base->getTPAPI()->acceptRequest($sender);
                        if($acre[0]){

                                if($acre[1]->hasPermission("tpa.instant")){

                                        $message = $this->base->getMessages()->get("tp_accept_request");
                                        $message = str_replace("@player", $acre[1]->getName(), $message);
                                        $sender->sendMessage(TeleportListener::colorMessage($message));

                                        $message1 = $this->base->getMessages()->get("tp_request_accepted");
                                        $message1 = str_replace("@player", $sender->getName(), $message);
                                        $acre[1]->sendMessage(TeleportListener::colorMessage($message));

                                        return;
                                }
                        }
                        $sender->sendMessage(TeleportListener::colorMessage($this->base->getMessages()->get("tp_accept_player_offline")));
                }
        }

        /**
         *
         * @return Plugin
         *
         */
        public function getPlugin(): Plugin{
                return $this->base->plugin;
        }
}