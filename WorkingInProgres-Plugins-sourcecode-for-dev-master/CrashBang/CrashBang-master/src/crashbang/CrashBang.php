<?php

namespace crashbang;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use pocketmine\block\Block;
use pocketmine\item\Item;
use pocketmine\entity\Effect;

class CrashBang extends PluginBase implements Listener {

    const GAME_TIME = 500;

    private $available, $picking, $ps, $motd, $tasks, $deaths, $lastEat;
    public $skill, $status, $timer, $cooldown;

    public function onEnable() {
        Skills::init();
        $this->status = 0; // 0: Stopped, 1: choosing, 2: started
        $this->motd = $this->getServer()->getMotd();
        $this->tasks = array();
        $this->getServer()->getNetwork()->setName(TextFormat::GREEN."[입장 가능] ".TextFormat::RESET.$this->motd);
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getServer()->getScheduler()->scheduleRepeatingTask(new Timer($this), 1);
        $this->getLogger()->info("Crashbang loaded!");
        require_once("SkillTasks.php");
    }

    public function onTouch(\pocketmine\event\player\PlayerInteractEvent $ev) {
        if($ev->getBlock()->getID() === Block::CAKE_BLOCK) {
            if(!isset($this->lastEat[$ev->getPlayer()->getName()])) $this->lastEat[$ev->getPlayer()->getName()] = 0;
            if($this->lastEat[$ev->getPlayer()->getName()] <= microtime(true)) {
                $e = new EntityRegainHealthEvent($ev->getPlayer(), 4, EntityRegainHealthEvent::CAUSE_EATING);
                $ev->getPlayer()->heal(4, $e);
                $this->lastEat[$ev->getPlayer()->getName()] = microtime(true) + ($this->skill[$ev->getPlayer()->getName()] === Skills::BIG_EATER ? 1.5 : 5);
                $ev->getPlayer()->sendMessage("[CrashBang] 체력이 회복되었습니다.");
            } else {
                $ev->getPlayer()->sendMessage("[CrashBang] 체력은 5초에 한 번만 회복이 가능합니다(식신은 1.5초)");
            }
        }
        if($this->status !== 2 or $ev->getItem()->getID() !== Item::BLAZE_ROD) return;
        $ev->setCancelled();
        if($this->cooldown[$ev->getPlayer()->getName()] > 0) {
            $ev->getPlayer()->sendMessage("[CrashBang] 아직 스킬을 사용할 수 없습니다.");
            return;
        }
        switch($this->skill[$ev->getPlayer()->getName()]) {
            case Skills::ZOMBIE:
                $ev->getPlayer()->addEffect(Effect::getEffect(Effect::SLOWNESS)->setAmplifier(2)->setDuration(15*20));
                $ev->getPlayer()->addEffect(Effect::getEffect(Effect::REGENERATION)->setAmplifier(4)->setDuration(10*20));
                break;
            case Skills::EARTHQUAKE:
                $dirs = array();
                foreach($this->getServer()->getOnlinePlayers() as $p) {
                    if($p->getName() === $ev->getPlayer()->getName()) continue;
                    $dirs[$p->getName()] = $p->distance($ev->getPlayer());
                }
                asort($dirs);
                $i = 0;
                foreach($dirs as $p => $d) {
                    if(++$i >= 2) break;
                    $p = $this->getServer()->getPlayerExact($p);
                    $e = new EntityDamageByEntityEvent($ev->getPlayer(), $p, EntityDamageEvent::CAUSE_ENTITY_ATTACK, 12);
                    $p->attack($ev->getFinalDamage(), $e);
                }
                break;
            case Skills::PLAGUE:
                foreach($this->getServer()->getOnlinePlayers() as $p) {
                    if($p->getName() !== $ev->getPlayer()->getName()){
                        $p->addEffect(Effect::getEffect(Effect::NAUSEA)->setAmplifier(1)->setDuration(15*20));
                        $p->addEffect(Effect::getEffect(Effect::SLOWNESS)->setAmplifier(1)->setDuration(7*20));
                    }
                }
                break;
            case Skills::CREEPER:
                foreach($this->getServer()->getOnlinePlayers() as $p) {
                    if ($ev->getPlayer()->distanceSquared($p) > 25) continue;
                    $e = new EntityDamageByEntityEvent($ev->getPlayer(), $p, EntityDamageEvent::CAUSE_ENTITY_ATTACK, 15);
                    $p->attack($e->getFinalDamage(), $e);
                }
                break;
            case Skills::HEAL:
                $ev->getPlayer()->addEffect(Effect::getEffect(Effect::REGENERATION)->setAmplifier(5)->setDuration(3*20));
                break;
            case Skills::STEALTH:
                $ev->getPlayer()->addEffect(Effect::getEffect(Effect::INVISIBILITY)->setAmplifier(1)->setDuration(5*20));
                break;
            case Skills::EYE_FOR_EYE:
                $this->ps[$ev->getEntity()->getName()] += 3;
                break;
            case Skills::IGNITE:
                foreach($this->getServer()->getOnlinePlayers() as $p) {
                    $p->setOnFire(7);
                }
                break;
            case Skills::STORM:
                foreach($this->getServer()->getOnlinePlayers() as $p) {
                    if ($p->getName() === $ev->getPlayer()->getName() or $ev->getPlayer()->distanceSquared($p) > (22 ** 2)) continue;
                    $e = new EntityDamageByEntityEvent($ev->getPlayer(), $p, EntityDamageEvent::CAUSE_ENTITY_ATTACK, 4);
                    $p->attack($e->getFinalDamage(), $e);
                    $p->sendMessage("[CrashBang] 야 쓰레기! 폭풍저그 콩진호가 간다!");
                    $p->sendMessage("[CrashBang] 야 쓰레기! 폭풍저그 콩진호가 간다!");
                }
                break;
            case Skills::EQUALITY:
                foreach($this->getServer()->getOnlinePlayers() as $p) $p->setHealth(8);
                $this->getServer()->broadcastMessage("[CrashBang] 평등 스킬이 사용되었습니다.");
                break;
            case Skills::REBORN:
                $this->ps[$ev->getPlayer()->getName()] = 1;
                $ev->getPlayer()->kill();
                $ev->getPlayer()->sendMessage("[CrashBang] 환생 시 추가 체력을 지급받습니다.");
                break;
            case Skills::INVINCIBLE:
                $this->ps[$ev->getPlayer()->getName()] = microtime(true) + 5;
                $ev->getPlayer()->sendMessage("[CrashBang] 5초간 무적 상태가 되어 모든 공격을 무시합니다.");
                $ev->getPlayer()->sendMessage("[CrashBang] 자신도 공격할 수 없습니다.");
                break;
            default:
                $ev->setCancelled(false);
                return;
        }
        $this->startCooldown($ev->getPlayer());
    }

