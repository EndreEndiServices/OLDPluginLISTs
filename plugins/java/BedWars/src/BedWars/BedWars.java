package BedWars;

import BedWars.Arena.Arena;
import BedWars.Entity.SpecialItem;
import BedWars.Entity.TNTShip;
import BedWars.Entity.Villager;
import BedWars.Entity.WinParticle;
import BedWars.MySQL.JoinQuery;
import BedWars.MySQL.ShowStatsQuery;

import BedWars.Utils.Items;
import GTCore.MTCore;

import cn.nukkit.entity.Entity;
import cn.nukkit.event.EventHandler;
import cn.nukkit.event.EventPriority;
import cn.nukkit.event.player.PlayerInteractEvent;
import cn.nukkit.item.Item;
import cn.nukkit.level.Level;
import cn.nukkit.plugin.PluginBase;
import cn.nukkit.utils.TextFormat;
import cn.nukkit.Player;
import cn.nukkit.level.Position;
import cn.nukkit.math.Vector3;
import cn.nukkit.event.player.PlayerJoinEvent;
import cn.nukkit.event.player.PlayerQuitEvent;
import cn.nukkit.event.Listener;
import cn.nukkit.command.Command;
import cn.nukkit.command.CommandSender;

import java.lang.*;
import java.util.Calendar;
import java.util.Date;
import java.util.HashMap;

public class BedWars extends PluginBase implements Listener {

    public HashMap<String, HashMap<String, Vector3>> maps = new HashMap<>();

    public Level level;

    public Position mainLobby;

    public HashMap<String, HashMap<String, Vector3>> arenas = new HashMap<>();

    public HashMap<String, Arena> ins = new HashMap<>();

    private long loadTime = 0;

    public MTCore mtcore;

    private static BedWars instance;

    public void onLoad() {
        instance = this;
        loadTime = System.currentTimeMillis();
    }

    @Override
    public void onEnable() {
        this.level = this.getServer().getDefaultLevel();
        this.mtcore = (MTCore) this.getServer().getPluginManager().getPlugin("GTCore");
        this.setMapsData();
        this.setArenasData();
        this.registerArena("bw-1");
        this.registerArena("bw-2");
        this.registerArena("bw-3");
        this.registerArena("bw-4");
        this.registerArena("bw-5");
        this.registerArena("bw-6");
        this.mainLobby = this.level.getSpawnLocation();
        this.getServer().getPluginManager().registerEvents(this, this);
        this.level.setTime(5000);
        this.level.stopTime();

        Entity.registerEntity("SpecialItem", SpecialItem.class);
        Entity.registerEntity("Villager", Villager.class);
        Entity.registerEntity("WinParticle", WinParticle.class);
        //Entity.registerEntity("TNTShip", TNTShip.class);
        Item.addCreativeItem(Item.get(Item.SPAWN_EGG, 15));

        getServer().getScheduler().scheduleRepeatingTask(new Runnable() {
            @Override
            public void run() {
                int hour = Calendar.getInstance().get(Calendar.HOUR_OF_DAY);

                System.out.println("hour: " + hour);

                if ((hour == 3 || hour == 4) && System.currentTimeMillis() - loadTime > 8000000) {
                    getServer().shutdown();
                }
            }
        }, 2000);
    }

    @Override
    public void onDisable() {
        for (Arena arena : this.ins.values()) {
            if (arena.game == 1) {
                arena.stopGame();
            }
        }
    }

    public void registerArena(String arena) {
        Arena a = new Arena(arena, this);
        this.getServer().getPluginManager().registerEvents(a, this);
        this.ins.put(arena, a);
    }

    public static String getPrefix() {
        return "§l§0[ §cBed§fWars§0 ] §r§f ";
    }

