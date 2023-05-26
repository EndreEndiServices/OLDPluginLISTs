<?php
/*
__PocketMine Plugin__
name=Sethome
description=Set's a home for a player
version=1.0b
author=Lambo
class=setHome
apiversion=11
*/

class setHome implements Plugin{
    private $api;

    public function __construct(ServerAPI $api, $server = false){
        $this->api = $api;
    }

    public function init(){
      $this->config = new Config($this->api->plugin->configPath($this)."messages.yml", CONFIG_YAML, array("sethome"=>"Home set.","no-home"=>"You haven't set a home yet!","home"=>"Teleporting..."));
      $this->homes = new Config($this->api->plugin->configPath($this)."homes.yml", CONFIG_YAML);
      $this->api->console->register('sethome', "Sets your home to your position.", array($this, 'commandHandler'));
      $this->api->console->register('home', "Teleports you to your home.",array($this, 'commandHandler'));

      $this->api->ban->cmdWhitelist("sethome");
      $this->api->ban->cmdWhitelist("home");
    }

    public function commandHandler($cmd, $params, $issuer, $alias){
      switch($cmd){
         case 'sethome':
           $user = $issuer->username;
           $this->homes->set($user,array("x"=>round($issuer->entity->x), "y"=>round($issuer->entity->y), "z"=>round($issuer->entity->z)));
           $issuer->sendChat($this->config->get("sethome"));
           $this->homes->save();
           break;
         case 'home':
           $level = $issuer->level;
           $user = $issuer->username;
           if($this->homes->exists($user))
           {
               $poss = $this->homes->get($user);
               $x = $poss["x"];
               $y = $poss["y"];
                $z = $poss["z"];

                $pos = new Position($x,$y,$z,$level);
                $issuer->teleport($pos);
                $issuer->sendChat($this->config->get("home"));
           }else{$issuer->sendChat($this->config->get("no-home"));}
         break;
      }
    }

    public function __destruct(){
      $this->config->save();
    }
}

?>