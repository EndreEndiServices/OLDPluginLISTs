<?php
namespace CRCore\commands;
use pocketmine\command\CommandSender;
use CRCore\core\Loader;
use CRCore\core\utils\RandomUtils;
use CRCore\core\utils\ServerUtils;
use pocketmine\plugin\Plugin;
use CRCore\commands\BaseCommand;
class OpHelpCommand extends BaseCommand{

        /** @var PrestigeSocietyCore */
        private $c;

        /**
         *
         * OpHelpCommand constructor.
         *
         * @param PrestigeSocietyCore $c
         *
         */
            public function __construct(Loader $plugin){
        parent::__construct($plugin, "ophelp", "Ask for op help!", "§8-=§bChrystal§fPE§r§8=- §b/ophelp <explain... >", ["ms, latency"]);
    }

        /**
         *
         * @param CommandSender $sender
         * @param string        $commandLabel
         * @param string[]      $args
         *
         * @return mixed
         *
         */
        public function execute(CommandSender $sender, string $commandLabel, array $args): bool{
                $reason = implode(" ", $args);
                if(empty($args)){
                        $sender->sendMessage($this->getUsage());
                        return false;
                }
                $msg = str_replace(["@explanation", "@player"], [$reason, $sender->getName()], Loader::getInstance()->getConfig()->getAll()["op_help_format"]);
                ServerUtils::bcToOps($msg);
                $sender->sendMessage($msg);
                return true;
        }

        /**
         * @return PrestigeSocietyCore
         */
        public function getPlugin(): Plugin{
                return $this->c;
        }
}