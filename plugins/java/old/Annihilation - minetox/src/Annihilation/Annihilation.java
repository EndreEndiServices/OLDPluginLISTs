package Annihilation;

import Annihilation.Arena.BlockEntity.EnderBrewing;
import Annihilation.Arena.BlockEntity.EnderFurnace;
import Annihilation.Entity.FishingHook;
import Annihilation.Entity.IronGolem;
import Annihilation.Entity.SlapperHuman;
import Annihilation.MySQL.BuyKitQuery;
import Annihilation.MySQL.JoinQuery;
import Annihilation.MySQL.StatsQuery;
import MTCore.MessageTask;
import MTCore.MTCore;
import cn.nukkit.blockentity.BlockEntity;
import cn.nukkit.entity.Entity;
import cn.nukkit.event.EventHandler;
import cn.nukkit.event.EventPriority;
import cn.nukkit.event.entity.EntityDamageByEntityEvent;
import cn.nukkit.event.Listener;
import cn.nukkit.event.player.PlayerJoinEvent;
import cn.nukkit.item.Item;
import cn.nukkit.level.Level;
import cn.nukkit.level.Position;
import cn.nukkit.plugin.PluginBase;
import cn.nukkit.utils.TextFormat;
import Annihilation.Arena.Arena;
import cn.nukkit.math.Vector3;
import cn.nukkit.command.Command;
import cn.nukkit.command.CommandSender;
import cn.nukkit.Player;

import java.util.HashMap;
import java.util.Map;

public class Annihilation extends PluginBase implements Listener {
    public HashMap<String, HashMap<String, Vector3>> arenas = new HashMap<>();

    //public MySQLManager mysql;
    public HashMap<String, HashMap<String, Object>> maps = new HashMap<>();

    public HashMap<String, Arena> ins = new HashMap<>();

    public Level level;
    public Position mainLobby;

    public MTCore mtcore;

    private static Annihilation instance;

    public void onLoad(){
        instance = this;
        Entity.registerEntity("SlapperHuman", SlapperHuman.class);
        Entity.registerEntity("FishingHook", FishingHook.class);
        Entity.registerEntity("IronGolem", IronGolem.class);

        BlockEntity.registerBlockEntity("EnderFurnace", EnderFurnace.class);
        BlockEntity.registerBlockEntity("EnderBrewing", EnderBrewing.class);
    }

