<?php
//(c) InvForever - vk.com/invforever
namespace InvForever;

use pocketmine\{
    command\CommandSender, event\player\PlayerJoinEvent, event\player\PlayerPreLoginEvent, event\player\PlayerQuitEvent, item\Item, Player, plugin\PluginBase, scheduler\CallbackTask, command\Command, utils\Config, event\Listener
};

class BanKickPro extends PluginBase implements Listener
{
    public $ban;
    public $ip;
    public $play;
    private $bans;
    private $ips;

    public function onEnable()
    {
        $this->getLogger()->info("§cBan§eKick§bPro§a загружен. §dInvForever - vk.com/invforever");
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        if (!is_dir($this->getDataFolder())) {
            @mkdir($this->getDataFolder());
        }
        $this->saveDefaultConfig();
        $this->bans = new Config($this->getDataFolder(). "bans.yml", Config::YAML);
        $this->ips = new Config($this->getDataFolder(). "ips.yml", Config::YAML);
    }
    public function Loading(PlayerPreLoginEvent $event)
    {
        $cfg = new Config($this->getDataFolder(). "bans.yml", Config::YAML);
        $pl = strtolower($event->getPlayer()->getName());
        $this->ban = $cfg->getAll();
        $ip = strtolower($event->getPlayer()->getAddress());
        $cfg2 = new Config($this->getDataFolder(). "ips.yml", Config::YAML);
        $this->ip = $cfg2->getAll();
        if($event->getPlayer()->hasPermission("antiban")){
            $this->ban[$pl] = "antiban";
            $cfg->setAll($this->ban);
            $cfg->save();
            unset($cfg);
        }
        if (!isset($this->ban[$pl])) {
            $this->ban[$pl] = "unban";
            $cfg->setAll($this->ban);
            $cfg->save();
            unset($cfg);
        }
        if($this->ban[$pl] == "ban"){
            $event->getPlayer()->close("", $this->getConfig()->get("banmessage"));
        }
        if (!isset($this->ip[$ip])) {
            $this->ip[$ip] = "unban";
            $cfg2->setAll($this->ip);
            $cfg2->save();
            unset($cfg2);
        }
        if($this->ip[$ip] == "ban"){
            $event->getPlayer()->close("", $this->getConfig()->get("banmessage"));
        }
    }

