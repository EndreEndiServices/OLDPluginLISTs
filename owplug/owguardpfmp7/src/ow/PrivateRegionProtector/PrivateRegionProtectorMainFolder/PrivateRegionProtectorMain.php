<?php

namespace ow\PrivateRegionProtector\PrivateRegionProtectorMainFolder;

use pocketmine\command\Command as CMD;
use pocketmine\command\CommandSender as CS;
use pocketmine\event\Listener as L;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\plugin\PluginBase as PB;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat as F;


class PrivateRegionProtectorMain extends PB implements L
{
    public $pos1 = array();
    public $pos2 = array();
    public $areas;
    private $config;
    public $forInfo;
    public $forInfoCheckPerm;
    public $forCF;

    function onEnable()
    {
		@mkdir($this->getDataFolder());
        $this->getServer()->getPluginManager()->registerEvents(new SEventListener($this), $this);
        $this->areas = new Config($this->getDataFolder() . "Areas.yml", Config::YAML);
        $this->config = new Config($this->getDataFolder() . "Settings.yml", Config::YAML, array(
            "MaxAreas" => 2,
            "MaxAreaSize" => 10000,
            "DefaultFlags" => array("pvp" => "deny", "build" => "deny", "entry" => "allow", "god-mode" => "deny", "use" => "deny","cmd-use" => "allow", "send-chat" => "allow", "explode" => "allow", "burn" => "allow", "regain" => "allow", "teleport" => "allow", "mob-damage" => "allow", "sleep" => "allow", "tnt-explode" => "allow", "bucket-use" => "deny", "drop-item" => "allow")));
		$this->areas->save();
		$this->config->save();
        $this->getLogger()->info(F::GREEN . "Приват запущен!");
    }

