<?php

namespace ParadoxUHC\Commands;

use ParadoxUHC\UHC;
use ParadoxUHC\Commands\BaseCommand;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat as TF;
use pocketmine\utils\Config;

class MainHostCommand extends BaseCommand {
    private $plugin;
    public $config;

    /**
     * MainUHCCommand constructor.
     * @param UHC $plugin
     */
    public function __construct(UHC $plugin) {
        $this->plugin = $plugin;
        parent::__construct($plugin, "host", "Allows for hosts to set scenarios, invite people, and many other options.", "/host scenario [set|rem]", ["h"]);
        $this->setPermission("uhc.perms.host");
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     * @return null;
     */
    public function execute(CommandSender $sender, $commandLabel, array $args)
    {
        $this->config = new Config($this->plugin->getDataFolder()."config.yml");
        $this->config->reload();
        if($sender->hasPermission("uhc.perms.host")) {
            if (isset($args[0])) {
                switch($args[0]){
                    case "scenario":
                        if(isset($args[1])){
                            switch(strtolower($args[1])){
                                case "set":
                                    if(isset($args[2])){
                                        switch(strtolower($args[2])){
                                            case 'cutclean':
                                                if($this->config->get("cutclean") === "true"){
                                                    $sender->sendMessage(TF::RED.'Cutclean is already a scenario!');
                                                    return;
                                                }
                                                $this->config->set("cutclean", "true");
                                                $this->config->save();
                                                $this->config->reload();
                                                $sender->sendMessage(TF::GOLD.'[Cutclean]'.TF::GRAY.' has successfully been added as a scenario!');
                                                break;
                                            case 'diamondless':
                                                if($this->config->get("diamondless") === "true"){
                                                    $sender->sendMessage(TF::RED.'Diamondless is already a scenario!');
                                                    return;
                                                }
                                                if($this->config->get("blood-diamonds") === "true"){
                                                    $sender->sendMessage(TF::RED.'You have Blood Diamonds enabled as a scenario, please remove it to enable Diamondless!');
                                                }
                                                $this->config->set("diamondless", "true");
                                                $this->config->save();
                                                $this->config->reload();
                                                $sender->sendMessage(TF::GOLD.'[Diamondless]'.TF::GRAY.' has successfully been added as a scenario!');
                                                break;
                                            case 'goldless':
                                                if($this->config->get("goldless") === "true"){
                                                    $sender->sendMessage(TF::RED.'Goldless is already a scenario!');
                                                    return;
                                                }
                                                if($this->config->get("barebones") === "true"){
                                                    $sender->sendMessage(TF::RED.'You have Barebones enabled as a command, please remove it to enable Goldless.');
                                                }
                                                else {
                                                    $this->config->set("goldless", "true");
                                                    $this->config->save();
                                                    $this->config->reload();
                                                    $sender->sendMessage(TF::GOLD . '[Goldless]' . TF::GRAY . ' has successfully been added as a scenario!');
                                                }
                                                break;
                                            case 'blood-diamonds':
                                                if($this->config->get("blood-diamonds") === "true"){
                                                    $sender->sendMessage(TF::RED.'Blood Diamonds is already a scenario!');
                                                    return;
                                                }
                                                if($this->config->get("diamondless") === "true"){
                                                    $sender->sendMessage(TF::RED.'You have Diamondless enabled as a scenario, please remove it to enable Blood Diamonds');
                                                }
                                                else {
                                                    $this->config->set("blood-diamonds", "true");
                                                    $this->config->save();
                                                    $this->config->reload();
                                                    $sender->sendMessage(TF::GOLD . '[Blood Diamonds]' . TF::GRAY . ' has successfully been added as a scenario!');
                                                }
                                                break;
                                            case 'fireless':
                                                if($this->config->get("fireless") == true){
                                                    $sender->sendMessage(TF::RED.'Fireless is already a scenario!');
                                                    return;
                                                }
                                                else {
                                                    $this->config->set("fireless", true);
                                                    $this->config->save();
                                                    $this->config->reload();
                                                    $sender->sendMessage(TF::GOLD . '[Fireless]' . TF::GRAY . ' has successfully been added as a scenario!');
                                                }
                                                break;
                                            case 'barebones':
                                                if($this->config->get("barebones") == true){
                                                    $sender->sendMessage(TF::RED.'Barebones is already a scenario!');
                                                    return;
                                                }
                                                if($this->config->get("goldless") == true){
                                                    $sender->sendMessage(TF::RED.'You have Goldless enabled as a scenario, please remove it to enable Barebones.');
                                                    return;
                                                }
                                                if($this->config->get("diamondless") == true){
                                                    $sender->sendMessage(TF::RED.'You have Diamondless enabled as a scenario, please remove it to enable Barebones.');
                                                    return;
                                                }
                                                if($this->config->get("blood-diamonds") == true){
                                                    $sender->sendMessage(TF::RED.'You have Blood Diamonds enabled as a scenario, please remove it to enable Barebones.');
                                                    return;
                                                }
                                                $this->config->set("barebones", true);
                                                $this->config->save();
                                                $this->config->reload();
                                                $sender->sendMessage(TF::GOLD . '[Barebones]' . TF::GRAY . ' has successfully been added as a scenario!');
                                                break;
                                            case "split":
                                                if($this->config->get("split") === "true"){
                                                    $sender->sendMessage(TF::RED.'Split is already enabled!');
                                                    return;
                                                }
                                                $this->config->set("split", "true");
                                                $this->config->save();
                                                $sender->sendMessage(TF::GOLD.'[Split]'.TF::GRAY. ' is now enabled!');
                                                break;
                                            case "nonsplit":
                                                if($this->config->get("split") === "false"){
                                                    $sender->sendMessage(TF::RED.'Non-Split is already enabled!');
                                                    return;
                                                }
                                                $this->config->set("split", "false");
                                                $this->config->save();
                                                $sender->sendMessage(TF::GOLD.'[Non-Split]'.TF::GRAY. ' is now enabled!');
                                                break;
                                            case "both":
                                                if($this->config->get("split") === "both"){
                                                    $sender->sendMessage(TF::RED.'Both are already enabled!');
                                                    return;
                                                }
                                                $this->config->set("split", "both");
                                                $this->config->save();
                                                $sender->sendMessage(TF::GOLD.'[Both]'.TF::GRAY. ' are now enabled!');
                                                break;
                                            case "nofall":
                                                if($this->config->get("nofall") == true){
                                                    $sender->sendMessage(TF::RED.'NoFall is already enabled!');
                                                    return;
                                                }
                                                $this->config->set("nofall", true);
                                                $this->config->save();
                                                $sender->sendMessage(TF::GOLD.'[No Fall]'.TF::GRAY. ' is now enabled!');
                                                break;
                                            case "cripple":
                                                if($this->config->get("cripple") == true){
                                                    $sender->sendMessage(TF::RED.'Cripple is already enabled!');
                                                    return;
                                                }
                                                $this->config->set("cripple", true);
                                                $this->config->save();
                                                $sender->sendMessage(TF::GOLD.'[Cripple]'.TF::GRAY. ' is now enabled!');
                                                break;
                                            case "lights-out":
                                                if($this->config->get("lights-out") == true){
                                                    $sender->sendMessage(TF::RED.'Lights Out is already enabled!');
                                                    return;
                                                }
                                                $this->config->set("lights-out", true);
                                                $this->config->save();
                                                $sender->sendMessage(TF::GOLD.'[LightsOut]'.TF::GRAY. ' is now enabled!');
                                                break;
                                            case "cat-eyes":
                                                if($this->config->get("cat-eyes") == true){
                                                    $sender->sendMessage(TF::RED.'CatEyes is already enabled!');
                                                    return;
                                                }
                                                $this->config->set("cat-eyes", true);
                                                $this->config->save();
                                                $sender->sendMessage(TF::GOLD.'[CatEyes]'.TF::GRAY. ' is now enabled!');
                                                break;
                                            case "vampire":
                                                if($this->config->get("vampire") == true){
                                                    $sender->sendMessage(TF::RED.'Vampire is already enabled!');
                                                    return;
                                                }
                                                $this->config->set("vampire", true);
                                                $this->config->save();
                                                $sender->sendMessage(TF::GOLD.'[Vampire]'.TF::GRAY. ' is now enabled!');
                                                break;
                                            case "amphibian":
                                                if($this->config->get("amphibian") == true){
                                                    $sender->sendMessage(TF::RED.'Vampire is already enabled!');
                                                    return;
                                                }
                                                $this->config->set("amphibian", true);
                                                $this->config->save();
                                                $sender->sendMessage(TF::GOLD.'[Amphibian]'.TF::GRAY. ' is now enabled!');
                                                break; 
                                            case "chicken":
                                                if($this->config->get("chicken") == true){
                                                    $sender->sendMessage(TF::RED.'Chicken is already enabled!');
                                                    return;
                                                }
                                                $this->config->set("chicken", true);
                                                $this->config->save();
                                                $sender->sendMessage(TF::GOLD.'[Chicken]'.TF::GRAY. ' is now enabled!');
                                                break;
                                            case "timebomb":
                                                if($this->config->get("timebomb") == true){
                                                    $sender->sendMessage(TF::RED.'Timebomb is already enabled!');
                                                    return;
                                                }
                                                $this->config->set("timebomb", true);
                                                $this->config->save();
                                                $sender->sendMessage(TF::GOLD.'[Timebomb]'.TF::GRAY. ' is now enabled!');
                                                break;

                                            case "multiore":
                                                if(isset($args[3])){
                                                    if(is_numeric($args[3])){
                                                        if($args[3] > 4){
                                                            $sender->sendMessage(TF::RED."You can't go above 4!");
                                                            return;
                                                        }
                                                        if($args[3] < 0){
                                                            $sender->sendMessage(TF::RED."You can't go below 0!");
                                                            return;
                                                        }
                                                        $this->config->set("multi-ore", $args[3]);
                                                        $this->config->save();
                                                        if($args[3] == 1){
                                                            $sender->sendMessage(TF::GREEN."The multiplier has been reset!");
                                                            return;
                                                        }
                                                        if($args[3] == 2){
                                                            $sender->sendMessage(TF::GOLD."[Double Ore]".TF::GRAY." has been enabled!");
                                                            return;
                                                        }
                                                        if($args[3] == 3){
                                                            $sender->sendMessage(TF::GOLD."[Triple Ore]".TF::GRAY." has been enabled!");
                                                            return;
                                                        }
                                                        if($args[3] == 4){
                                                            $sender->sendMessage(TF::GOLD."[Quad Ore]".TF::GRAY." has been enabled!");
                                                            return;
                                                        }
                                                    }
                                                    else {
                                                        $sender->sendMessage(TF::RED."Please choose a valid number!");
                                                        return;
                                                    }
                                                }
                                        }
                                    }
                                    else {
                                        $sender->sendMessage(TF::RED.'Usage: /host scenario set [scenario]');
                                    }
                                    break;
                                case "rem":
                                    if(isset($args[2])){
                                        switch(strtolower($args[2])){
                                            case "cutclean":
                                                if($this->config->get("cutclean") == false){
                                                    $sender->sendMessage(TF::RED.'Cutclean is already disabled!');
                                                }
                                                else {
                                                    $this->config->set("cutclean", false);
                                                    $this->config->save();
                                                    $this->config->reload();
                                                    $sender->sendMessage(TF::GOLD.'[Cutclean]'.TF::GRAY.' has now been disabled!');
                                                }
                                                break;
                                            case "diamondless":
                                                if($this->config->get("diamondless") == false){
                                                    $sender->sendMessage(TF::RED.'Diamondless is already disabled!');
                                                }
                                                else {
                                                    $this->config->set("diamondless", false);
                                                    $this->config->save();
                                                    $this->config->reload();
                                                    $sender->sendMessage(TF::GOLD . '[Diamondless]' . TF::GRAY . ' has now been disabled!');
                                                }
                                                break;
                                            case "goldless":
                                                if($this->config->get("goldless") == false){
                                                    $sender->sendMessage(TF::RED.'Goldless is already disabled!');
                                                }
                                                else {
                                                    $this->config->set("goldless", false);
                                                    $this->config->save();
                                                    $this->config->reload();
                                                    $sender->sendMessage(TF::GOLD . '[Goldless]' . TF::GRAY . ' has now been disabled!');
                                                }
                                                break;
                                            case "blood-diamonds":
                                                if($this->config->get("blood-diamonds") == false){
                                                    $sender->sendMessage(TF::RED.'Blood Diamonds is already disabled!');
                                                }
                                                else {
                                                    $this->config->set("blood-diamonds", false);
                                                    $this->config->save();
                                                    $this->config->reload();
                                                    $sender->sendMessage(TF::GOLD . '[Blood Diamonds]' . TF::GRAY . ' has now been disabled!');
                                                }
                                                break;
                                            case "barebones":
                                                if($this->config->get("barebones") == false){
                                                    $sender->sendMessage(TF::RED.'Barebones is already disabled!');
                                                }
                                                else {
                                                    $this->config->set("barebones", false);
                                                    $this->config->save();
                                                    $this->config->reload();
                                                    $sender->sendMessage(TF::GOLD . '[Barebones]' . TF::GRAY . ' has now been disabled!');
                                                }
                                                break;
                                            case "vampire":
                                                if($this->config->get("vampire") == false){
                                                    $sender->sendMessage(TF::RED.'Vampire is already disabled!');
                                                }
                                                else {
                                                    $this->config->set("vampire", false);
                                                    $this->config->save();
                                                    $this->config->reload();
                                                    $sender->sendMessage(TF::GOLD . '[Vampire]' . TF::GRAY . ' has now been disabled!');
                                                }
                                                break;    
                                            case "fireless":
                                                if($this->config->get("fireless") == false){
                                                    $sender->sendMessage(TF::RED.'Fireless is already disabled!');
                                                }
                                                else {
                                                    $this->config->set("fireless", false);
                                                    $this->config->save();
                                                    $this->config->reload();
                                                    $sender->sendMessage(TF::GOLD . '[Fireless]' . TF::GRAY . ' has now been disabled!');
                                                }
                                                break;
                                            case "amphibian":
                                                if($this->config->get("amphibian") == false){
                                                    $sender->sendMessage(TF::RED.'Amphibian is already disabled!');
                                                }
                                                else {
                                                    $this->config->set("amphibian", false);
                                                    $this->config->save();
                                                    $this->config->reload();
                                                    $sender->sendMessage(TF::GOLD . '[Amphibian]' . TF::GRAY . ' has now been disabled!');
                                                }
                                                break;    
                                            case "nofall":
                                                if($this->config->get("nofall") == false){
                                                    $sender->sendMessage(TF::RED.'No-Fall is already disabled!');
                                                }
                                                else {
                                                    $this->config->set("nofall", false);
                                                    $this->config->save();
                                                    $this->config->reload();
                                                    $sender->sendMessage(TF::GOLD . '[No-Fall]' . TF::GRAY . ' has now been disabled!');
                                                }
                                                break;
                                            case "cripple":
                                                if($this->config->get("cripple") == false){
                                                    $sender->sendMessage(TF::RED.'Cripple is already disabled!');
                                                }
                                                else {
                                                    $this->config->set("nofall", false);
                                                    $this->config->save();
                                                    $this->config->reload();
                                                    $sender->sendMessage(TF::GOLD . '[Cripple]' . TF::GRAY . ' has now been disabled!');
                                                }    
                                                break;
                                            case "cat-eyes":
                                                if($this->config->get("cat-eyes") == false){
                                                    $sender->sendMessage(TF::RED.'CatEyes is already disabled!');
                                                }
                                                else {
                                                    $this->config->set("cat-eyes", false);
                                                    $this->config->save();
                                                    $this->config->reload();
                                                    $sender->sendMessage(TF::GOLD . '[CatEyes]' . TF::GRAY . ' has now been disabled!');
                                                }    
                                                break;
                                            case "lights-out":
                                                if($this->config->get("lights-out") == false){
                                                    $sender->sendMessage(TF::RED.'LightsOut is already disabled!');
                                                }
                                                else {
                                                    $this->config->set("lights-out", false);
                                                    $this->config->save();
                                                    $this->config->reload();
                                                    $sender->sendMessage(TF::GOLD . '[LightsOut]' . TF::GRAY . ' has now been disabled!');
                                                }    
                                                break;
                                            case "chicken":
                                                if($this->config->get("chicken") == false){
                                                    $sender->sendMessage(TF::RED.'Chicken is already disabled!');
                                                }
                                                else {
                                                    $this->config->set("chicken", false);
                                                    $this->config->save();
                                                    $this->config->reload();
                                                    $sender->sendMessage(TF::GOLD . '[Chicken]' . TF::GRAY . ' has now been disabled!');
                                                }    
                                                break;    
                                        }
                                    }
                                
                            }
                        }
                    else {
                        $sender->sendMessage(TF::RED."Usage: ".$this->getUsage());
                    }
                }
            }
            else {
                $sender->sendMessage(TF::RED."Usage: ".$this->getUsage());
            }
        }
        else {
            $sender->sendMessage(TF::RED.'You do not have permission to use this command!');
        }
    }

    /**
     * @return mixed
     */
    public function getPlugin()
    {
        return $this->plugin;
    }
}