    public void onEnable() {
        this.level = getServer().getDefaultLevel();
        this.mtcore = (MTCore) getServer().getPluginManager().getPlugin("MTCore");
        getLogger().info(TextFormat.GREEN + "Annihilation enabled");
        //this.mysql = new MySQLManager(this);
        this.mainLobby = level.getSpawnLocation();
        this.level.setTime(5000);
        this.level.stopTime();
        setMapsData();
        setArenasData();
        registerArena("anni-1");
        getServer().getPluginManager().registerEvents(this, this);
        MessageTask.messages.add(TextFormat.AQUA + "Brew potions and defeat other teams faster!");
        MessageTask.messages.add(TextFormat.AQUA + "Kill the boss and get a rare item!");
        MessageTask.messages.add(TextFormat.AQUA + "Use different kits for better experience of the game!");
        MessageTask.messages.add(TextFormat.AQUA + "Change your kit using /class command!");

        kitMessages.put("miner", "         " + TextFormat.GOLD + TextFormat.BOLD + "You are the backbone." + TextFormat.RESET + "\n" + TextFormat.GRAY + "You support the war effort by gathering\n" + TextFormat.GRAY + "the raw materials your soldiers' gear needs.");
        kitMessages.put("archer", "         " + TextFormat.GOLD + TextFormat.BOLD + "You are the arrow." + TextFormat.RESET + "\n" + TextFormat.GRAY + "The last word in ranged combat, deal +1 damage with any bow.");
        kitMessages.put("spy", "         " + TextFormat.GOLD + TextFormat.BOLD + "You are the deceiver." + TextFormat.RESET + "\n" + TextFormat.GRAY + "Vanish into thin air when still and sneaking!");
        kitMessages.put("acrobat", "         " + TextFormat.GOLD + TextFormat.BOLD + "You are the feather." + TextFormat.RESET + "\n" + TextFormat.GRAY + "You take no fall damage at all.");
        kitMessages.put("operative", "         " + TextFormat.GOLD + TextFormat.BOLD + "You are the <something>" + TextFormat.RESET + "\n" + TextFormat.GRAY + "Carry out your plans for\n" + TextFormat.GRAY + "offense and safely escape!");
        kitMessages.put("berserker", "         " + TextFormat.GOLD + TextFormat.BOLD + "You are the anger." + TextFormat.RESET + "\n" + TextFormat.GRAY + "Killing players will give an additional heart,\n" + TextFormat.GRAY + "until you reach 15 total hearts.\n" + TextFormat.GRAY + "Dying resets your hearts back to 7.");
        kitMessages.put("lumberjack", "         " + TextFormat.GOLD + TextFormat.BOLD + "You are the wedge." + TextFormat.RESET + "\n" + TextFormat.GRAY + "Gather wood with an efficiency axe and\n" + TextFormat.GRAY + "with the chance of gaining double yield,\n" + TextFormat.GRAY + "ensuring quick work of any trees in your way!");
        kitMessages.put("warrior", "         " + TextFormat.GOLD + TextFormat.BOLD + "You are the Sword." + TextFormat.RESET + "\n" + TextFormat.GRAY + "You do +1 damage with any melee weapon");
        kitMessages.put("handyman", "         " + TextFormat.GOLD + TextFormat.BOLD + "You are the fixer." + TextFormat.RESET + "\n" + TextFormat.GRAY + "Every hit you get on an opposing team's nexus\n" + TextFormat.GRAY + "has a chance of repairing your nexus!\n" + TextFormat.GRAY + "Phase 2: 20%\n" + TextFormat.GRAY + "Phase 3: 15%\n" + TextFormat.GRAY + "Phase 4: 10%\n" + TextFormat.GRAY + "Phase 5: 7%");
        kitMessages.put("civilian", "         " + TextFormat.GOLD + TextFormat.BOLD + "You are the worker." + TextFormat.RESET + "\n" + TextFormat.GRAY + "You may not have the special abilities of the other classes, but don't worry!\n" + TextFormat.GRAY + "Civilians fuel the war effort with their set of stone tools.\n" + TextFormat.GRAY + "Get to work!");
        kitMessages.put("scout", "         " + TextFormat.GOLD + TextFormat.BOLD + "You are the feet." + TextFormat.RESET + "\n"
                + TextFormat.GRAY + "Use your grapple to climb obstacles\n"
                + TextFormat.GRAY + "grapple to climb obstacles");
    }

    public void onDisable() {
        getLogger().info(TextFormat.RED + "Annihilation disabled");
    }

    public void registerArena(String arena) {
        Arena a = new Arena(arena, this);
        getServer().getPluginManager().registerEvents((Listener) a, this);
        ins.put(arena, a);
    }

    @EventHandler(priority = EventPriority.NORMAL, ignoreCancelled = false)
    public void onJoin(PlayerJoinEvent e) {
        Player p = e.getPlayer();

        new JoinQuery(this, p.getName().toLowerCase());
    }

    public static String getPrefix() {
        return "§l§6[Annihilation]§r§f " + TextFormat.RESET + TextFormat.WHITE;
    }

    public void setArenasData() {
        arenas.put("anni-1", new HashMap<String, Vector3>());

        HashMap<String, Vector3> anni1 = arenas.get("anni-1");

        anni1.put("sign", new Vector3(125, 20, 172));
        anni1.put("1sign", new Vector3(488, 21, 493));
        anni1.put("2sign", new Vector3(488, 21, 491));
        anni1.put("3sign", new Vector3(490, 21, 489));
        anni1.put("4sign", new Vector3(492, 21, 489));
        anni1.put("lobby", new Vector3(528, 20, 497));
    }

