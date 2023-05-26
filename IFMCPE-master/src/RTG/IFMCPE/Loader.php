<?php

/* 
 * Copyright (C) 2017 RTG
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace RTG\IFMCPE;

/* Essentials */
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Server;
use pocketmine\Player;
use pocketmine\utils\TextFormat as TF;
use pocketmine\utils\Config;
use pocketmine\event\player\PlayerJoinEvent;

class Loader extends PluginBase implements Listener {
    
    public $config;

    public function onEnable() {
        
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->saveDefaultConfig();
        @mkdir($this->getDataFolder());
        $this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML, array(
            "jcmd" => array()
        ));
        
    }
    
    public function onJoin(PlayerJoinEvent $e) {
        
        $cfg = $this->config->getAll();
        $p = $e->getPlayer();
        
            foreach($cfg['jcmd'] as $cmd) {
                
                $this->getServer()->dispatchCommand(new \pocketmine\command\ConsoleCommandSender(), str_replace("{player}", $p->getName(), $cmd));
                
            }
        
    }
    
    public function onCommand(CommandSender $sender, Command $command, $label, array $args) {
        
        switch(strtolower($command->getName())) {
            
            case "if":
                
                if($sender->isOp() or $sender->hasPermission("ifmcpe.command")) {
                    
                    if(isset($args[0])) {
                        
                        switch (strtolower($args[0])) {
                            
                            case "event":
                                
                                if(isset($args[1])) {
                                    
                                    switch(strtolower($args[1])) {
                                        
                                        case "join":
                                            
                                            if(isset($args[2])) {
                                                
                                                $c = $this->config->get("jcmd");
                                                $d = implode(" ", array_splice($args, 2));
                                                
                                                array_push($c, $d);
                                                
                                                $this->config->set('jcmd', $c);
                                                $this->config->save();
                                                $sender->sendMessage("Added!");
                                                
                                            }
                                            else {
                                                $this->args($sender);
                                            }
                                            
                                            return true;
                                            
                                    }
                                      
                                }
                                else {
                                    $this->args($sender);
                                }
                                
                                return true;
                                
                            case "list":
                                
                                $c = $this->config->get("jcmd");
                                
                                    foreach($c as $list) {
                                        
                                        if(count($c) > 0) {
                                        
                                            $sender->sendMessage(" -- Your Commands! -- ");
                                            $sender->sendMessage($list);
                                        
                                        }
                                        else {
                                            $sender->sendMessage(TF::RED . "You have 0 commands!");
                                        }
                                        
                                    }
                                    
                                return true;
                            
                            case "rm":
                                
                                if(isset($args[1])) {
                                    
                                    $l = implode(" ", array_splice($args, 1));
                                    $c = $this->config->get("jcmd");
                                        
                                        if(in_array($l, $c)) {
                                            
                                            unset($c[array_search($l, $c)]);
                                            $this->config->set('jcmd', $c);
                                            $this->config->save();
                                            $sender->sendMessage("You have removed " . TF::GREEN . $l . TF::RESET . " from the list!");
                                            
                                        }
                                        else {
                                            $sender->sendMessage(TF::GREEN . $l . TF::RESET . " isnt in the list!");
                                        }
                                         
                                }
                                else {
                                    $this->args($sender);
                                }
                            
                        }
                          
                    }
                    else {
                        $this->args($sender);
                    }
                      
                }
                else {
                    $sender->sendMessage(TF::RED . "You have no permission to use this command!");
                }
            
        }
        
    }
    
    public function args($p) {
        
        $p->sendMessage(TF::YELLOW . " -- Invalid Args -- ");
        $p->sendMessage(TF::RED . "You can use {player}, this acts as all players!");
        $p->sendMessage(TF::GREEN . "You can also send this commands to individual players by replacing {player} with their name!");
        $p->sendMessage("/if event [join] [what to do?]");
        $p->sendMessage("/if list - lists all your saved commands!");
        $p->sendMessage("/if rm [command] - removes your command!");
        
    }
    
    public function onDisable() {
        $this->config->save();
    }
    
}