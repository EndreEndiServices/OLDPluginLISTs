<?php

namespace ChatG;

use pocketmine\plugin\Plugin;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat as TF;
use pocketmine\scheduler\PluginTask;
use pocketmine\scheduler\CallbackTask;
use pocketmine\utils\Config;
use pocketmine\event\player\PlayerChatEvent as PCE;
 
class ChatG extends PluginBase implements Listener {

     public $cfg;
     public $word;

	public function onEnable(){
        if(!is_dir($this->getDataFolder())){ 
            @mkdir($this->getDataFolder());
        }
        if(! file_exists($this->getDataFolder()."ChatGame.cfg")) {
        $this->cfg = new Config($this->getDataFolder()."ChatGame.cfg",Config::YAML);
        $this->setData();
        }else{ $this->cfg = new Config($this->getDataFolder()."ChatGame.cfg",Config::YAML);
        }
         $this->getServer()->getPluginManager()->registerEvents($this, $this); 
	    $this->getServer()->getScheduler()->scheduleRepeatingTask(new CallbackTask(array($this, "ChatGame")), 20 * $this->cfg->get("Время"));
         $this->e = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");
}

     public function onDisable(){
    $this->getServer()->getScheduler()->cancelTasks($this);
    }

public function setData(){
$this->cfg->set("Время", 60);
$this->cfg->set("Префикс", "§l§f(§bEns§eGame§f)§r");
$this->cfg->setNested("Сложение.Минимальное число", 1);
$this->cfg->setNested("Сложение.Максимальное число", 1000);
$this->cfg->setNested("Сложение.Сообщение игры", "{prefix} §fРеши§7, §eчтобы §aполучить§c награду§7: §b{number1} §f+ §b{number2} §c(Сложение)");
$this->cfg->setNested("Сложение.Сообщение победы", "{prefix} §fИгрок§b {player} §aдал §eправильный§6 ответ! §b{result} §c(Сложение)");
$this->cfg->setNested("Сложение.Приз", 100);
$this->cfg->setNested("Вычитание.Минимальное число", 1);
$this->cfg->setNested("Вычитание.Максимальное число", 1000);
$this->cfg->setNested("Вычитание.Сообщение игры", "{prefix} §fРеши§7, §eчтобы §aполучить§c награду§7: §b{number1} §f- §b{number2} §c(Вычитание)");
$this->cfg->setNested("Вычитание.Сообщение победы", "{prefix} §fИгрок§b {player} §aдал §eправильный§6 ответ! §b{result} §c(Вычитание) ");
$this->cfg->setNested("Вычитание.Приз", 100);
$this->cfg->setNested("Умножение.Минимальное число", 1);
$this->cfg->setNested("Умножение.Максимальное число", 100);
$this->cfg->setNested("Умножение.Сообщение игры", "{prefix} §fРеши§7, §eчтобы §aполучить§c награду§7: §b{number1} §f* §b{number2} §c(Умножение)");
$this->cfg->setNested("Умножение.Сообщение победы", "{prefix} §fИгрок§b {player} §aдал §eправильный§6 ответ! §b{result} §c(Умножение)");
$this->cfg->setNested("Умножение.Приз", 100);
$this->cfg->save();
}

public function giveData(){
$a = array();

$a["pref"] = $this->cfg->get("Префикс");
$a["minp"] = $this->cfg->getNested("Сложение.Минимальное число");
$a["maxp"] = $this->cfg->getNested("Сложение.Максимальное число");
$a["brp"] = $this->cfg->getNested("Сложение.Сообщение игры");
$a["winp"] = $this->cfg->getNested("Сложение.Сообщение победы");
$a["prizp"] = $this->cfg->getNested("Сложение.Приз");

$a["minm"] = $this->cfg->getNested("Вычитание.Минимальное число");
$a["maxm"] = $this->cfg->getNested("Вычитание.Максимальное число");
$a["brm"] = $this->cfg->getNested("Вычитание.Сообщение игры");
$a["winm"] = $this->cfg->getNested("Вычитание.Сообщение победы");
$a["prizm"] = $this->cfg->getNested("Вычитание.Приз");

$a["minu"] = $this->cfg->getNested("Умножение.Минимальное число");
$a["maxu"] = $this->cfg->getNested("Умножение.Максимальное число");
$a["bru"] = $this->cfg->getNested("Умножение.Сообщение игры");
$a["winu"] = $this->cfg->getNested("Умножение.Сообщение победы");
$a["prizu"] = $this->cfg->getNested("Умножение.Приз");

return $a;
}

public function ChatGame(){
$d = $this->giveData();

$type = mt_rand(1, 3);

switch($type){
case 1;
$one = mt_rand($d["minp"], $d["maxp"]);
$two = mt_rand($d["minp"], $d["maxp"]);
$d["brp"] = str_replace("{number1}", $one, $d["brp"]);
$d["brp"] = str_replace("{number2}", $two, $d["brp"]);
$d["brp"] = str_replace("{prefix}", $d["pref"], $d["brp"]);
$result = $one + $two;
$this->word = array("result" => $result, "type" => $type);
$this->getServer()->broadcastMessage($d["brp"]);
break;
case 2;
$one = mt_rand($d["minm"], $d["maxm"]);
$two = mt_rand($d["minm"], $d["maxm"]);
$d["brm"] = str_replace("{number1}", $one, $d["brm"]);
$d["brm"] = str_replace("{number2}", $two, $d["brm"]);
$d["brm"] = str_replace("{prefix}", $d["pref"], $d["brm"]);
$result = $one - $two;
$this->word = array("result" => $result, "type" => $type);
$this->getServer()->broadcastMessage($d["brm"]);
break;
case 3;
$one = mt_rand($d["minu"], $d["maxu"]);
$two = mt_rand($d["minu"], $d["maxu"]);
$d["bru"] = str_replace("{number1}", $one, $d["bru"]);
$d["bru"] = str_replace("{number2}", $two, $d["bru"]);
$d["bru"] = str_replace("{prefix}", $d["pref"], $d["bru"]);
$result = $one * $two;
$this->word = array("result" => $result, "type" => $type);
$this->getServer()->broadcastMessage($d["bru"]);
break;
}
}

public function Win(PCE $ev){
$p = $ev->getPlayer();
$m = $ev->getMessage();
$d = $this->giveData();

if($m == $this->word["result"]){
$ev->setCancelled();
switch($this->word["type"]){
case 1:
$d["winp"] = str_replace("{player}", $p->getName(), $d["winp"]);
$d["winp"] = str_replace("{result}", $this->word["result"], $d["winp"]);
$d["winp"] = str_replace("{prefix}", $d["pref"], $d["winp"]);
$this->getServer()->broadcastMessage($d["winp"]);
$this->e->addMoney($p, $d["prizp"]);
$this->word = null;
break;
case 2:
$d["winm"] = str_replace("{player}", $p->getName(), $d["winm"]);
$d["winm"] = str_replace("{result}", $this->word["result"], $d["winm"]);
$d["winm"] = str_replace("{prefix}", $d["pref"], $d["winm"]);
$this->getServer()->broadcastMessage($d["winm"]);
$this->e->addMoney($p, $d["prizm"]);
$this->word = null;
break;
case 3:
$d["winu"] = str_replace("{player}", $p->getName(), $d["winu"]);
$d["winu"] = str_replace("{result}", $this->word["result"], $d["winu"]);
$d["winu"] = str_replace("{prefix}", $d["pref"], $d["winu"]);
$this->getServer()->broadcastMessage($d["winu"]);
$this->e->addMoney($p, $d["prizu"]);
$this->word = null;
break;

}
}
}







}