    public boolean onCommand(CommandSender sender, Command cmd, String label, String[] args) {
        if (sender instanceof Player) {

            Arena arena = getPlayerArena((Player) sender);

            switch (cmd.getName().toLowerCase()) {
                case "class":
                    /*if(arena == null){
                        sender.sendMessage(cmd.getPermissionMessage());
                        break;
                    }
                    if(args[0] == null){
                        sender.sendMessage(getPrefix()+TextFormat.RED+"use /class <kit>");
                        break;
                    }
                    kitMessages = array_keys($arena-> kitManager-> kitMessages);

                    switch(strtolower($args[0])){
                        case "help":
                            $msg = self::getPrefix().TextFormat::GREEN."Available kitMessages: ".TextFormat::YELLOW;

                            foreach($kits as $kit){
                                $msg .= $kit.TextFormat::GRAY.", ";
                            }

                            $sender->sendMessage(substr($msg, 0, -2));
                            break;
                        default:
                            if(!in_array(strtolower($args[0]), $kits)){
                                $sender->sendMessage(self::getPrefix().TextFormat::RED."This class doesn't exist. Use /class help for list of classes");
                                break;
                            }

                            $arena->kitManager->onKitChange($sender, strtolower($args[0]));
                            $sender->sendMessage(self::getPrefix().TextFormat::GREEN.'Selected class '.TextFormat::BLUE.strtolower($args[0]));
                            break;
                    }*/
                    break;
                case "blue":
                    if (arena == null) {
                        break;
                    }
                    arena.joinTeam((Player) sender, 1);
                    break;
                case "red":
                    if (arena == null) {
                        break;
                    }
                    arena.joinTeam((Player) sender, 2);
                    break;
                case "yellow":
                    if (arena == null) {
                        break;
                    }
                    arena.joinTeam((Player) sender, 3);
                    break;
                case "green":
                    if (arena == null) {
                        break;
                    }
                    arena.joinTeam((Player) sender, 4);
                    break;
                /*case 'lobby':
                    if($arena instanceof Arena){
                        $arena->handlePlayerQuit($sender);
                    }
                    else{
                        $sender->teleport($this->mainLobby);
                    }
                    $sender->getInventory()->clearAll();
                    break;*/
                case "stats":
                    /*sender.sendMessage(TextFormat.BLUE + "> Your " + TextFormat.GOLD + TextFormat.BOLD + "Annihilation" + TextFormat.RESET + TextFormat.BLUE + " stats " + TextFormat.BLUE + " <\n"
                            + TextFormat.DARK_GREEN + "Kills: " + TextFormat.DARK_PURPLE + this.mysql.getKills(sender.getName()) + "\n"
                            + TextFormat.DARK_GREEN + "Deaths: " + TextFormat.DARK_PURPLE + this.mysql.getDeaths(sender.getName()) + "\n"
                            + TextFormat.DARK_GREEN + "Wins: " + TextFormat.DARK_PURPLE + this.mysql.getWins(sender.getName()) + "\n"
                            + TextFormat.DARK_GREEN + "Losses: " + TextFormat.DARK_PURPLE + this.mysql.getLosses(sender.getName()) + "\n"
                            + TextFormat.DARK_GREEN + "Nexuses destroyed: " + TextFormat.DARK_PURPLE + this.mysql.getNexuses(sender.getName()) + "\n"
                            + TextFormat.DARK_GREEN + "Nexus damaged: " + TextFormat.DARK_PURPLE + this.mysql.getNexusDmg(sender.getName()) + "\n"
                            + TextFormat.GRAY + "---------------------");*/
                    new StatsQuery(this, sender.getName().toLowerCase());
                    break;
                /*case "msg":
                    if(!isset($args[0]) || !isset($args[1])){
                        $sender->sendMessage($this->getPrefix().TextFormat::GRAY."use /msg [player] message");
                        break;
                    }
                    $p = $this->getServer()->getPlayer(array_shift($args));
                    if(!$p instanceof Player){
                        $sender->sendMessage($this->getPrefix()."Tento hrac neexistuje");
                        break;
                    }
                    $p->sendMessage($sender->getDisplayName().TextFormat::DARK_AQUA." -> ".TextFormat::AQUA.implode(' ', $args));
                    break;*/
                case "vote":
                    if (arena == null) {
                        break;
                    }
                    if (args.length != 1) {
                        sender.sendMessage(Annihilation.getPrefix() + TextFormat.GRAY + "use /vote [map]");
                        break;
                    }
                    arena.votingManager.onVote((Player) sender, args[0]);
                    break;
                case "start":
                    if (sender.isOp()) {
                        if (arena == null || arena.phase >= 1) {
                            break;
                        }
                        arena.selectMap(true);
                        arena.startGame(true);
                    }
                    break;
            }
        }
        return true;
    }

    public Arena getPlayerArena(Player p) {


        for (Map.Entry<String, Arena> entry : ins.entrySet()) {
            Arena a = entry.getValue();

            if (a.inArena(p)) {
                return a;
            }
        }
        return null;
    }