    public void setArenasData() {
        HashMap<String, Vector3> bw1 = new HashMap<>();
        bw1.put("sign", new Vector3(127, 38, 90));
        bw1.put("1sign", new Vector3(-198, 27, -21));
        bw1.put("2sign", new Vector3(-198, 27, -23));
        bw1.put("3sign", new Vector3(-196, 27, -25));
        bw1.put("4sign", new Vector3(-194, 27, -25));
        bw1.put("lobby", new Vector3(-158, 26, -17));
        this.arenas.put("bw-1", bw1);


        HashMap<String, Vector3> bw2 = new HashMap<>();
        bw2.put("sign", new Vector3(128, 38, 90));
        bw2.put("1sign", new Vector3(-198, 27, -21));
        bw2.put("2sign", new Vector3(-198, 27, -23));
        bw2.put("3sign", new Vector3(-196, 27, -25));
        bw2.put("4sign", new Vector3(-194, 27, -25));
        bw2.put("lobby", new Vector3(-158, 26, -17));
        this.arenas.put("bw-2", bw2);

        HashMap<String, Vector3> bw3 = new HashMap<>();
        bw3.put("sign", new Vector3(129, 38, 90));
        bw3.put("1sign", new Vector3(-198, 27, -21));
        bw3.put("2sign", new Vector3(-198, 27, -23));
        bw3.put("3sign", new Vector3(-196, 27, -25));
        bw3.put("4sign", new Vector3(-194, 27, -25));
        bw3.put("lobby", new Vector3(-158, 26, -17));
        this.arenas.put("bw-3", bw3);

        HashMap<String, Vector3> bw4 = new HashMap<>();
        bw4.put("sign", new Vector3(127, 37, 90));
        bw4.put("1sign", new Vector3(-198, 27, -21));
        bw4.put("2sign", new Vector3(-198, 27, -23));
        bw4.put("3sign", new Vector3(-196, 27, -25));
        bw4.put("4sign", new Vector3(-194, 27, -25));
        bw4.put("lobby", new Vector3(-158, 26, -17));
        this.arenas.put("bw-4", bw4);

        HashMap<String, Vector3> bw5 = new HashMap<>();
        bw5.put("sign", new Vector3(128, 37, 90));
        bw5.put("1sign", new Vector3(-198, 27, -21));
        bw5.put("2sign", new Vector3(-198, 27, -23));
        bw5.put("3sign", new Vector3(-196, 27, -25));
        bw5.put("4sign", new Vector3(-194, 27, -25));
        bw5.put("lobby", new Vector3(-158, 26, -17));
        this.arenas.put("bw-5", bw5);

        HashMap<String, Vector3> bw6 = new HashMap<>();
        bw6.put("sign", new Vector3(129, 37, 90));
        bw6.put("1sign", new Vector3(-198, 27, -21));
        bw6.put("2sign", new Vector3(-198, 27, -23));
        bw6.put("3sign", new Vector3(-196, 27, -25));
        bw6.put("4sign", new Vector3(-194, 27, -25));
        bw6.put("lobby", new Vector3(-158, 26, -17));
        this.arenas.put("bw-6", bw6);
    }

