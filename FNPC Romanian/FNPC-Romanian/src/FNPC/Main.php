<?php
namespace FNPC;

/*
Copyright © 2016 FENGberd All right reserved.
GitHub Project:
https://github.com/fengberd/FNPC
*/

use pocketmine\utils\TextFormat;

use FNPC\npc\NPC;
use FNPC\npc\CommandNPC;
use FNPC\npc\ReplyNPC;
use FNPC\npc\TeleportNPC;

class Main extends \pocketmine\plugin\PluginBase implements \pocketmine\event\Listener
{
	private static $obj=null;
	private static $registeredNPC=array();
	
	public static function getInstance()
	{
		return self::$obj;
	}
	
	public static function getRegisteredNpcClass($name)
	{
		$name=strtolower($name);
		if(isset(self::$registeredNPC[$name]))
		{
			return self::$registeredNPC[$name][0];
		}
		return false;
	}
	
	public static function unregisterNpc($name)
	{
		$name=strtolower($name);
		unset(self::$registeredNPC[$name]);
	}
	
	public static function registerNpc($name,$description,$className,$force=false)
	{
		$class=new \ReflectionClass($className);
		$name=strtolower($name);
		if(is_a($className,NPC::class,true) && !$class->isAbstract() && (!isset(self::$registeredNPC[$name]) || $force))
		{
			self::$registeredNPC[$name]=array($className,$description);
			NPC::reloadUnknownNPC();
			unset($className,$class,$force,$name,$description);
			return true;
		}
		unset($className,$class,$force,$name,$description);
		return false;
	}
	
	public function onEnable()
	{
		$start=microtime(true);
		if(!self::$obj instanceof Main)
		{
			self::$obj=$this;
			self::registerNpc('normal','',NPC::class,true);
			self::registerNpc('reply','',ReplyNPC::class,true);
			self::registerNpc('command','',CommandNPC::class,true);
			self::registerNpc('teleport','',TeleportNPC::class,true);
		}
		$base='\\pocketmine\\entity\\Entity::';
		if(!defined($base.'DATA_NAMETAG') || !defined($base.'DATA_FLAGS') || !defined($base.'DATA_FLAG_CAN_SHOW_NAMETAG') || !defined($base.'DATA_FLAG_ALWAYS_SHOW_NAMETAG'))
		{
			$this->getLogger()->warning('de bază minunată a problemei există în prezent, aceasta va duce la un nume de afișare normale NPC');
		}
		if(defined($base.'DATA_LEAD_HOLDER') && !class_exists('\\pocketmine\\network\\protocol\\SetEntityLinkPacket',false))
		{
			$this->getLogger()->warning('Ștergeți această bază minunat SetEntityLink Pachetul, eu nu pot garanta nu va apărea coarda ciudat între jucător și NPC');
		}
		$reflect=new \ReflectionClass('\\pocketmine\\entity\\Entity');
		$reflect=$reflect->getDefaultProperties();
		if(!isset($reflect['dataProperties']))
		{
			throw new \Exception('Ne pare rău, dar utilizați un FNPC să nu fie compatibil cu miez intelectual');
		}
		NPC::$metadata=$reflect['dataProperties'];
		SystemProvider::init($this);
		NPC::init();
		
		$this->initTasks();
		$this->getServer()->getPluginManager()->registerEvents($this,$this);
		$this->getLogger()->info(TextFormat::GREEN.'date NPC este încărcat, consumatoare de timp'.(microtime(true)-$start).'秒');
	}
	
	public function initTasks()
	{
		$this->quickSystemTask=new Tasks\QuickSystemTask($this);
		$this->getServer()->getScheduler()->scheduleRepeatingTask($this->quickSystemTask,1);
	}
	
