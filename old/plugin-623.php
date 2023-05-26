<?php

/*
  __PocketMine Plugin__
  name=PerWorldGamemode
  description=Control Gamemode on differant worlds
  version=1.2.6
  author=tschrock
  class=PerWorldGamemode
  apiversion=12
 */

class PerWorldGamemode implements Plugin {

    private $api, $list, $config;

    public function __construct(ServerAPI $api, $server = false) {
        $this->api = $api;
        $this->list = array();
    }

    public function init() {
        $this->api->console->register("perworldgamemode", "/pwgm set <gamemode> (world) or /pwgm <exclude|include> <player>", array($this, "commandHandler"));
        $this->api->console->alias("pwgm", "perworldgamemode");

        $this->api->schedule(20, array($this, "timeHandler"), array(), true);

        $this->api->addHandler("player.quit", array($this, "eventHandler"));
        $this->api->addHandler("player.spawn", array($this, "eventHandler"));
        $this->api->addHandler("player.teleport.level", array($this, "eventHandler"));

        $this->config = new Config($this->api->plugin->configPath($this) . "config.yml", CONFIG_YAML, array(
            "countdownTime" => 5,
            "excludedPlayers" => array(),
            "worlds" => array(),
        ));
    }

    public function eventHandler($data, $event) {
        switch ($event) {
            case "player.quit":
                $this->checkPlayer($data, true);
                break;
            case "player.spawn":
                break;
            case "player.teleport.level":
                $this->checkPlayer($data);
                break;
        }
    }

    public function commandHandler($cmd, $params, $issuer) {
        if (strtolower($cmd) == "perworldgamemode") {

            switch (strtolower(array_shift($params))) {
                case "set":

                    // Is gamemode? $this->checkGamemode($mode) !== false
                    // Is world? $this->api->level->get($world) !== false
                    // Is Player? $issuer instanceof Player
                    // Get player's world. $issuer->level->getname();
                    // "You must put a gamemode!"
                    // "Thats not a supported gamemode!"
                    // "You must put a world!"
                    // "That world doesn\'t exist! (World names are case-sensitive)."
                    // "Usage: /pwgm set <gamemode> (world)"



                    if (count($params) == 1) {
                        if ($this->checkGamemode($params[0]) !== false) {
                            $mode = $this->checkGamemode($params[0]);

                            if ($issuer instanceof Player) {
                                $world = $issuer->level->getname();
                            } else {
                                return "You must put a world!";
                            }
                        } else {
                            return "You must put a correct gamemode! (survival, creative, view, or adventure)";
                        }
                    } elseif (count($params) == 2) {

                        if ($this->checkGamemode($params[0]) !== false) {
                            $mode = $this->checkGamemode($params[0]);

                            if ($this->api->level->get($params[1]) !== false) {
                                $world = $params[1];
                            } else {
                                return "You must put a correct world! (World names are case-sensitive)";
                            }
                        } elseif ($this->checkGamemode($params[1]) !== false) {
                            $mode = $this->checkGamemode($params[1]);

                            if ($this->api->level->get($params[0]) !== false) {
                                $world = $params[0];
                            } else {
                                return "You must put a correct world! (World names are case-sensitive)";
                            }
                        } else {
                            return "You must put a correct gamemode! (survival, creative, view, or adventure)";
                        }
                    } else {
                        return "Usage: /pwgm set <gamemode> (world)";
                    }


                    $this->setWorldGamemode($world, $mode);
                    return "Set world $world to gamemode $mode.";

                case "exclude":
                    if (is_null($playerpar = array_shift($params))) {
                        return "Usage: /pwgm exclude <player>";
                    }
                    if (false !== $player = $this->api->player->get($playerpar)) {
                    if ($this->addprop("excludedPlayers", $player->iusername)) {
                        return $player->iusername . " is now excluded.";
                    } else {
                        return $player->iusername . " is already excluded.";
                    }
                } else {
                    return "$playerpar is not a player!";
                }
                case "include":
                    if (is_null($playerpar = array_shift($params))) {
                        return "Usage: /pwgm include <player>";
                    }

                    if (false !== $player = $this->api->player->get($playerpar)) {
                    if ($this->removeprop("excludedPlayers", $player->iusername)) {
                        return $player->iusername . " is now included.";
                    } else {
                        return $player->iusername . " is already included.";
                    }
                } else {
                    return "$playerpar is not a player!";
                }
                default:
                    return "/pwgm set <survival|creative> (world) or /pwgm <exclude|include> <player>";
            }
        }
    }

