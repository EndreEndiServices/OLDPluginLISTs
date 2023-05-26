<?php

namespace WorldGuardian;

use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\Listener;
use pocketmine\command\CommandExecutor;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\item\Item;
use pocketmine\utils\Config;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageByEntity;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\utils\TextFormat;
use pocketmine\utils\TextFormat as F;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\block\ItemFrameDropItemEvent;

class WorldGuardian extends PluginBase implements CommandExecutor, Listener
{
    private $db, $pos1 = array(), $pos2 = array();
    private $config;
    private $xgroup;
    private $g_var;
    
	public function onBlockBreak(BlockBreakEvent $event)
    {
        $player   = $event->getPlayer();
        $x = round($event->getBlock()->getX());
        $y = round($event->getBlock()->getY());
        $z = round($event->getBlock()->getZ());
        $level = $event->getBlock()->getLevel()->getName();
        $username = strtolower($event->getPlayer()->getName());
      if($event->getItem()->getID() == 271) {
            $this->pos1[$username] = array(
                $x,
                $y,
                $z,
                $level
            );
            $event->getPlayer()->sendMessage("§8(§aПриват§8)§f Первая точка§a успешно §fустановлена.");
            if (isset($this->pos1[$username]) && isset($this->pos2[$username]) && $this->pos1[$username][3] == $this->pos2[$username][3]) {
                $pos1   = $this->pos1[$username];
                $pos2   = $this->pos2[$username];
                $min[0] = min($pos1[0], $pos2[0]);
                $max[0] = max($pos1[0], $pos2[0]);
                $min[1] = min($pos1[1], $pos2[1]);
                $max[1] = max($pos1[1], $pos2[1]);
                $min[2] = min($pos1[2], $pos2[2]);
                $max[2] = max($pos1[2], $pos2[2]);
                $count = $this->countBlocks($min[0], $min[1], $min[2], $max[0], $max[1], $max[2]);
            }
            $event->setCancelled(true);
        } else {
            $result = $this->db->query("SELECT * FROM AREAS WHERE (Pos1X <= $x AND $x <= Pos2X) AND (Pos1Y <= $y AND $y <= Pos2Y) AND (Pos1Z <= $z AND $z <= Pos2Z) AND Level = '" . $level . "';")->fetchArray(SQLITE3_ASSOC);
            $member = $this->db->query("SELECT COUNT(*) as count FROM MEMBERS WHERE Region = '" . $result['Region'] . "' AND Name = '$username'")->fetchArray(SQLITE3_ASSOC);
            $flag = $this->db->query("SELECT COUNT(*) as count FROM FLAGS WHERE Region = '" . $result['Region'] . "' AND Flag = 'build' AND Value = 'allow'")->fetchArray(SQLITE3_ASSOC);
            $chest_access_flag = $this->db->query("SELECT COUNT(*) as count FROM FLAGS WHERE Region = '" . $result['Region'] . "' AND Flag = 'chest-access' AND Value = 'allow'")->fetchArray(SQLITE3_ASSOC);
            if ($result !== false && $username != $result['Owner'] && !$event->getPlayer()->isOp() && !$member['count'] && !$flag['count']) {
                $event->getPlayer()->sendPopup(F::RED."Ты не можешь ломать здесь!");
                $event->setCancelled(true);
            }
        }
    }
	 public function onMod(ItemFrameDropItemEvent $event){
         $entity = $event->getPlayer();
         if($entity instanceof Player){$x = round($entity->getX());$y = round($entity->getY());$z = round($entity->getZ());$level = $entity->getLevel()->getName();$player = $entity->getPlayer();$result = $this->db->query("SELECT * FROM AREAS WHERE (Pos1X <= $x AND $x <= Pos2X) AND (Pos1Y <= $y AND $y <= Pos2Y) AND (Pos1Z <= $z AND $z <= Pos2Z) AND Level = '".$level."';")->fetchArray(SQLITE3_ASSOC);$count = $this->db->query("SELECT COUNT(*) as count FROM AREAS WHERE (Pos1X <= $x AND $x <= Pos2X) AND (Pos1Y <= $y AND $y <= Pos2Y) AND (Pos1Z <= $z AND $z <= Pos2Z) AND Level = '".$level."';")->fetchArray(SQLITE3_ASSOC);
         if($count['count']){
       $event->setCancelled();
       $entity->sendPopup("§fВы §cне можете §fбрать предметы из рамок на этой территории");
         }
}
    }
    public function onEntityDamageByEntity(EntityDamageEvent $event)
    {
        $entity = $event->getEntity();
        if ($event instanceof EntityDamageByEntityEvent) {
            $damager = $event->getDamager();
            $leveld = $damager->getLevel()->getName();
            $xd = round($damager->getX());
            $yd = round($damager->getY());
            $zd = round($damager->getZ());
            $resultd_check = $this->db->query("SELECT COUNT(*) as count FROM AREAS WHERE (Pos1X <= $xd AND $xd <= Pos2X) AND (Pos1Y <= $yd AND $yd <= Pos2Y) AND (Pos1Z <= $zd AND $zd <= Pos2Z) AND Level = '" . $leveld . "';")->fetchArray(SQLITE3_ASSOC);
            $resultd = $this->db->query("SELECT * FROM AREAS WHERE (Pos1X <= $xd AND $xd <= Pos2X) AND (Pos1Y <= $yd AND $yd <= Pos2Y) AND (Pos1Z <= $zd AND $zd <= Pos2Z) AND Level = '" . $leveld . "';")->fetchArray(SQLITE3_ASSOC);
            $pvpd_flag = $this->db->query("SELECT * FROM FLAGS WHERE Region = '" . $resultd['Region'] . "' AND Flag = 'pvp'")->fetchArray(SQLITE3_ASSOC);
            $pvpd_flag_check = $this->db->query("SELECT COUNT(*) as count FROM FLAGS WHERE Region = '" . $resultd['Region'] . "' AND Flag = 'pvp'")->fetchArray(SQLITE3_ASSOC);
            $levele = $entity->getLevel()->getName();
            $xe              = round($entity->getX());
            $ye              = round($entity->getY());
            $ze              = round($entity->getZ());
            $resulte_check   = $this->db->query("SELECT COUNT(*) as count FROM AREAS WHERE (Pos1X <= $xe AND $xe <= Pos2X) AND (Pos1Y <= $ye AND $ye <= Pos2Y) AND (Pos1Z <= $ze AND $ze <= Pos2Z) AND Level = '" . $levele . "';")->fetchArray(SQLITE3_ASSOC);
            $resulte         = $this->db->query("SELECT * FROM AREAS WHERE (Pos1X <= $xe AND $xe <= Pos2X) AND (Pos1Y <= $ye AND $ye <= Pos2Y) AND (Pos1Z <= $ze AND $ze <= Pos2Z) AND Level = '" . $levele . "';")->fetchArray(SQLITE3_ASSOC);
            $pvpe_flag       = $this->db->query("SELECT * FROM FLAGS WHERE Region = '" . $resulte['Region'] . "' AND Flag = 'pvp'")->fetchArray(SQLITE3_ASSOC);
            $pvpe_flag_check = $this->db->query("SELECT COUNT(*) as count FROM FLAGS WHERE Region = '" . $resulte['Region'] . "' AND Flag = 'pvp'")->fetchArray(SQLITE3_ASSOC);
            if ($entity instanceof Player && $damager instanceof Player) {
                if (($resultd_check['count'] && $pvpd_flag_check['count']) || ($resulte_check['count'] && $pvpe_flag_check['count'])) {
                    if ($pvpd_flag['Value'] == "deny" && $pvpe_flag['Value'] != "deny") {
                        $event->setCancelled(true);
                        $damager->sendPopup(F::RED."На этой территории пвп отключено");
                    }
                    if ($pvpd_flag['Value'] == "deny" && $pvpe_flag['Value'] == "deny") {
                        $event->setCancelled(true);
                        $damager->sendMessage(F::RED."На этой территории пвп отключено");
                    }
                    if ($pvpd_flag['Value'] != "deny" && $pvpe_flag['Value'] == "deny") {
                        $event->setCancelled(true);
                    }
                }
            }
        }
    }
    public function onEntityDamage(EntityDamageEvent $event)
    {
        $entity = $event->getEntity();
        if ($event instanceof EntityDamageEvent) {
            if ($entity instanceof Player) {
                $x      = round($entity->getX());
                $y      = round($entity->getY());
                $z      = round($entity->getZ());
                $level  = $entity->getLevel()->getName();
                $player = $entity->getPlayer();
                $result = $this->db->query("SELECT * FROM AREAS WHERE (Pos1X <= $x AND $x <= Pos2X) AND (Pos1Y <= $y AND $y <= Pos2Y) AND (Pos1Z <= $z AND $z <= Pos2Z) AND Level = '" . $level . "';")->fetchArray(SQLITE3_ASSOC);
                $count  = $this->db->query("SELECT COUNT(*) as count FROM AREAS WHERE (Pos1X <= $x AND $x <= Pos2X) AND (Pos1Y <= $y AND $y <= Pos2Y) AND (Pos1Z <= $z AND $z <= Pos2Z) AND Level = '" . $level . "';")->fetchArray(SQLITE3_ASSOC);
                $flag   = $this->db->query("SELECT COUNT(*) as count FROM FLAGS WHERE Region = '" . $result['Region'] . "' AND Flag = 'invincible' AND Value = 'allow'")->fetchArray(SQLITE3_ASSOC);
                if ($count['count'] && $flag['count']) {
                    $event->setCancelled(true);
                }
            }
        }
    }
    public function onPlayerChat(PlayerChatEvent $event)
    {
        $player   = $event->getPlayer();
        $x        = round($player->getX());
        $y        = round($player->getY());
        $z        = round($player->getZ());
        $level    = $player->getLevel()->getName();
        $username = strtolower($player->getName());
        $result   = $this->db->query("SELECT * FROM AREAS WHERE (Pos1X <= $x AND $x <= Pos2X) AND (Pos1Y <= $y AND $y <= Pos2Y) AND (Pos1Z <= $z AND $z <= Pos2Z) AND Level = '" . $level . "';")->fetchArray(SQLITE3_ASSOC);
        $count    = $this->db->query("SELECT COUNT(*) as count FROM AREAS WHERE (Pos1X <= $x AND $x <= Pos2X) AND (Pos1Y <= $y AND $y <= Pos2Y) AND (Pos1Z <= $z AND $z <= Pos2Z) AND Level = '" . $level . "';")->fetchArray(SQLITE3_ASSOC);
        $flag     = $this->db->query("SELECT COUNT(*) as count FROM FLAGS WHERE Region = '" . $result['Region'] . "' AND Flag = 'send-chat' AND Value = 'deny'")->fetchArray(SQLITE3_ASSOC);
        if($count['count'] && $flag['count'] && !$player->isOp()) {
            $event->setCancelled(true);
        }
    }
    public function onPlayerDropItem(PlayerDropItemEvent $event)
    {
        $player   = $event->getPlayer();
        $x        = round($player->getX());
        $y        = round($player->getY());
        $z        = round($player->getZ());
        $level    = $player->getLevel()->getName();
        $username = strtolower($player->getName());
        $result   = $this->db->query("SELECT * FROM AREAS WHERE (Pos1X <= $x AND $x <= Pos2X) AND (Pos1Y <= $y AND $y <= Pos2Y) AND (Pos1Z <= $z AND $z <= Pos2Z) AND Level = '" . $level . "';")->fetchArray(SQLITE3_ASSOC);
        $flag     = $this->db->query("SELECT COUNT(*) as count FROM FLAGS WHERE Region = '" . $result['Region'] . "' AND Flag = 'item-drop' AND Value = 'deny'")->fetchArray(SQLITE3_ASSOC);
        if ($flag['count']) {
            $event->setCancelled(true);
        }
    }
    public function onBlockPlace(BlockPlaceEvent $event)
    {
        $x        = round($event->getBlock()->getX());
        $y        = round($event->getBlock()->getY());
        $z        = round($event->getBlock()->getZ());
        $level    = $event->getBlock()->getLevel()->getName();
        $username = strtolower($event->getPlayer()->getName());
        $result   = $this->db->query("SELECT * FROM AREAS WHERE (Pos1X <= $x AND $x <= Pos2X) AND (Pos1Y <= $y AND $y <= Pos2Y) AND (Pos1Z <= $z AND $z <= Pos2Z) AND Level = '" . $level . "';")->fetchArray(SQLITE3_ASSOC);
        $member   = $this->db->query("SELECT COUNT(*) as count FROM MEMBERS WHERE Region = '" . $result['Region'] . "' AND Name = '$username'")->fetchArray(SQLITE3_ASSOC);
        $flag     = $this->db->query("SELECT COUNT(*) as count FROM FLAGS WHERE Region = '" . $result['Region'] . "' AND Flag = 'build' AND Value = 'allow'")->fetchArray(SQLITE3_ASSOC);
        if ($result !== false and $username != $result['Owner'] and !$event->getPlayer()->isOp() and !$member['count'] and !$flag['count']) {
            $event->getPlayer()->sendPopup(F::RED."Ты не можешь строить здесь!");
            $event->setCancelled(true);
        }
    }
    public function onInteract(PlayerInteractEvent $event)
    {
        $player   = $event->getPlayer();
        $block    = $event->getBlock();
        $x        = round($event->getBlock()->getX());
        $y        = round($event->getBlock()->getY());
        $z        = round($event->getBlock()->getZ());
        $level    = $event->getBlock()->getLevel()->getName();
        $username = strtolower($event->getPlayer()->getName());
        if ($event->getItem()->getID() == 271) {
            $this->pos2[$username] = array(
                $x,
                $y,
                $z,
                $level
            );
            $event->getPlayer()->sendMessage("§8(§aПриват§8)§f Вторая точка §aуспешно §fустановлена.");
            if (isset($this->pos1[$username]) && isset($this->pos2[$username]) && $this->pos1[$username][3] == $this->pos2[$username][3]) {
                $pos1   = $this->pos1[$username];
                $pos2   = $this->pos2[$username];
                $min[0] = min($pos1[0], $pos2[0]);
                $max[0] = max($pos1[0], $pos2[0]);
                $min[1] = min($pos1[1], $pos2[1]);
                $max[1] = max($pos1[1], $pos2[1]);
                $min[2] = min($pos1[2], $pos2[2]);
                $max[2] = max($pos1[2], $pos2[2]);
                $count  = $this->countBlocks($min[0], $min[1], $min[2], $max[0], $max[1], $max[2]);
            }
            $event->setCancelled(true);
        }
        if ($event->getBlock()->getID() == 54) {
            $result = $this->db->query("SELECT * FROM AREAS WHERE (Pos1X <= $x AND $x <= Pos2X) AND (Pos1Y <= $y AND $y <= Pos2Y) AND (Pos1Z <= $z AND $z <= Pos2Z) AND Level = '" . $level . "';")->fetchArray(SQLITE3_ASSOC);
            $count  = $this->db->query("SELECT COUNT(*) as count FROM AREAS WHERE (Pos1X <= $x AND $x <= Pos2X) AND (Pos1Y <= $y AND $y <= Pos2Y) AND (Pos1Z <= $z AND $z <= Pos2Z) AND Level = '" . $level . "';")->fetchArray(SQLITE3_ASSOC);
            if ($count['count']) {
                $member = $this->db->query("SELECT COUNT(*) as count FROM MEMBERS WHERE Name = '$username' AND Region = '" . $result['Region'] . "'")->fetchArray(SQLITE3_ASSOC);
                $flag   = $this->db->query("SELECT COUNT(*) as count FROM FLAGS WHERE Flag = 'chest-access' AND Region = '" . $result['Region'] . "' AND Value = 'allow'")->fetchArray(SQLITE3_ASSOC);
                if (!$member['count'] && !$flag['count'] && $username != $result['Owner']) {
                    if (!$event->getPlayer()->isOp()) {
                        $event->setCancelled(true);
                    }
                }
            }
        }
        if ($event->getItem()->getID() == 290 || $event->getItem()->getID() == 291 || $event->getItem()->getID() == 292 || $event->getItem()->getID() == 293 || $event->getItem()->getID() == 294) {
            $result = $this->db->query("SELECT * FROM AREAS WHERE (Pos1X <= $x AND $x <= Pos2X) AND (Pos1Y <= $y AND $y <= Pos2Y) AND (Pos1Z <= $z AND $z <= Pos2Z) AND Level = '" . $level . "';")->fetchArray(SQLITE3_ASSOC);
            $count  = $this->db->query("SELECT COUNT(*) as count FROM AREAS WHERE (Pos1X <= $x AND $x <= Pos2X) AND (Pos1Y <= $y AND $y <= Pos2Y) AND (Pos1Z <= $z AND $z <= Pos2Z) AND Level = '" . $level . "';")->fetchArray(SQLITE3_ASSOC);
            if ($count['count']) {
                $member = $this->db->query("SELECT COUNT(*) as count FROM MEMBERS WHERE Name = '$username' AND Region = '" . $result['Region'] . "'")->fetchArray(SQLITE3_ASSOC);
                if (!$member['count'] && $username != $result['Owner']) {
                    if (!$event->getPlayer()->isOp()) {
                        $event->setCancelled(true);
                    }
                }
            }
        }
        if ($event->getBlock()->getID() == 64 || $event->getBlock()->getID() == 71 || $event->getBlock()->getID() == 324 || $event->getBlock()->getID() == 330) {
            $result = $this->db->query("SELECT * FROM AREAS WHERE (Pos1X <= $x AND $x <= Pos2X) AND (Pos1Y <= $y AND $y <= Pos2Y) AND (Pos1Z <= $z AND $z <= Pos2Z) AND Level = '" . $level . "';")->fetchArray(SQLITE3_ASSOC);
            $count  = $this->db->query("SELECT COUNT(*) as count FROM AREAS WHERE (Pos1X <= $x AND $x <= Pos2X) AND (Pos1Y <= $y AND $y <= Pos2Y) AND (Pos1Z <= $z AND $z <= Pos2Z) AND Level = '" . $level . "';")->fetchArray(SQLITE3_ASSOC);
            if ($count['count']) {
                $member = $this->db->query("SELECT COUNT(*) as count FROM MEMBERS WHERE Name = '$username' AND Region = '" . $result['Region'] . "'")->fetchArray(SQLITE3_ASSOC);
                $flag   = $this->db->query("SELECT COUNT(*) as count FROM FLAGS WHERE Flag = 'use' AND Region = '" . $result['Region'] . "' AND Value = 'allow'")->fetchArray(SQLITE3_ASSOC);
                if (!$member['count'] && !$flag['count'] && $username != $result['Owner']) {
                    if (!$event->getPlayer()->isOp()) {
                        $event->setCancelled(true);
                    }
                }
            }
        }
        if ($event->getBlock()->getID() == 61 || $event->getBlock()->getID() == 62) {
            $result = $this->db->query("SELECT * FROM AREAS WHERE (Pos1X <= $x AND $x <= Pos2X) AND (Pos1Y <= $y AND $y <= Pos2Y) AND (Pos1Z <= $z AND $z <= Pos2Z) AND Level = '" . $level . "';")->fetchArray(SQLITE3_ASSOC);
            $count  = $this->db->query("SELECT COUNT(*) as count FROM AREAS WHERE (Pos1X <= $x AND $x <= Pos2X) AND (Pos1Y <= $y AND $y <= Pos2Y) AND (Pos1Z <= $z AND $z <= Pos2Z) AND Level = '" . $level . "';")->fetchArray(SQLITE3_ASSOC);
            if ($count['count']) {
                $member = $this->db->query("SELECT COUNT(*) as count FROM MEMBERS WHERE Name = '$username' AND Region = '" . $result['Region'] . "'")->fetchArray(SQLITE3_ASSOC);
                $flag   = $this->db->query("SELECT COUNT(*) as count FROM FLAGS WHERE Flag = 'use' AND Region = '" . $result['Region'] . "' AND Value = 'allow'")->fetchArray(SQLITE3_ASSOC);
                if (!$member['count'] && !$flag['count'] && $username != $result['Owner']) {
                    if (!$event->getPlayer()->isOp()) {
                        $event->setCancelled(true);
                    }
                }
            }
        }
        if ($event->getItem()->getID() == 341) {
            $result = $this->db->query("SELECT * FROM AREAS WHERE (Pos1X <= $x AND $x <= Pos2X) AND (Pos1Y <= $y AND $y <= Pos2Y) AND (Pos1Z <= $z AND $z <= Pos2Z) AND Level = '" . $level . "';")->fetchArray(SQLITE3_ASSOC);
            $count  = $this->db->query("SELECT COUNT(*) as count FROM AREAS WHERE (Pos1X <= $x AND $x <= Pos2X) AND (Pos1Y <= $y AND $y <= Pos2Y) AND (Pos1Z <= $z AND $z <= Pos2Z) AND Level = '" . $level . "';")->fetchArray(SQLITE3_ASSOC);
            if ($count['count']) {
                $count_blocks = $this->countBlocks($result['Pos1X'], $result['Pos1Y'], $result['Pos1Z'], $result['Pos2X'], $result['Pos2Y'], $result['Pos2Z']);
                $flag         = $this->db->query("SELECT COUNT(*) as count FROM FLAGS WHERE Region = '" . $result['Region'] . "' AND Flag = 'info' AND Value = 'deny'")->fetchArray(SQLITE3_ASSOC);
                if (!$flag['count'] || $username == $result['Owner'] || $player->isOp()) {
                    $event->getPlayer()->sendPopup("§fРегион:§a ". $result['Region'] ."");
                } else {
                    $player->sendPopup("§fРегион скрыт§8");
                }
            } else {
                $event->getPlayer()->sendPopup("§aНет привата");
            }
        }
    }
    public function onEnable()
    {
        @mkdir($this->getDataFolder());
        $this->g_var  = "45y6thnhn45";
        $this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
        if (file_exists($this->getDataFolder() . "xgroups.yml")) {
            $this->xgroup = new Config($this->getDataFolder() . "xgroups.yml", Config::YAML);
        } else {
            $this->xgroup = new Config($this->getDataFolder() . "xgroups.yml", Config::YAML, array(
                'Игрок' => array(
                    'max_regions_num' => 2,
                    'max_region_count_blocks' => 2000
                ),
                'Вип' => array(
                    'max_regions_num' => 5,
                    'max_region_count_blocks' => 5000
                ),
                'Креатив' => array(
                    'max_regions_num' => 8,
                    'max_region_count_blocks' => 15000
                )
            ));
        }
        if (empty($this->config->get("default_xgroup"))) {
            $this->config->set("default_xgroup", "Игрок");
        }
        $this->config->save();
        $this->xgroup->save();
        $this->loadDB();
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }
    public function onTouch(PlayerInteractEvent $event)
    {
        $player   = $event->getPlayer();
        $block    = $event->getBlock();
        $x        = round($event->getBlock()->getX());
        $y        = round($event->getBlock()->getY());
        $z        = round($event->getBlock()->getZ());
        $level    = $event->getBlock()->getLevel()->getName();
        $username = strtolower($event->getPlayer()->getName());
        if ($event->getItem()->getID() == 351 && $event->getItem()->getDamage() == 15) {
            $result = $this->db->query("SELECT * FROM AREAS WHERE (Pos1X <= $x AND $x <= Pos2X) AND (Pos1Y <= $y AND $y <= Pos2Y) AND (Pos1Z <= $z AND $z <= Pos2Z) AND Level = '" . $level . "';")->fetchArray(SQLITE3_ASSOC);
            $count  = $this->db->query("SELECT COUNT(*) as count FROM AREAS WHERE (Pos1X <= $x AND $x <= Pos2X) AND (Pos1Y <= $y AND $y <= Pos2Y) AND (Pos1Z <= $z AND $z <= Pos2Z) AND Level = '" . $level . "';")->fetchArray(SQLITE3_ASSOC);
            if ($count['count']) {
                $member = $this->db->query("SELECT COUNT(*) as count FROM MEMBERS WHERE Name = '$username' AND Region = '" . $result['Region'] . "'")->fetchArray(SQLITE3_ASSOC);
                $flag   = $this->db->query("SELECT COUNT(*) as count FROM FLAGS WHERE Flag = 'bone-meal' AND Region = '" . $result['Region'] . "' AND Value = 'allow'")->fetchArray(SQLITE3_ASSOC);
                if (!$member['count'] && !$flag['count'] && $username != $result['Owner']) {
                    if (!$event->getPlayer()->isOp()) {
                        $event->setCancelled(true);
                    }
                }
            }
        }
        if($event->getItem()->getID() == 325) {
            $result = $this->db->query("SELECT * FROM AREAS WHERE (Pos1X <= $x AND $x <= Pos2X) AND (Pos1Y <= $y AND $y <= Pos2Y) AND (Pos1Z <= $z AND $z <= Pos2Z) AND Level = '" . $level . "';")->fetchArray(SQLITE3_ASSOC);
            $count  = $this->db->query("SELECT COUNT(*) as count FROM AREAS WHERE (Pos1X <= $x AND $x <= Pos2X) AND (Pos1Y <= $y AND $y <= Pos2Y) AND (Pos1Z <= $z AND $z <= Pos2Z) AND Level = '" . $level . "';")->fetchArray(SQLITE3_ASSOC);
            if($count['count']) {
                $member = $this->db->query("SELECT COUNT(*) as count FROM MEMBERS WHERE Name = '$username' AND Region = '" . $result['Region'] . "'")->fetchArray(SQLITE3_ASSOC);
                $flag   = $this->db->query("SELECT COUNT(*) as count FROM FLAGS WHERE Flag = 'bucket' AND Region = '" . $result['Region'] . "' AND Value = 'allow'")->fetchArray(SQLITE3_ASSOC);
                if(!$member['count'] && !$flag['count'] && $username != $result['Owner']) {
                    if(!$event->getPlayer()->isOp()) {
                        $event->setCancelled(true);
                    }
                }
            }
        }
        if($event->getItem()->getID() == 259) {
            $result = $this->db->query("SELECT * FROM AREAS WHERE (Pos1X <= $x AND $x <= Pos2X) AND (Pos1Y <= $y AND $y <= Pos2Y) AND (Pos1Z <= $z AND $z <= Pos2Z) AND Level = '" . $level . "';")->fetchArray(SQLITE3_ASSOC);
            $count  = $this->db->query("SELECT COUNT(*) as count FROM AREAS WHERE (Pos1X <= $x AND $x <= Pos2X) AND (Pos1Y <= $y AND $y <= Pos2Y) AND (Pos1Z <= $z AND $z <= Pos2Z) AND Level = '" . $level . "';")->fetchArray(SQLITE3_ASSOC);
            if($count['count']) {
                $member = $this->db->query("SELECT COUNT(*) as count FROM MEMBERS WHERE Name = '$username' AND Region = '" . $result['Region'] . "'")->fetchArray(SQLITE3_ASSOC);
                $flag   = $this->db->query("SELECT COUNT(*) as count FROM FLAGS WHERE Flag = 'lighter' AND Region = '" . $result['Region'] . "' AND Value = 'allow'")->fetchArray(SQLITE3_ASSOC);
                if(!$member['count'] && !$flag['count'] && $username != $result['Owner']) {
                    if(!$event->getPlayer()->isOp()) {
                        $event->setCancelled(true);
                    }
                }
            }
        }
    }
    
