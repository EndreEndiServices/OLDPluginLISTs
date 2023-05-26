<?php

namespace LuckyBlocks;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\level\Level;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\Enum;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\tile\Chest;
use pocketmine\utils\Config;
use pocketmine\level\Position;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\block\Block;
use pocketmine\level\Explosion;
use pocketmine\utils\TextFormat;
use pocketmine\item\Item;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\Int;
use pocketmine\nbt\tag\String;
use pocketmine\tile\Sign;
use pocketmine\tile\Tile;
use pocketmine\block\Sapling;

class Main extends PluginBase implements Listener{ 
    /** @var string */
    private $tag = TextFormat::YELLOW."[LuckyBlock] ".TextFormat::WHITE;
    /** @var Config */
    private $setup, $message;
    /** @var array */
    private $data = [
        "lucky_block" => 19,
        "status" => "on",
        "level" => [],
        "explosion_min" => 1,
        "explosion_max" => 3,
        "prison_block" => 49,
        "money_min" => 0,
        "money_max" => 1,
        "max_chest_item" => 4,
        "items_chest" => [],
        "items_dropped" => []
    ];
    /** @var MoneyManager */
    private $moneyManager;

    public function onEnable(){
        $dataResources = $this->getDataFolder()."/resources/";
        if(!file_exists($this->getDataFolder())) 
            @mkdir($this->getDataFolder(), 0755, true);
        if(!file_exists($dataResources)) 
            @mkdir($dataResources, 0755, true);
        
        $this->setup = new Config($dataResources. "config.yml", Config::YAML, $this->data);
        $this->setup->save();

        $this->message = new Config($dataResources. "message.yml", Config::YAML, [
            "tree" => "Tree spammed",
            "explosion" => "BOOOM!!!",
            "drop" => "Lucky",
            "sign" => "It's your problem!",
            "signText" => "It's your problem!",
            "prison" => "OPS...",
            "unlucky" => "Try again maybe you will be more lucky",
            "spawn" => "Muahahahahha",
            "money" => "You just won %MONEY%",
            "chest" => "You are very lucky!",
            "not_allowed" => "You are not authorized to use the plugin",
            ]
        );
        $this->message->save();
        $this->reloadSetup();

        $this->moneyManager = new MoneyManager($this);
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onCommand(CommandSender $sender, Command $command, $label, array $args){
        if(strtolower($command->getLabel()) != "luckyblock" && strtolower($command->getLabel()) != "lb")
            return;
        $cmd = "/".strtolower($command->getLabel())." ";
        if(!$sender->hasPermission("luckyblock.command")){
            $sender->sendMessage($this->tag.TextFormat::DARK_RED."You do not have permission to use this command!");
            return;
        }
        if(!isset($args) || !isset($args[0]))
            $args[0] = "help";
        $cmds = "";
        foreach($args as $var)
            $cmds = $cmds. "<".$var."> ";

        $args[0] = strtolower($args[0]);
        $sender->sendMessage($this->tag.TextFormat::DARK_AQUA."Usage: ".$cmd.$cmds);

        switch($args[0]) {
            case "?":
            case "h":
            case "help":
                $var = ["on", "off", "block <item>", "prison <item>", "explosion <min|max|info>", "chest <add|rmv|list|max>", "drop <add|rmv|list>", "world <allow|deny|list>", "money <min|max|info>"];
                $message = "";
                foreach ($var as $c)
                    $message .= $this->tag . TextFormat::AQUA . $cmd . $c . "\n";
                $sender->sendMessage($message);
                return;

            case "drop":
                if(!isset($args[1]) || empty($args[1]))
                    $args[1] = "help";
                switch ($args[1]) {
                    case "?":
                    case "h":
                    case "help":
                        $sender->sendMessage($this->tag.TextFormat::YELLOW.$cmd.TextFormat::WHITE." drop ".TextFormat::YELLOW." <add|rmv|list>");
                        return;
                    case "add":
                        if(!isset($args[2]) || empty($args[2])){
                            $sender->sendMessage($this->tag.TextFormat::DARK_RED."Invalid parameters.");
                            return;
                        }
                        $item = $this->getItem($args[2]);
                        if($item->getId() != Item::AIR){
                            if(!$this->isExists($this->data["items_dropped"], $item)){
                                $arr = $this->setup->get("items_dropped");
                                $arr[count($arr)] = $item->getId().":".$item->getDamage();
                                $this->setup->set("items_dropped", $arr);
                                $sender->sendMessage($this->tag.TextFormat::GREEN."The item '".$item->getName()."' has been added successfully.");
                            }else
                                $sender->sendMessage($this->tag.TextFormat::YELLOW."The item '".$item->getName()."' is already present in the configuration.");
                        }else
                            $sender->sendMessage($this->tag.TextFormat::DARK_RED."The item is not valid.");
                        break;
                    case "rmw":
                    case "rmv":
                    case "remove":
                        if(!isset($args[2]) || empty($args[2])){
                            $sender->sendMessage($this->tag.TextFormat::DARK_RED."Invalid parameters.");
                            return;
                        }
                        $item = $this->getItem($args[2]);
                        if($item->getId() != Item::AIR) {
                            if ($this->isExists($this->data["items_dropped"], $item)) {
                                $it = [];
                                foreach($this->data["items_dropped"] as $i){
                                    if($i->getId() !== $item->getId() && $i->getDamage() !== $item->getId())
                                        $it = $i->getId().":".$i->getDamage();
                                }
                                $this->setup->set("items_dropped", $it);
                                $sender->sendMessage($this->tag.TextFormat::GREEN."The item '".$item->getName()."' has been successfully removed.");
                            }else
                                $sender->sendMessage($this->tag.TextFormat::GREEN."the item '".$item->getName()."' was not found in the configuration.");
                        }else
                            $sender->sendMessage($this->tag.TextFormat::DARK_RED."The item is not valid.");
                        break;
                    case "list":
                        $list = $this->tag."List of items dropped: ";
                        foreach($this->data["items_dropped"] as $item)
                            $list .= $item->getName()."(id=".$item->getId()." damage=".$item->getDamage()."); ";
                        $sender->sendMessage($list);
                        break;
                }
                $this->reloadSetup();
                return;

            case "chest":
                if(!isset($args[1]) || empty($args[1]))
                    $args[1] = "help";
                switch ($args[1]) {
                    case "?":
                    case "h":
                    case "help":
                        $sender->sendMessage($this->tag.TextFormat::YELLOW.$cmd.TextFormat::WHITE." chest ".TextFormat::YELLOW." <add|rmv|list>");
                        return;
                    case "add":
                        if(!isset($args[2]) || empty($args[2])){
                            $sender->sendMessage($this->tag.TextFormat::DARK_RED."Invalid parameters.");
                            return;
                        }
                        $item = $this->getItem($args[2]);
                        if($item->getId() != Item::AIR){
                            if(!$this->isExists($this->data["items_chest"], $item)){
                                $arr = $this->setup->get("items_chest");
                                $arr[count($arr)] = $item->getId().":".$item->getDamage();
                                $this->setup->set("items_chest", $arr);
                                $sender->sendMessage($this->tag.TextFormat::GREEN."The item '".$item->getName()."' has been added successfully.");
                            }else
                                $sender->sendMessage($this->tag.TextFormat::YELLOW."The item '".$item->getName()."' is already present in the configuration.");
                        }else
                            $sender->sendMessage($this->tag.TextFormat::DARK_RED."The item is not valid.");
                        break;
                    case "rmw":
                    case "rmv":
                    case "remove":
                        if(!isset($args[2]) || empty($args[2])){
                            $sender->sendMessage($this->tag.TextFormat::DARK_RED."Invalid parameters.");
                            return;
                        }
                        $item = $this->getItem($args[2]);
                        if($item->getId() != Item::AIR) {
                            if ($this->isExists($this->data["items_chest"], $item)) {
                                $it = [];
                                foreach($this->data["items_chest"] as $i){
                                    if($i->getId() !== $item->getId() && $i->getDamage() !== $item->getId())
                                        $it = $i->getId().":".$i->getDamage();
                                }
                                $this->setup->set("items_chest", $it);
                                $sender->sendMessage($this->tag.TextFormat::GREEN."The item '".$item->getName()."' has been successfully removed.");
                            }else
                                $sender->sendMessage($this->tag.TextFormat::GREEN."the item '".$item->getName()."' was not found in the configuration.");
                        }else
                            $sender->sendMessage($this->tag.TextFormat::DARK_RED."The item is not valid.");
                        break;
                    case "list":
                        $list = $this->tag."List of items of the chest: ";
                        foreach($this->data["items_chest"] as $item)
                            $list .= $item->getName()."(id=".$item->getId()." damage=".$item->getDamage()."); ";
                        $sender->sendMessage($list);
                        break;
                    case "max":
                        if(!isset($args[2]) || !is_numeric($args[2]) || $args[2] <= 0){
                            $sender->sendMessage($this->tag.TextFormat::DARK_RED."Invalid parameters.");
                            return;
                        }
                        $this->setup->set("max_chest_item", $args[2]);
                        $sender->sendMessage($this->tag.TextFormat::GREEN."The maximum of the items generated inside the chest set to ".$args[2]);
                        break;
                }
                $this->reloadSetup();
                return;
            case "explosion":
                if(!isset($args[1]) || empty($args[1])){
                    $sender->sendMessage($this->tag.TextFormat::DARK_RED."Invalid parameters.");
                    return;
                }
                switch($args[1]){
                    case "min":
                        if(!isset($args[2]) || !is_