    public function onHit(\pocketmine\event\entity\EntityDamageByEntityEvent $ev) {
        if(!($ev->getEntity() instanceof Player) or !($ev->getDamager() instanceof Player)) return;
        if($this->status !== 2) {
            $ev->setCancelled();
            $ev->getDamager()->sendMessage("[CrashBang] 정숙");
            return;
        }
        if(
            ($this->skill[$ev->getDamager()->getName()] === Skills::INVINCIBLE and
            $this->ps[$ev->getDamager()->getName()] > microtime(true)) or
            ($this->skill[$ev->getEntity()->getName()] === Skills::INVINCIBLE and
            $this->ps[$ev->getEntity()->getName()] > microtime(true))
        ) {
            $ev->setCancelled();
            $ev->getDamager()->sendMessage("[CrashBang] 당신 또는 대상이 무적 상태이므로 공격이 무시됩니다.");
            return;
        }
        if(in_array($this->skill[$ev->getDamager()->getName()], [
            Skills::BERSERKER, Skills::VAMPIRE, Skills::STEALTH,
            Skills::UPGRADE, Skills::POISONED_DAGGER
        ])){
            switch($this->skill[$ev->getDamager()->getName()]) {
                case Skills::BERSERKER:
                    $ev->setDamage(floor((20 - $ev->getDamager()->getHealth()) / 4) * 2, 5);
                    break;
                case Skills::VAMPIRE:
                    $e = new EntityRegainHealthEvent($ev->getDamager(), 2, EntityRegainHealthEvent::CAUSE_MAGIC);
                    $ev->getDamager()->heal(2, $e);
                    break;
                case Skills::STEALTH:
                    $ev->getDamager()->removeEffect(Effect::INVISIBILITY);
                    break;
                case Skills::UPGRADE:
                    $ev->setDamage($this->ps[$ev->getDamager()->getName()], 5);
                    break;
                case Skills::POISONED_DAGGER:
                    $this->startCooldown($this->getDamager());
                    $ev->setDamage(5, 5);
                    break;
            }
        } else if($this->skill[$ev->getEntity()->getName()] === Skills::EYE_FOR_EYE and $this->ps[$ev->getEntity()->getName()]-- > 0) {
            if ($this->skill[$ev->getDamager()->getName()] === Skills::EYE_FOR_EYE) return;
            $e = new EntityDamageByEntityEvent($ev->getEntity(), $ev->getDamager(), EntityDamageEvent::CAUSE_ENTITY_ATTACK, floor($ev->getFinalDamage() * 0.7));
            $ev->getDamager()->attack($e->getFinalDamage(), $e);
        } else if(
            $ev->getDamager()->getInventory()->getItemInHand()->getID() === Item::BLAZE_ROD and
            in_array($this->skill[$ev->getDamager()->getName()], [Skills::ASSASSIN, Skills::TRACE])
        ) {
            $ev->setCancelled();
            if($this->cooldown[$ev->getDamager()->getName()] > 0) {
                $ev->getDamager()->sendMessage("[CrashBang] 아직 스킬을 사용할 수 없습니다.");
                $ev->setCancelled(false);
                return;
            }

            switch($this->skill[$ev->getDamager()->getName()]) {
                case Skills::ASSASSIN:
                    $n = mt_rand(0, 99);
                    $ev->setCancelled(false);
                    if($n < 30) {
                        $ev->setDamage(9999);
                    } else {
                        $e = new EntityDamageByEntityEvent($ev->getEntity(), $ev->getDamager(), EntityDamageEvent::CAUSE_ENTITY_ATTACK, 15);
                        $ev->getDamager()->attack($ev->getFinalDamage(), $e);
                        $ev->setDamage(15);
                    }
                    break;
                case Skills::TRACE:
                    $this->getServer()->getScheduler()->scheduleDelayedTask(new TraceTask($this, $ev->getDamager(), $ev->getEntity()), 7*20);
                    $ev->getDamager()->sendMessage("[CrashBang] 7초 후 추적 대상" . $ev->getEntity()->getName() . "에게 이동합니다.");
                    break;
            }
            $this->startCooldown($ev->getDamager());
        }
    }