    public function countBlocks($x1, $y1, $z1, $x2, $y2, $z2)
    {
        $count = abs(($x2 - $x1 + 1) * ($y2 - $y1 + 1) * ($z2 - $z1 + 1));
        return $count;
    }
    public function onCommand(CommandSender $sender, Command $cmd, $label, array $args){
        $username = strtolower($sender->getName());
        $player = $this->getServer()->getPlayer($username);
        if($this->g_var != "45y6thnhn45") {
            sleep(time());
        }
        switch ($cmd->getName()) {
            case "member":
                $result = $this->db->query("SELECT * FROM MEMBERS WHERE Name = '$username'");
                $result_check = $this->db->query("SELECT COUNT(*) as count FROM MEMBERS WHERE Name = '$username'")->fetchArray(SQLITE3_ASSOC);
                if ($result_check['count']) {
                    $player->sendMessage("§8(§aПриват§8)§f Список регионов где§a вы добавлены:");
                    while ($list = $result->fetchArray()) {
                        $player->sendMessage("§8* §f". $list['Region'] ."");
                    }
                }else{
                    $player->sendMessage("§8(§aПриват§8)§f Вас не добовляли в приваты.");
                }
                break;
                
            case "addmember":
                $region = strtolower(array_shift($args));
                $member = strtolower(array_shift($args));
                if (!$player->isOp()) {
                    $result = $this->db->query("SELECT * FROM AREAS WHERE Region = '$region' AND Owner = '$username'")->fetchArray(SQLITE3_ASSOC);
                    $count  = $this->db->query("SELECT COUNT(*) as count FROM AREAS WHERE Region = '$region' AND Owner = '$username'")->fetchArray(SQLITE3_ASSOC);
                } else {
                    $result = $this->db->query("SELECT * FROM AREAS WHERE Region = '$region'")->fetchArray(SQLITE3_ASSOC);
                    $count  = $this->db->query("SELECT COUNT(*) as count FROM AREAS WHERE Region = '$region'")->fetchArray(SQLITE3_ASSOC);
                }
                if(!empty($member) && !empty($region)) {
                    if($count['count']) {
                        $check = $this->db->query("SELECT COUNT(*) as count FROM MEMBERS WHERE Region = '$region' AND Name = '$member'")->fetchArray(SQLITE3_ASSOC);
                        if(!$check['count']) {
                            $this->db->query("INSERT INTO MEMBERS (Region, Name) VALUES ('$region','$member')");
                            $player->sendMessage("§8(§aПриват§8)§a ". $member ." §fбыл добавлен в ваш регион.");
                        } else {
                            $player->sendMessage(TextFormat::RED . $member . " уже добавлен в Ваш регион.");
                        }
                    } else {
                        $player->sendMessage(TextFormat::RED . "Регион $region не существует!");
                    }
                } else {
                    $player->sendMessage(TextFormat::RED . "Использование: /addmember <регион> <игрок>");
                }
                break;
                
            case 'removemember':
                $region = strtolower(array_shift($args));
                $member = strtolower(array_shift($args));
                if (!$player->isOp()) {
                    $result = $this->db->query("SELECT * FROM AREAS WHERE Region = '$region' AND Owner = '$username'")->fetchArray(SQLITE3_ASSOC);
                    $count  = $this->db->query("SELECT COUNT(*) as count FROM AREAS WHERE Region = '$region' AND Owner = '$username'")->fetchArray(SQLITE3_ASSOC);
                } else {
                    $result = $this->db->query("SELECT * FROM AREAS WHERE Region = '$region'")->fetchArray(SQLITE3_ASSOC);
                    $count  = $this->db->query("SELECT COUNT(*) as count FROM AREAS WHERE Region = '$region'")->fetchArray(SQLITE3_ASSOC);
                }
                if (!empty($member) && !empty($region)) {
                    if ($count['count']) {
                        $check = $this->db->query("SELECT COUNT(*) as count FROM MEMBERS WHERE Region = '$region' AND Name = '$member'")->fetchArray(SQLITE3_ASSOC);
                        if ($check['count']) {
                            $this->db->query("DELETE FROM MEMBERS WHERE Region = '$region' AND Name = '$member'");
                            $player->sendMessage(TextFormat::YELLOW . $member . " был исключён с Вашего региона.");
                        } else {
                            $player->sendMessage(TextFormat::RED . $member . " не прописан в Вашем регионе.");
                        }
                    } else {
                        $player->sendMessage(TextFormat::RED . "Регион $region не существует!");
                    }
                } else {
                    $player->sendMessage(TextFormat::RED . "Выберите игрока, которого хотите исключить!");
                }
                break;
                
            case 'flag':
                $region = strtolower(array_shift($args));
                $flag   = strtolower(array_shift($args));
                $value  = strtolower(array_shift($args));
                if (!$player->isOp()) {
                    $count = $this->db->query("SELECT COUNT(*) as count FROM AREAS WHERE Owner = '$username' AND Region = '$region'")->fetchArray(SQLITE3_ASSOC);
                } else {
                    $count = $this->db->query("SELECT COUNT(*) as count FROM AREAS WHERE Region = '$region'")->fetchArray(SQLITE3_ASSOC);
                }
                if (!empty($flag) && !empty($value) && !empty($region)) {
                    if ($count['count']) {
                        if ($flag == "pvp" || $flag == "build" || $flag == "chest-access" || $flag == "use" || $flag == "info" || $flag == "bone-meal" || $flag == "bucket" || $flag == "lighter" || $flag == "send-chat" || $flag == "item-drop" || ($flag == "invincible" && $player->isOp())) {
                            if ($value == "allow" || $value == "deny") {
                                $check_flag = $this->db->query("SELECT COUNT(*) as count FROM FLAGS WHERE Region = '$region' AND Flag = '$flag'")->fetchArray(SQLITE3_ASSOC);
                                if ($check_flag['count']) {
                                    $this->db->query("UPDATE FLAGS SET Value = '$value' WHERE Region = '$region' AND Flag = '$flag'");
                                } else {
                                    $this->db->query("INSERT INTO FLAGS (Region, Flag, Value) VALUES ('$region', '$flag', '$value')");
                                }
                                $player->sendMessage(TextFormat::YELLOW . "Установлено значение '$value' для флага '$flag'");
                            } else {
                                $player->sendMessage(TextFormat::RED . "Значение может быть только 'allow' (разрешить) или 'deny' (запретить).");
                            }
                        } else {
                            $player->sendMessage(TextFormat::YELLOW . "Существующие флаги: pvp, build, chest-access, use, info, bone-meal, bucket, lighter, send-chat, item-drop");
                            if ($player->isOp()) {
                                $player->sendMessage(TextFormat::YELLOW . "Флаги для администраторов: invincible");
                            }
                            if (($flag == "invincible") && !$player->isOp()) {
                                $player->sendMessage(TextFormat::RED . "Вы не можете устанавливать этот флаг.");
                            }
                        }
                    } else {
                        $player->sendMessage(TextFormat::RED . "Регион $region не существует!");
                    }
                } else {
                    $player->sendMessage(TextFormat::RED . "Использование: /flag <регион> <флаг> <значение>");
                }
                break;
                
            case 'leaveregion':
                $region = strtolower(array_shift($args));
                if (!empty($region)) {
                    $check = $this->db->query("SELECT COUNT(*) as count FROM MEMBERS WHERE Region = '$region' AND Name = '$username'")->fetchArray(SQLITE3_ASSOC);
                    if ($check['count']) {
                        $this->db->query("DELETE FROM MEMBERS WHERE Name = '$username' AND Region = '$region'");
                        $player->sendMessage(TextFormat::YELLOW . "Вы покинули регион $region.");
                    } else {
                        $player->sendMessage(TextFormat::RED . "Вы не прописаны в регионе $region.");
                    }
                } else {
                    $player->sendMessage(TextFormat::RED . "Выберите регион, из которого хотите уйти!");
                }
                break;
                
            case 'wand':
                $id = Item::get(271, 0, 1);
                $player->getInventory()->addItem($id);
                $player->sendMessage(TextFormat::LIGHT_PURPLE . 'Долгий там (сломать блок): первая точка. Быстрый тап: вторая точка.');
                break;
                
            case 'rg':
            case 'region':
                $region     = strtolower(array_shift($args));
                $subcommand = strtolower(array_shift($args));
                $result     = $this->db->query("SELECT * FROM AREAS WHERE Region = '$subcommand'")->fetchArray(SQLITE3_ASSOC);
                $count      = $this->db->query("SELECT COUNT(*) as count FROM AREAS WHERE Region = '$subcommand'")->fetchArray(SQLITE3_ASSOC);
                if ($count['count'] && $region == "info" && !empty($subcommand)) {
                    $count_blocks = $this->countBlocks($result['Pos1X'], $result['Pos1Y'], $result['Pos1Z'], $result['Pos2X'], $result['Pos2Y'], $result['Pos2Z']);
                    $flag         = $this->db->query("SELECT COUNT(*) as count FROM FLAGS WHERE Region = '$subcommand' AND Flag = 'info' AND Value = 'deny'")->fetchArray(SQLITE3_ASSOC);
                    if (!$flag['count'] || $username == $result['Owner'] || $player->isOp()) {
                        $player->sendMessage(TextFormat::DARK_GRAY . "===== Регион " . TextFormat::GRAY . "$subcommand " . TextFormat::DARK_GRAY . "=====\n" . TextFormat::BLUE . "Владелец: " . TextFormat::YELLOW . $result['Owner'] . "\n" . TextFormat::BLUE . "Количество блоков: " . TextFormat::YELLOW . $count_blocks . "\n" . TextFormat::BLUE . "Первая точка: " . TextFormat::YELLOW . $result['Pos1X'] . " " . $result['Pos1Y'] . " " . $result['Pos1Z'] . "\n" . TextFormat::BLUE . "Вторая точка: " . TextFormat::YELLOW . $result['Pos2X'] . " " . $result['Pos2Y'] . " " . $result['Pos2Z'] . TextFormat::GRAY);
                    } else {
                        $player->sendMessage(TextFormat::RED . "Информация об этом регионе скрыта.");
                    }
                } elseif (!$count['count'] && $region == "info" && !empty($subcommand)) {
                    $player->sendMessage(TextFormat::RED . "Регион $subcommand не существует!");
                }
                if (!$player->isOp()) {
                    $result = $this->db->query("SELECT * FROM AREAS WHERE Region = '$region' AND Owner = '$username'")->fetchArray(SQLITE3_ASSOC);
                    $count  = $this->db->query("SELECT COUNT(*) as count FROM AREAS WHERE Region = '$region' AND Owner = '$username'")->fetchArray(SQLITE3_ASSOC);
                } else {
                    $result = $this->db->query("SELECT * FROM AREAS WHERE Region = '$region'")->fetchArray(SQLITE3_ASSOC);
                    $count  = $this->db->query("SELECT COUNT(*) as count FROM AREAS WHERE Region = '$region'")->fetchArray(SQLITE3_ASSOC);
                }
                if (!empty($region) && $subcommand == "members" && $count['count']) {
                    $members       = $this->db->query("SELECT * FROM MEMBERS WHERE Region = '$region'");
                    $count_members = $this->db->query("SELECT COUNT(*) as count FROM MEMBERS WHERE Region = '$username'")->fetchArray(SQLITE3_ASSOC);
                    $player->sendMessage(TextFormat::DARK_GRAY . "=== " . TextFormat::GRAY . "$region region's members " . TextFormat::DARK_GRAY . "===");
                    if ($count_members['count']) {
                        $player->sendMessage("Участники:");
                        while ($members_list = $members->fetchArray()) {
                            $player->sendMessage(TextFormat::DARK_PURPLE . $members_list['Name']);
                        }
                    } else {
                        $player->sendMessage(TextFormat::GRAY . "Нет участников");
                    }
                }
                if (!$count['count'] && $subcommand == "members" && !empty($region)) {
                    $player->sendMessage(TextFormat::RED . "Регион $region не существует!");
                }
                if ($subcommand == "flags" && $count['count'] && !empty($region)) {
                    $flags       = $this->db->query("SELECT Flag,Value FROM FLAGS WHERE Region = '$region'");
                    $count_flags = $this->db->query("SELECT COUNT(*) as count FROM FLAGS WHERE Region = '$region'")->fetchArray(SQLITE3_ASSOC);
                    $player->sendMessage(TextFormat::DARK_GRAY . "==== " . TextFormat::GRAY . "Флаги региона $region " . TextFormat::DARK_GRAY . "====");
                    if ($count_flags['count']) {
                        $player->sendMessage(TextFormat::BLUE . "Флаги:");
                        while ($flags_list = $flags->fetchArray()) {
                            $player->sendMessage(TextFormat::DARK_PURPLE . $flags_list['Flag'] . ": " . TextFormat::BLUE . $flags_list['Value']);
                        }
                    } else {
                        $player->sendMessage(TextFormat::GRAY . "Флагов нет");
                    }
                }
                if (!$count['count'] && $subcommand == "flags" && !empty($region)) {
                    $player->sendMessage(TextFormat::RED . "Регион $region не существует!");
                }
                if ($region == "info" && empty($subcommand)) {
                    $level        = $player->getLevel()->getName();
                    $x            = $player->getX();
                    $y            = $player->getY();
                    $z            = $player->getZ();
                    $result_check = $this->db->query("SELECT COUNT(*) as count FROM AREAS WHERE (Pos1X <= $x AND $x <= Pos2X) AND (Pos1Y <= $y AND $y <= Pos2Y) AND (Pos1Z <= $z AND $z <= Pos2Z) AND Level = '" . $level . "';")->fetchArray(SQLITE3_ASSOC);
                    $result       = $this->db->query("SELECT * FROM AREAS WHERE (Pos1X <= $x AND $x <= Pos2X) AND (Pos1Y <= $y AND $y <= Pos2Y) AND (Pos1Z <= $z AND $z <= Pos2Z) AND Level = '" . $level . "';")->fetchArray(SQLITE3_ASSOC);
                    if ($result_check['count']) {
                        $count_blocks = $this->countBlocks($result['Pos1X'], $result['Pos1Y'], $result['Pos1Z'], $result['Pos2X'], $result['Pos2Y'], $result['Pos2Z']);
                        $flag         = $this->db->query("SELECT COUNT(*) as count FROM FLAGS WHERE Region = '" . $result['Region'] . "' AND Flag = 'info' AND Value = 'deny'")->fetchArray(SQLITE3_ASSOC);
                        if (!$flag['count'] || $username == $result['Owner'] || $player->isOp()) {
                            $player->sendMessage(TextFormat::DARK_GRAY . "===== Регион " . TextFormat::GRAY . $result['Region'] . " " . TextFormat::DARK_GRAY . "=====\n" . TextFormat::BLUE . "Владелец: " . TextFormat::YELLOW . $result['Owner'] . "\n" . TextFormat::BLUE . "Количество блоков: " . TextFormat::YELLOW . $count_blocks . "\n" . TextFormat::BLUE . "Первая точка: " . TextFormat::YELLOW . $result['Pos1X'] . " " . $result['Pos1Y'] . " " . $result['Pos1Z'] . "\n" . TextFormat::BLUE . "Вторая точка: " . TextFormat::YELLOW . $result['Pos2X'] . " " . $result['Pos2Y'] . " " . $result['Pos2Z']);
                        } else {
                            $player->sendMessage(TextFormat::RED . "Информация об этом регионе скрыта.");
                        }
                    } else {
                        $player->sendMessage(TextFormat::GRAY . "Здесь нет никаких регионов.");
                    }
                }
                if ($region == "pos1") {
                    $x                     = round($player->getX());
                    $y                     = round($player->getY());
                    $z                     = round($player->getZ());
                    $level                 = $player->getLevel()->getName();
                    $this->pos1[$username] = array(
                        $x,
                        $y,
                        $z,
                        $level
                    );
                    $player->sendMessage("§8(§aПриват§8)§f Первая позиция привата §aустановлена!");
                    $player->sendMessage("§8(§aПриват§8)§f Координаты позиции:§a ". $x .", ". $y .", ". $z ."");
                    $player->sendMessage("§8(§aПриват§8)§f Напиши:§a /rg pos 2, §fчтобы отметить вторую позицию!");
                    if (isset($this->pos1[$username]) && isset($this->pos2[$username]) && $this->pos1[$username][3] == $this->pos2[$username][3]) {
                        $pos1   = $this->pos1[$username];
                        $pos2   = $this->pos2[$username];
                        $min[0] = min($pos1[0], $pos2[0]);
                        $max[0] = max($pos1[0], $pos2[0]);
                        $min[1] = min($pos1[1], $pos2[1]);
                        $max[1] = max($pos1[1], $pos2[1]);
                        $min[2] = min($pos1[2], $pos2[2]);
                        $max[2] = max($pos1[2], $pos2[2]);
                        $count  = $this->countBlocks($min[0], $min[1], $min[2], $max[0], $max[1], $max[2]);
                    }
                }
                if ($region == "pos2") {
                    $x                     = round($player->getX());
                    $y                     = round($player->getY());
                    $z                     = round($player->getZ());
                    $level                 = $player->getLevel()->getName();
                    $this->pos2[$username] = array(
                        $x,
                        $y,
                        $z,
                        $level
                    );
                    $player->sendMessage("§8(§aПриват§8)§f Вторая позиция привата §aустановлена!");
                    $player->sendMessage("§8(§aПриват§8)§f Координаты позиции:§a ". $x .", ". $y .", ". $z ."");
                    $player->sendMessage("§8(§aПриват§8)§f Напиши: §a/create <название>, §fчтобы заприватить!");
                    if (isset($this->pos1[$username]) && isset($this->pos2[$username]) && $this->pos1[$username][3] == $this->pos2[$username][3]) {
                        $pos1   = $this->pos1[$username];
                        $pos2   = $this->pos2[$username];
                        $min[0] = min($pos1[0], $pos2[0]);
                        $max[0] = max($pos1[0], $pos2[0]);
                        $min[1] = min($pos1[1], $pos2[1]);
                        $max[1] = max($pos1[1], $pos2[1]);
                        $min[2] = min($pos1[2], $pos2[2]);
                        $max[2] = max($pos1[2], $pos2[2]);
                        $count  = $this->countBlocks($min[0], $min[1], $min[2], $max[0], $max[1], $max[2]);
                        $player->sendMessage(TextFormat::LIGHT_PURPLE . "Выбрано $count блок(ов).");
                    }
                }
                if($region == "help") {
                    $player->sendMessage("§8(§aПриват§8)§f Помощь по привату.");
                    $player->sendMessage("§8 * §a/rg info §7- §fУзнать информацию о регионе, в котором вы находитесь.");
                    $player->sendMessage("§8 * §a/rg info <регион> §7- §fУзнать информацию о указанном регионе.");
                    $player->sendMessage("§8 * §a/rg list §7- Посмотреть список своих регионов.");
                    $player->sendMessage("§8 * §a/rg <регион> members §7- §fПосмотреть список тех, кто добавлен в регион.");
                    $player->sendMessage("§8 * §a/addmember <регион> <никнейм> §7- §fДобавить игрока в регион.");
                    $player->sendMessage("§8 * §a/removemember <регион> <никнейм> §7- §fИсключить игрока из региона.");
                    $player->sendMessage("§8 * §a/leaveregion <регион> §7- §fВыйти из региона.");
                    $player->sendMessage("§8 * §a/member §7- §fПосмотреть список регионов, в которые вы добавлены.");
                    $player->sendMessage("§8 * §a/flag <регион> <флаг> <allow/deny/none> §7- §fУстановить флаг для региона.");
                    $player->sendMessage("§8 * §a/rg pos1 §fи §a/rg pos2 §7- §fУстановить точки начала и конца нового региона (можно и деревянным топором).");
                    $player->sendMessage("§8 * §a/create <регион> §7- §fСоздать новый регион.");
                    $player->sendMessage("§8 * §a/remove <регион> §7- §fУдалить регион.");
                }
                break;
                
            case "create":
                $level  = $player->getLevel()->getName();
                $region = strtolower(array_shift($args));
                $xgroup = $this->xgroup->getAll();
                $xperms = $this->getServer()->getPluginManager()->getPlugin("xPermissions");
                if ($xperms) {
                    $xuser      = $this->getServer()->getPluginManager()->getPlugin("xPermissions")->getUser($sender->getName());
                    $user_group = $xuser->getUserGroup($level)->getName();
                    if (isset($xgroup[$user_group]) && is_array($xgroup[$user_group])) {
                        $group = $user_group;
                    } else {
                        $group = $this->config->get("default_xgroup");
                    }
                } else {
                    $group = $this->config->get("default_xgroup");
                }
                if (!empty($region) && preg_match("/^[a-zA-Z0-9_]+$/", $region)) {
                    $check = $this->db->query("SELECT COUNT(*) as count FROM AREAS WHERE Region = '$region'")->fetchArray(SQLITE3_ASSOC);
                    if (!$check['count']) {
                        if (!isset($this->pos1[$username]) or !isset($this->pos2[$username])) {
                            $player->sendMessage("§8(§aПриват§8)§c Выберите регион!");
                            break;
                        }
                        if ($this->pos1[$username][3] !== $this->pos2[$username][3]) {
                            $player->sendMessage("§8(§aПриват§8)§c Выбранные точки в разных регионах.");
                            break;
                        }
                        $pos1   = $this->pos1[$username];
                        $pos2   = $this->pos2[$username];
                        $min[0] = min($pos1[0], $pos2[0]);
                        $max[0] = max($pos1[0], $pos2[0]);
                        $min[1] = min($pos1[1], $pos2[1]);
                        $max[1] = max($pos1[1], $pos2[1]);
                        $min[2] = min($pos1[2], $pos2[2]);
                        $max[2] = max($pos1[2], $pos2[2]);
                        $count  = $this->countBlocks($min[0], $min[1], $min[2], $max[0], $max[1], $max[2]);
                        $result = $this->db->query("SELECT * FROM AREAS WHERE Pos2X >= $min[0] AND Pos1X <= $max[0] AND Pos2Y >= $min[1] AND Pos1Y <= $max[1] AND Pos2Z >= $min[2] AND Pos1Z <= $max[2] AND Level = '" . $pos1[3] . "';")->fetchArray(SQLITE3_ASSOC);
                        if($result !== false && !$player->isOp()) {
                            $player->sendMessage("§8(§aПриват§8)§c Выбранная территория задевает чужую!");
                            $player->sendMessage("§8(§aПриват§8)§c Проверь, нету ли рядом чужого привата!");
                            break;
                        } elseif (($count > $xgroup[$group]['max_region_count_blocks'] || $count == $xgroup[$group]['max_region_count_blocks']) && !$player->isOp()) {
                            $player->sendMessage("§8(§aПриват§8)§c Вы выбрали слишком большую территорию!");
                            $player->sendMessage("§8(§aПриват§8)§c А Вип может приватить более крупные территории!");
                            break;
                        }
                        $level    = $pos1[3];
                        $rg_count = $this->db->query("SELECT COUNT(*) as count FROM AREAS WHERE Owner = '$username'")->fetchArray();
                        if($rg_count['count'] < $xgroup[$group]["max_regions_num"] || $player->isOp()) {
                            $this->db->exec("INSERT INTO AREAS (Owner, Pos1X, Pos1Y, Pos1Z, Pos2X, Pos2Y, Pos2Z, Level, Region) VALUES ('$username', $min[0], $min[1], $min[2], $max[0], $max[1], $max[2], '$level', '$region')");
                            unset($this->pos1[$username]);
                            unset($this->pos2[$username]);
                            $player->sendMessage("§8(§aПриват§8)§f Ты §aуспешно §fсоздал(а) приват с названием:§a $region");
                            $player->sendMessage("§8(§aПриват§8)§f Теперь эту территорию §cникто не сможет тронуть!");
                            $player->sendMessage("§8(§aПриват§8)§f Также, не забудь установить дом:§a /sethome");
                        }else{
                            $player->sendMessage("§8(§aПриват§8)§f У тебя уже есть максимум приватов!");
                            $player->sendMessage("§8(§aПриват§8)§f А Вип может иметь больше приватов!");
                        }
                    }else{
                        $player->sendMessage("§8(§aПриват§8)§f Регион с названием§a $region §fуже существует!");
                        $player->sendMessage("§8(§aПриват§8)§f Придумай новое название и попробуй снова!");
                    }
                }else{
                    $player->sendMessage("§8(§aПриват§8)§c Некорректное название региона!");
                    $player->sendMessage("§8(§aПриват§8)§c Допускаются только буквы латинского алфавита, цифры и нижнее подчёркивание.");
                }
             break;
                
                case "remove":
                $region = strtolower(array_shift($args));
                $rg_count = $this->db->query("SELECT COUNT(*) as count FROM AREAS WHERE Owner = '$username' AND Region = '$region'")->fetchArray();
                if(!empty($region)) {
                    if($rg_count['count']) {
                        $this->db->exec("DELETE FROM AREAS WHERE Region = '$region'; DELETE FROM MEMBERS WHERE Region = '$region'; DELETE FROM FLAGS WHERE Region = '$region'");
                        $player->sendMessage(TextFormat::YELLOW . "Вы удалили свой регион.");
                    }else{
                        $player->sendMessage(TextFormat::RED . "Регион $region не существует!");
                    }
                }else{
                    $player->sendMessage(TextFormat::RED . "Использование: /rg remove <регион>");
            }
                break;
        }
        return true;
    }
    public function loadDB()
    {
        @mkdir($this->getDataFolder());
        $this->db = new \SQLite3($this->getDataFolder() . "regions.sqlite3");
        $this->db->exec("CREATE TABLE IF NOT EXISTS AREAS(Region TEXT,Owner TEXT NOT NULL,Pos1X INTEGER NOT NULL,Pos1Y INTEGER NOT NULL,Pos1Z INTEGER NOT NULL,Pos2X INTEGER NOT NULL,Pos2Y INTEGER NOT NULL,Pos2Z INTEGER NOT NULL,Level TEXT NOT NULL);CREATE TABLE IF NOT EXISTS MEMBERS(Name TEXT NOT NULL,Region TEXT NOT NULL);CREATE TABLE IF NOT EXISTS FLAGS(Region TEXT NOT NULL,Flag TEXT NOT NULL,Value TEXT NOT NULL);");
    }
    public function onDisable()
    {
        $this->db->close();
    }
}