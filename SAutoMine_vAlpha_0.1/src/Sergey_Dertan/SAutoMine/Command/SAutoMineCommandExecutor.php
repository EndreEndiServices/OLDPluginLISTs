<?php
namespace Sergey_Dertan\SAutoMine\Command;

use pocketmine\utils\TextFormat as F;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use Sergey_Dertan\SAutoMine\SAutoMineMainFolder\SAutoMineMain;
use pocketmine\block\Block;
use pocketmine\Player;

class SAutoMineCommandExecutor
{
 
    function __construct(SAutoMineMain $plugin, CommandSender $s, Command $cmd, array $args)
    {
        $this->plugin = $plugin;
        $this->executeCommand($s, $cmd, $args);
    }

    private function executeCommand(CommandSender $s, Command $cmd, array $args)
    {
        switch ($cmd->getName()) {
            case"sam":
                if(!isset($args[0])) {
                    $s->sendMessage("§b     > §fАвто-Шахта§b <");
                    $s->sendMessage("§8* §b/sam pos1§7 - §fПервая позиция.");
                    $s->sendMessage("§8* §b/sam pos2 §7- §fВторая позиция.");
                    $s->sendMessage("§8* §b/sam save §7- §fСохранить шахту.");
                    $s->sendMessage("§8* §b/sam add §7- §fДобавить блок в шахту.");
                    $s->sendMessage("§8* §b/sam del §7- §fУдалить блок из шахты.");
                    return true;
                }
                switch (array_shift($args)) {
                    case"pos1":
                        if (!$s instanceof Player) {
                            $s->sendMessage(F::RED . "[SAM] Только в игре!(/sam pos1)");
                            return true;
                        }
                        $this->plugin->pos1[$s->getName()] = array(0 => $s->getFloorX(), 1 => $s->getFloorY(), 2 => $s->getFloorZ());
                        $s->sendMessage("§8(§aАвто-Шахта§8)§f Позиция §b1 §fуспешно установлена");
                        return true;
                        break;
                    case"pos2":
                        if (!$s instanceof Player) {
                            $s->sendMessage(F::RED . "[SAM] Только в игре(/sam pos2)");
                            return true;
                        }
                        $this->plugin->pos2[$s->getName()] = array(0 => $s->getFloorX(), 1 => $s->getFloorY(), 2 => $s->getFloorZ());
                        $s->sendMessage("§8(§aАвто-Шахта§8)§f Позиция §b2 §fуспешно установлена");
                        return true;
                        break;
                    case"save":
                        if (!$s instanceof Player) {
                            $s->sendMessage(F::RED . "[SAM] Только в игре (/sam save)");
                            return true;
                        }
                        if (!isset($this->plugin->pos1[$s->getName()]) or !isset($this->plugin->pos2[$s->getName()])) {
                            $s->sendMessage("§8(§aАвто-Шахта§8)§f Для начала установите§c 1 §fпозицию затем §c2 §fпозицию.");
                            return true;
                        }
                        $pos1 = $this->plugin->pos1[$s->getName()];
                        $pos2 = $this->plugin->pos2[$s->getName()];
                        $maxX = max($pos1[0], $pos2[0]);
                        $minX = min($pos1[0], $pos2[0]);
                        $maxY = max($pos1[1], $pos2[1]);
                        $minY = min($pos1[1], $pos2[1]);
                        $maxZ = max($pos1[2], $pos2[2]);
                        $minZ = min($pos1[2], $pos2[2]);
                        $this->plugin->config->set("MinePos", array("max" => array(0 => $maxX, 1 => $maxY, 2 => $maxZ), "min" => array(0 => $minX, 1 => $minY, 2 => $minZ)));
                        $s->sendMessage("§8(§aАвто-Шахта§8)§f Позиция шахты §aуспешно §fустановлена.");
                        return true;
                        break;
                    case"add":
                        if (!isset($args[0])) {
                            $s->sendMessage("§8(§aАвто-Шахта§8)§f Используйте: §b/sam add <ID блока>");
                            return true;
                        }
                        $this->addBlock($s, $args[0]);
                        return true;
                        break;
                    case"del":
                        if (!isset($args[0])) {
                            $s->sendMessage("§8(§aАвто-Шахта§8)§f Используйте: §b/sam del <ID блока>");
                            return true;
                        }
                        $this->removeBlock($s, $args[0]);
                        break;
                }
                break;
        }
        return true;
    }

    private function addBlock(CommandSender $s, $id)
    {
        if (!is_numeric($id)) {
            $s->sendMessage("§8(§aАвто-Шахта§8)§f Не §cкорректный§f ID блока.");
            return true;
        }
        $all = $this->plugin->config->getAll();
        if (in_array($id, $all["MineBlocks"])) {
            $s->sendMessage("§8(§aАвто-Шахта§8)§f Блок§c ". Block::get($id)->getName() ."§f уже есть в шахте.");
            return true;
        }
        $all["MineBlocks"][count($all["MineBlocks"])] = $id;
        $this->plugin->config->setAll($all);
        $s->sendMessage("§8(§aАвто-Шахта§8)§f Блок§a " . Block::get($id)->getName() . "§f успешно добавлен.");
        return true;
    }

    private function removeBlock(CommandSender $s, $id)
    {
        $all = $this->plugin->config->getAll();
        if (!is_numeric($id)) {
            $s->sendMessage("§8(§aАвто-Шахта§8)§f Не §cкорректный§f ID блока.");
            return true;
        }
        if (!in_array($id, $all["MineBlocks"])) {
            $s->sendMessage("§8(§aАвто-Шахта§8)§f Блок§b ". $id ." §fне обнаружен в шахте.");
            return true;
        }
        $i = 0;
        foreach ($all["MineBlocks"] as $n => $d) {
            if ($d == $id) {
                unset($all["MineBlocks"][$i]);
            } else {
                $i++;
            }
        }
        $this->plugin->config->setAll($all);
        $s->sendMessage("§8(§aАвто-Шахта§8)§f Блок§b " . Block::get($id)->getName() . " §fуспешно удален.");
        return true;
    }
}