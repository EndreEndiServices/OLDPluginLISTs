<?php

declare(strict_types=1);

namespace fcore\task;

use fcore\bossbar\BossBarAPI;
use fcore\FCore;
use fcore\lang\Language;
use fcore\profile\ProfileManager;
use fcore\profile\RankManager;
use pocketmine\entity\Effect;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\scheduler\Task;

/**
 * Class JoinTask
 * @package fcore\task
 */
class JoinTask extends Task {

    /** @var FCore $plugin */
    public $plugin;

    /** @var Player $player */
    public $player;

    public $join;

    /**
     * JoinTask constructor.
     * @param FCore $plugin
     * @param Player $player
     */
    public function __construct(FCore $plugin, Player $player, $join = true) {
        $this->plugin = $plugin;
        $this->player = $player;
        $this->join = $join;
    }

    /**
     * @param int $currentTick
     */
    public function onRun(int $currentTick) {
        if(!$this->player->isOnline()) {
            $this->plugin->scheduleMgr->runJoinTask($this->player, $this->join, true);
            return;
        }
        if($this->join) {
            ProfileManager::onJoin($this->player);
            $this->player->addTitle("§aWELCOME, ".strtoupper($this->player->getName()),"§6ON FACTIONPE [BETA]", 20, 20, 20);

            $msg = str_replace("%player", $this->player->getName(), Language::_(ProfileManager::lang($this->player), "join-msg"));
            $this->player->sendMessage($msg);

            $rank = ProfileManager::getPlayerRank($this->player);
            if($rank != "guest") {
                $rank = RankManager::$displayRanks[$rank];
            }
            else {
                $rank = "";
            }
            $this->plugin->getServer()->broadcastMessage("§7> $rank §r§7{$this->player->getName()} §7joined the game!");
        }

        $this->player->teleport($this->plugin->getServer()->getLevelByName(FCore::DEFAULT_LEVEL_NAME)->getSafeSpawn());

        $this->player->getArmorInventory()->clearAll();

        $inv = $this->player->getInventory();
        $inv->clearAll();
        $inv->setItem(0, Item::get(Item::MOB_HEAD)->setCustomName("§aProfile"));
        $inv->setItem(1, Item::get(Item::COMPASS)->setCustomName("§r§bTeleporter\n§7§o- EggWars\n- MurderMystery"));
        $inv->setItem(4, Item::get(175)->setCustomName("§r§6Shop"));
        $inv->setItem(5, Item::get(Item::EMERALD)->setCustomName("§r§8§k|§r§7§k|§r§f§k|§r§e§l VIP §r§f§k|§r§7§k|§r§k§8|"));
        $inv->setItem(7, Item::get(Item::NETHER_STAR)->setCustomName("§r§6Cosmetics\n§7§o- Gadgets\n- Particles"));
        $inv->setItem(8, Item::get(Item::DYE, 10)->setCustomName("§r§aHide players"));
        $p = $this->player;
        $p->setGamemode($p::ADVENTURE);
        $p->setFood(20);
        $p->setMaxHealth(40);
        $p->setHealth(40);
        if(!is_int($this->plugin->barEid)) {
            $this->plugin->barEid = BossBarAPI::addBossBar([$p], "§7WELCOME ON §6PLAY.FACTIONPE.TK §7{$p->getName()} §f|| §7CHECK OUR WEBSITE §6FACTIONPE.TK\n§aYou are playing on Lobby #1");
            BossBarAPI::setPercentage(100, $this->plugin->barEid);
        }
        BossBarAPI::sendBossBarToPlayer($p, $this->plugin->barEid, "§7WELCOME ON §6PLAY.FACTIONPE.TK §7{$p->getName()} §f|| §7CHECK OUR WEBSITE §6FACTIONPE.TK\n\n".str_repeat(" ", 28)."§aYou are playing on Lobby #1");
        BossBarAPI::setPercentage(100, $this->plugin->barEid);
        $this->plugin->floatingTextMgr->spawnLobbyParticles();
        $speed = Effect::getEffect(Effect::SPEED);
        $speed->setDuration(9999999);
        $speed->setAmplifier(9);
        $speed->setVisible(false);
        $jump = Effect::getEffect(Effect::JUMP);
        $jump->setDuration(9999999);
        $jump->setAmplifier(5);
        $jump->setVisible(false);
        $p->addEffect($speed);
        $p->addEffect($jump);
        $p->setScale(1.0);
    }
}