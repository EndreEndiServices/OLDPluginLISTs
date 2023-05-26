<?php
namespace Sergey_Dertan\SClearLagg\Command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat as F;
use Sergey_Dertan\SClearLagg\SClearLaggMainFolder\SClearLaggMain;

/**
 * Class SClearLaggCommandExecutor
 * @package Sergey_Dertan\SClearLagg\Command
 */
class SClearLaggCommandExecutor
{
    /**
     * @param CommandSender $s
     * @param Command $cmd
     * @param array $args
     */
    function __construct(CommandSender $s, Command $cmd, array $args)
    {
        $this->executeCommand($s, $cmd, $args);
    }

    /**
     * @param CommandSender $s
     * @param Command $cmd
     * @param array $args
     * @return bool
     */
    private function executeCommand(CommandSender $s, Command $cmd, array $args)
    {
        $main = SClearLaggMain::getInstance();
        $entitiesManager = $main->getEntityManager();
        switch ($cmd->getName()) {
            case"scl":
                if (!isset($args[0])) {
                    $s->sendMessage(F::RED . "§7SClearLagg V_" . $main->getDescription()->getVersion() . "\n §7- /scl clear - §6удалить предметы с земли\n §7 - /scl killmob - §6удалить мобов");
                    return true;
                }
                if (!in_array(strtolower($args[0]), array("clear", "mobkill"))) {
                    $s->sendMessage(F::RED . "§7Суб команда §6' $args[0] ' §7не найдена\n §7Используйте §6' /scl ' §7для просмотра всех команд");
                    return true;
                }
                switch (array_shift($args)) {
                    case"clear":
                        $s->sendMessage(F::YELLOW . "§7Удалено§6 " . $entitiesManager->removeEntities() . "§7 вещей");
                        return true;
                        break;
                    case"mobkill":
                        $s->sendMessage(F::YELLOW . "§7Удалено§6 " . $entitiesManager->removeMobs() . " §7мобов");
                        return true;
                        break;
                }
                break;
        }
        return true;
    }
}