    public function curl($url){
        $se = curl_init($url);
        curl_setopt($se, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($se, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($se, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($se);
        curl_close($se);
        return $response;
    }

    public function onCommand(CommandSender $sender, Command $cmd, $label, array $args)
    {
        $config = $this->getConfig();
        $id = $config->get("id");
        $token = $config->get("token");
        $cfg = new Config($this->getDataFolder() . "bans.yml", Config::YAML);
        $this->ban = $cfg->getAll();
        $cfg2 = new Config($this->getDataFolder() . "ips.yml", Config::YAML);
        $this->ip = $cfg2->getAll();
        $cfg3 = new Config($this->getDataFolder(). "cids.yml", Config::YAML);
        $this->cid = $cfg3->getAll();
        if ($cmd->getName() == "pban") {
            if (!empty($args[0])) {
                if (!empty($args[1])) {
                    $player = $sender->getServer()->getPlayer($args[0]);
                    if ($player !== null) {
                        $name = $this->getServer()->getPlayer($args[0])->getName();
                        if ($sender->hasPermission("pban")) {
                            if (!$this->getServer()->getPlayer($args[0])->hasPermission("antiban")) {
                                $reason = "";
                                for ($i = 1; $i < count($args); $i++) {
                                    $reason .= $args[$i];
                                    $reason .= " ";
                                }
                                $reason = substr($reason, 0, strlen($reason) - 1);
                                $sender->getServer()->getPlayer($args[0])->close("", " §c> §fТвой аккаунт заблокировали! \n §c> §fТебя заблокировал:§c " . $sender->getName() . "§f \n §c> §fПричина:§c " . $reason . "§f.");
                                $pl = strtolower($name);
                                $this->ban[$pl] = "ban";
                                $cfg->setAll($this->ban);
                                $cfg->save();
                                unset($cfg);
                                $mess = "Игрок $name был забанен игроком " . $sender->getName() . " по причине: " . $reason . ".";
                                foreach ($this->getServer()->getOnlinePlayers() as $players) {
                                    $players->sendMessage("§a> §fИгрок§c $name §fбыл забанен игроком§a " . $sender->getName() . " §fпо причине:§c " . $reason . "§f.");
                                }
                                $this->curl("https://api.vk.com/method/messages.send?user_id=" . $id . "&message=" . urlencode($mess) . "&access_token=" . $token);
                            } else {
                                $sender->sendMessage("§c> §fУ игрока§c $name §fиммунитет к бану.");
                            }
                        } else {
                            $sender->sendMessage("§c> §fТы не можешь использовать эту команду.");
                        }
                    } else {
                        if ($sender->hasPermission("pban")) {
                            $offpl = strtolower($args[0]);
                            if (!isset($this->ban[$offpl])) {
                                $this->ban[$offpl] = "unban";
                                $cfg->setAll($this->ban);
                                $cfg->save();
                            }
                            if ($this->ban[$offpl] !== "antiban") {
                                $reason = "";
                                for ($i = 1; $i < count($args); $i++) {
                                    $reason .= $args[$i];
                                    $reason .= " ";
                                }
                                $reason = substr($reason, 0, strlen($reason) - 1);
                                $pl = strtolower($args[0]);
                                $this->ban[$pl] = "ban";
                                $cfg->setAll($this->ban);
                                $cfg->save();
                                unset($cfg);
                                $mess = "Игрок $args[0] был забанен игроком " . $sender->getName() . " по причине: " . $reason . ".";
                                foreach ($this->getServer()->getOnlinePlayers() as $players) {
                                    $players->sendMessage("§a> §fИгрок§c $args[0] §fбыл забанен игроком§a " . $sender->getName() . " §fпо причине:§c " . $reason . "§f.");
                                }
                                $this->curl("https://api.vk.com/method/messages.send?user_id=" . $id . "&message=" . urlencode($mess) . "&access_token=" . $token);
                            } else {
                                $sender->sendMessage("§c> §fУ игрока§c $args[0] §fиммунитет к бану.");
                            }
                        } else {
                            $sender->sendMessage("§c> §fТы не можешь использовать эту команду.");
                        }
                    }
                } else {
                    $sender->sendMessage("§c/pban (ник) (причина)");
                }
            } else {
                $sender->sendMessage("§b/pban (ник) (причина)");
            }
        }
        if ($cmd->getName() == "pkick") {
            if (!empty($args[0])) {
                if (!empty($args[1])) {
                    $player = $sender->getServer()->getPlayer($args[0]);
                    if ($player !== null) {
                        $name = $this->getServer()->getPlayer($args[0])->getName();
                        if ($sender->hasPermission("pkick")) {
                            if (!$this->getServer()->getPlayer($args[0])->hasPermission("antikick")) {
                                $reason = "";
                                for ($i = 1; $i < count($args); $i++) {
                                    $reason .= $args[$i];
                                    $reason .= " ";
                                }
                                $reason = substr($reason, 0, strlen($reason) - 1);
                                $sender->getServer()->getPlayer($args[0])->close("", " §c> §fТебя выкинули из игры! \n §c> §fТебя выкинул:§c " . $sender->getName() . "§f \n §c> §fПричина:§c " . $reason . "§f.");
                                $mess = "Игрок $name был выгнан из игры игроком " . $sender->getName() . " по причине: " . $reason . ".";
                                foreach ($this->getServer()->getOnlinePlayers() as $players) {
                                    $players->sendMessage("§a> §fИгрок§c $name §fбыл выгнан из игры игроком§a " . $sender->getName() . " §fпо причине:§c " . $reason . "§f.");
                                }
                                $this->curl("https://api.vk.com/method/messages.send?user_id=" . $id . "&message=" . urlencode($mess) . "&access_token=" . $token);
                            } else {
                                $sender->sendMessage("§c> §fУ игрока§c $name §fиммунитет к кику.");
                            }

                        } else {
                            $sender->sendMessage("§c> §fТы не можешь использовать эту команду.");
                        }
                    } else {
                        $sender->sendMessage("§c> §fИгрок§c $args[0] §fне онлайн.");
                    }
                } else {
                    $sender->sendMessage("§c/pkick (ник) (причина)");
                }
            } else {
                $sender->sendMessage("§b/pkick (ник) (причина)");
            }
        }
        if ($cmd->getName() == "punban") {
            if ($sender->hasPermission("punban")) {
                if (!empty($args[0])) {
                    $pl = strtolower($args[0]);
                    $this->ban[$pl] = "unban";
                    $cfg->setAll($this->ban);
                    $cfg->save();
                    unset($cfg);
                    $sender->sendMessage("§a> §fИгрок§a $args[0] §fразбанен.");
                    $mess = "Игрок $args[0] был разбанен игроком " . $sender->getName() . ".";
                    $this->curl("https://api.vk.com/method/messages.send?user_id=" . $id . "&message=" . urlencode($mess) . "&access_token=" . $token);
                } else {
                    $sender->sendMessage("§c/punban (ник)");
                }
            } else {
                $sender->sendMessage("§c> §fТы не можешь использовать эту команду.");
            }
        }
        if ($cmd->getName() == "ptimeban") {
            if (!empty($args[0])) {
                if (!empty($args[1])) {
                    if (!empty($args[2])) {
                        $player = $sender->getServer()->getPlayer($args[0]);
                        if ($player !== null) {
                            $name = $this->getServer()->getPlayer($args[0])->getName();
                            if ($sender->hasPermission("ptimeban")) {

                                if (!$this->getServer()->getPlayer($args[0])->hasPermission("antiban")) {
                                    $reason = "";
                                    for ($i = 2; $i < count($args); $i++) {
                                        $reason .= $args[$i];
                                        $reason .= " ";
                                    }
                                    $time = (int)$args[1];
                                    $this->play = $name;
                                    $this->getServer()->getScheduler()->scheduleDelayedTask(new CallbackTask(array($this, "StopBan")), 20 * 60 * $time);
                                    $reason = substr($reason, 0, strlen($reason) - 1);
                                    $sender->getServer()->getPlayer($args[0])->close("", " §c> §fТвой аккаунт заблокирован на§c ". $time ."§f мин! \n §c> §fТебя заблокировал:§c " . $sender->getName() . "§f \n §c> §fПричина:§c " . $reason . "§f.");
                                    $pl = strtolower($name);
                                    $this->ban[$pl] = "ban";
                                    $cfg->setAll($this->ban);
                                    $cfg->save();
                                    unset($cfg);
                                    $mess = "Игрок $name был забанен игроком " . $sender->getName() . " на $time мин. по причине: " . $reason . ".";
                                    foreach ($this->getServer()->getOnlinePlayers() as $players) {
                                        $players->sendMessage("§a> §fИгрок§c $name §fбыл забанен игроком§a " . $sender->getName() . " §fна§a $time мин. по причине:§c " . $reason . "§f.");
                                    }
                                    $this->curl("https://api.vk.com/method/messages.send?user_id=" . $id . "&message=" . urlencode($mess) . "&access_token=" . $token);
                                } else {
                                    $sender->sendMessage("§c> §fУ игрока§c $name §fиммунитет к бану.");
                                }

                            } else {
                                $sender->sendMessage("§c> §fТы не можешь использовать эту команду.");
                            }
                        } else {
                            $sender->sendMessage("§c> §fИгрок сейчас не играет на сервере.");
                        }
                    } else {
                        $sender->sendMessage("§c/ptimeban (ник) (мин) (причина)");
                    }
                } else {
                    $sender->sendMessage("§c/ptimeban (ник) (мин) (причина)");
                }
            } else {
                $sender->sendMessage("§b/ptimeban (ник) (мин) (причина)");
            }
        }
        if ($cmd->getName() == "pbancid") {
            if (!empty($args[0])) {
                if (!empty($args[1])) {
                    $player = $sender->getServer()->getPlayer($args[0]);
                    if ($player !== null) {
                        $cid = strtolower($this->getServer()->getPlayer($args[0])->getClientId());
                        $name = $this->getServer()->getPlayer($args[0])->getName();
                        if ($sender->hasPermission("pbancid")) {
                            if (!$this->getServer()->getPlayer($args[0])->hasPermission("antiban")) {
                                $reason = "";
                                for ($i = 1; $i < count($args); $i++) {
                                    $reason .= $args[$i];
                                    $reason .= " ";
                                }
                                $reason = substr($reason, 0, strlen($reason) - 1);
                                $sender->getServer()->getPlayer($args[0])->close("", " §c> §fТвой аккаунт заблокировали по CID! \n §c> §fТебя заблокировал:§c " . $sender->getName() . "§f \n §c> §fПричина:§c " . $reason . "§f.");
                                $this->getServer()->getCIDBans()->addBan($cid);
                                $mess = "Игрок $name был забанен игроком " . $sender->getName() . " по CID " . $cid . ", по причине: " . $reason . ".";
                                foreach ($this->getServer()->getOnlinePlayers() as $players) {
                                    $players->sendMessage("§a> §fИгрок§c $name §fбыл забанен игроком§a " . $sender->getName() . " §fпо CID, по причине:§c " . $reason . "§f.");
                                }
                                $this->curl("https://api.vk.com/method/messages.send?user_id=" . $id . "&message=" . urlencode($mess) . "&access_token=" . $token);
                            } else {
                                $sender->sendMessage("§c> §fУ игрока§c $name §fиммунитет к бану.");
                            }
                        } else {
                            $sender->sendMessage("§c> §fТы не можешь использовать эту команду.");
                        }
                    } else {
                        $sender->sendMessage("§c> §fИгрок§c $args[0] §fне онлайн.");
                    }
                } else {
                    $sender->sendMessage("§c/pbancid (ник) (причина)");
                }
            } else {
                $sender->sendMessage("§b/pbancid (ник) (причина)");
            }
        }
        if ($cmd->getName() == "pbanip") {
            if (!empty($args[0])) {
                if (!empty($args[1])) {
                    $player = $sender->getServer()->getPlayer($args[0]);
                    if ($player !== null) {
                        $ip = strtolower($this->getServer()->getPlayer($args[0])->getAddress());
                        $name = $this->getServer()->getPlayer($args[0])->getName();
                        if ($sender->hasPermission("pbanip")) {
                            if (!$this->getServer()->getPlayer($args[0])->hasPermission("antiban")) {
                                $reason = "";
                                for ($i = 1; $i < count($args); $i++) {
                                    $reason .= $args[$i];
                                    $reason .= " ";
                                }
                                $reason = substr($reason, 0, strlen($reason) - 1);
                                $sender->getServer()->getPlayer($args[0])->close("", " §c> §fТвой аккаунт заблокировали по IP! \n §c> §fТебя заблокировал:§c " . $sender->getName() . "§f \n §c> §fПричина:§c " . $reason . "§f.");
                                $this->ip[$ip] = "ban";
                                $cfg2->setAll($this->ip);
                                $cfg2->save();
                                unset($cfg2);
                                $mess = "Игрок $name был забанен игроком " . $sender->getName() . " по IP " . $ip . ", по причине: " . $reason . ".";
                                foreach ($this->getServer()->getOnlinePlayers() as $players) {
                                    $players->sendMessage("§a> §fИгрок§c $name §fбыл забанен игроком§a " . $sender->getName() . " §fпо IP, по причине:§c " . $reason . "§f.");
                                }
                                $this->curl("https://api.vk.com/method/messages.send?user_id=" . $id . "&message=" . urlencode($mess) . "&access_token=" . $token);
                            } else {
                                $sender->sendMessage("§c> §fУ игрока§c $name §fиммунитет к бану.");
                            }
                        } else {
                            $sender->sendMessage("§c> §fТы не можешь использовать эту команду.");
                        }
                    } else {
                        $sender->sendMessage("§c> §fИгрок§c $args[0] §fне онлайн.");
                    }
                } else {
                    $sender->sendMessage("§c/pbanip (ник) (причина)");
                }
            } else {
                $sender->sendMessage("§b/pbanip (ник) (причина)");
            }
        }
        if ($cmd->getName() == "punbanip") {
            if ($sender->hasPermission("punbanip")) {
                if (!empty($args[0])) {
                    $ip = strtolower($args[0]);
                    if(isset($this->ip[$ip])) {
                        $this->ip[$ip] = "unban";
                        $cfg2->setAll($this->ip);
                        $cfg2->save();
                        unset($cfg2);
                        $sender->sendMessage("§a> §fIP§a $ip §fразбанен.");
                        $mess = "IP $args[0] разбанен игроком " . $sender->getName() . ".";
                        $this->curl("https://api.vk.com/method/messages.send?user_id=" . $id . "&message=" . urlencode($mess) . "&access_token=" . $token);
                    }else{
                        $sender->sendMessage("§c> §fIP§c $ip §fне найден.");
                    }
                } else {
                    $sender->sendMessage("§c/punbanip (ник)");
                }
            } else {
                $sender->sendMessage("§c> §fТы не можешь использовать эту команду.");
            }
        }
        if($cmd->getName() == "phelp"){
            if ($sender->hasPermission("phelp")) {
                $sender->sendMessage("§f> §cBan§eKick§bPro §f<");
                $sender->sendMessage("§f > §c/pban §f- §6Забанить игрока.");
                $sender->sendMessage("§f > §c/punban §f- §6Разбанить игрока.");
                $sender->sendMessage("§f > §c/ptimeban §f- §6Забанить игрока на время.");
                $sender->sendMessage("§f > §c/pbanip §f- §6Забанить игрока по IP.");
                $sender->sendMessage("§f > §c/punbanip §f- §6Разбанить IP.");
                $sender->sendMessage("§f > §c/pbancid §f- §6Забанить игрока по CID.");
                $sender->sendMessage("§f > §c/pkick §f- §6Выгнать игрока из игры.");
                $sender->sendMessage("§f > §cПермишен для защиты от бана §f- §6antiban.");
                $sender->sendMessage("§f > §cПермишен для защиты от кика §f- §6antikick.");
            }else{
                $sender->sendMessage("§c> §fТы не можешь использовать эту команду.");
            }
        }
    }
    public function onDisable()
    {
        $this->StopBan();
    }

    public function StopBan(){
        $cfg = new Config($this->getDataFolder(). "bans.yml", Config::YAML);
        $pl = strtolower($this->play);
        $this->ban = $cfg->getAll();
        $this->ban[$pl] = "unban";
        $cfg->setAll($this->ban);
        $cfg->save();
        unset($cfg);
    }
}
?>