    public void setMapsData() {
        HashMap<String, Vector3> kingdoms = new HashMap<>();
        //kingdoms.put("world", this.getServer().getLevelByName("Kingdoms"));
        kingdoms.put("1spawn", new Vector3(-19, 10, 386));
        kingdoms.put("1bed", new Vector3(-21, 14, 386));
        kingdoms.put("1bed2", new Vector3(-22, 14, 386));
        kingdoms.put("1bronze", new Vector3(-6, 8, 400));
        kingdoms.put("1iron", new Vector3(70, 9, 390));
        kingdoms.put("1gold", new Vector3(106, 9, 390));
        kingdoms.put("2spawn", new Vector3(237, 10, 394));
        kingdoms.put("2bed", new Vector3(239, 14, 394));
        kingdoms.put("2bed2", new Vector3(140, 14, 394));
        kingdoms.put("2bronze", new Vector3(224, 8, 380));
        kingdoms.put("2iron", new Vector3(148, 9, 390));
        kingdoms.put("2gold", new Vector3(112, 9, 390));
        kingdoms.put("3spawn", new Vector3(105, 10, 518));
        kingdoms.put("3bed", new Vector3(105, 14, 520));
        kingdoms.put("3bed2", new Vector3(105, 14, 521));
        kingdoms.put("3bronze", new Vector3(119, 8, 505));
        kingdoms.put("3iron", new Vector3(109, 9, 429));
        kingdoms.put("3gold", new Vector3(109, 9, 393));
        kingdoms.put("4spawn", new Vector3(113, 10, 262));
        kingdoms.put("4bed", new Vector3(113, 14, 260));
        kingdoms.put("4bed2", new Vector3(113, 14, 259));
        kingdoms.put("4bronze", new Vector3(99, 8, 275));
        kingdoms.put("4iron", new Vector3(109, 9, 351));
        kingdoms.put("4gold", new Vector3(109, 9, 387));
        this.maps.put("Kingdoms", kingdoms);

        HashMap<String, Vector3> chinese = new HashMap<>();
        //chinese.put("world", this.getServer().getLevelByName("Chinese"));
        chinese.put("1spawn", new Vector3(-1028, 121, 237));
        chinese.put("1bed", new Vector3(-1038, 121, 237));
        chinese.put("1bed2", new Vector3(-1039, 121, 237));
        chinese.put("1bronze", new Vector3(-1032, 121, 237));
        chinese.put("1iron", new Vector3(-1022, 119, 235));
        chinese.put("1gold", new Vector3(-951, 109, 238));
        chinese.put("2spawn", new Vector3(-872, 121, 237));
        chinese.put("2bed", new Vector3(-862, 121, 237));
        chinese.put("2bed2", new Vector3(-861, 121, 237));
        chinese.put("2bronze", new Vector3(-868, 121, 237));
        chinese.put("2iron", new Vector3(-878, 119, 239));
        chinese.put("2gold", new Vector3(-948, 109, 237));
        chinese.put("3spawn", new Vector3(-950, 121, 315));
        chinese.put("3bed", new Vector3(-950, 121, 325));
        chinese.put("3bed2", new Vector3(-950, 121, 326));
        chinese.put("3bronze", new Vector3(-950, 121, 319));
        chinese.put("3iron", new Vector3(-952, 119, 309));
        chinese.put("3gold", new Vector3(-949, 109, 239));
        chinese.put("4spawn", new Vector3(-950, 121, 159));
        chinese.put("4bed", new Vector3(-950, 121, 149));
        chinese.put("4bed2", new Vector3(-950, 121, 148));
        chinese.put("4bronze", new Vector3(-950, 121, 155));
        chinese.put("4iron", new Vector3(-948, 118, 165));
        chinese.put("4gold", new Vector3(-950, 108, 236));
        this.maps.put("Chinese", chinese);

        HashMap<String, Vector3> phizzle = new HashMap<>();
        //phizzle.put("world", this.getServer().getLevelByName("Phizzle"));
        phizzle.put("1spawn", new Vector3(-6, 111, 1));
        phizzle.put("1bed", new Vector3(0, 111, -4));
        phizzle.put("1bed2", new Vector3(-1, 111, -4));
        phizzle.put("1bronze", new Vector3(-8, 111, 4));
        phizzle.put("1iron", new Vector3(-9, 110, -5));
        phizzle.put("1gold", new Vector3(-1, 111, 53));
        phizzle.put("2spawn", new Vector3(51, 111, 56));
        phizzle.put("2bed", new Vector3(56, 111, 62));
        phizzle.put("2bed2", new Vector3(56, 111, 61));
        phizzle.put("2bronze", new Vector3(48, 111, 54));
        phizzle.put("2iron", new Vector3(57, 110, 53));
        phizzle.put("2gold", new Vector3(-1, 111, 61));
        phizzle.put("3spawn", new Vector3(-61, 111, 58));
        phizzle.put("3bed", new Vector3(-66, 111, 53));
        phizzle.put("3bed2", new Vector3(-66, 111, 52));
        phizzle.put("3bronze", new Vector3(-58, 111, 60));
        phizzle.put("3iron", new Vector3(-67, 110, 61));
        phizzle.put("3gold", new Vector3(-10, 111, 53));
        phizzle.put("4spawn", new Vector3(-4, 111, 113));
        phizzle.put("4bed", new Vector3(-9, 111, 118));
        phizzle.put("4bed2", new Vector3(-10, 111, 118));
        phizzle.put("4bronze", new Vector3(-2, 111, 110));
        phizzle.put("4iron", new Vector3(-1, 110, 119));
        phizzle.put("4gold", new Vector3(-10, 111, 61));
        this.maps.put("Phizzle", phizzle);

        HashMap<String, Vector3> stw5 = new HashMap<>();
        //stw5.put("world", this.getServer().getLevelByName("STW5"));
        stw5.put("1spawn", new Vector3(-349, 35, 257));
        stw5.put("1bed", new Vector3(-330, 38, 255));
        stw5.put("1bed2", new Vector3(-330, 38, 254));
        stw5.put("1bronze", new Vector3(-345, 34, 260));
        stw5.put("1iron", new Vector3(-346, 34, 214));
        stw5.put("1gold", new Vector3(-339, 40, 181));
        stw5.put("2spawn", new Vector3(-343, 35, 91));
        stw5.put("2bed", new Vector3(-362, 38, 93));
        stw5.put("2bed2", new Vector3(-362, 38, 94));
        stw5.put("2bronze", new Vector3(-347, 34, 88));
        stw5.put("2iron", new Vector3(-346, 34, 134));
        stw5.put("2gold", new Vector3(-353, 40, 167));
        stw5.put("3spawn", new Vector3(-429, 35, 171));
        stw5.put("3bed", new Vector3(-427, 38, 190));
        stw5.put("3bed2", new Vector3(-426, 38, 190));
        stw5.put("3bronze", new Vector3(-432, 34, 175));
        stw5.put("3iron", new Vector3(-386, 34, 174));
        stw5.put("3gold", new Vector3(-353, 40, 181));
        stw5.put("4spawn", new Vector3(-263, 35, 177));
        stw5.put("4bed", new Vector3(-265, 38, 158));
        stw5.put("4bed2", new Vector3(-266, 38, 158));
        stw5.put("4bronze", new Vector3(-260, 34, 173));
        stw5.put("4iron", new Vector3(-306, 34, 174));
        stw5.put("4gold", new Vector3(-339, 40, 167));
        this.maps.put("STW5", stw5);

        HashMap<String, Vector3> bw1 = new HashMap<>();
        //bw1.put("world", this.getServer().getLevelByName("BedWars1"));
        bw1.put("1spawn", new Vector3(-1267, 98, -981));
        bw1.put("1bed", new Vector3(-1267, 102, -986));
        bw1.put("1bed2", new Vector3(-1267, 102, -985));
        bw1.put("1bronze", new Vector3(-1267, 98, -983));
        bw1.put("1iron", new Vector3(-1302, 98, -950));
        bw1.put("1gold", new Vector3(-1267, 98, -917));
        bw1.put("2spawn", new Vector3(-1267, 98, -849));
        bw1.put("2bed", new Vector3(-1267, 102, -844));
        bw1.put("2bed2", new Vector3(-1267, 102, -845));
        bw1.put("2bronze", new Vector3(-1267, 98, -847));
        bw1.put("2iron", new Vector3(-1232, 98, -880));
        bw1.put("2gold", new Vector3(-1267, 98, -913));
        bw1.put("3spawn", new Vector3(-1333, 98, -915));
        bw1.put("3bed", new Vector3(-1338, 102, -915));
        bw1.put("3bed2", new Vector3(-1337, 102, -915));
        bw1.put("3bronze", new Vector3(-1335, 98, -915));
        bw1.put("3iron", new Vector3(-1302, 98, -880));
        bw1.put("3gold", new Vector3(-1269, 98, -915));
        bw1.put("4spawn", new Vector3(-1201, 98, -915));
        bw1.put("4bed", new Vector3(-1196, 102, -915));
        bw1.put("4bed2", new Vector3(-1197, 102, -915));
        bw1.put("4bronze", new Vector3(-1199, 98, -915));
        bw1.put("4iron", new Vector3(-1232, 98, -950));
        bw1.put("4gold", new Vector3(-1265, 98, -915));
        this.maps.put("BedWars1", bw1);

        HashMap<String, Vector3> bw2 = new HashMap<>();
        //bw2.put("world", this.getServer().getLevelByName("BedWars2"));
        bw2.put("1spawn", new Vector3(353, 39, 630));
        bw2.put("1bed", new Vector3(353, 39, 627));
        bw2.put("1bed2", new Vector3(353, 39, 626));
        bw2.put("1bronze", new Vector3(353, 39, 640));
        bw2.put("1iron", new Vector3(351, 39, 639));
        bw2.put("1gold", new Vector3(354, 40, 540));
        bw2.put("2spawn", new Vector3(353, 39, 446));
        bw2.put("2bed", new Vector3(353, 39, 449));
        bw2.put("2bed2", new Vector3(353, 39, 450));
        bw2.put("2bronze", new Vector3(353, 39, 436));
        bw2.put("2iron", new Vector3(355, 39, 437));
        bw2.put("2gold", new Vector3(351, 40, 536));
        bw2.put("3spawn", new Vector3(445, 39, 538));
        bw2.put("3bed", new Vector3(442, 39, 538));
        bw2.put("3bed2", new Vector3(441, 39, 538));
        bw2.put("3bronze", new Vector3(455, 39, 538));
        bw2.put("3iron", new Vector3(454, 39, 540));
        bw2.put("3gold", new Vector3(355, 40, 536));
        bw2.put("4spawn", new Vector3(261, 39, 538));
        bw2.put("4bed", new Vector3(264, 39, 538));
        bw2.put("4bed2", new Vector3(265, 39, 538));
        bw2.put("4bronze", new Vector3(251, 39, 538));
        bw2.put("4iron", new Vector3(252, 39, 536));
        bw2.put("4gold", new Vector3(351, 40, 540));
        this.maps.put("BedWars2", bw2);

        HashMap<String, Vector3> nether = new HashMap<>();
        //nether.put("world", this.getServer().getLevelByName("Nether"));
        nether.put("1spawn", new Vector3(178, 64, 746));
        nether.put("1bed", new Vector3(178, 71, 735));
        nether.put("1bed2", new Vector3(178, 71, 734));
        nether.put("1bronze", new Vector3(174, 64, 740));
        nether.put("1iron", new Vector3(182, 64, 740));
        nether.put("1gold", new Vector3(178, 65, 793));
        nether.put("2spawn", new Vector3(230, 64, 798));
        nether.put("2bed", new Vector3(241, 71, 798));
        nether.put("2bed2", new Vector3(242, 71, 798));
        nether.put("2bronze", new Vector3(236, 64, 794));
        nether.put("2iron", new Vector3(236, 64, 802));
        nether.put("2gold", new Vector3(182, 65, 799));
        nether.put("3spawn", new Vector3(178, 64, 850));
        nether.put("3bed", new Vector3(178, 71, 861));
        nether.put("3bed2", new Vector3(178, 71, 862));
        nether.put("3bronze", new Vector3(182, 64, 856));
        nether.put("3iron", new Vector3(174, 64, 856));
        nether.put("3gold", new Vector3(178, 65, 803));
        nether.put("4spawn", new Vector3(126, 64, 798));
        nether.put("4bed", new Vector3(115, 71, 798));
        nether.put("4bed2", new Vector3(114, 71, 798));
        nether.put("4bronze", new Vector3(120, 64, 802));
        nether.put("4iron", new Vector3(120, 64, 794));
        nether.put("4gold", new Vector3(173, 65, 799));
        this.maps.put("Nether", nether);
    }

