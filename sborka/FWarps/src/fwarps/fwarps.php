<?php
namespace fwarps;

use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\permission\Permission;
use pocketmine\utils\TextFormat as F;


class fwarps extends PluginBase implements CommandExecutor, Listener{
    /** @var  Permission */
    public $perm;
    /** @var  warp[] */
    public $warps;
    /** @var Config */
    public $config;

    public function onEnable(){
        @mkdir($this->getDataFolder());
        $this->config = new Config($this->getDataFolder()."warps.yml", Config::YAML, array());
        $this->perm = $this->getServer()->getPluginManager()->getPermission("warp");
        $this->warps = $this->parseWarps($this->config->getAll());
    }
    public function onCommand(CommandSender $sender, Command $cmd, $label, array $args){
        switch($cmd->getName()){
            case "warp":
                if (isset($args[0])){
                    if (isset($this->warps[$args[0]])){
                        if(isset($args[1])){
                            if($sender->hasPermission("warp.other")){
                                $p = $this->getServer()->getPlayer($args[1]);
                                if($p !== null && $p->isOnline()){
                                    $this->warps[$args[0]]->warpAs($sender, $p);
                                    return true;
                                }
                                else{
                                    $sender->sendMessage(F::DARK_RED. "»" .F::RED. " Игрока нет на сервере или ник введен неправильно!");
                                    return true;
                                }
                            }else{
                                $sender->sendMessage(F::DARK_RED. "»".F::RED. " У тебя нет прав, чтобы телепортировать игроков на варпы!");
                                return true;
                            }
                        }
                        elseif($sender instanceof Player){
                            $this->warps[$args[0]]->warp($sender);
                            return true;
                        }
                        else{
                            $sender->sendMessage(F::YELLOW. "»" .F::GOLD. " Вы должны указать название варпа");
                            return true;
                        }
                    }
                    else{
                        $sender->sendMessage(F::YELLOW. "»" .F::GOLD. " Варпа ".F::RED.$args[0].F::GOLD." не существует!");
                        $sender->sendMessage(F::YELLOW. "»" .F::GOLD. " Список варпов - " .F::GREEN. "/warp!");
                        return true;
                    }
                }
                else{
                    if($sender->hasPermission("warp.list")){
                        $ret = F::GOLD. "§aСписок варпов:\n";
                        foreach($this->warps as $w){
                            if($w->canUse($sender)){
                                $ret .= "" .F::YELLOW. "§a-§e " . $w->name . "\n";
                            }
                        }
                        $sender->sendMessage(($ret !== "Список варпов:\n" ? $ret : "На сервере еще нет варпов"));
                        return true;
                    }
                    else{
                        return false;
                    }
                }
                break;
            case "setwarp":
                if ($sender instanceof Player){
                    if(isset($args[0])){
                        $this->config->set($args[0], [$sender->getFloorX(), $sender->getFloorY(), (int) $sender->getFloorZ(), $sender->getLevel()->getName()]);
                        $this->config->save();
                        $this->warps = $this->parseWarps($this->config->getAll());
                        $sender->sendMessage(F::YELLOW. "»" .F::GOLD. " Создан новый варп, " .F::GREEN. $args[0]);
                        return true;
                    }
                }
                else {
                    $sender->sendMessage(F::RED."Комманда вводиться только в игре");
                    return true;
                }
                break;
            case "delwarp":
                if (isset($args[0])) {
                    if(isset($this->warps[$args[0]])){
                        $this->getServer()->getPluginManager()->removePermission($this->getServer()->getPluginManager()->getPermission("fapi.warp." . $args[0]));
                        $this->config->remove($args[0]);
                        $this->config->save();
                        unset($this->warps[$args[0]]);
                        $sender->sendMessage(F::RED.$args[0] . " §bВарп удален");
                        return true;
                    }
                    else{
                        $sender->sendMessage(F::RED.$args[0] . " не существует");
                        return true;
                    }
                }
                break;
            default:
                return false;
                break;
        }
    }
    public function parseWarps(array $w) {
        $ret = [];
        foreach ($w as $n => $data) {
            $this->getServer()->loadLevel($data[3]);
            if(($level = $this->getServer()->getLevelByName($data[3])) === null) $this->getLogger()->error($data[3] . " не загружается. Варп " . $n . " отключен");
            else{
                $ret[$n] = new warp(new Position($data[0], $data[1], $data[2], $level), $n);
                $this->warpPermission($ret[$n]);
            }
        }
        return $ret;
    }
    public function warpPermission(warp $w){
        $p = new Permission("warp." . $w->name,"Разрешить использовать варп " . $w->name);
        $this->perm->getChildren()[$p->getName()] = true;
        $this->getServer()->getPluginManager()->addPermission($p);
    }
}