    public void setMapsData() {
        maps.put("Canyon", new HashMap<>());
        HashMap<String, Object> canyon = maps.get("Canyon");

        canyon.put("1Spawn", new Vector3(-108, 76, -121));
        canyon.put("2Spawn", new Vector3(-121, 76, 233));
        canyon.put("3Spawn", new Vector3(233, 76, 246));
        canyon.put("4Spawn", new Vector3(246, 76, -108));
        canyon.put("1Nexus", new Vector3(-113, 70, -114));
        canyon.put("2Nexus", new Vector3(-114, 70, 238));
        canyon.put("3Nexus", new Vector3(238, 70, 239));
        canyon.put("4Nexus", new Vector3(239, 70, -113));
        canyon.put("1Chest", new Vector3(-102, 73, -112));
        canyon.put("2Chest", new Vector3(-112, 73, 227));
        canyon.put("3Chest", new Vector3(227, 73, 237));
        canyon.put("4Chest", new Vector3(237, 73, -102));
        canyon.put("1Furnace", new Vector3(-103, 73, -112));
        canyon.put("2Furnace", new Vector3(-112, 73, 228));
        canyon.put("3Furnace", new Vector3(228, 73, 237));
        canyon.put("4Furnace", new Vector3(237, 73, -103));

        canyon.put("1EnderBrewing", new Vector3(-100, 73, -112));
        canyon.put("2EnderBrewing", new Vector3(-112, 73, 225));
        canyon.put("3EnderBrewing", new Vector3(225, 73, 237));
        canyon.put("4EnderBrewing", new Vector3(237, 73, -100));
        canyon.put("1Brewing", new Vector3(-122, 81, -126));
        canyon.put("1Weapons", new Vector3(-126, 81, -122));
        canyon.put("2Brewing", new Vector3(-126, 81, 247));
        canyon.put("2Weapons", new Vector3(-122, 81, 251));
        canyon.put("3Brewing", new Vector3(247, 81, 251));
        canyon.put("3Weapons", new Vector3(251, 81, 247));
        canyon.put("4Brewing", new Vector3(251, 81, -122));
        canyon.put("4Weapons", new Vector3(247, 81, -126));

        canyon.put("diamonds", new Vector3[]{new Vector3(82, 67, 52), new Vector3(80, 67, 59), new Vector3(71, 71, 57),
                new Vector3(58, 67, 62), new Vector3(57, 66, 65), new Vector3(61, 64, 61), new Vector3(70, 70, 75),
                new Vector3(73, 67, 82), new Vector3(44, 68, 48), new Vector3(49, 68, 58), new Vector3(39, 67, 63),
                new Vector3(44, 68, 73)});
        canyon.put("boss1name", "§bFerwin");
        canyon.put("boss1pos", new Vector3(61, 15, -52));
        canyon.put("boss1chest", new Vector3(62, 15, -42));

        canyon.put("boss2name", "§cCelariel");
        canyon.put("boss2pos", new Vector3(64, 15, 177));
        canyon.put("boss2chest", new Vector3(63, 15, 167));
        canyon.put("corner2", new Vector3(254, 128, 254));
        canyon.put("corner1", new Vector3(-129, 0, -129));

        maps.put("Hamlet", new HashMap<>());
        canyon = maps.get("Hamlet");

        canyon.put("1Spawn", new Vector3(-190, 44, 193));
        canyon.put("2Spawn", new Vector3(95, 44, -100));
        canyon.put("3Spawn", new Vector3(-194, 44, -96));
        canyon.put("4Spawn", new Vector3(99, 44, 189));
        canyon.put("1Nexus", new Vector3(-216, 39, 210));
        canyon.put("2Nexus", new Vector3(121, 39, -117));
        canyon.put("3Nexus", new Vector3(-211, 39, -122));
        canyon.put("4Nexus", new Vector3(116, 39, 215));
        canyon.put("1Chest", new Vector3(-217, 44, 215));
        canyon.put("2Chest", new Vector3(122, 44, -122));
        canyon.put("3Chest", new Vector3(-216, 44, -123));
        canyon.put("4Chest", new Vector3(121, 44, 216));
        canyon.put("1Furnace", new Vector3(-217, 44, 214));
        canyon.put("2Furnace", new Vector3(122, 44, -121));
        canyon.put("3Furnace", new Vector3(-215, 44, -123));
        canyon.put("4Furnace", new Vector3(120, 44, 216));

        canyon.put("1EnderBrewing", new Vector3(-217, 45, 214));
        canyon.put("2EnderBrewing", new Vector3(122, 45, -121));
        canyon.put("3EnderBrewing", new Vector3(-215, 45, -123));
        canyon.put("4EnderBrewing", new Vector3(120, 45, 216));
        canyon.put("1Brewing", new Vector3(-217, 46, 214));
        canyon.put("1Weapons", new Vector3(-217, 46, 210));
        canyon.put("2Brewing", new Vector3(122, 46, -121));
        canyon.put("2Weapons", new Vector3(122, 46, -117));
        canyon.put("3Brewing", new Vector3(-215, 46, -123));
        canyon.put("3Weapons", new Vector3(-211, 46, -123));
        canyon.put("4Brewing", new Vector3(120, 46, 216));
        canyon.put("4Weapons", new Vector3(117, 46, 216));

        canyon.put("diamonds", new Vector3[]{new Vector3(-52, 43, 50), new Vector3(-51, 43, 42), new Vector3(-57, 42, 50),
                new Vector3(-40, 43, 48), new Vector3(-47, 43, 44), new Vector3(-44, 43, 42), new Vector3(-47, 42, 36),
                new Vector3(-75, 45, 21), new Vector3(-72, 45, 76), new Vector3(-20, 45, 71), new Vector3(-23, 45, 18),
                new Vector3(-42, 42, 55)});
        canyon.put("boss1name", "§bFerwin");
        canyon.put("boss1pos", new Vector3(-204, 42, 47));
        canyon.put("boss1chest", new Vector3(-218, 44, 47));

        canyon.put("boss2name", "§cCelariel");
        canyon.put("boss2pos", new Vector3(111, 42, 46));
        canyon.put("boss2chest", new Vector3(124, 44, 46));
        canyon.put("corner2", new Vector3(127, 128, 221));
        canyon.put("corner1", new Vector3(-222, 0, -128));

        maps.put("Amazon", new HashMap<>());
        canyon = maps.get("Amazon");

        canyon.put("1Spawn", new Vector3(246, 24, 260));
        canyon.put("2Spawn", new Vector3(14, 24, 260));
        canyon.put("3Spawn", new Vector3(14, 24, 462));
        canyon.put("4Spawn", new Vector3(246, 24, 462));
        canyon.put("1Nexus", new Vector3(262, 44, 218));
        canyon.put("2Nexus", new Vector3(-2, 44, 218));
        canyon.put("3Nexus", new Vector3(-2, 44, 504));
        canyon.put("4Nexus", new Vector3(262, 44, 504));
        canyon.put("1Chest", new Vector3(245, 24, 252));
        canyon.put("2Chest", new Vector3(15, 24, 252));
        canyon.put("3Chest", new Vector3(15, 24, 470));
        canyon.put("4Chest", new Vector3(245, 24, 470));
        canyon.put("1Furnace", new Vector3(245, 24, 251));
        canyon.put("2Furnace", new Vector3(15, 24, 251));
        canyon.put("3Furnace", new Vector3(15, 24, 471));
        canyon.put("4Furnace", new Vector3(245, 24, 471));

        canyon.put("1EnderBrewing", new Vector3(241, 24, 253));
        canyon.put("2EnderBrewing", new Vector3(19, 24, 253));
        canyon.put("3EnderBrewing", new Vector3(19, 24, 469));
        canyon.put("4EnderBrewing", new Vector3(241, 24, 471));
        canyon.put("1Brewing", new Vector3(248, 25, 230));
        canyon.put("1Weapons", new Vector3(248, 26, 230));
        canyon.put("2Brewing", new Vector3(122, 25, -121));
        canyon.put("2Weapons", new Vector3(122, 26, -117));
        canyon.put("3Brewing", new Vector3(-215, 25, -123));
        canyon.put("3Weapons", new Vector3(-211, 26, -123));
        canyon.put("4Brewing", new Vector3(120, 25, 216));
        canyon.put("4Weapons", new Vector3(117, 26, 216));

        canyon.put("corner2", new Vector3(329, 128, 558));
        canyon.put("corner1", new Vector3(-69, 0, 164));

        canyon.put("diamonds", new Vector3[]{new Vector3(131, 32, 352), new Vector3(126, 31, 357), new Vector3(123, 31, 363),
                new Vector3(129, 32, 370), new Vector3(134, 31, 365), new Vector3(139, 32, 361), new Vector3(129, 28, 362),
                new Vector3(133, 29, 360)});
        canyon.put("boss1name", "§bFerwin");
        canyon.put("boss1pos", new Vector3(-57, 21, 361));
        canyon.put("boss1chest", new Vector3(-49, 22, 361));

        canyon.put("boss2name", "§cCelariel");
        canyon.put("boss2pos", new Vector3(317, 21, 361));
        canyon.put("boss2chest", new Vector3(309, 22, 361));

        /*$this->maps['Coastal'] = ["1Spawn" => new Vector3(-190, 44, 193),
            "2Spawn" => new Vector3(95, 44, -100),
            "3Spawn" => new Vector3(-194, 44, -96),
            "4Spawn" => new Vector3(99, 44, 189),
            "1Nexus" => new Vector3(-216, 39, 210),
            "2Nexus" => new Vector3(121, 39, -117),
            "3Nexus" => new Vector3(-211, 39, -122),
            "4Nexus" => new Vector3(116, 39, 215),
            "1Chest" => new Vector3(-217, 44, 215),
            "2Chest" => new Vector3(122, 44, -122),
            "3Chest" => new Vector3(-216, 44, -123),
            "4Chest" => new Vector3(121, 44, 216),
            "1Furnace" => new Vector3(-217, 44, 214),
            "2Furnace" => new Vector3(122, 44, -121),
            "3Furnace" => new Vector3(-215, 44, -123),
            "4Furnace" => new Vector3(120, 44, 216),
            //signs
            "1Brewing" => new Vector3(-217, 46, 214),
            "1Weapons" => new Vector3(-217, 46, 210),
            "2Brewing" => new Vector3(122, 46, -121),
            "2Weapons" => new Vector3(122, 46, -117),
            "3Brewing" => new Vector3(-215, 46, -123),
            "3Weapons" => new Vector3(-211, 46, -123),
            "4Brewing" => new Vector3(120, 46, 216),
            "4Weapons" => new Vector3(117, 46, 216),
            //diamonds
            "diamonds" => [ new Vector3(-52, 43, 50), new Vector3(-51, 43, 42), new Vector3(-57, 42, 50),
                new Vector3(-40, 43, 48), new Vector3(-47, 43, 44), new Vector3(-44, 43, 42), new Vector3(-47, 42, 36),
                new Vector3(-75, 45, 21), new Vector3(-72, 45, 76), new Vector3(-20, 45, 71), new Vector3(-23, 45, 18),
                new Vector3(-42, 42, 55)],
            "bosses" => [1 => ['name' => '§bFerwin', 'pos' => new Vector3(-204, 42, 47), 'chest' => new Vector3(-218, 44, 47)], 2 => ['name' => '§cCelariel', 'pos' => new Vector3(111, 42, 46), 'chest' => new Vector3(124, 44, 46)]],
            "corner2" => new Vector3(127, 128, 221),
            "corner1" => new Vector3(-222, 0, -128)
        ];*/
    }

    public static HashMap<String, String> kitMessages = new HashMap<>();

    public void onHit(EntityDamageByEntityEvent e) {
        Entity entity = e.getEntity();
        Player p = (Player) e.getDamager();

        String name = entity.getNameTag().toLowerCase().trim();

        for(Map.Entry<String, String> entry : kitMessages.entrySet()){
            if(name.contains(entry.getKey())){
                name = entry.getKey();
            }
        }

        Item item = p.getInventory().getItemInHand();

        if (item.getId() == Item.GOLD_INGOT) {
            new BuyKitQuery(this, p.getName().toLowerCase(), name, BuyKitQuery.ACTION_BUY);
        } else {
            new BuyKitQuery(this, p.getName().toLowerCase(), name, BuyKitQuery.ACTION_INFO);
            //p.sendMessage(kitMessages.get(name));
            //p.sendMessage(getPrefix() + TextFormat.YELLOW + ">> To buy this kit use a gold ingot");
        }
    }

    public static Annihilation getInstance(){
        return instance;
    }

    public Arena getArena(String name){
        return ins.get(name);
    }
}