    public function timeHandler() {

        $removekeys = array();

        foreach ($this->list as $key => &$countdown) {
            $countdown[0]->sendChat("Gamemode change in $countdown[2]!");
            if ($countdown[2] <= 0) {
                $countdown[0]->setGamemode($countdown[1]);
                $removekeys[] = $key;
            } else {
                $countdown[2] = $countdown[2] - 1;
            }
        }

        foreach ($removekeys as $value) {
            unset($this->list[$value]);
        }
    }

    public function startCountdown($player, $gamemode) {
        $player->sendChat("Gamemode Change! You will need to log back in!!!!");
        $this->list[$player->iusername] = array(
            $player,
            $gamemode,
            5
        );
    }

    public function checkPlayer($data, $immediate = false) {

        if ($data instanceof Player) {
            $player = $data;
            $world = $player->level->getName();
        } elseif (is_array($data) && isset($data["player"]) && isset($data["target"])) {
            $player = $data["player"];
            $world = $data["target"]->getName();
        } else {
            return false;
        }

        $this->cancelCountdown($player);

        //console($world);
        //console($player->iusername);
        //console($player->getGamemode());

        //var_dump($this->config->get("excludedPlayers"));
        //var_dump(array_map('strtolower', $this->config->get("excludedPlayers")));
        //var_dump(in_array(strtolower($player->iusername), array_map('strtolower', $this->config->get("excludedPlayers"))));

        if (!in_array(strtolower($player->iusername), array_map('strtolower', $this->config->get("excludedPlayers"))) && ($gm = $this->checkGamemode($this->getWorldGamemode($world))) !== false && $player->gamemode !== ($gm = $this->getGamemodeNumber($gm))) {


            //console($world);
            //console($player->iusername);
            //console($player->getGamemode());

            if ($immediate) {
                $player->setGamemode($gm);
            } else {
                $this->startCountdown($player, $gm);
            }

            console($world);
            console($player->iusername);
            console($player->getGamemode());
        } else {
            return false;
        }
    }

    public function cancelCountdown($player) {
        if (isset($this->list[$player->iusername])) {
            $player->sendChat("Gamemode change canceled!");
            unset($this->list[$player->iusername]);
        }
    }

    public function getWorldGamemode($world) {
        return (isset($this->config->get("worlds")[$world])) ? $this->config->get("worlds")[$world] : $this->api->getProperty("gamemode", "survival");
    }

    public function setWorldGamemode($world, $gamemode) {
        $worlds = $this->config->get("worlds");
        $worlds[$world] = $gamemode;
        $this->config->set("worlds", $worlds);
        $this->config->save();
    }

    public function unsetWorldGamemode($world) {
        $worlds = $this->config->get("worlds");
        unset($worlds[$world]);
        $this->config->set("worlds", $worlds);
        $this->config->save();
    }

    public function removeprop($arrname, $value) {
        if (in_array(strtolower($value), array_map('strtolower', $conf = $this->config->get($arrname)))) {
            $this->config->set($arrname, array_diff($conf, array($value)));
            $this->config->save();
            return true;
        } else {
            return false;
        }
    }

    public function addprop($arrname, $value) {
        if (!in_array(strtolower($value), array_map('strtolower', $conf = $this->config->get($arrname)))) {
            $arr = $this->config->get($arrname);
            $arr[] = $value;
            $this->config->set($arrname, $arr);
            $this->config->save();
            return true;
        } else {
            return false;
        }
    }

    public function checkGamemode($gamemode) {
        switch (strtolower($gamemode)) {
            case "survival":
            case "s":
                return "survival";
            case "creative":
            case "c":
                return "creative";
            case "view":
            case "v":
                return "view";
            case "adventure":
            case "a":
                return "adventure";
            default:
                return ($gamemode === SURVIVAL) ? "survival" : (($gamemode === CREATIVE) ? "creative" : (($gamemode === VIEW) ? "view" : (($gamemode === ADVRENTURE) ? "adventure" : false)));
        }
    }

    public function getGamemodeNumber($gamemode) {
        return ("survival" === $gm = $this->checkGamemode($gamemode)) ? SURVIVAL : (("creative" === $gm) ? CREATIVE : $gm);
    }

    public function __destruct() {
        
    }

}
