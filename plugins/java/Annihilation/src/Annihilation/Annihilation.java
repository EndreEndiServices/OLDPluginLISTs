package Annihilation;

import Annihilation.Arena.BlockEntity.EnderBrewing;
import Annihilation.Arena.BlockEntity.EnderFurnace;
import Annihilation.Arena.Kits.Kit;
import Annihilation.Arena.Object.BlockList;
import Annihilation.Entity.AnnihilationNPC;
import Annihilation.Entity.FishingHook;
import Annihilation.Entity.IronGolem;
import Annihilation.Entity.SlapperHuman;
import Annihilation.MySQL.BuyKitQuery;
import Annihilation.MySQL.JoinQuery;
import Annihilation.MySQL.StatsQuery;
import GTCore.MTCore;
import GTCore.Task.MessageTask;
import cn.nukkit.block.Block;
import cn.nukkit.block.BlockAir;
import cn.nukkit.block.BlockUnknown;
import cn.nukkit.blockentity.BlockEntity;
import cn.nukkit.entity.Entity;
import cn.nukkit.entity.EntityHuman;
import cn.nukkit.event.EventHandler;
import cn.nukkit.event.EventPriority;
import cn.nukkit.event.entity.EntityDamageByEntityEvent;
import cn.nukkit.event.Listener;
import cn.nukkit.event.player.PlayerJoinEvent;
import cn.nukkit.item.Item;
import cn.nukkit.level.Level;
import cn.nukkit.level.Position;
import cn.nukkit.level.format.FullChunk;
import cn.nukkit.nbt.tag.*;
import cn.nukkit.plugin.PluginBase;
import cn.nukkit.utils.TextFormat;
import Annihilation.Arena.Arena;
import cn.nukkit.math.Vector3;
import cn.nukkit.command.Command;
import cn.nukkit.command.CommandSender;
import cn.nukkit.Player;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
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

    public void onLoad() {
        instance = this;
        Entity.registerEntity("SlapperHuman", SlapperHuman.class);
        Entity.registerEntity("FishingHook", FishingHook.class);
        Entity.registerEntity("IronGolem", IronGolem.class);
        Entity.registerEntity("AnnihilationNPC", AnnihilationNPC.class);

        BlockEntity.registerBlockEntity("EnderFurnace", EnderFurnace.class);
        BlockEntity.registerBlockEntity("EnderBrewing", EnderBrewing.class);
    }

    public void onEnable() {
        this.level = getServer().getDefaultLevel();
        this.mtcore = (MTCore) getServer().getPluginManager().getPlugin("GTCore");
        getLogger().info(TextFormat.GREEN + "Annihilation enabled");
        //this.mysql = new MySQLManager(this);
        this.mainLobby = level.getSpawnLocation();
        this.level.setTime(5000);
        this.level.stopTime();
        setMapsData();
        setArenasData();
        registerArena("anni-1");
        getServer().getPluginManager().registerEvents(this, this);
        MessageTask.messages.add(TextFormat.AQUA + "Delej lektvary a vyrid ostatni teamy driv");
        MessageTask.messages.add(TextFormat.AQUA + "Zabij bosse a ziskej specialni item!");
        MessageTask.messages.add(TextFormat.AQUA + "Pouzivej ruzne kity pro vetsi zazitek ze hry!");
        //MessageTask.messages.add(TextFormat.AQUA + "Change your kit using /class command!");

        mtcore.enableItemRemoving();

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
        anni1.put("inLobby", new Vector3(528, 20, 497));
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
                /*case 'inLobby':
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
                        //arena.startGame(true);
                    }
                    break;
                case "npc":
                    if (!sender.isOp()) {
                        break;
                    }

                    if (args.length != 2) {//name, cost
                        break;
                    }

                    String name = args[0];
                    int cost = Integer.valueOf(args[1]);

                    /*for(String str : args) {
                        name += " " + str;
                    }

                    name = name.trim();*/

                    CompoundTag nbt = new CompoundTag()
                            .putList(new ListTag<>("Pos")
                                    .add(new DoubleTag("0", ((Player) sender).getFloorX() + 0.5))
                                    .add(new DoubleTag("1", ((Player) sender).y))
                                    .add(new DoubleTag("2", ((Player) sender).getFloorZ() + 0.5)))
                            .putList(new ListTag<DoubleTag>("Motion")
                                    .add(new DoubleTag("0", 0))
                                    .add(new DoubleTag("1", 0))
                                    .add(new DoubleTag("2", 0)))
                            .putList(new ListTag<FloatTag>("Rotation")
                                    .add(new FloatTag("0", (float) ((Player) sender).getYaw()))
                                    .add(new FloatTag("1", (float) ((Player) sender).getPitch())))
                            .putBoolean("Invulnerable", true)
                            .putCompound("Skin", new CompoundTag()
                                    .putBoolean("Transparent", false)
                                    .putByteArray("Data", ((Player) sender).getSkin().getData())
                                    .putString("ModelId", ((Player) sender).getSkin().getModel()))
                            .putInt("Cost", cost)
                            .putString("KitName", name);

                    AnnihilationNPC npc = new AnnihilationNPC(((Player) sender).getLevel().getChunk((int) ((Player) sender).x >> 4, (int) ((Player) sender).z >> 4), nbt);
                    //npc.setSkin(((Player) sender).getSkin());
                    npc.getInventory().setItemInHand(((Player) sender).getInventory().getItemInHand());

                    if (((Player) sender).isSneaking()) {
                        npc.setDataFlag(EntityHuman.DATA_PLAYER_FLAGS, Entity.DATA_FLAG_SNEAKING, true);
                    }
                    //npc.setCost(cost);
                    //npc.setName(name);
                    //npc.getInventory().setArmorContents(((Player) sender).getInventory().getArmorContents());
                    npc.spawnToAll();

                    break;
                case "removeall":
                    if (!sender.isOp()) {
                        break;
                    }

                    int count = 0;

                    for (Entity e : ((Player) sender).getLevel().getEntities()) {
                        if (!(e instanceof Player)) {
                            e.despawnFromAll();
                            e.close();
                            count++;
                        }
                    }

                    sender.sendMessage(MTCore.getPrefix() + TextFormat.GREEN + "Removed " + TextFormat.BLUE + count + TextFormat.GREEN + " entities");
                    break;
                case "remove":
                    if (!sender.isOp()) {
                        break;
                    }


                    break;
                case "cleantiles":
                    if (!sender.isOp()) {
                        break;
                    }

                    cleanTiles();
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

        /*$this->maps['Andorra'] = [
                "1Spawn" =>   new Vector3(183, 80, 0),
                "2Spawn" =>   new Vector3(0, 80, -183),
                "3Spawn" =>   new Vector3(0, 80, 183),
                "4Spawn" =>   new Vector3(-183, 80, 0),
                "1Nexus" =>   new Vector3(199, 101, 0),
                "2Nexus" =>   new Vector3(0, 101, -99),
                "3Nexus" =>   new Vector3(0, 101, 199),
                "4Nexus" =>   new Vector3(-199, 101, 0),
                "1Chest" =>   new Vector3(189, 80, 18),
                "2Chest" =>   new Vector3(18, 80, -189),
                "3Chest" =>   new Vector3(-18, 80, 189),
                "4Chest" =>   new Vector3(-189, 80, -18),
                "1Furnace" => new Vector3(189, 81, 10),
                "2Furnace" => new Vector3(10, 81, -189),
                "3Furnace" => new Vector3(-10, 81, 189),
                "4Furnace" => new Vector3(-189, 81, -10),
                "1Brewing" => new Vector3(190, 81, 12),
                "2Brewing" => new Vector3(12, 81, -190),
                "3Brewing" => new Vector3(-12, 81, 190),
                "4Brewing" => new Vector3(-190, 81, -112),
                //diamonds
                "diamonds" => [ new Vector3(9, 69, 11), new Vector3(11, 69, -9), new Vector3(-9, 69, -11),
                new Vector3(-11, 69, 9), new Vector3(-2, 85, 2), new Vector3(2, 85, -2), new Vector3(40, 78, 40),
                new Vector3(40, 78, -40), new Vector3(-40, 78, -40), new Vector3(-40, 78, -40), new Vector3(0, 69, 0)],
        "bosses" => [1 => ['name' => '§bFerwin', 'pos' => new Vector3(-163, 91, -163), 'chest' => new Vector3(-163, 91, -156)], 2 => ['name' => '§cCelariel', 'pos' => new Vector3(163, 91, 163), 'chest' => new Vector3(163, 91, 156)]],
        "corner2" => new Vector3(200, 128, 200),
                "corner1" => new Vector3(-200, 0, -200)
        ];*/

        maps.put("Andorra", new HashMap<>());
        canyon = maps.get("Andorra");

        canyon.put("1Spawn", new Vector3(183, 80, 0));
        canyon.put("2Spawn", new Vector3(0, 80, -183));
        canyon.put("3Spawn", new Vector3(0, 80, 183));
        canyon.put("4Spawn", new Vector3(-183, 80, 0));
        canyon.put("1Nexus", new Vector3(199, 101, 0));
        canyon.put("2Nexus", new Vector3(0, 101, -99));
        canyon.put("3Nexus", new Vector3(0, 101, 199));
        canyon.put("4Nexus", new Vector3(-199, 101, 0));
        canyon.put("1Chest", new Vector3(189, 80, 18));
        canyon.put("2Chest", new Vector3(18, 80, -189));
        canyon.put("3Chest", new Vector3(-18, 80, 189));
        canyon.put("4Chest", new Vector3(-189, 80, -18));
        canyon.put("1Furnace", new Vector3(189, 81, 10));
        canyon.put("2Furnace", new Vector3(10, 81, -189));
        canyon.put("3Furnace", new Vector3(-10, 81, 189));
        canyon.put("4Furnace", new Vector3(-189, 81, -10));
        canyon.put("1EnderBrewing", new Vector3(190, 81, 12));
        canyon.put("2EnderBrewing", new Vector3(12, 81, -190));
        canyon.put("3EnderBrewing", new Vector3(-12, 81, 190));
        canyon.put("4EnderBrewing", new Vector3(-190, 81, -112));
        /*canyon.put("1Brewing",      new Vector3(248, 25, 230));
        canyon.put("1Weapons",      new Vector3(248, 26, 230));
        canyon.put("2Brewing",      new Vector3(122, 25, -121));
        canyon.put("2Weapons",      new Vector3(122, 26, -117));
        canyon.put("3Brewing",      new Vector3(-215, 25, -123));
        canyon.put("3Weapons",      new Vector3(-211, 26, -123));
        canyon.put("4Brewing",      new Vector3(120, 25, 216));
        canyon.put("4Weapons",      new Vector3(117, 26, 216));*/

        canyon.put("corner2", new Vector3(329, 128, 558));
        canyon.put("corner1", new Vector3(-69, 0, 164));

        canyon.put("diamonds", new Vector3[]{new Vector3(9, 69, 11), new Vector3(11, 69, -9), new Vector3(-9, 69, -11),
                new Vector3(-11, 69, 9), new Vector3(-2, 85, 2), new Vector3(2, 85, -2), new Vector3(40, 78, 40),
                new Vector3(40, 78, -40), new Vector3(-40, 78, -40), new Vector3(-40, 78, -40), new Vector3(0, 69, 0)});
        canyon.put("boss1name", "§bFerwin");
        canyon.put("boss1pos", new Vector3(-163, 91, -163));
        canyon.put("boss1chest", new Vector3(-163, 91, -156));

        canyon.put("boss2name", "§cCelariel");
        canyon.put("boss2pos", new Vector3(163, 91, 163));
        canyon.put("boss2chest", new Vector3(163, 91, 156));

        maps.put("Planities", new HashMap<>());
        canyon = maps.get("Planities");


        canyon.put("1Spawn", new Vector3(917, 72, -1213));
        canyon.put("2Spawn", new Vector3(1214, 72, -1223));
        canyon.put("3Spawn", new Vector3(1204, 72, -1520));
        canyon.put("4Spawn", new Vector3(907, 72, -1510));
        canyon.put("1Nexus", new Vector3(895, 53, -1201));
        canyon.put("2Nexus", new Vector3(1226, 53, -1201));
        canyon.put("3Nexus", new Vector3(1226, 53, -1532));
        canyon.put("4Nexus", new Vector3(895, 53, -1532));
        canyon.put("1Chest", new Vector3(915, 73, -1197));
        canyon.put("2Chest", new Vector3(1230, 73, -1221));
        canyon.put("3Chest", new Vector3(1206, 73, -1536));
        canyon.put("4Chest", new Vector3(891, 73, -1512));
        canyon.put("1Furnace", new Vector3(914, 73, -1197));
        canyon.put("2Furnace", new Vector3(1230, 73, -1220));
        canyon.put("3Furnace", new Vector3(1207, 73, -1536));
        canyon.put("4Furnace", new Vector3(891, 73, -1513));
        canyon.put("1EnderBrewing", new Vector3(913, 72, -1198));
        canyon.put("2EnderBrewing", new Vector3(1229, 72, -1219));
        canyon.put("3EnderBrewing", new Vector3(1208, 72, -1535));
        canyon.put("4EnderBrewing", new Vector3(892, 72, -1514));
        //signs
        canyon.put("1Brewing", new Vector3(904, 73, -1203));
        canyon.put("1Weapons", new Vector3(904, 73, -1207));
        canyon.put("2Brewing", new Vector3(1224, 73, -1210));
        canyon.put("2Weapons", new Vector3(1220, 73, -1210));
        canyon.put("3Brewing", new Vector3(1217, 73, -1530));
        canyon.put("3Weapons", new Vector3(1217, 73, -1526));
        canyon.put("4Brewing", new Vector3(897, 73, -1523));
        canyon.put("4Weapons", new Vector3(901, 73, -1523));
        //diamonds
        canyon.put("diamonds", new Vector3[]{new Vector3(1055, 72, -1354), new Vector3(1052, 71, -1366), new Vector3(1057, 68, -1372),
                new Vector3(1067, 70, -1376), new Vector3(1068, 68, -1369), new Vector3(1074, 69, -1361), new Vector3(1065, 68, -1362),
                new Vector3(1081, 55, -1361), new Vector3(1066, 58, -1358), new Vector3(1055, 56, -1377), new Vector3(1066, 58, -1371), new Vector3(1056, 67, -1358)});

        canyon.put("boss1name", "§bFerwin");
        canyon.put("boss1pos", new Vector3(1199, 37, -1367));
        canyon.put("boss1chest", new Vector3(1199, 38, -1363));

        canyon.put("boss2name", "§cCelariel");
        canyon.put("boss2pos", new Vector3(922, 37, -1366));
        canyon.put("boss2chest", new Vector3(922, 38, -1370));

        canyon.put("corner2", new Vector3(1235, 128, -1192));
        canyon.put("corner1", new Vector3(886, 0, -1541));


        maps.put("Cavern", new HashMap<>());
        canyon = maps.get("Cavern");

        canyon.put("1Spawn", new Vector3(-152, 31, -155));
        canyon.put("2Spawn", new Vector3(-137, 31, 152));
        canyon.put("3Spawn", new Vector3(170, 31, 137));
        canyon.put("4Spawn", new Vector3(155, 31, -170));
        canyon.put("1Nexus", new Vector3(-171, 44, -174));
        canyon.put("2Nexus", new Vector3(-156, 44, 171));
        canyon.put("3Nexus", new Vector3(189, 44, 156));
        canyon.put("4Nexus", new Vector3(174, 44, -189));
        canyon.put("1Chest", new Vector3(-167, 38, -170));
        canyon.put("2Chest", new Vector3(-152, 38, 167));
        canyon.put("3Chest", new Vector3(185, 38, 152));
        canyon.put("4Chest", new Vector3(170, 38, -185));
        canyon.put("1Furnace", new Vector3(-166, 38, -170));
        canyon.put("2Furnace", new Vector3(-152, 38, 166));
        canyon.put("3Furnace", new Vector3(184, 38, 152));
        canyon.put("4Furnace", new Vector3(170, 38, -184));
        canyon.put("1EnderBrewing", new Vector3(-164, 37, -170));
        canyon.put("2EnderBrewing", new Vector3(-152, 37, 164));
        canyon.put("3EnderBrewing", new Vector3(182, 37, 152));
        canyon.put("4EnderBrewing", new Vector3(170, 37, -182));
        //signs
        canyon.put("1Brewing", new Vector3(-164, 38, -170));
        canyon.put("1Weapons", new Vector3(-167, 38, -167));
        canyon.put("2Brewing", new Vector3(-152, 38, 164));
        canyon.put("2Weapons", new Vector3(-149, 38, 167));
        canyon.put("3Brewing", new Vector3(182, 38, 152));
        canyon.put("3Weapons", new Vector3(185, 38, 149));
        canyon.put("4Brewing", new Vector3(170, 38, -182));
        canyon.put("4Weapons", new Vector3(167, 38, -185));
        //diamonds
        canyon.put("diamonds", new Vector3[]{new Vector3(10, 2, -10), new Vector3(9, 6, -8), new Vector3(8, 4, -9),
                new Vector3(10, 6, -10), new Vector3(9, 1, -9), new Vector3(10, 5, -8)});

        canyon.put("boss1name", "§bFerwin");
        canyon.put("boss1pos", new Vector3(87, 83, -90));
        canyon.put("boss1chest", new Vector3(88, 85, -84));

        canyon.put("boss2name", "§cCelariel");
        canyon.put("boss2pos", new Vector3(90, 83, 69));
        canyon.put("boss2chest", new Vector3(84, 85, 70));

        canyon.put("corner2", new Vector3(193, 128, 193));
        canyon.put("corner1", new Vector3(-193, 0, -193));

        maps.put("Kingdoms", new HashMap<>());
        canyon = maps.get("Kingdoms");

        canyon.put("1Spawn", new Vector3(3, 36, -153));
        canyon.put("2Spawn", new Vector3(1, 36, 175));
        canyon.put("3Spawn", new Vector3(-162, 36, 10));
        canyon.put("4Spawn", new Vector3(166, 36, 12));
        canyon.put("1Nexus", new Vector3(3, 46, -177));
        canyon.put("2Nexus", new Vector3(1, 46, 199));
        canyon.put("3Nexus", new Vector3(-186, 46, 10));
        canyon.put("4Nexus", new Vector3(190, 46, 12));
        canyon.put("1Chest", new Vector3(-10, 36, -151));
        canyon.put("2Chest", new Vector3(14, 36, 173));
        canyon.put("3Chest", new Vector3(-160, 36, 23));
        canyon.put("4Chest", new Vector3(164, 36, -1));
        canyon.put("1Furnace", new Vector3(-6, 36, -156));
        canyon.put("2Furnace", new Vector3(10, 36, 178));
        canyon.put("3Furnace", new Vector3(-165, 36, 19));
        canyon.put("4Furnace", new Vector3(169, 36, 3));
        canyon.put("1EnderBrewing", new Vector3(-103, 73, -112));
        canyon.put("2EnderBrewing", new Vector3(-112, 73, 228));
        canyon.put("3EnderBrewing", new Vector3(228, 73, 237));
        canyon.put("4EnderBrewing", new Vector3(237, 73, -103));
        //signs
        canyon.put("1Brewing", new Vector3(248, 25, 230));
        canyon.put("1Weapons", new Vector3(248, 26, 230));
        canyon.put("2Brewing", new Vector3(122, 25, -121));
        canyon.put("2Weapons", new Vector3(122, 26, -117));
        canyon.put("3Brewing", new Vector3(-215, 25, -123));
        canyon.put("3Weapons", new Vector3(-211, 26, -123));
        canyon.put("4Brewing", new Vector3(120, 25, 216));
        canyon.put("4Weapons", new Vector3(117, 26, 216));
        //diamonds
        canyon.put("diamonds", new Vector3[]{new Vector3(-3, 6, 2), new Vector3(11, 6, 16), new Vector3(0, 2, 17),
                new Vector3(-5, 3, 15), new Vector3(-3, 6, 20), new Vector3(-5, 5, 4), new Vector3(8, 2, 6),
                new Vector3(12, 5, 7), new Vector3(9, 2, 15), new Vector3(-40, 78, -40)});

        canyon.put("boss1name", "§bFerwin");
        canyon.put("boss1pos", new Vector3(-146, 13, 156));
        canyon.put("boss1chest", new Vector3(-146, 12, 162));

        canyon.put("boss2name", "§cCelariel");
        canyon.put("boss2pos", new Vector3(150, 13, -134));
        canyon.put("boss2chest", new Vector3(150, 12, -140));

        canyon.put("corner2", new Vector3(202, 128, 211));
        canyon.put("corner1", new Vector3(-198, 0, -189));

        maps.put("Cliffs", new HashMap<>());
        canyon = maps.get("Cliffs");

        canyon.put("1Spawn", new Vector3(195, 50, 211));
        canyon.put("2Spawn", new Vector3(212, 50, -67));
        canyon.put("3Spawn", new Vector3(-68, 50, -84));
        canyon.put("4Spawn", new Vector3(-86, 50, 195));
        canyon.put("1Nexus", new Vector3(220, 64, 227));
        canyon.put("2Nexus", new Vector3(227, 64, -93));
        canyon.put("3Nexus", new Vector3(-93, 64, -100));
        canyon.put("4Nexus", new Vector3(-102, 64, 220));
        canyon.put("1Chest", new Vector3(219, 50, 200));
        canyon.put("2Chest", new Vector3(200, 50, -92));
        canyon.put("3Chest", new Vector3(-75, 50, 219));
        canyon.put("4Chest", new Vector3(-94, 50, 198));
        canyon.put("1Furnace", new Vector3(197, 50, 219));
        canyon.put("2Furnace", new Vector3(219, 50, -70));
        canyon.put("3Furnace", new Vector3(-70, 50, -92));
        canyon.put("4Furnace", new Vector3(-94, 50, 197));
        canyon.put("1EnderBrewing", new Vector3(218, 50, 199));
        canyon.put("2EnderBrewing", new Vector3(199, 50, -91));
        canyon.put("3EnderBrewing", new Vector3(-91, 50, -72));
        canyon.put("4EnderBrewing", new Vector3(-74, 50, 218));
        //signs
        canyon.put("1Brewing", new Vector3(218, 51, 200));
        canyon.put("1Weapons", new Vector3(220, 51, 200));
        canyon.put("2Brewing", new Vector3(200, 51, -91));
        canyon.put("2Weapons", new Vector3(200, 51, -93));
        canyon.put("3Brewing", new Vector3(-75, 51, 218));
        canyon.put("3Weapons", new Vector3(-75, 51, 220));
        canyon.put("4Brewing", new Vector3(120, 51, 216));
        canyon.put("4Weapons", new Vector3(117, 51, 216));
        //diamonds
        canyon.put("diamonds", new Vector3[]{new Vector3(44, 47, 79), new Vector3(42, 45, 76), new Vector3(52, 43, 75),
                new Vector3(50, 47, 86), new Vector3(41, 45, 63), new Vector3(47, 46, 58), new Vector3(53, 43, 64),
                new Vector3(52, 45, 54), new Vector3(64, 45, 55), new Vector3(65, 43, 66), new Vector3(70, 47, 59),
                new Vector3(72, 46, 65), new Vector3(73, 46, 76), new Vector3(68, 44, 80), new Vector3(62, 43, 76),
                new Vector3(63, 49, 88)});

        canyon.put("boss1name", "§bFerwin");
        canyon.put("boss1pos", new Vector3(49, 14, 213));
        canyon.put("boss1chest", new Vector3(49, 13, 219));

        canyon.put("boss2name", "§cCelariel");
        canyon.put("boss2pos", new Vector3(63, 15, -79));
        canyon.put("boss2chest", new Vector3(58, 15, -79));

        canyon.put("corner2", new Vector3(255, 128, 255));
        canyon.put("corner1", new Vector3(-128, 0, -128));

        maps.put("Solumque", new HashMap<>());
        canyon = maps.get("Solumque");

        canyon.put("1Spawn", new Vector3(115, 30, 125));
        canyon.put("2Spawn", new Vector3(-122, 30, 121));
        canyon.put("3Spawn", new Vector3(119, 30, -112));
        canyon.put("4Spawn", new Vector3(-118, 30, -116));
        canyon.put("1Nexus", new Vector3(113, 16, 119));
        canyon.put("2Nexus", new Vector3(-116, 16, 119));
        canyon.put("3Nexus", new Vector3(113, 16, -110));
        canyon.put("4Nexus", new Vector3(-116, 16, -110));
        canyon.put("1Chest", new Vector3(114, 30, 134));
        canyon.put("2Chest", new Vector3(-131, 30, 120));
        canyon.put("3Chest", new Vector3(128, 30, -111));
        canyon.put("4Chest", new Vector3(-117, 30, -125));
        canyon.put("1Furnace", new Vector3(114, 30, 133));
        canyon.put("2Furnace", new Vector3(-130, 30, 120));
        canyon.put("3Furnace", new Vector3(127, 30, -111));
        canyon.put("4Furnace", new Vector3(-117, 30, -124));
        canyon.put("1EnderBrewing", new Vector3(111, 30, 133));
        canyon.put("2EnderBrewing", new Vector3(-130, 30, 117));
        canyon.put("3EnderBrewing", new Vector3(127, 30, -108));
        canyon.put("4EnderBrewing", new Vector3(-114, 30, -124));
        //signs
        canyon.put("1Brewing", new Vector3(126, 29, 130));
        canyon.put("1Weapons", new Vector3(124, 29, 132));
        canyon.put("2Brewing", new Vector3(-127, 29, 132));
        canyon.put("2Weapons", new Vector3(-129, 29, 130));
        canyon.put("4Brewing", new Vector3(-129, 29, -121));
        canyon.put("4Weapons", new Vector3(-127, 29, -123));
        canyon.put("3Brewing", new Vector3(124, 29, -123));
        canyon.put("3Weapons", new Vector3(126, 29, -121));
        //diamonds
        canyon.put("diamonds", new Vector3[]{new Vector3(-2, 13, 15), new Vector3(-11, 14, 6), new Vector3(-2, 12, -2),
                new Vector3(7, 14, 3), new Vector3(-4, 11, 4), new Vector3(-1, 11, 8), new Vector3(-27, 21, -25),
                new Vector3(-31, 21, 30), new Vector3(24, 21, 34), new Vector3(28, 21, -21)});

        canyon.put("boss1name", "§bFerwin");
        canyon.put("boss1pos", new Vector3(-116, 34, 4));
        canyon.put("boss1chest", new Vector3(-116, 35, -2));

        canyon.put("boss2name", "§cCelariel");
        canyon.put("boss2pos", new Vector3(113, 34, 5));
        canyon.put("boss2chest", new Vector3(113, 35, 11));

        canyon.put("corner2", new Vector3(130, 128, 136));
        canyon.put("corner1", new Vector3(-133, 0, -127));

        maps.put("Districts", new HashMap<>());
        canyon = maps.get("Districts");

        canyon.put("1Spawn", new Vector3(314, 87, -54));
        canyon.put("2Spawn", new Vector3(-55, 87, -43));
        canyon.put("3Spawn", new Vector3(-44, 87, 326));
        canyon.put("4Spawn", new Vector3(325, 87, 315));
        canyon.put("1Nexus", new Vector3(331, 100, -54));
        canyon.put("2Nexus", new Vector3(-55, 100, -60));
        canyon.put("3Nexus", new Vector3(-61, 100, 326));
        canyon.put("4Nexus", new Vector3(325, 100, 332));
        canyon.put("1Chest", new Vector3(317, 88, -47));
        canyon.put("2Chest", new Vector3(-48, 88, -46));
        canyon.put("3Chest", new Vector3(-47, 88, 319));
        canyon.put("4Chest", new Vector3(318, 88, 318));
        canyon.put("1Furnace", new Vector3(317, 88, -48));
        canyon.put("2Furnace", new Vector3(-49, 88, -46));
        canyon.put("3Furnace", new Vector3(-47, 88, 320));
        canyon.put("4Furnace", new Vector3(319, 88, 318));
        canyon.put("1EnderBrewing", new Vector3(317, 88, -49));
        canyon.put("2EnderBrewing", new Vector3(-50, 88, -46));
        canyon.put("3EnderBrewing", new Vector3(-47, 88, 321));
        canyon.put("4EnderBrewing", new Vector3(320, 88, 318));
        //signs
        canyon.put("1Brewing", new Vector3(310, 83, -47));
        canyon.put("1Weapons", new Vector3(306, 83, -47));
        canyon.put("2Brewing", new Vector3(-48, 83, -39));
        canyon.put("2Weapons", new Vector3(-48, 83, -35));
        canyon.put("4Brewing", new Vector3(318, 83, 268));
        canyon.put("4Weapons", new Vector3(318, 83, 307));
        canyon.put("3Brewing", new Vector3(-40, 83, 319));
        canyon.put("3Weapons", new Vector3(-36, 83, 319));
        //diamonds
        canyon.put("diamonds", new Vector3[]{new Vector3(149, 81, 136), new Vector3(157, 78, 158), new Vector3(113, 78, 158),
                new Vector3(113, 78, 114), new Vector3(157, 78, 114), new Vector3(135, 80, 122), new Vector3(121, 79, 136),
                new Vector3(131, 80, 140), new Vector3(135, 81, 135), new Vector3(142, 85, 136), new Vector3(139, 87, 137),
                new Vector3(134, 92, 135), new Vector3(129, 90, 137), new Vector3(131, 96, 136)});

        canyon.put("boss1name", "§bFerwin");
        canyon.put("boss1pos", new Vector3(-7, 77, 122));
        canyon.put("boss1chest", new Vector3(-7, 77, 117));

        canyon.put("boss2name", "§cCelariel");
        canyon.put("boss2pos", new Vector3(227, 77, 150));
        canyon.put("boss2chest", new Vector3(277, 77, 155));

        canyon.put("corner2", new Vector3(335, 128, 336));
        canyon.put("corner1", new Vector3(-65, 0, -64));

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
        AnnihilationNPC entity = (AnnihilationNPC) e.getEntity();
        Player p = (Player) e.getDamager();

        Kit kit = Kit.valueOf(entity.getKitName().toUpperCase());

        Item item = p.getInventory().getItemInHand();

        if (item.getId() == Item.GOLD_INGOT) {
            new BuyKitQuery(this, p.getName(), kit, BuyKitQuery.ACTION_BUY);
        } else {
            new BuyKitQuery(this, p.getName(), kit, BuyKitQuery.ACTION_INFO);
            //p.sendMessage(kitMessages.get(name));
            //p.sendMessage(getPrefix() + TextFormat.YELLOW + ">> To buy this kit use a gold ingot");
        }
    }

    public static Annihilation getInstance() {
        return instance;
    }

    public Arena getArena(String name) {
        return ins.get(name);
    }

    private void cleanTiles() {
        for (Map.Entry e : maps.entrySet()) {
            String name = (String) e.getKey();

            getServer().loadLevel(name);
            Level level = getServer().getLevelByName(name);

            Vector3 v = new Vector3();

            if (level != null) {
                int count = 0;
                int countB = 0;

                List<Integer> metaBlocks = new ArrayList<>();

                level.setAutoSave(true);

                Map<String, Object> map = (Map<String, Object>) e.getValue();
                Vector3 pos1 = (Vector3) map.get("corner1");
                Vector3 pos2 = (Vector3) map.get("corner2");

                int minX = (int) Math.min(pos1.x, pos2.x);
                int minZ = (int) Math.min(pos1.z, pos2.z);
                int maxX = (int) Math.max(pos1.x, pos2.x);
                int maxZ = (int) Math.max(pos1.z, pos2.z);

                int prevX = Integer.MAX_VALUE;
                int prevZ = Integer.MAX_VALUE;

                for (int x = minX; x <= maxX; x++) {
                    for (int z = minZ; z <= maxZ; z++) {
                        for(int y = 0; y <= 128; y++){
                            int meta = level.getBlockDataAt(x, y, z);
                            int id = level.getBlockIdAt(x, y, z);

                            if(Block.list[id] == null){
                                countB++;
                                level.setBlock(v.setComponents(x, y, z), new BlockAir(), true, false);
                            } else if(meta != 0 && !BlockList.metadatable.contains(id)) {
                                if(!metaBlocks.contains(id)) {
                                    metaBlocks.add(id);
                                }

                                countB++;
                                level.setBlock(v.setComponents(x, y, z), new BlockAir(), true, false);
                            }

                            /*if(Block.list[id] == null){
                                level.setBlock(v.setComponents(x, y, z), new BlockAir(), true, false);
                                countB++;
                            }*/
                        }

                        /*if (x >> 4 == prevX && z >> 4 == prevZ) {
                            continue;
                        }

                        prevX = x >> 4;
                        prevZ = z >> 4;

                        FullChunk chunk = level.getChunk(prevX, prevZ);

                        for (BlockEntity be : new HashMap<>(chunk.getBlockEntities()).values()) {
                            be.close();
                            count++;
                        }*/
                    }
                }

                level.save();
                System.out.println(name + ": " + count + " blockentities");
                System.out.println(name + ": " + countB + " blocks");

                String blocks = name + ": meta blocks: ";

                for(Integer i : metaBlocks){
                    blocks += i + "|";
                }

                System.out.println(blocks);
            }
        }
    }
}