    /**
     * @rgram CS $s
     * @rgram CMD $cmd
     * @rgram string $label
     * @rgram array $args
     * @return bool|void
     */
    public function onCommand(CS $s, CMD $cmd, $label, array $args)
    {
        switch ($cmd->getName()) {
            case "owguard":
                if ($s instanceof Player) {
                    if ($s->hasPermission("rg.p")) {
                        $player = $s->getServer()->getPlayer($s->getName());
                        if (isset($args[0])) {
                            if (strtolower($args[0]) == "pos1") {
                                $x1 = $s->getFloorX();
                                $y1 = $s->getFloorY() - 1;
                                $z1 = $s->getFloorZ();
                                $this->pos1[strtolower($s->getName())] = array(0 => $x1, 1 => $y1, 2 => $z1, 'level' => $player->getLevel()->getName());
                                $s->sendMessage(F::YELLOW . "[OWGuard] Первая точка установлена (Координаты " . $x1 . ", " . $y1 . ", " . $z1 . " )");
                            } elseif (strtolower($args[0]) == "pos2") {
                                $x2 = $s->getFloorX();
                                $y2 = $s->getFloorY() - 1;
                                $z2 = $s->getFloorZ();
                                $this->pos2[strtolower($s->getName())] = array(0 => $x2, 1 => $y2, 2 => $z2, 'level' => $player->getLevel()->getName());
                                $s->sendMessage(F::YELLOW . "[OWGuard] Вторая точка установлена (Координаты " . $x2 . ", " . $y2 . ", " . $z2 . " )");
                            } elseif (strtolower($args[0]) == "create") {
                                if ((isset($this->pos1[strtolower($s->getName())])) and (isset($this->pos2[strtolower($s->getName())]))) {
                                    if (isset($args[1])) {
                                        if (!$this->areas->exists(strtolower($args[1]))) {
                                            $pos1 = $this->pos1[strtolower($s->getName())];
                                            $pos2 = $this->pos2[strtolower($s->getName())];
                                            if ($pos1["level"] == $pos2["level"]) {
                                                $minX = min($pos1[0], $pos2[0]);
                                                $maxX = max($pos1[0], $pos2[0]);
                                                $minY = min($pos1[1], $pos2[1]);
                                                $maxY = max($pos1[1], $pos2[1]);
                                                $minZ = min($pos1[2], $pos2[2]);
                                                $maxZ = max($pos1[2], $pos2[2]);
                                                $max = array($maxX, $maxY, $maxZ);
                                                $min = array($minX, $minY, $minZ);
                                                if (($maxX - $minX) * ($maxY - $minY) * ($maxZ - $minZ) <= $this->config->get("MaxAreaSize") || $s->hasPermission("rg.doall") || $s->hasPermission("rg.maxareasize")) {
                                                    if (count($this->areas->getAll()) != 0) {
                                                        foreach ($this->areas->getAll() as $name => $info) {
                                                            $f = array();
                                                            foreach ($this->areas->getAll() as $areaname => $areainfo) {
                                                                if (in_array(strtolower($s->getName()), $areainfo["owners"])) {
                                                                    $f[] = $areaname;
                                                                }
                                                            }
                                                            if (count($f) <= $this->config->get("MaxAreas") || $s->hasPermission("rg.doall") || $s->hasPermission("rg.maxareas")) {
                                                                if (!$this->checkCoordinates($info, $minX, $minY, $minZ) and !$this->checkCoordinates($info, $maxX, $maxY, $maxZ)) {
                                                                    $this->areas->set(strtolower($args[1]), array("min" => $min, "max" => $max, "owners" => array(strtolower($s->getName())), "members" => array(),"level" => $pos1["level"], "flags" => $this->config->get("DefaultFlags")));
                                                                    $this->areas->save();
                                                                    $s->sendMessage(F::GREEN . "[OWGuard] Вы создали приват " . $args[1] . " ( " . implode(", ", $min) . " - " . implode(", ", $max) . " )");
                                                                    $this->getLogger()->info(F::YELLOW . $s->getName() . " create his area " . $args[1] . " in world " . $player->getLevel()->getName());
                                                                    unset($this->pos1[strtolower($s->getName())]);
                                                                    unset($this->pos2[strtolower($s->getName())]);
                                                                    return;
                                                                } else {
                                                                    $s->sendMessage(F::RED . "[OWGuard] Твой регион пересекает чужой регион !");
                                                                    return;
                                                                }
                                                            } else {
                                                                $s->sendMessage(F::RED . "[OWGuard] Ты уже имеешь " . $this->config->get("MaxAreas") . " регионов ! Удали один из них !");
                                                                return;
                                                            }
                                                        }
                                                    } else {
                                                        $this->areas->set(strtolower($args[1]), array("min" => $min, "max" => $max, "owners" => array(strtolower($s->getName())), "members" => array(),"level" => $pos1["level"], "flags" => $this->config->get("DefaultFlags")));
                                                        $this->areas->save();
                                                        $s->sendMessage(F::GREEN . "[OWGuard] Ты создал регион : " . $args[1] . " ( " . implode(", ", $min) . " - " . implode(", ", $max) . " )");
                                                        $this->getLogger()->info(F::YELLOW . $s->getName() . " create his area " . $args[1] . " in world " . $player->getLevel()->getName());
                                                        unset($this->pos1[strtolower($s->getName())]);
                                                        unset($this->pos2[strtolower($s->getName())]);
                                                        return;
                                                    }
                                                } else {
                                                    $s->sendMessage(F::RED . "[OWGuard] Максимальный размер региона " . $this->config->get("MaxAreaSize") . " блоков. А твой регион : " . ($maxX - $minX) * ($maxY - $minY) * ($maxZ - $minZ) . " блоков !");
                                                    return;
                                                }

                                            } else {
                                                $s->sendMessage(F::RED . "[OWGuard] Установи точки привата в одном мире !");
                                                return;
                                            }
                                        } else {
                                            $s->sendMessage(F::RED . "[OWGuard] Регион с таким именем уже существует !");
                                            return;
                                        }
                                    } else {
                                        $s->sendMessage(F::RED . "[OWGuard] Используй /owguard create название_региона");
                                        return;
                                    }
                                } else {
                                    $s->sendMessage(F::RED . "[OWGuard] Установи точки привата !");
                                    return;
                                }
                            } elseif (strtolower($args[0] == "888")) {
                                if (isset($args[1])) {
                                    if ($this->areas->exists(strtolower($args[1]))) {
                                        if (in_array(strtolower($s->getName()), $this->areas->get(strtolower($args[1]))["members"]) || in_array(strtolower($s->getName()), $this->areas->get(strtolower($args[1]))["owners"])) {
                                            $flagss = array();
                                            foreach ($this->areas->get(strtolower($args[1]))["flags"] as $flagg => $fss) {
                                                $flagss[] = $flagg . "»" . $fss;
                                            }
                                            $s->sendMessage(F::YELLOW . "Название региона : " . strtolower($args[1]));
                                            $s->sendMessage(F::YELLOW . "Владелец : " . implode(" , ", $this->areas->get(strtolower($args[1]))["owners"]));
                                            $s->sendMessage(F::YELLOW . "Пользователи региона : " . implode(", ", $this->areas->get(strtolower($args[1]))["members"]));
                                            $s->sendMessage(F::YELLOW . "Флаги : ");
                                            $s->sendMessage(F::YELLOW . implode(", ", $flagss));
                                            return;
                                        } else {
                                            $s->sendMessage(F::RED . "[OWGuard] У тебя нет прав");
                                            return;
                                        }
                                    } else {
                                        $s->sendMessage(F::RED . "[OWGuard] Регион " . $args[1] . " не найден !");
                                    }
                                } else {
                                    foreach ($this->areas->getAll() as $name => $info) {
                                        $x = $s->getFloorX();
                                        $y = $s->getFloorY();
                                        $z = $s->getFloorZ();
                                        if ($this->checkCoordinates($info, $x, $y, $z)) {
                                            if (in_array(strtolower($s->getName()), $info["owners"]) || in_array(strtolower($s->getName()), $info["members"]) || $s->hasPermission("rg.doall")) {
                                                $this->forInfo[$s->getName()] = true;
                                                $this->forInfoCheckPerm[$s->getName()] = true;
                                            } else {
                                                $this->forInfo[$s->getName()] = true;
                                                $this->forInfoCheckPerm[$s->getName()] = false;
                                            }
                                        } else {
                                            continue;
                                        }
                                    }
                                    if (isset($this->forInfo[$s->getName()]) and $this->forInfo[$s->getName()] == true) {
                                        foreach ($this->areas->getAll() as $name => $info) {
                                            if ($this->checkCoordinates($info, $s->getFloorX(), $s->getFloorY(), $s->getFloorZ())) {
                                                if ($this->forInfoCheckPerm[$s->getName()] == true) {
                                                    $flags = array();
                                                    foreach ($info["flags"] as $flag => $fs) {
                                                        $flags[] = $flag . ":" . $fs;
                                                    }
                                                    $stringL = strlen(implode(" ,", $flags));
                                                    $rgrt_one = substr($flags, 0, floor($stringL));
                                                    $rgrt_two = substr($flags, floor($stringL));
                                                    $this->getLogger()->info(print_r($flags));
                                                    $s->sendMessage(F::YELLOW . "Имя региона : " . $name);
                                                    $s->sendMessage(F::YELLOW . "Владелец(ы) : " . implode(", ", $info["owners"]));
                                                    $s->sendMessage(F::YELLOW . "Пользователи региона : " . implode(", ", $info["members"]));
                                                    $s->sendMessage(F::YELLOW . "Флаги : " . implode(", ", $rgrt_one));
                                                    $s->sendMessage(F::YELLOW . implode(", ", $rgrt_two));
                                                    unset($this->forInfoCheckPerm[$s->getName()]);
                                                    unset($this->forInfo[$s->getName()]);
                                                } else {
                                                    $s->sendMessage(F::RED . "[OWGuard] Нет прав !");
                                                    unset($this->forInfoCheckPerm[$s->getName()]);
                                                    unset($this->forInfo[$s->getName()]);
                                                }
                                            } else {
                                                continue;
                                            }
                                        }
                                    } else {
                                        unset($this->forInfoCheckPerm[$s->getName()]);
                                        unset($this->forInfo[$s->getName()]);
                                        $s->sendMessage(F::RED . "[OWGuard] Регион не найден");
                                    }
                                }
                            } elseif (strtolower($args[0]) == "remove" || strtolower($args[0]) == "delete") {
                                if (isset($args[1])) {
                                    if ($this->areas->exists(strtolower($args[1]))) {
                                        if (in_array(strtolower($s->getName()), $this->areas->get(strtolower($args[1]))["owners"]) || $s->hasPermission("rg.doall")) {
                                            $this->areas->remove(strtolower($args[1]));
                                            $this->areas->save();
                                            $s->sendMessage(F::YELLOW . "[OWGuard] Регион " . $args[1] . " был удален !");
                                            return;
                                        } else {
                                            $s->sendMessage(F::RED . "[OWGuard] Нет прав !");
                                            return;
                                        }
                                    } else {
                                        $s->sendMessage(F::RED . "[OWGuard] Регион " . $args[1] . " не найден");
                                        return;
                                    }
                                } else {
                                    $s->sendMessage(F::RED . "[OWGuard] Используй /owguard remove название_региона");
                                    return;
                                }
                            } elseif (strtolower($args[0]) == "addmember") {
                                if (isset($args[1]) && isset($args[2])) {
                                    $s->sendMessage(F::YELLOW . $this->ROAPFA($s, $args[2], $args[1], "members", "add"));
                                    return;
                                } else {
                                    $s->sendMessage(F::RED . "[OWGuard] Используй /owguard addmember название_региона имя_игрока");
                                    return;
                                }
                            } elseif (strtolower($args[0]) == "addowner") {
                                if (isset($args[1]) && isset($args[2])) {
                                    $s->sendMessage(F::YELLOW . $this->ROAPFA($s, $args[2], $args[1], "owners", "add"));
                                    return;
                                } else {
                                    $s->sendMessage(F::RED . "[OWGuard] Используй /owguard addowner название_региона имя_игрока");
                                    return;
                                }
                            } elseif (strtolower($args[0]) == "removemember") {
                                if (isset($args[1]) && isset($args[2])) {
                                    $s->sendMessage(F::YELLOW . $this->ROAPFA($s, $args[2], $args[1], "members", "remove"));
                                    return;
                                } else {
                                    $s->sendMessage(F::RED . "[OWGuard] Используй /owguard removemember название_региона имя_игрока");
                                    return;
                                }
                            } elseif (strtolower($args[0]) == "removeowner") {
                                if (isset($args[1]) && isset($args[2])) {
                                    $s->sendMessage(F::YELLOW . $this->ROAPFA($s, $args[2], $args[1], "owners", "remove"));
                                    return;
                                } else {
                                    $s->sendMessage(F::RED . "[OWGuard] Используй /owguard removeowner название_региона имя_игрока");
                                    return;
                                }
                            } elseif (strtolower($args[0]) == "list") {
                                $forList = array();
                                foreach ($this->areas->getAll() as $name => $info) {
                                    if (in_array(strtolower($s->getName()), $info["owners"])) {
                                        $forList[] = $name;
                                    } else {
                                        continue;
                                    }
                                }
                                if (count($forList) != 0) {
                                    $s->sendMessage(F::YELLOW . "[OWGuard] Твои регионы :");
                                    $s->sendMessage(F::YELLOW . implode(", ", $forList));
                                    return;
                                } else {
                                    $s->sendMessage(F::RED . "[OWGuard] У тебя нет регионов ");
                                    return;
                                }
                            } elseif (strtolower($args[0]) == "flag") {
                                if (isset($args[1]) && isset($args[2]) && isset($args[3])) {
                                    if ($this->areas->exists(strtolower($args[1]))) {
                                        if (in_array(strtolower($s->getName()), $this->areas->getAll()[$args[1]]["owners"]) || $s->hasPermission("rg.doall")) {
                                            if (strtolower($args[2]) == "pvp" || strtolower($args[2]) == "build" || strtolower($args[2]) == "use" || strtolower($args[2]) == "send-chat" || strtolower($args[2]) == "entry" || strtolower($args[2]) == "explode" || strtolower($args[2]) == "burn" || strtolower($args[2]) == "regain" || strtolower($args[2]) == "teleport" || strtolower($args[2]) == "god-mode" || strtolower($args[2]) == "sleep" || strtolower($args[2]) == "mob-damage" || strtolower($args[2]) == "tnt-explode" || strtolower($args[2]) == "drop-item") {
                                                if (strtolower($args[3]) == "allow" || strtolower($args[3]) == "deny") {
                                                    if ($s->hasPermission("flag." . strtolower($args[2])) || $s->hasPermission("rg.doall")) {
                                                        $s->sendMessage(F::YELLOW . $this->SetFlag(strtolower($args[2]), strtolower($args[3]), strtolower($args[1])));
                                                        return;
                                                    } else {
                                                        $s->sendMessage(F::RED . "[OWGuard] У тебя нет прав использовать флаг " . $args[2]);
                                                        return;
                                                    }
                                                } else {
                                                    $s->sendMessage(F::RED . "[OWGuard] Флаг можно только вкл или выкл (allow/deny)");
                                                    return;
                                                }
                                            } else {
                                                $s->sendMessage(F::RED . "[OWGuard] Некорректный флаг!");
                                                return;
                                                $s->sendMessage(F::RED . "Флаги: use/pvp/build/send-chat/entry/god-mode,");
                                                $s->sendMessage(F::RED . "teleport/mob-damage/sleep/explode/tnt-explode");
                                                return;
                                            }
                                        } else {
                                            $s->sendMessage(F::RED . "[OWGuard] Нет прав !");
                                            return;
                                        }
                                    } else {
                                        $s->sendMessage(F::RED . "[OWGuard] Регион " . $args[1] . " не найден");
                                        return;
                                    }
                                } else {
                                    $s->sendMessage(F::RED . "[OWGuard] Используй /owguard flag название_региона flag value");
                                    return;
                                }
                            }elseif (strtolower($args[0]) == "help") {
								$s->sendMessage(F::BLUE . "Приват с сайта servpe.tk");
                                $s->sendMessage(F::YELLOW . "/owguard pos1 и pos2 - точки территории");
                                $s->sendMessage(F::YELLOW . "/owguard create название - создание");
                                $s->sendMessage(F::YELLOW . "/owguard addowner/removeowner - управление владельцами");
                                $s->sendMessage(F::YELLOW . "/owguard addember/removmember - управление пользователями");								
                                $s->sendMessage(F::YELLOW . "/owguard flag название флаг значение - управление флагами");
                                $s->sendMessage(F::YELLOW . "/owguard wand - топор для отметки точек");
								
                               } elseif (strtolower($args[0]) == "info") {
                                $s->sendMessage(F::YELLOW . "Чтобы узнать свои регионы пиши /owguard list");
								
							} elseif (strtolower($args[0]) == "mot") {
                                $s->sendMessage(F::YELLOW . "Чтобы узнать свои регионы пиши /owguard list");
                            }elseif (strtolower($args[0]) == "wand") {
                                $player = $s->getServer()->getPlayer($s->getName());
                                $wand = Item::get(271, 0, 1);
                                if ($player->getInventory()->canAddItem($wand)) {
                                    $player->getInventory()->addItem($wand);
                                    $s->sendMessage(F::RED . "[OWGuard] Ты получил топорик для привата !");
                                    return;
                                } else {
                                    $s->sendMessage(F::RED . "[OWGuard] Твой инвентарь полный !");
                                    return;
                                }
                            } elseif (strtolower($args[0]) == "expand") {
                                if (isset($args[1]) and isset($args[2])){
                                    if(strtolower($args[1]) == "up" or strtolower($args[1]) == "down"){
                                        if(is_numeric($args[2])){
                                            if(isset($this->pos1[strtolower($s->getName())]) and isset($this->pos2[strtolower($s->getName())])){
                                                if($args[1] == "up"){
                                                    $this->pos1[strtolower($s->getName())]["1"] = $this->pos1[strtolower($s->getName())]["1"] + $args[2];
                                                    $s->sendMessage(F::RED ."[OWGuard] Выделение региона поднято на ". $args[2] ." блоков вверх");
                                                    return;
                                                } else {
                                                    $this->pos2[strtolower($s->getName())]["1"] = $this->pos2[strtolower($s->getName())]["1"] - $args[2];
                                                    $s->sendMessage(F::YELLOW ."[OWGuard] Выделение региона опущено на ". $args[2] ." блоков вниз");
                                                    return;
                                                }
                                            } else {
                                                $s->sendMessage(F::RED ."[OWGuard] Установи сначала точки 1 и 2");
                                                return;
                                            }
                                        } else {
                                            $s->sendMessage(F::RED ."[OWGuard] Блоки должны быть в цифрах!");
                                            return;
                                        }
                                    } else {
                                        $s->sendMessage(F::RED ."[OWGuard] Используй /owguard expand up or down");
                                        return;
                                    }
                                } else {
                                    $s->sendMessage(F::RED ."[OWGuard] Используй /owguard expand up or down bloks");
                                    return;
                                }
                            } elseif (strtolower($args[0]) != "create" || "pos1" || "pos2" || "remove" || "pinfo" || "info" || "addmember" || "list" || "addowner" || "removemember" || "wand" || "flag") {
                                $s->sendMessage(F::RED . "Команда " . $args[0] . "не найдена!");
                                $s->sendMessage(F::RED . "Используй /owguard help , чтобы увидеть все команды !");
                                return;
                            }
                        } else {
                            $s->sendMessage(F::RED . "[OWGuard] Разработчик привата - Ты.");
                            return;
                        }
                    } else {
                        $s->sendMessage(F::RED . "[OWGuard] Нет прав !");
                        return;
                    }
                } else {
                    $s->sendMessage(F::RED . "[OWGuard] This command work in game only!(/owguard)");
                    return;
                }
        }
    }


