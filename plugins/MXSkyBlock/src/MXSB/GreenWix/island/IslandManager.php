<?php

namespace MXSB\GreenWix\island;

use MXSB\GreenWix\Main;
use pocketmine\math\Vector3;
use pocketmine\item\Item;
use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\block\Block;


class IslandManager
{

    public function __construct(Main $plugin)
    {
        $this->plugin = $plugin;
        $this->islands = [];

    }
    public function checkOwnIsland(Player $player)
    {
        if ($this->plugin->db->query("SELECT owner FROM islands WHERE owner = '" . strtolower($player->getName()) . "'")->fetchArray()[0] != null) {
            return true;
        } else {
            return false;
        }
    }
    public function initIsland($owner)
    {
        if($owner instanceof Player){
            $data = $this->plugin->db->query("SELECT * FROM islands WHERE owner = '" . strtolower($owner->getName()) . "' ;")->fetchArray(SQLITE3_ASSOC);
            $this->islands[strtolower($owner->getName())] = new Island($data);
        } else {
            $data = $this->plugin->db->query("SELECT * FROM islands WHERE owner = '" . strtolower($owner) . "' ;")->fetchArray(SQLITE3_ASSOC);
            $this->islands[strtolower($owner)] = new Island($data);
            var_dump($this->islands);
        }
    }
    public function getIsland($player){
        if($player instanceof Player){
        $data = $this->plugin->db->query("SELECT * FROM users WHERE nickname = '" . strtolower($player->getName()) . "' ;")->fetchArray();
    } else {
        $data = $this->plugin->db->query("SELECT * FROM users WHERE nickname = '" . strtolower($player) . "' ;")->fetchArray();
    }
        if($data[0] != null){
            if(!isset($this->islands[$data["island"]])){
                $this->initIsland($data["island"]);
            }
            return $this->islands[$data["island"]];
        }
        else{
            return null;
        }
    }
    public function deleteIsland(Player $player){
        $is = $this->getIsland($player);
        if($is != null){
            if ($this->checkOwnIsland($player)) {
                unset($this->islands[strtolower($player->getName())]);
                $this->plugin->db->query("DELETE FROM users WHERE nickname = '" . strtolower($player->getName()) . "' ;");
                $this->plugin->db->query("DELETE FROM islands WHERE owner = '" . strtolower($player->getName()) . "' ;");
                $player->addTitle(("§c§l»§f§l Ваш остров успешно удален §c§l«§r"), ("§f§lНовый можно создать через §b12 §f§lчасов!"));
            } else {
                $player->addTitle("","§c§l»§f§l Вы должны быть лидером острова §c§l«§r");
            }
        }else{
        $player->addTitle("§c§l»§f§l У Вас нет острова §c§l«§r");
        }
        }
        public function toIsland(Player $player, $guest = false)
        {
         $is = $this->getIsland($player);
         if($is != null){
           
               $vec = $is->getSpawn();
               $sb = Server::getInstance()->getLevelByName("sev");
               $player->teleport(new Position((float) $vec[0], (float) $vec[1], (float) $vec[2], $sb));
           
           $player->addTitle(("§a§l»§f Теперь вы на §a«§r"), ("§f§lсвоем острове! Приятной игры!§l"));
       } else {
        $player->addTitle("§c§l»§f§l У Вас нет острова §c§l«§r");
    }


}



public function createIsland(Player $player)
{
//todo проверки и запись
    if (!$this->checkOwnIsland($player) && !$this->getIsland($player)) {
        $sb = Server::getInstance()->getLevelByName("sev");
//var_dump($sb);
        $x = (int) $this->plugin->data->getNested("X");
        $y = (int) $this->plugin->data->getNested("Y");
        var_dump($x);
        if ($x < 10 * 512) {
            $x = $x + 512;
        } else {
            $y = $y + 512;
            $x = 0;
        }
        $this->plugin->data->setNested("X", $x);
        $this->plugin->data->setNested("Y", $y);
        $this->plugin->data->save();
//$player->teleport(new Position($x * 16 + 8, 130, $y * 16 + 8, $sb));
        $sb->loadChunk(0, 0);
        $islandbase = $sb->getChunk(0, 0);
//var_dump($islandbase);
//$new = 
        $sb->loadChunk($x, $y);
        $buffer = $islandbase->fastSerialize();
            //  $sb->setChunk($x, $y , $islandbase);
        $sb->setChunk($x, $y, $sb->getChunk($x, $y)->fastDeserialize($buffer));
        $player->teleport(new Position($x * 16 + 8, 130, $y * 16 + 8, $sb));
//islandbase->fastDeserialize($buffer);
        $stmt = $this->plugin->db->prepare("INSERT INTO users (nickname, island) VALUES(:nick, :nick);");
        $stmt->bindValue(":nick", strtolower($player->getName()));
        $result = $stmt->execute();
        $stmt = $this->plugin->db->prepare("INSERT INTO islands (owner, islandX, islandZ, locked, members, spawn, point) VALUES(:nick, :x, :y, :locked, :members, :spawn, :point);");
        $stmt->bindValue(":nick", strtolower($player->getName()));
        $stmt->bindValue(":x", $x);
        $stmt->bindValue(":y", $y);
        $stmt->bindValue(":locked", "false");
        $stmt->bindValue(":members", serialize(array()));
        $stmt->bindValue(":spawn", serialize([
0 => $x * 16 + 8,
1 => 128,
2 => $y * 16 + 8,
]));
        $stmt->bindValue(":point", serialize([
0 => $x * 16 + 8,
1 => 128,
2 => $y * 16 + 8,
]));
        $result = $stmt->execute();
            $player->addTitle("§aSky§bBlock", "Мой милый дом", 20, 40, 20);//потом сделать мультиязык
            //+9 +13 »
            //$sb->setBlock(new Vector3($x * 16 + 9, 128, $y * 16 + 13), Block::get(54));

            $this->initIsland($player);
        } elseif($this->checkOwnIsland($player) && $this->getIsland($player)) {
            $player->addTitle("", "У вас уже есть остров!");//потом сделать мультиязык
        } elseif(!$this->checkOwnIsland($player) && $this->getIsland($player)){
            $player->addTitle("", "Вы уже явлыетесь участниокм другого устрова!");
        }
    }
    public function saveIsland($data){
        if($data == null) {
            return false;
        }
        $stmt = $this->plugin->db->prepare("UPDATE islands SET owner = :owner, members = :members, locked = :locked, spawn = :spawn, point = :point WHERE owner = :owner;");
        $stmt->bindValue(":owner", $data->ownerName);
        $stmt->bindValue(":members", serialize($data->members));
        $stmt->bindValue(":spawn", serialize($data->spawn));
        $stmt->bindValue(":point", serialize($data->point));
        $stmt->bindValue(":locked", $data->locked);
        $result = $stmt->execute();
    }

}