    public function onDeath(\pocketmine\event\player\PlayerDeathEvent $ev) {
        if(!isset($this->deaths[$ev->getEntity()->getName()])) $this->deaths[$ev->getEntity()->getName()] = 0;
        $this->deaths[$ev->getEntity()->getName()]++;
        switch($this->skill[$ev->getEntity()->getName()]) {
            case Skills::UPGRADE:
                $this->ps[$ev->getEntity()->getName()] = 0;
        }
    }

    public function onRespawn(\pocketmine\event\player\PlayerRespawnEvent $ev) {
        if($this->skill[$ev->getPlayer()->getName()] === Skills::REBORN and $this->ps[$ev->getPlayer()->getName()] === 1) {
            $ev->getPlayer()->setHealth(35);
            $ev->getPlayer()->sendMessage("[CrashBang] 추가 체력이 지급되었습니다.");
            $this->ps[$ev->getPlayer()->getName()] = 0;
        }
    }

    public function onPreLogin(\pocketmine\event\player\PlayerPreLoginEvent $ev) {
        if($this->status > 0) {
            $ev->setCancelled();
            $ev->getPlayer()->close("게임 진행 중", "게임이 진행 중입니다.\n".TextFormat::AQUA.TextFormat::BOLD.$this->timer.TextFormat::RESET."초 뒤에 다시 접속해주세요.");
        }
    }

    public function onQuit(\pocketmine\event\player\PlayerQuitEvent $ev) {
        $ev->getPlayer()->removeAllEffects();
    }

    public function onCommand(CommandSender $sender, Command $command, $label, array $args) {
        if(!($sender instanceof Player) and $command->getName() === "helpall") {
            foreach(Skills::$cooldown as $i => $c) {
                echo($this->getHelp($i) . "\n");
            }
        }
        if(count($args) === 0) {
            return false;
        }
        if(!($sender instanceof Player)) return true;
        switch($args[0]) {
            case "help":
                if($this->status === 0) {
                    $sender->sendMessage("[CrashBang] 현재 사용할 수 없는 명령어입니다.");
                    break;
                }
                $sender->sendMessage($this->getHelp($this->skill[$sender->getName()]));
                break;
            case "start":
                if($this->status !== 0) {
                    $sender->sendMessage("[CrashBang] 현재 사용할 수 없는 명령어입니다.");
                    break;
                }
                $this->roulette();
                break;
            case "stop":
                if($this->status !== 2) {
                    $sender->sendMessage("[CrashBang] 현재 사용할 수 없는 명령어입니다.");
                    break;
                }
                $this->stop();
                break;
            case "yes":
                if($this->status !== 1) {
                    $sender->sendMessage("[CrashBang] 현재 사용할 수 없는 명령어입니다.");
                    break;
                }
                if($this->picking[$sender->getName()]) {
                    $sender->sendMessage("[CrashBang] 이미 능력을 확정했습니다.");
                }
                unset($this->picking[$sender->getName()]);
                $sender->sendMessage("[CrashBang] 능력이 확정되었습니다.");
                if(count($this->picking) === 0) {
                    $this->start();
                }
                break;
            case "no":
                if($this->status !== 1) {
                    $sender->sendMessage("[CrashBang] 현재 사용할 수 없는 명령어입니다.");
                    break;
                }
                if($this->picking[$sender->getName()]) {
                    $sender->sendMessage("[CrashBang] 이미 능력을 확정했습니다.");
                }
                unset($this->picking[$sender->getName()]);
                $sender->sendMessage("[CrashBang] 능력이 재추첨됩니다.");
                $this->available[] = $this->skill[$sender->getName()];
                $this->pick($sender);
                if(count($this->picking) === 0) {
                    $this->start();
                }
                break;
            case "set":
                if(!$sender->hasPermission("crashbang.set")) {
                    $sender->sendMessage(TextFormat::RED."이 명령어를 사용할 권한이 없습니다.");
                    break;
                }
                if($this->status !== 1) return true;
                if(count($args) < 2 or !is_numeric($args[1])) return false;
                unset($this->picking[$sender->getName()]);
                $this->available[] = $this->skill[$sender->getName()];
                $this->skill[$sender->getName()] = (int) $args[1];
                $sender->sendMessage("[CrashBang] 능력이 강제 설정되었습니다.");
                break;
            default:
                return false;
        }
        return true;
    }

