<?php

namespace MTCore;

use MTCore\MTCore;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class Auth{

    public $plugin;

    public function __construct(MTCore $plugin){
        $this->plugin = $plugin;
    }

    public function register(Player $p, $heslo){
        if($this->isRegistered($p)){
            $p->sendMessage($this->plugin->getPrefix().TextFormat::GOLD."You are already registered");
            return;
        }
        if($this->plugin->isAuthed($p)){
            $p->sendMessage($this->plugin->getPrefix().TextFormat::GOLD."You are already logged in");
            return;
        }
        if(strlen($heslo) >= 4 && strlen($heslo) <= 20){
            $this->plugin->mysqlmgr->setPassword($p->getName(), $heslo);
            $this->plugin->mysqlmgr->setIP($p->getName(), $p->getAddress());
            $this->plugin->mysqlmgr->setUUID($p->getName(), $p->getUniqueId());
            $this->plugin->players[strtolower($p->getName())]['auth'] = true;
            $this->plugin->checkRank($p);
            $p->sendMessage($this->plugin->getPrefix().TextFormat::GREEN."You have been successfully registered");
            return;
        }
        $p->sendMessage($this->plugin->getPrefix().TextFormat::RED."Password lenght must be between 4 and 20 characters");
    }

    public function login(Player $p, $heslo){
        if(!$this->isRegistered($p)){
            $p->sendMessage($this->plugin->getPrefix().TextFormat::RED."You are not registered\n§cUse /register [password] [password]");
            return;
        }
        if($this->plugin->isAuthed($p)){
            $p->sendMessage($this->plugin->getPrefix().TextFormat::RED."You are already logged in");
            return;
        }
        if($heslo !== $this->plugin->mysqlmgr->getPassword($p->getName())){
            $p->sendMessage($this->plugin->getPrefix().TextFormat::RED."Wrong password");
            return;
        }
        $this->plugin->mysqlmgr->setIP($p->getName(), $p->getAddress());
        $this->plugin->mysqlmgr->setUUID($p->getName(), $p->getUniqueId());
        $this->plugin->players[strtolower($p->getName())]['auth'] = true;
        $this->plugin->checkRank($p);
        $p->sendMessage($this->plugin->getPrefix().TextFormat::GREEN."You have been successfully logged in");
    }

    public function isRegistered(Player $p){
        $heslo = $this->plugin->mysqlmgr->getPlayer($p->getName())["heslo"];
        if(is_string($heslo)){
            if(strlen($heslo) >= 4){
                return true;
            }
        }
        return false;
    }

    public function checkLogin(Player $p){
        if($this->plugin->isAuthed($p)){
            return;
        }
        if(!$this->isRegistered($p)){
            $p->sendMessage($this->plugin->getPrefix().TextFormat::GOLD."Use /register [password] [password]");
            return;
        }
        $ip = $p->getAddress();
        $id = $p->getUniqueId();
        if($this->plugin->mysqlmgr->getIP($p->getName()) == $ip && $this->plugin->mysqlmgr->getUUID($p->getName()) == $id){
            $p->sendMessage($this->plugin->getPrefix().TextFormat::GREEN."You have been successfully logged in");
            $this->plugin->players[strtolower($p->getName())]['auth'] = true;
            $this->plugin->checkRank($p);
            return;
        }
        $p->sendMessage($this->plugin->getPrefix().TextFormat::GOLD."Use /login [password]");
    }
}