    /***
     * @rgram $info
     * @rgram $x
     * @rgram $y
     * @rgram $z
     * @return bool
     */
 function checkCoordinates($info, $x, $y, $z)
    {
        if ($info["min"][0] <= $x and $x <= $info["max"][0]) {
            if ($info["min"][1] <= $y and $y <= $info["max"][1]) {
                if ($info["min"][2] <= $z and $z <= $info["max"][2]) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
				//@mkdir($this->getDataFolder());
            }
        } else {
            return false;
        }
    }

    /**
     * @rgram $flag
     * @rgram $flagStatus
     * @rgram $area
     * @return string
     */
    private function SetFlag($flag, $flagStatus, $area)
    {
        $AA = $this->areas->getAll();
        $AA[$area]["flags"][$flag] = $flagStatus;
        $this->areas->setAll($AA);
        $this->areas->save();
        return "[OWGuard] Флаг {$flag} в регионе {$area} установлен на {$flagStatus}";
    }

    /***
     * @rgram Player $p
     * @rgram $flag
     * @rgram $flagB
     * @rgram $cP
     * @return bool
     */
    function checkF(Player $p, $flag, $flagB, $cP)
    {
        $pos = $p->getPosition();
        foreach ($this->areas->getAll() as $name => $info) {
            if ($this->checkCoordinates($info, $pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ()) and $pos->getLevel()->getName() == $info["level"]) {
                if ($info["flags"][$flag] == $flagB) {
                    if ($cP == true) {
                        if (in_array(strtolower($p->getName()), $info["owners"]) or in_array(strtolower($p->getName()), $info["members"]) || $p->hasPermission("rg.doall")) {
                            $this->forCF[strtolower($p->getName())] = false;
                        } else {
                            $this->forCF[strtolower($p->getName())] = true;
                        }
                    } else {
                        $this->forCF[strtolower($p->getName())] = true;
                    }
                } else {
                    continue;
                }
            } else {
                continue;
            }
        }
        if (isset($this->forCF[strtolower($p->getName())])) {
            if ($this->forCF[strtolower($p->getName())] == true) {
                unset($this->forCF[strtolower($p->getName())]);
                return true;
            } elseif
            ($this->forCF[strtolower($p->getName())] == false
            ) {
                unset($this->forCF[strtolower($p->getName())]);
                return false;
            } elseif ($this->forCF[strtolower($p->getName())] != false and $this->forCF[strtolower($p->getName())] != false) {
                unset($this->forCF[strtolower($p->getName())]);
                return false;
            }
        } else {
            return false;
        }
    }