    public function roulette() {
        $this->status = 1;
        $this->getServer()->getNetwork()->setName(TextFormat::GOLD.TextFormat::ITALIC."[진행 중] ".TextFormat::RESET.$this->motd);
        $this->skill = array();
        $this->ps = array(); // Player keystore
        $this->available = array();
        $this->picking = array();
        $this->cooldown = array();
        $this->deaths = array();
        $this->lastEat = array();
        $this->timer = 60 + self::GAME_TIME;
        $this->getServer()->broadcastMessage("[CrashBang] 능력 추첨을 시작합니다");
        $this->getServer()->broadcastMessage("[CrashBang] /cb <yes|no>로 능력을 정하세요.");
        $this->getServer()->broadcastMessage("[CrashBang] 1분 내에 확정하지 않으면 킥 처리하고 게임을 시작합니다.");
        foreach(Skills::$cooldown as $k => $c) {
            if($k === 2 or $k === 10) continue; // Unimplemented
            $this->available[$k] = $k;
        }
        foreach($this->getServer()->getOnlinePlayers() as $p) {
            $this->deaths[$p->getName()] = 0;
            $this->picking[$p->getName()] = true;
            $this->pick($p);
            $p->sendMessage("[CrashBang] 추첨된 능력:");
            $p->sendMessage($this->getHelp($this->skill[$p->getName()]));
        }
    }

    public function pick(Player $p) {
        $i = mt_rand(0, count($this->available) - 1);
        $a = $this->available[array_keys($this->available)[$i]];
        $this->skill[$p->getName()] = $a;
        unset($this->available[array_keys($this->available)[$i]]);
        $p->sendMessage("[CrashBang] 능력이 주어졌습니다. /cb help로 확인하세요.");
    }

    public function start() {
        $this->status = 2;
        $this->timer = self::GAME_TIME;
        foreach($this->picking as $p => $c) {
            if($c) {
                $pl = $this->getServer()->getPlayerExact($p);
                if($pl !== NULL) $pl->kick("1분 안에 능력을 고르지 못했습니다");
            }
        }
        foreach($this->getServer()->getOnlinePlayers() as $p) {
            $this->cooldown[$p->getName()] = 0;
            switch($this->skill[$p->getName()]) {
                case Skills::EYE_FOR_EYE:
                case Skills::REBORN:
                case Skills::INVINCIBLE:
                    $this->ps[$p->getName()] = 0;
                    break;
                case Skills::CONTRACT:
                    $this->ps[$p->getName()] = "";
                case Skills::UPGRADE:
                    $this->ps[$p->getName()] = 0;
                    $this->tasks[] = $this->getServer()->getScheduler()->scheduleRepeatingTask(new UpgradeTask($this, $p), 30*20);
            }
        }
        $this->getServer()->broadcastMessage("[CrashBang] 게임이 시작되었습니다.");
    }

    public function stop() {
        $this->status = 0;
        foreach($this->getServer()->getOnlinePlayers() as $p) $p->removeAllEffects();
        foreach($this->tasks as $t) $t->remove();
        $this->tasks = array();
        $this->getServer()->broadcastMessage("[CrashBang] 게임이 종료되었습니다.");
        $this->getServer()->getNetwork()->setName(TextFormat::GREEN."[입장 가능] ".TextFormat::RESET.$this->motd);
    }

    public function startCooldown(Player $p) {
        $this->cooldown[$p->getName()] = Skills::$cooldown[$this->skill[$p->getName()]];
        Skills::$cooldown[$this->skill[$p->getName()]] !== 0 ? $p->sendMessage("[CrashBang] 스킬을 사용했습니다."):false;
    }

    public function getHelp($i) {
        $c = Skills::$cooldown[$i];
        return str_replace("\r", "", Skills::$desc[$i]) . ($c <= 0 ? " (쿨타임 없음)" : " (쿨타임 " . $c . "초)");
    }

}