	public function onCommand(\pocketmine\command\CommandSender $sender,\pocketmine\command\Command $command,$label,array $args)
	{
		unset($command,$label);
		if(!isset($args[0]))
		{
			unset($sender,$args);
			return false;
		}
		if(isset($args[1]) && is_numeric($args[1]))
		{
			$sender->sendMessage('[NPC] '.TextFormat::YELLOW.'Pur ID-ul digital va avea ca rezultat nu poate fi găsit NPC-uri, vă rugăm să folosiți limba engleză / chineză / ID Mixt');
		}
		switch($args[0])
		{
		case 'type':
			$data=TextFormat::GREEN.'=========='.TextFormat::YELLOW.'FNPC Type List'.TextFormat::GREEN.'==========';
			foreach(self::$registeredNPC as $key=>$val)
			{
				$data.="\n".TextFormat::YELLOW.$key.TextFormat::WHITE.' - '.TextFormat::AQUA.$val[1];
				unset($key,$val);
			}
			$sender->sendMessage($data);
			unset($data);
			break;
		case 'add':
			if(!isset($args[3]))
			{
				unset($sender,$args);
				return false;
			}
			if(isset(NPC::$pool[$args[2]]))
			{
				$sender->sendMessage('[NPC] '.TextFormat::RED.'există deja NPC același ID');
				break;
			}
			$args[1]=strtolower($args[1]);
			if(!isset(self::$registeredNPC[$args[1]]))
			{
				$sender->sendMessage('[NPC] '.TextFormat::RED.'Tipul specificat nu există, vă rugăm să folosiți /fnpc type Vizualizați tipurile disponibile');
				break;
			}
			$npc=new self::$registeredNPC[$args[1]][0]($args[2],$args[3],$sender->x,$sender->y,$sender->z);
			$npc->level=$sender->getLevel()->getFolderName();
			$npc->spawnToAll();
			$npc->save();
			unset($npc);
			$sender->sendMessage('[NPC] '.TextFormat::GREEN.'NPC creat cu succes');
			break;
		case 'transfer':
			if(!isset($args[3]))
			{
				unset($sender,$args);
				return false;
			}
			if(!isset(NPC::$pool[$args[1]]))
			{
				$sender->sendMessage('[NPC] '.TextFormat::RED.'Acest NPC nu există');
				break;
			}
			if(!NPC::$pool[$args[1]] instanceof TeleportNPC)
			{
				$sender->sendMessage('[NPC] '.TextFormat::RED.'NPC nu este un tip de transfer NPC');
				break;
			}
			NPC::$pool[$args[1]]->setTeleport(array(
				'ip'=>$args[2],
				'port'=>$args[3]
			));
			$sender->sendMessage('[NPC] '.TextFormat::GREEN.'NPC Teleport set cu succes');
			break;
		case 'remove':
			if(!isset($args[1]))
			{
				unset($sender,$args);
				return false;
			}
			if(!isset(NPC::$pool[$args[1]]))
			{
				$sender->sendMessage('[NPC] '.TextFormat::RED.'Acest NPC nu există');
				break;
			}
			NPC::$pool[$args[1]]->close();
			$sender->sendMessage('[NPC] '.TextFormat::GREEN.'S-a eliminat!');
			break;
		case 'reset':
			if(!isset($args[1]))
			{
				unset($sender,$args);
				return false;
			}
			if(!isset(NPC::$pool[$args[1]]))
			{
				$sender->sendMessage('[NPC] '.TextFormat::RED.'Acest NPC nu există');
				break;
			}
			$npc=NPC::$pool[$args[1]];
			if($npc instanceof TeleportNPC)
			{
				$npc->setTeleport(false);
				$sender->sendMessage('[NPC] '.TextFormat::GREEN.'NPC Teleport eliminat cu succes');
			}
			else if($npc instanceof CommandNPC)
			{
				$npc->command=array();
				$npc->save();
				$sender->sendMessage('[NPC] '.TextFormat::GREEN.'comandă NPC șters cu succes');
			}
			break;
		case 'teleport':
			if(!isset($args[1]))
			{
				unset($sender,$args);
				return false;
			}
			if(!isset(NPC::$pool[$args[1]]))
			{
				$sender->sendMessage('[NPC] '.TextFormat::RED.'Acest NPC nu există');
				break;
			}
			if(!NPC::$pool[$args[1]] instanceof TeleportNPC)
			{
				$sender->sendMessage('[NPC] '.TextFormat::RED.'NPC nu este un tip de transfer NPC');
				break;
			}
			NPC::$pool[$args[1]]->setTeleport($sender);
			$sender->sendMessage('[NPC] '.TextFormat::GREEN.'NPC Teleport set cu succes');
			break;
		case 'command':
			if(!isset($args[2]))
			{
				unset($sender,$args);
				return false;
			}
			if(!isset(NPC::$pool[$args[1]]))
			{
				$sender->sendMessage('[NPC] '.TextFormat::RED.'Acest NPC nu există');
				break;
			}
			if(!NPC::$pool[$args[1]] instanceof CommandNPC)
			{
				$sender->sendMessage('[NPC] '.TextFormat::RED.'NPC nu este un NPC tip de comandă');
				break;
			}
			switch($args[2])
			{
			case 'add':
				if(!isset($args[3]))
				{
					unset($sender,$args);
					return false;
				}
				$cmd='';
				for($i=3;$i<count($args);$i++)
				{
					$cmd.=$args[$i];
					if($i!=count($args)-1)
					{
						$cmd.=' ';
					}
				}
				unset($i);
				NPC::$pool[$args[1]]->addCommand($cmd);
				$sender->sendMessage('[NPC] '.TextFormat::GREEN.'NPC-uri de instruire a adăugat cu succes');
				break;
			case 'remove':
				if(!isset($args[3]))
				{
					unset($sender,$args);
					return false;
				}
				$cmd='';
				for($i=3;$i<count($args);$i++)
				{
					$cmd.=$args[$i];
					if($i!=count($args)-1)
					{
						$cmd.=' ';
					}
				}
				unset($i);
				if(NPC::$pool[$args[1]]->removeCommand($cmd))
				{
					$sender->sendMessage('[NPC] '.TextFormat::GREEN.'comandă NPC eliminat cu succes');
				}
				else
				{
					$sender->sendMessage('[NPC] '.TextFormat::RED.'NPC nu adaugă comanda');
				}
				break;
			case 'list':
				$msg=TextFormat::GREEN.'===FNPC==='."\n";
				foreach(NPC::$pool[$args[1]]->command as $cmd)
				{
					$msg.=TextFormat::YELLOW.$cmd."\n";
					unset($cmd);
				}
				$sender->sendMessage($msg);
				unset($msg);
				break;
			default:
				unset($sender,$args);
				return false;
			}
			break;
		case 'chat':
			if(!isset($args[2]))
			{
				unset($sender,$args);
				return false;
			}
			if(!isset(NPC::$pool[$args[1]]))
			{
				$sender->sendMessage('[NPC] '.TextFormat::RED.'Acest NPC nu există');
				break;
			}
			if(!NPC::$pool[$args[1]] instanceof ReplyNPC)
			{
				$sender->sendMessage('[NPC] '.TextFormat::RED.'NPC nu este un tip de NPC Răspuns');
				break;
			}
			switch($args[2])
			{
			case 'add':
				if(!isset($args[3]))
				{
					unset($sender,$args);
					return false;
				}
				$cmd='';
				for($i=3;$i<count($args);$i++)
				{
					$cmd.=$args[$i];
					if($i!=count($args)-1)
					{
						$cmd.=' ';
					}
				}
				unset($i);
				NPC::$pool[$args[1]]->addChat($cmd);
				$sender->sendMessage('[NPC] '.TextFormat::GREEN.'NPC sesiune de date se adaugă cu succes');
				break;
			case 'remove':
				if(!isset($args[3]))
				{
					unset($sender,$args);
					return false;
				}
				$cmd='';
				for($i=3;$i<count($args);$i++)
				{
					$cmd.=$args[$i];
					if($i!=count($args)-1)
					{
						$cmd.=' ';
					}
				}
				unset($i);
				if(NPC::$pool[$args[1]]->removeChat($cmd))
				{
					$sender->sendMessage('[NPC] '.TextFormat::GREEN.'NPC a sesiune de date eliminat cu succes');
				}
				else
				{
					$sender->sendMessage('[NPC] '.TextFormat::RED.'NPC-uri nu adaugă datele de sesiune');
				}
				break;
			default:
				unset($sender,$args);
				return false;
			}
			break;
		case 'name':
			if(!isset($args[2]))
			{
				unset($sender,$args);
				return false;
			}
			if(!isset(NPC::$pool[$args[1]]))
			{
				$sender->sendMessage('[NPC] '.TextFormat::RED.'Acest NPC nu există');
				break;
			}
			NPC::$pool[$args[1]]->setName($args[2]);
			$sender->sendMessage('[NPC] '.TextFormat::GREEN.'NameTag');
			break;
		case 'skin':
			if(!isset($args[2]))
			{
				unset($sender,$args);
				return false;
			}
			if(!isset(NPC::$pool[$args[1]]))
			{
				$sender->sendMessage('[NPC] '.TextFormat::RED.'Acest NPC nu există');
				break;
			}
			switch(NPC::$pool[$args[1]]->setPNGSkin($args[2],false))
			{
			case 0:
				$sender->sendMessage('[NPC] '.TextFormat::GREEN.'Succesul de înlocuire a pielii');
				break;
			case -1:
				$sender->sendMessage('[NPC] '.TextFormat::RED.'fișier piele nu există, vă rugăm să verificați calea introdusă este corectă, și are nevoie de.png');
				break;
			case -2:
				$sender->sendMessage('[NPC] '.TextFormat::RED.'fișier piele nevalid, utilizați MCPE încărcat corect pielea png');
				break;
			case -3:
			default:
				$sender->sendMessage('[NPC] '.TextFormat::RED.'Eroare necunoscută, vă rugăm să verificați calea este corectă și dacă pielea poate fi utilizat în mod normal în MCPE');
				break;
			}
			break;
		case 'item':
			if(!isset($args[2]))
			{
				unset($sender,$args);
				return false;
			}
			if(!isset(NPC::$pool[$args[1]]))
			{
				$sender->sendMessage('[NPC] '.TextFormat::RED.'Acest NPC nu există');
				break;
			}
			$item=explode(':',$args[2]);
			if(!isset($item[1]))
			{
				$item[1]=0;
			}
			$item[0]=intval($item[0]);
			$item[1]=intval($item[1]);
			NPC::$pool[$args[1]]->setHandItem(\pocketmine\item\Item::get($item[0],$item[1]));
			$sender->sendMessage('[NPC] '.TextFormat::GREEN.'Înlocuiți elementele de succes portabile');
			break;
		case 'tphere':
		case 'teleporthere':
			if(!isset($args[1]))
			{
				unset($sender,$args);
				return false;
			}
			if(!isset(NPC::$pool[$args[1]]))
			{
				$sender->sendMessage('[NPC] '.TextFormat::RED.'Acest NPC nu există');
				break;
			}
			NPC::$pool[$args[1]]->teleport($sender);
			$sender->sendMessage('[NPC] '.TextFormat::GREEN.'A trimis cu succes');
			break;
		case 'help':
			$help=TextFormat::GREEN.'======NPC======'."\n";
			$help.=TextFormat::GREEN.'§4Tradus de MateiGamingYTB."\n";
			$help.=TextFormat::YELLOW.'add <Type> <ID> <Name> - 'adaugare npc'."\n";
			$help.=TextFormat::YELLOW.'type - typurile existente de npcuri'."\n";
			$help.=TextFormat::YELLOW.'remove <ID> - sterge un npc'."\n";
			$help.=TextFormat::YELLOW.'skin <ID> <File> - skinuri'."\n";
			$help.=TextFormat::YELLOW.'name <ID> <Name> - '."\n";
			$help.=TextFormat::YELLOW.'command <ID> <add/remove> <Command> - /'."\n";
			$help.=TextFormat::YELLOW.'command <ID> list - adaugare vomanda la npc'."\n";
			$help.=TextFormat::YELLOW.'tphere <ID> - '."\n";
			$help.=TextFormat::YELLOW.'teleport <ID> - '."\n";
			$help.=TextFormat::YELLOW.'transfer <ID> <IP> <Port> - '."\n";
			$help.=TextFormat::YELLOW.'reset <ID> - 重置NPC的设置'."\n";
			$help.=TextFormat::YELLOW.'chat <ID> <add/remove> <Chat> - '."\n";
			$help.=TextFormat::YELLOW.'item <ID> <Item[:Damage]> - '."\n";
			$help.=TextFormat::YELLOW.'help - ajutor';
			$sender->sendMessage($help);
			unset($help);
			break;
		default:
			unset($sender,$args);
			return false;
		}
		unset($sender,$args);
		return true;
	}
	
	public function onPlayerMove(\pocketmine\event\player\PlayerMoveEvent $event)
	{
		NPC::playerMove($event->getPlayer());
		unset($event);
	}
	
	public function onDataPacketReceive(\pocketmine\event\server\DataPacketReceiveEvent $event)
	{
		NPC::packetReceive($event->getPlayer(),$event->getPacket());
		unset($event);
	}
	
	public function onPlayerJoin(\pocketmine\event\player\PlayerJoinEvent $event)
	{
		NPC::spawnAllTo($event->getPlayer());
		unset($event);
	}
	
	public function onEntityLevelChange(\pocketmine\event\entity\EntityLevelChangeEvent $event)
	{
		if($event->getEntity() instanceof \pocketmine\Player)
		{
			NPC::spawnAllTo($event->getEntity(),$event->getTarget());
		}
		unset($event);
	}
}