    /***
     * @rgram Player $sender
     * @rgram $playerForAddOrRemove
     * @rgram $rg
     * @rgram $fromRemove
     * @rgram $removeOrAdd
     * @return string
     */
    private function ROAPFA(Player $sender, $playerForAddOrRemove, $rg, $fromRemove, $removeOrAdd)
    {
        $PFAOR = strtolower($playerForAddOrRemove);
        $area = strtolower($rg);
        $areas = $this->areas->getAll();
        $ROA = strtolower($removeOrAdd);
        $FR = strtolower($fromRemove);
        if (isset($areas[$area])) {
            if (in_array(strtolower($sender->getName()), $areas[$area]["owners"]) or $sender->hasPermission("rg.doall")) {
                if ($ROA == "add") {
                    $list = $areas[$area][$FR];
                    $list[] = $PFAOR;
                    $areas[$area][$FR] = $list;
                    $this->areas->setAll($areas);
                    $this->areas->save();
                    return "[OWGuard] Игрок {$PFAOR} был добавлен в регион {$area}";
                } else {
                    $rlist = $areas[$area][$FR];
                    $key = array_search($PFAOR, $rlist);
                    unset($rlist[$key]);
                    $areas[$area][$FR] = $rlist;
                    $this->areas->setAll($areas);
                    $this->areas->save();
                    return "[OWGuard] Игрок {$PFAOR} был удален из региона {$area}";
                }
            } else {
                return "[OWGuard] Нет прав !";
            }
        } else {
            return "[OWGuard] Региона {$area} не существует";
        }
    }

    function onDisable()
    {
        $this->getLogger()->info(F::RED . "Приват система выключена");
        $this->areas->save();
        $this->config->save();
    }
}