<?php

declare(strict_types=1);

namespace Zedstar16\DevStats;

use JackMD\ScoreFactory\ScoreFactory;
use pocketmine\entity\Living;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Utils;

class Main extends PluginBase implements Listener
{

    public static $indev = [];
    public static $lastUpload;
    public static $lastDownload;

    public function onEnable(): void
    {
        $this->getScheduler()->scheduleRepeatingTask(new StatsTask(), 5);
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getScheduler()->scheduleRepeatingTask(new ClosureTask(
            function (int $currentTick): void {
                $s = $this->getServer();
                $u = $s->getNetwork()->getUpload();
                $d = $s->getNetwork()->getDownload();
                if ($u !== 0) Main::$lastUpload = round(($u / 1000), 1);
                if ($d !== 0) Main::$lastDownload = round(($d / 1000), 1);
            }
        ), 5);
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {

        switch ($command->getName()) {
            case "dev":
                $data = Main::getStats();
                if (!$sender instanceof Player) {
                    $sender->sendMessage("\n
                    §6Load: §f$data[Load]\n
                    §eTPS: §f$data[TPS]\n
                    §eNetwork Upload: §f$data[NetworkUpload] kb/s\n
                    §aMemory In Use: §f$data[InUseMemory] MB \n
                    §cNetwork Download: §f$data[NetworkDownload] kb/s\n
                    §bTotal Memory: §f$data[TotalMemoryAvailable] MB \n
                    §dLevels Loaded: §f$data[LoadedLevels]\n
                    §9Online: §f$data[OnlinePlayers]\n
                    §cEntities: §f$data[Entites]\n
                    §6Live Entities: §f$data[LiveEntites]"
                    );
                    return false;
                }
                if ($sender->hasPermission("dev")) {
                    $p = $this->getServer()->getPlayer($sender->getName());
                    $pn = $p->getName();
                    if (isset(Main::$indev[$pn])) {
                        ScoreFactory::removeScore($p);
                        unset(Main::$indev[$pn]);
                        $sender->sendMessage("§cDisabled DevStats mode");
                    } else {
                        Main::$indev[$pn] = $pn;
                        $sender->sendMessage("§aEnabled DevStats Mode");
                    }
                } else $sender->sendMessage(TextFormat::RED . "You do not have permission to use this command");

                return true;
            default:
                return false;
        }
    }

    public static function addsc(Player $p)
    {
        ScoreFactory::setScore($p, "§bDev§cStats");
        Main::stats($p);
    }

    public static function stats(Player $p)
    {
        $data = Main::getStats();
        ScoreFactory::setScoreLine($p, 1, "§cPing: §f" . $p->getPing() . " ms");
        ScoreFactory::setScoreLine($p, 2, "§6Load: §f$data[Load]%%");
        ScoreFactory::setScoreLine($p, 3, "§eUpload: §f$data[NetworkUpload] KB/s");
        ScoreFactory::setScoreLine($p, 4, "§aDownload: §f$data[NetworkDownload] KB/s");
        ScoreFactory::setScoreLine($p, 5, "§bTPS: §f$data[TPS]");
        ScoreFactory::setScoreLine($p, 6, "§dMemory In Use: §f$data[InUseMemory] MB ");
        ScoreFactory::setScoreLine($p, 7, "§cTotal Memory: §f$data[TotalMemoryAvailable] MB ");
        ScoreFactory::setScoreLine($p, 8, "§6Levels Loaded: §f$data[LoadedLevels]");
        ScoreFactory::setScoreLine($p, 9, "§eOnline: §f$data[OnlinePlayers]");
        ScoreFactory::setScoreLine($p, 10, "§aEntities: §f$data[Entites]");
        ScoreFactory::setScoreLine($p, 11, "§bLive Entities: §f$data[LiveEntites]");
    }

    public static function getStats(): array
    {
        $s = Server::getInstance();
        $lvls = 0;
        $entities = 0;
        $liveentities = 0;
        $memory = Utils::getMemoryUsage(true);
        foreach (Server::getInstance()->getLevels() as $level) {
            foreach ($level->getEntities() as $ent) {
                $entities++;
                if ($ent instanceof Living) {
                    $liveentities++;
                }
            }
        }
        foreach ($s->getLevels() as $l) {
            $lvls++;
        }
        $u = $s->getNetwork()->getUpload();
        $d = $s->getNetwork()->getDownload();
        if ($u !== 0) {
            $upload = round(($u / 1000), 1);
        } else $upload = Main::$lastUpload;
        if ($d !== 0) {
            $download = round(($d / 1000), 1);
        } else $download = Main::$lastDownload;
        $data = [
            "Load" => $s->getTickUsage(),
            "TPS" => $s->getTicksPerSecond(),
            "NetworkUpload" => $upload,
            "NetworkDownload" => $download,
            "InUseMemory" => (round($memory[0] / 1000000)),
            "TotalMemoryAvailable" => (round($memory[2] / 1000000)),
            "LoadedLevels" => $lvls,
            "OnlinePlayers" => count($s->getOnlinePlayers()),
            "Entites" => $entities,
            "LiveEntites" => $liveentities
        ];
        return $data;
    }
}