    @EventHandler
    public void onJoin(PlayerJoinEvent e) {
        Player p = e.getPlayer();
        new JoinQuery(this, p.getName());
    }

    @Override
    public boolean onCommand(CommandSender sd, Command cmd, String label, String[] args) {
        if (sd instanceof Player) {
            Player sender = (Player) sd;
            Arena arena = this.getPlayerArena(sender);

            if (arena != null) {
                switch (cmd.getName().toLowerCase()) {
                    case "blue":
                        if (arena.game == 1) {
                            break;
                        }
                        arena.addToTeam(sender, 1);
                        break;
                    case "red":
                        if (arena.game == 1) {
                            break;
                        }
                        arena.addToTeam(sender, 2);
                        break;
                    case "yellow":
                        if (arena.game == 1) {
                            break;
                        }
                        arena.addToTeam(sender, 3);
                        break;
                    case "green":
                        if (arena.game == 1) {
                            break;
                        }
                        arena.addToTeam(sender, 4);
                        break;
                    case "lobby":
                        arena.leaveArena(sender);
                        sender.getInventory().clearAll();
                        break;
                    case "stats":
                        new ShowStatsQuery(sender.getName());
                        break;
                    case "bw":
                        if (!sender.isOp()) {
                            //sender.sendMessage(TextFormat.GRAY+"");
                            break;
                        }
                        Integer c = args.length;
                        if (c < 1) {
                            return false;
                        }
                        switch (args[0]) {
                            case "start":
                                arena.selectMap(true);
                                //arena.startGame();
                                break;
                            case "stop":
                                arena.stopGame();
                                break;
                        }
                        break;
                    case "vote":
                        if (args.length != 1) {
                            sender.sendMessage(BedWars.getPrefix() + TextFormat.GRAY + "use " + TextFormat.YELLOW + "/vote " + TextFormat.GRAY + "[" + TextFormat.YELLOW + "map" + TextFormat.GRAY + "]");
                            break;
                        }
                        arena.votingManager.onVote(sender, args[0].toLowerCase());
                        break;
                }
            } else {
                switch (cmd.getName().toLowerCase()) {
                    case "lobby":
                        sender.teleport(this.mainLobby);
                        sender.getInventory().clearAll();
                        break;
                }
            }
        }
        return true;
    }

    public Arena getPlayerArena(Player p) {
        for (Arena arena : this.ins.values()) {
            if (arena.inArena(p) || arena.isSpectator(p)) {
                return arena;
            }
        }
        return null;
    }

    public Arena getArena(String arena) {
        if (this.ins.containsKey(arena)) {
            return this.ins.get(arena);
        }
        return null;
    }

    public static BedWars getInstance() {
        return instance;
    }

    /*@EventHandler
    public void onInteract(PlayerInteractEvent e){
        Player p = e.getPlayer();

        if(!p.isOp()){
            return;
        }

        Item item = p.getInventory().getItemInHand();

        if(item.getId() == Item.SPAWN_EGG && item.getDamage() ==)
    }*/
}