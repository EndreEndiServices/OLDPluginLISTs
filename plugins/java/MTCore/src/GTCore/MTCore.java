package GTCore;

import Annihilation.Annihilation;
import Annihilation.Entity.AnnihilationNPC;
import GTCore.Generator.EmptyGenerator;
import GTCore.Mysql.*;
import GTCore.Object.PlayerData;
import GTCore.Task.MessageTask;
import GTCore.Task.NoCheatTask;
import cn.nukkit.Player;
import cn.nukkit.block.Block;
import cn.nukkit.block.BlockSlab;
import cn.nukkit.block.BlockStairs;
import cn.nukkit.command.Command;
import cn.nukkit.command.CommandSender;
import cn.nukkit.command.ConsoleCommandSender;
import cn.nukkit.entity.Entity;
import cn.nukkit.entity.item.EntityItem;
import cn.nukkit.entity.item.EntityXPOrb;
import cn.nukkit.entity.projectile.EntityArrow;
import cn.nukkit.event.EventHandler;
import cn.nukkit.event.EventPriority;
import cn.nukkit.event.Listener;
import cn.nukkit.event.TextContainer;
import cn.nukkit.event.block.BlockBreakEvent;
import cn.nukkit.event.block.BlockPlaceEvent;
import cn.nukkit.event.entity.EntityDamageByEntityEvent;
import cn.nukkit.event.entity.EntityDamageEvent;
import cn.nukkit.event.entity.EntityShootBowEvent;
import cn.nukkit.event.level.WeatherChangeEvent;
import cn.nukkit.event.player.*;
import cn.nukkit.event.server.DataPacketReceiveEvent;
import cn.nukkit.event.server.QueryRegenerateEvent;
import cn.nukkit.item.Item;
import cn.nukkit.level.Level;
import cn.nukkit.level.Location;
import cn.nukkit.level.Position;
import cn.nukkit.level.generator.Generator;
import cn.nukkit.level.particle.FloatingTextParticle;
import cn.nukkit.math.AxisAlignedBB;
import cn.nukkit.math.Vector2;
import cn.nukkit.math.Vector3;
import cn.nukkit.network.protocol.PlayerActionPacket;
import cn.nukkit.network.protocol.ProtocolInfo;
import cn.nukkit.network.protocol.SetSpawnPositionPacket;
import cn.nukkit.plugin.Plugin;
import cn.nukkit.plugin.PluginBase;
import cn.nukkit.potion.Effect;
import cn.nukkit.utils.TextFormat;

import java.lang.reflect.Array;
import java.util.*;
import java.util.Map.Entry;

public class MTCore extends PluginBase implements Listener {
    //public MySQLManager mysqlmgr;
    //public Auth authmgr;

    public HashMap<String, PlayerData> players = new HashMap<>();

    public Level level;
    public Position lobby;

    public Annihilation anni;
    public Plugin bedwars;
    public Plugin survival;

    private static MTCore instance;

    public static String serverFullMessage;

    private static FloatingTextParticle[] particles = new FloatingTextParticle[20];

    @Override
    public void onLoad() {
        instance = this;
        serverFullMessage = TextFormat.RED + "Omlouvame se ale server je momentalne plny. " + TextFormat.YELLOW + "Muzes si zakoupit " + TextFormat.AQUA + "VIP rank na " + TextFormat.GREEN + "gameteam.cz/vip-a-kredity/";
        Generator.addGenerator(EmptyGenerator.class, "emptyworld", 1);
    }

    @Override
    public void onEnable() {
        this.level = this.getServer().getDefaultLevel();
        this.getServer().getPluginManager().registerEvents(this, this);
        this.level.setTime(5000);
        this.level.stopTime();
        this.lobby = this.level.getSpawnLocation();
        this.getServer().getNetwork().setName(TextFormat.RED + "Game" + TextFormat.GREEN + "Team   "/* + TextFormat.ITALIC + TextFormat.GRAY + "experimental"*/);

        //MessageTask.messages.addAll(Arrays.asList(TextFormat.AQUA + "Vote for " + TextFormat.GOLD + "MineTox " + TextFormat.AQUA + "here " + TextFormat.GREEN + "goo.gl/cMTX9s", TextFormat.AQUA + "need help? Ask " + TextFormat.GREEN + "@MineTox_MCPE " + TextFormat.AQUA + "on the twitter", TextFormat.AQUA + "You can buy VIP rank here: " + TextFormat.GREEN + "bit.ly/mtBUY"));
        MessageTask.messages.add(TextFormat.AQUA + "Pro vice informaci navstiv nase webove stranky " + TextFormat.GREEN + "www.GameTeam.cz" + TextFormat.AQUA + " nebo " + TextFormat.GREEN + "mcpe.GameTeam.cz");
        MessageTask.messages.add(TextFormat.AQUA + "Pokud se ti nas server libi, muzes nas podporit zakoupenim " + TextFormat.GREEN + "VIP" + TextFormat.AQUA + " nebo " + TextFormat.GREEN + "kreditu");

        this.getServer().getScheduler().scheduleDelayedTask(new Runnable() {
            @Override
            public void run() {
                Plugin anni = getServer().getPluginManager().getPlugin("Annihilation");
                Plugin bedwars = getServer().getPluginManager().getPlugin("BedWars");
                Plugin survival = getServer().getPluginManager().getPlugin("Survival");

                if(anni != null){
                    getServer().getNetwork().setName(TextFormat.RED + "Game" + TextFormat.GREEN + "Team   "+TextFormat.BLUE+TextFormat.ITALIC+"Annihilation");
                } else if(bedwars != null){
                    getServer().getNetwork().setName(TextFormat.RED + "Game" + TextFormat.GREEN + "Team   "+TextFormat.ITALIC+TextFormat.RED+"Bed"+TextFormat.WHITE+"Wars");
                }
                else if (survival != null){
                    getServer().getNetwork().setName(TextFormat.RED + "Game" + TextFormat.GREEN + "Team   " + TextFormat.WHITE + "Survival");
                    MTCore.removeParticles(); //Z důvodu malého lobby
                }
            }
        }, 5);

        this.getServer().getScheduler().scheduleDelayedRepeatingTask(new MessageTask(this), 2400, 2400);

        this.getServer().getScheduler().scheduleRepeatingTask(new NoCheatTask(this), 20);

        double x = lobby.getFloorX() + 0.5;
        double z = lobby.getFloorZ() + 0.5;
        double y = lobby.getFloorY();

        //█

        particles[0] = new FloatingTextParticle(new Vector3(x, y + 4.5, z), "", TextFormat.BOLD + "§e[x][x][x][x][x][x][x][x][x][x]".replace("&", "§").replace("[x]", "II"));
        particles[1] = new FloatingTextParticle(new Vector3(x, y + 4.2, z), "", TextFormat.BOLD + "&e[x][x][x][x][x][x][x][x][x][x][x][x][x][x]".replace("&", "§").replace("[x]", "II"));
        particles[2] = new FloatingTextParticle(new Vector3(x, y + 3.9, z), "", TextFormat.BOLD + "&e[x][x][x][x][x]&0[x][x][x][x][x][x]&e[x][x][x][x][x]".replace("&", "§").replace("[x]", "II"));
        particles[3] = new FloatingTextParticle(new Vector3(x, y + 3.6, z), "", TextFormat.BOLD + "§e[x][x][x][x]&0[x][x][x][x][x][x][x][x][x][x][x][x]&e[x][x][x][x]".replace("&", "§").replace("[x]", "II"));
        particles[5] = new FloatingTextParticle(new Vector3(x, y + 3.3, z), "", TextFormat.BOLD + "§e[x][x][x][x]&0[x][x][x][x][x][x][x][x][x][x][x][x][x][x][x][x]§e[x][x][x][x]".replace("&", "§").replace("[x]", "II"));
        particles[7] = new FloatingTextParticle(new Vector3(x, y + 3, z), "", TextFormat.BOLD + "§e[x][x][x]§0[x][x][x][x][x][x][x][x][x][x][x][x][x][x][x][x][x][x][x][x]§e[x][x][x]".replace("&", "§").replace("[x]", "II"));
        particles[8] = new FloatingTextParticle(new Vector3(x, y + 2.7, z), "", TextFormat.BOLD + "§e[x][x][x][x][x][x][x][x][x][x][x][x][x][x][x][x][x][x][x][x]&0[x][x][x]&f[x][x][x][x][x][x]&0[x]&f[x][x][x][x][x][x][x][x][x]&0[x]&e[x][x][x][x][x][x][x][x][x][x][x][x][x][x][x][x][x][x][x][x]".replace("&", "§").replace("[x]", "II"));
        particles[9] = new FloatingTextParticle(new Vector3(x, y + 2.4, z), "", TextFormat.BOLD + "§e[x][x][x][x][x][x][x][x][x][x][x][x][x][x][x][x][x][x]&0[x][x][x]&f[x][x][x][x][x][x][x][x][x][x][x][x][x][x][x][x][x]&0[x][x]&e[x][x][x][x][x][x][x][x][x][x][x][x][x][x][x][x][x][x]".replace("&", "§").replace("[x]", "II"));
        particles[10] = new FloatingTextParticle(new Vector3(x, y + 2.1, z), "", TextFormat.BOLD + "§e[x][x][x][x][x][x][x][x][x][x][x][x][x][x][x][x][x]&0[x][x]&f[x][x][x][x]&0[x][x]&f[x][x][x]&0[x][x][x]&f[x][x][x]&0[x][x][x][x][x]&e[x][x][x][x][x][x][x][x][x][x][x][x][x][x][x][x][x]".replace("&", "§").replace("[x]", "II"));
        particles[11] = new FloatingTextParticle(new Vector3(x, y + 1.8, z), "", TextFormat.BOLD + "§e[x][x][x][x][x][x][x][x][x][x][x][x][x][x][x][x]&0[x][x]&f[x][x][x]&0[x][x][x][x][x][x][x][x][x]&f[x][x][x]&0[x][x][x][x][x]&e[x][x][x][x][x][x][x][x][x][x][x][x][x][x][x][x]".replace("&", "§").replace("[x]", "II"));
        particles[12] = new FloatingTextParticle(new Vector3(x, y + 1.5, z), "", TextFormat.BOLD + "§e[x][x][x]§0[x][x]&f[x][x][x]&0[x][x]&f[x][x][x][x]&0[x][x][x]&f[x][x][x]&0[x][x][x][x][x]&e[x][x][x]".replace("&", "§").replace("[x]", "II"));
        particles[13] = new FloatingTextParticle(new Vector3(x, y + 1.2, z), "", TextFormat.BOLD + "§e[x][x][x][x][x][x][x][x][x][x][x][x][x][x]&0[x][x]&f[x][x][x]&0[x][x][x]&f[x][x][x]&0[x][x][x]&f[x][x][x]&0[x][x][x][x][x]&e[x][x][x][x][x][x][x][x][x][x][x][x][x][x]".replace("&", "§").replace("[x]", "II"));
        particles[14] = new FloatingTextParticle(new Vector3(x, y + 0.9, z), "", TextFormat.BOLD + "§e[x][x][x][x][x][x][x][x][x][x][x][x][x]&0[x][x]&f[x][x][x][x]&0[x][x]&f[x][x][x]&0[x][x][x]&f[x][x][x]&0[x][x][x][x][x]&e[x][x][x][x][x][x][x][x][x][x][x][x][x]".replace("&", "§").replace("[x]", "II"));
        particles[15] = new FloatingTextParticle(new Vector3(x, y + 0.6, z), "", TextFormat.BOLD + "§e[x][x][x][x][x][x][x][x][x][x][x][x]&0[x][x][x]&f[x][x][x][x][x][x][x][x]&0[x][x][x]&f[x][x][x]&0[x][x][x][x][x]&e[x][x][x][x][x][x][x][x][x][x][x][x]".replace("&", "§").replace("[x]", "II"));
        particles[16] = new FloatingTextParticle(new Vector3(x, y + 0.3, z), "", TextFormat.BOLD + "§e[x][x][x][x][x][x][x][x][x][x][x][x]&0[x][x][x]&f[x][x][x][x][x][x]&0[x][x][x][x]&f[x][x][x]&0[x][x][x][x]&e[x][x][x][x][x][x][x][x][x][x][x][x]".replace("&", "§").replace("[x]", "II"));
        particles[17] = new FloatingTextParticle(new Vector3(x, y, z), "", TextFormat.BOLD + "§e[x][x][x][x]&0[x][x][x][x][x][x][x][x][x][x][x][x][x][x][x][x][x][x]&e[x][x][x][x]".replace("&", "§").replace("[x]", "II"));
        particles[18] = new FloatingTextParticle(new Vector3(x, y - 0.3, z), "", TextFormat.BOLD + "§e[x][x][x][x]&0[x][x][x][x][x][x][x][x][x][x][x][x][x][x][x][x]&e[x][x][x][x]".replace("&", "§").replace("[x]", "II"));
        //particles[19] = new FloatingTextParticle(new Vector3(x, y - 0.12, z), "", TextFormat.BOLD+"§f&lwww.&e&lGame&f&lTeam.cz &4[COPYRIGHT]".replace("&", "§").replace("[x]", "X"));
    }

    public static void removeParticles(){
        particles = new FloatingTextParticle[]{};
    }

    public void enableItemRemoving() {
        this.getServer().getScheduler().scheduleDelayedRepeatingTask(new Runnable() {
            @Override
            public void run() {
                int count = 0;

                for (Level level : getServer().getLevels().values()) {
                    for (Entity ent : level.getEntities()) {
                        if (ent.getNetworkId() == EntityArrow.NETWORK_ID || ent.getNetworkId() == EntityItem.NETWORK_ID || ent.getNetworkId() == EntityXPOrb.NETWORK_ID) {
                            count++;
                            ent.close();
                        }
                    }
                }

                for (Player p : new ArrayList<>(getServer().getOnlinePlayers().values())) {
                    p.sendMessage(MTCore.getPrefix() + TextFormat.GREEN + "Smazano " + count + " itemu.");
                }
                getLogger().info(MTCore.getPrefix() + TextFormat.GREEN + "Smazano " + count + " itemu.");
            }
        }, 303 * 20, 304 * 20);
    }

    @EventHandler
    public void onLogin(PlayerLoginEvent e) {
        Player p = e.getPlayer();

        if (getServer().getOnlinePlayers().size() >= 80 && !p.hasPermission("gameteam.vip")) {
            e.setCancelled();
            e.setKickMessage(serverFullMessage);
            return;
        }

        /*for (Player player : new ArrayList<>(getServer().getOnlinePlayers().values())) {
            if (player.getName() == null) {
                continue;
            }

            if (player.getName().toLowerCase().equals(p.getName().toLowerCase()) && p != player) {
                e.setCancelled();
                e.setKickMessage(TextFormat.RED + "Stejny nick je jiz ve hre");
                return;
            }
        }*/

        //p.addEffect(Effect.getEffect(Effect.BLINDNESS).setDuration(999999999));

        players.put(p.getName().toLowerCase(), new PlayerData(p));

        //new LoginDataQuery(this, p.getName().toLowerCase(), p.getUniqueId().toString());
    }

    @EventHandler
    public void onJoin(PlayerJoinEvent e) {
        e.setJoinMessage(new TextContainer(""));
        Player p = e.getPlayer();

        //this.getServer().getScheduler().scheduleDelayedTask(new JoinDelay(this, p), 1);

        this.setLobby(p, true);

        //p.gamemode = 3;

        new JoinQuery(this, p.getName(), p.getAddress(), p.getUniqueId().toString());

        p.removeAllEffects();
    }

    @EventHandler
    public void onDeath(PlayerDeathEvent e) {
        Player p = e.getEntity();

        PlayerData data = getPlayerData(p);

        e.setDeathMessage(new TextContainer(""));

        if (data.isInLobby()) {
            e.setDrops(new Item[0]);
        }
    }

    @Override
    public boolean onCommand(CommandSender sender, Command cmd, String label, String[] args) {
        if (sender instanceof Player) {
            switch (cmd.getName().toLowerCase()) {
                case "tokens":
                case "cash":
                case "coins":
                case "money":
                    new DisplayMoneyQuery(this, sender.getName());
                    break;
                case "register":
                    if (getPlayerData((Player) sender).isLoggedIn()) {
                        sender.sendMessage(MTCore.getPrefix() + "§c" + "Jiz jsi prihlasen");
                    } else if (args.length != 2) {
                        sender.sendMessage(getPrefix() + TextFormat.GOLD + "Pouzij /register <heslo> <heslo znova>");
                    } else if (!args[0].equals(args[1])) {
                        sender.sendMessage(getPrefix() + TextFormat.RED + "Obe hesla musi byt stejna");
                    } else if (args[1].length() < 4 || args[1].length() > 20) {
                        sender.sendMessage(MTCore.getPrefix() + TextFormat.RED + "Delka hesla musi byt mezi 4 az 20 znaky");
                    } else {
                        new RegisterQuery(this, sender.getName(), args[0], ((Player) sender).getAddress(), ((Player) sender).getUniqueId().toString());
                    }
                    break;
                case "login":
                    if (args.length != 1) {
                        sender.sendMessage(getPrefix() + "§6" + "Pouzij /login <password>");
                    } else if (getPlayerData((Player) sender).isLoggedIn()) {
                        sender.sendMessage(MTCore.getPrefix() + "§c" + "Jiz jsi prihlasen");
                    } else {
                        new LoginQuery(this, sender.getName(), args[0], ((Player) sender).getAddress(), ((Player) sender).getUniqueId().toString());
                    }
                    break;
                case "changepassword":
                    if (args.length != 2) {
                        sender.sendMessage(getPrefix() + "§6" + "Pouzij /changepassword <password> <new password>");
                    } else if (args[1].length() < 4 || args[1].length() > 20) {
                        sender.sendMessage(MTCore.getPrefix() + TextFormat.RED + "Delka hesla musi byt mezi 4 az 20 znaky");
                    } else {
                        new ChangePasswordQuery(this, sender.getName(), args[0], args[1]);
                    }
                case "setspawn":
                    SetSpawnPositionPacket pk = new SetSpawnPositionPacket();
                    pk.x = (int) ((Player) sender).x;
                    pk.y = (int) ((Player) sender).y;
                    pk.z = (int) ((Player) sender).z;

                    ((Player) sender).dataPacket(pk);
                    break;
            }
        }

        switch (cmd.getName().toLowerCase()) {
            /*case "setrank":
                if (!sender.isOp()) {
                    sender.sendMessage(cmd.getPermissionMessage());
                } else if (args.length == 2) {
                    sender.sendMessage(args[0] + "\'s Rank updated");
                    this.setRank(args[0], args[1]);
                }
                break;*/
            case "addcoins":
                if (!sender.isOp()) {
                    sender.sendMessage(cmd.getPermissionMessage());
                } else if (args.length == 2) {
                    new AddCoinsQuery(this, args[0], Integer.valueOf(args[1]));
                }
                break;
            case "addexp":
                if (!sender.isOp()) {
                    sender.sendMessage(cmd.getPermissionMessage());
                } else if (args.length == 2) {
                    new AddExperienceQuery(this, args[0], Integer.valueOf(args[1]));
                }
                break;
        }

        return false;
    }

    /*public void checkPlayer(Player pl) {
        String p = pl.getName();
        if(!this.mysqlmgr.isPlayerRegistered(p)) {
            this.mysqlmgr.registerPlayer(p);
        } else {
            String var3 = p.toLowerCase();
            byte var4 = -1;
            switch(var3.hashCode()) {
                case -1350192949:
                    if(var3.equals("creeperface")) {
                        var4 = 0;
                    }
                    break;
                case -1349696537:
                    if(var3.equals("themike")) {
                        var4 = 2;
                    }
                    break;
                case -1079843748:
                    if(var3.equals("far7sen")) {
                        var4 = 5;
                    }
                    break;
                case -85696700:
                    if(var3.equals("creeeperface")) {
                        var4 = 1;
                    }
                    break;
                case 258476836:
                    if(var3.equals("coolmencz")) {
                        var4 = 4;
                    }
                    break;
                case 641591176:
                    if(var3.equals("shadowknightyt")) {
                        var4 = 3;
                    }
                    break;
                case 1159161119:
                    if(var3.equals("zexynekcz")) {
                        var4 = 6;
                    }
            }

            switch(var4) {
                case 0:
                    pl.setOp(true);
                    break;
                case 1:
                    pl.setOp(true);
                    break;
                case 2:
                    pl.setOp(true);
                    break;
                case 3:
                    pl.setOp(true);
                    break;
                case 4:
                    pl.setOp(true);
                    break;
                case 5:
                    pl.setOp(true);
                    break;
                case 6:
                    pl.setOp(true);
                    break;
                default:
                    pl.setOp(false);
            }

        }
    }*/

    public static String getDisplayRank(String rank) {
        String result = "";

        switch (rank.toLowerCase()) {
            case "youtuber":
                result = "§f§lYou§cTuber§r§f";
                break;
            case "vip":
                result = "§b§l[§r§bVIP§l]§r§f";
                break;
            case "vip+":
                result = "§b§l[§r§bVIP+§l]§r§f";
                break;
            case "extra":
                result = "§c§l[§r§cExtra§l]§r§f";
                break;
            case "owner":
                result = "§a§l[§r§aOwner§l]§r§f";
                break;
            case "builder":
                result = "§2§l[§r§2Builder§l]§r§f";
        }

        return result;
    }

    public static String getChatColor(String rank) {
        String color = TextFormat.DARK_AQUA;

        switch (rank.toLowerCase()) {
            case "youtuber":
                color = "§6";
                break;
            case "vip":
                color = "§b";
                break;
            case "vip+":
                color = "§b";
                break;
            case "extra":
                color = "§c";
                break;
            case "owner":
                color = "§a";
                break;
            case "builder":
                color = "§2";
        }

        return color;
    }

    private String[] ips = new String[]{"leet.cc", ".tk", "lbsg.net", "inpvp.net", "93.91.250.135", "93.91", "instantmcpe", "cookiebuild", "lbsg", "lifeboat", "rapidpe", "bladestorm", "hypixel", "inpvp"};
    //private String[] swear =

    @EventHandler(
            priority = EventPriority.NORMAL,
            ignoreCancelled = false
    )
    public void onChat(PlayerChatEvent e) {
        Player p = e.getPlayer();
        PlayerData data = getPlayerData(p);

        long time = getTime();

        e.setCancelled(true);
        if (!data.isLoggedIn()) {
            p.sendMessage(getPrefix() + "§c" + "Nejsi prihlasen");
        } else if (data.getNextChat() >= time) {
            p.sendMessage("§cVyckej prosim jeste " + (data.getNextChat() - time) + " sekund");
        } else {
            if (!p.isOp()) {
                data.setNextChat(time + 8);
            }

            if (this.inLobby(p)) {
                this.messageLobbyPlayers(e.getMessage(), p);
            } else {
                e.setCancelled(false);
            }
        }
    }

    public void messageLobbyPlayers(String message) {
        this.messageLobbyPlayers(message, null);
    }

    public void messageLobbyPlayers(String message, Player p) {
        if (p != null) {
            PlayerData data = getPlayerData(p);
            String msg = p.getDisplayName() + TextFormat.GRAY + " > " + data.getChatColor() + message;

            //System.out.println(data.getChatColor());

            for (PlayerData pl : players.values()) {
                if (!pl.isInLobby()) {
                    continue;
                }
                pl.getPlayer().sendMessage(msg);
            }

            getServer().getLogger().info(msg);
        }

    }

    public static String getPrefix() {
        return TextFormat.GRAY + TextFormat.BOLD + "[" + TextFormat.RESET + TextFormat.RED + "Game" + TextFormat.GREEN + "Team" + TextFormat.BOLD + TextFormat.GRAY + "] " + TextFormat.WHITE + TextFormat.RESET;
    }

    @EventHandler
    public void onPlayerInteract(PlayerInteractEvent e) {
        Player p = e.getPlayer();
        Block b = e.getBlock();
        int action = e.getAction();

        //p.sendMessage(b.getName()+":"+b.getId()+"   "+b.x+":"+b.y+":"+b.z);

        Vector2 target = new Vector2(b.x, b.z);
        Vector2 player = new Vector2(p.x, p.z);

        if ((action == PlayerInteractEvent.LEFT_CLICK_BLOCK || action == PlayerInteractEvent.RIGHT_CLICK_BLOCK) && target.distance(player) > 5.9 && !p.isCreative()) {
            e.setCancelled();
            return;
        }

        PlayerData data = getPlayerData(p);

        if (!data.isLoggedIn()) {
            p.sendMessage(getPrefix() + TextFormat.RED + "Nejsi prihlasen");
            e.setCancelled();
        }else if(p.getLevel().getId() == getServer().getDefaultLevel().getId() && data.isInLobby()){
            e.setCancelled();
        }

        /* else {
            if (data.isInLobby() && p.getInventory().getItemInHand().getId() == 347 && (e.getAction() == 3 || e.getAction() == 2)) {
                if (data.isVanish()) {
                    this.spawnPlayersTo(p);
                    data.setVanish(false);
                    p.sendMessage(TextFormat.YELLOW + "All players are now visible");
                    level.addSound(new FizzSound(p), new Player[]{p});
                    return;
                }

                this.despawnPlayersFrom(p);
                data.setVanish(true);
                p.sendMessage(TextFormat.YELLOW + "Vanished all players");
                level.addSound(new FizzSound(p), new Player[]{p});
            }

        }*/
    }

    @EventHandler
    public void onPlayerDropItem(PlayerDropItemEvent event) {
        if (!this.isAuthed(event.getPlayer())) {
            event.getPlayer().sendMessage(getPrefix() + TextFormat.RED + "Nejsi prihlasen");
            event.setCancelled();
        }

    }

    @EventHandler
    public void onPlayerItemConsume(PlayerItemConsumeEvent event) {
        if (!this.isAuthed(event.getPlayer())) {
            event.getPlayer().sendMessage(getPrefix() + TextFormat.RED + "Nejsi prihlasen");
            event.setCancelled();
        }

    }

    @EventHandler
    public void onPlayerQuit(PlayerQuitEvent e) {
        PlayerData data = getPlayerData(e.getPlayer());

        if (data != null) {
            if (data.isLoggedIn()) {
                this.getServer().getScheduler().scheduleAsyncTask(new QuitTask(e.getPlayer().getName(), data.getPlayTime()));
            }

            this.players.remove(e.getPlayer().getName().toLowerCase());
        }

        e.setQuitMessage(new TextContainer(""));
        //e.setAutoSave(false);
    }

    @EventHandler
    public void onEntityShootBow(EntityShootBowEvent e) {
        if (e.getEntity() instanceof Player && !this.isAuthed((Player) e.getEntity())) {
            e.setCancelled();
        }

    }

    @EventHandler
    public void onEntityDamage(EntityDamageEvent e) {
        Entity entity = e.getEntity();

        PlayerData data = null;

        if (e instanceof EntityDamageByEntityEvent) {
            Entity damager = ((EntityDamageByEntityEvent) e).getDamager();
            if (damager instanceof Player) {
                Player attacker = (Player) damager;

                PlayerData aData = getPlayerData(attacker);

                Vector2 target = new Vector2(entity.x, entity.z);
                Vector2 player = new Vector2(attacker.x, attacker.z);

                if (target.distance(player) > 5.1) {
                    e.setCancelled();
                    return;
                }


                if (!aData.isLoggedIn()) {
                    e.setCancelled();
                    return;
                }

                if (entity instanceof Player) {
                    if (aData.isInLobby()) {
                        e.getEntity().despawnFrom(attacker);
                    }

                    data = getPlayerData((Player) entity);

                    data.setLastHit(System.currentTimeMillis());
                }

                if (anni != null && entity instanceof AnnihilationNPC) {
                    this.anni.onHit((EntityDamageByEntityEvent) e);
                    return;
                }
            }
        }

        if (entity instanceof Player) {
            if (data == null) {
                data = getPlayerData((Player) entity);
            }

            if (data.isInLobby() || !data.isLoggedIn()) {
                e.setCancelled();
                if (e.getCause() == EntityDamageEvent.CAUSE_VOID) {
                    entity.teleport(this.lobby);
                }
            }
        }

    }

    @EventHandler(
            priority = EventPriority.NORMAL,
            ignoreCancelled = true
    )
    public void onBlockPlace(BlockPlaceEvent e) {
        Player p = e.getPlayer();
        if (!this.isAuthed(p)) {
            p.sendMessage(getPrefix() + TextFormat.RED + "Nejsi prihlasen");
            e.setCancelled();
        }

        if (p.getLevel().equals(this.getServer().getDefaultLevel()) && (!p.isOp() || !p.isCreative()) && !getServer().getDefaultLevel().getName().equals("survival1")) {
            e.setCancelled();
        }

    }

    @EventHandler(
            priority = EventPriority.NORMAL,
            ignoreCancelled = true
    )
    public void onBlockBreak(BlockBreakEvent e) {
        Player p = e.getPlayer();
        Block b = e.getBlock();
        Item item = e.getItem();

        if (!this.isAuthed(p)) {
            p.sendMessage(getPrefix() + TextFormat.RED + "Nejsi prihlasen");
            e.setCancelled();
            return;
        }

        if (p.getLevel().getId() == getServer().getDefaultLevel().getId() && !getServer().getDefaultLevel().getName().equals("survival1")) {
            if(!p.isOp() || !p.isCreative()) {
                e.setCancelled();
            } else {
                System.out.println("block break: "+p.getName());
            }
            return;
        }

        /*if (p.isCreative()) {
            return;
        }

        Vector2 target = new Vector2(b.x, b.z);
        Vector2 player = new Vector2(p.x, p.z);

        if (target.distance(player) > 5) {
            e.setCancelled();
            return;
        }

        e.setInstaBreak(true);

        double breakTime = b.getBreakTime(item) * 1000;

        if (p.hasEffect(Effect.SWIFTNESS)) {
            breakTime *= 1 - (0.2 * (p.getEffect(Effect.SWIFTNESS).getAmplifier() + 1));
        }

        if (p.hasEffect(Effect.MINING_FATIGUE)) {
            breakTime *= 1 - (0.3 * (p.getEffect(Effect.MINING_FATIGUE).getAmplifier() + 1));
        }

        Enchantment eff = item.getEnchantment(Enchantment.ID_EFFICIENCY);

        if (eff != null) {
            breakTime *= 1 - (0.3 * eff.getLevel());
        }

        breakTime -= 0.10;

        long time = System.currentTimeMillis();

        double realTime = p.lastBreak + breakTime;

        //System.out.println("\nbreak time: "+breakTime+"     expected time: "+realTime+"          real time: "+(time - p.lastBreak));

        if (realTime > time) {
            //System.out.println("\nfast break");
            e.setCancelled();
        }*/

        if (e.isFastBreak()) {
            p.lastBreak = System.currentTimeMillis();
        }
    }

    public boolean isAuthed(Player p) {
        return getPlayerData(p).isLoggedIn();
    }

    @EventHandler
    public void commandPreprocces(PlayerCommandPreprocessEvent e) {
        Player p = e.getPlayer();
        String msg = e.getMessage();

        if(!p.isOp() && msg.toLowerCase().startsWith("/kill")){
            e.setCancelled();
            return;
        }

        if (!this.isAuthed(p) && !msg.toLowerCase().startsWith("/login") && !msg.toLowerCase().startsWith("/register")) {
            p.sendMessage(getPrefix() + TextFormat.RED + "Nejsi prihlasen");
            e.setCancelled();
        } else if (p.isOp() || (!msg.startsWith("/me") && !msg.startsWith("/tell"))) {
            e.setMessage(msg.replace("&", "§"));
        } else {
            e.setCancelled();
            p.sendMessage(TextFormat.RED + "Unknown command. Try /help for list of commands");
        }
    }

    public void setRank(String p, String rank) {
        switch (rank.toLowerCase()) {
            case "vip":
                new SetRankQuery(this, p, "VIP", (int) (System.currentTimeMillis() / 1000L + 2592000L));
                break;
            case "vip+":
                new SetRankQuery(this, p, "VIP+", (int) (System.currentTimeMillis() / 1000L + 2592000L));
                break;
            case "extra":
                new SetRankQuery(this, p, "extra", 0);
                break;
            case "hrac":
                new SetRankQuery(this, p, "hrac", 0);
        }

        this.getServer().getLogger().info("§a" + p + " group changed");
    }

    @EventHandler
    public void onQueryRegenerate(QueryRegenerateEvent e) {
        e.setMaxPlayerCount(100);
    }

    public boolean inLobby(Player p) {
        return getPlayerData(p).isInLobby();
    }

    public void setLobby(Player p) {
        this.setLobby(p, false);
    }

    public void setLobby(Player p, boolean join) {
        PlayerData data = getPlayerData(p);

        data.setInLobby(true);
        p.setGamemode(0);

        p.gamemode = 3;

        if (!join) {
            p.teleport(lobby);
            p.setNameTag(data.getPrefix() + " " + data.getPlayer().getName());
        }

        //p.setRotation(270, 0);
        p.setExperience(0, 0);
        p.setHealth(20);
        p.getFoodData().setLevel(20);
        p.removeAllEffects();
        p.setSpawn(this.lobby);

        if (p.getInventory() != null) {
            p.getInventory().clearAll();
            //p.getInventory().setItem(0, Item.get(Item.CLOCK, 0, 1)/*.setCustomName(TextFormat.RESET + TextFormat.YELLOW + "Hide players")*/);
            p.getInventory().setItem(1, Item.get(Item.GOLD_INGOT, 0, 1));
            p.getInventory().setHotbarSlotIndex(0, 0);
            p.getInventory().setHotbarSlotIndex(1, 1);
            p.getInventory().sendContents(p);
            //String name = this.getDisplayRank(p) + " " + p.getName();
            //p.setDisplayName(name);
            //p.setNameTag(name);
        }

        for (FloatingTextParticle pc : particles) {
            if (pc == null) {
                continue;
            }
            this.level.addParticle(pc, p);
        }


    }

    public void unsetLobby(Player p) {
        getPlayerData(p).setInLobby(false);
        p.gamemode = 0;
        //p.spawnToAll();
    }

    /*public void checkRank(Player pl) {
        String p = pl.getName();
        String name = this.mysqlmgr.getRank(p);
        byte var4 = -1;
        switch(name.hashCode()) {
            case -334232255:
                if(name.equals("Sponzor")) {
                    var4 = 2;
                }
                break;
            case 84989:
                if(name.equals("VIP")) {
                    var4 = 0;
                }
                break;
            case 2634702:
                if(name.equals("VIP+")) {
                    var4 = 1;
                }
        }

        int time;
        switch(var4) {
            case 0:
                if(System.currentTimeMillis() / 1000L < (long)this.mysqlmgr.getTime(p)) {
                    time = Math.round((float)((long)this.mysqlmgr.getTime(p) - System.currentTimeMillis() / 1000L / 86400L));
                    pl.sendMessage("§6[MT_Core] §aVIP rank expires in $time days");
                } else {
                    pl.sendMessage("§6[MT_Core] §aVIP rank expired");
                    this.setRank(p, "hrac");
                }
                break;
            case 1:
                if(System.currentTimeMillis() / 1000L < (long)this.mysqlmgr.getTime(p)) {
                    time = Math.round((float)((long)this.mysqlmgr.getTime(p) - System.currentTimeMillis() / 1000L / 86400L));
                    pl.sendMessage("§6[MT_Core] §aVIP+ rank expires in $time days");
                } else {
                    pl.sendMessage("§6[MT_Core] §aVIP+ rank expired");
                    this.setRank(p, "hrac");
                }
                break;
            case 2:
                if(System.currentTimeMillis() / 1000L < (long)this.mysqlmgr.getTime(p)) {
                    time = Math.round((float)((long)this.mysqlmgr.getTime(p) - System.currentTimeMillis() / 1000L / 86400L));
                    pl.sendMessage("§6[MT_Core] §aExtra rank expires in $time days");
                } else {
                    pl.sendMessage("§6[MT_Core] §aExtra rank expired");
                    this.setRank(p, "hrac");
                }
        }

        name = this.getDisplayRank(pl) + " " + pl.getName();
        pl.setDisplayName(name);
        pl.setNameTag(name);
    }*/

    public void despawnPlayersFrom(Player p) {
        Iterator var2 = this.level.getPlayers().entrySet().iterator();

        while (var2.hasNext()) {
            Entry entry = (Entry) var2.next();
            p.despawnFrom((Player) entry.getValue());
        }

    }

    public void spawnPlayersTo(Player p) {
        Iterator var2 = this.level.getPlayers().entrySet().iterator();

        while (var2.hasNext()) {
            Entry entry = (Entry) var2.next();
            p.spawnTo((Player) entry.getValue());
        }

    }

    public void loadPlugins() {
        Plugin bedwars = this.getServer().getPluginManager().getPlugin("BedWars");
        if (bedwars != null && bedwars.isEnabled()) {
            this.bedwars = bedwars;
        }

        Annihilation anni = Annihilation.getInstance();
        if (anni != null && anni.isEnabled()) {
            this.anni = anni;
        }

    }

    @EventHandler
    public void onFoodChange(PlayerFoodLevelChangeEvent e) {
        Player p = e.getPlayer();
        if (this.inLobby(p)) {
            e.setCancelled();
        }
    }

    @EventHandler(
            priority = EventPriority.NORMAL,
            ignoreCancelled = false
    )
    public void onWeatherChange(WeatherChangeEvent e) {
        /*if(e.getLevel().getName().equals("BedWars_hub")) {
            e.setCancelled();
        }*/
    }

    @EventHandler(
            priority = EventPriority.NORMAL,
            ignoreCancelled = false
    )
    public void onInvalidMove(PlayerInvalidMoveEvent e) {
        e.setRevert(false);
    }

    //AxisAlignedBB temporalBB = new AxisAlignedBB(0, 0, 0, 0, 0, 0);

    @EventHandler
    public void onMove(PlayerMoveEvent e) {
        Player p = e.getPlayer();

        Vector3 from = e.getFrom();
        Vector3 to = e.getTo();

        if (p.gamemode > 0 || (to.x == from.x && to.y == from.y && to.z == from.z)) {
            return;
        }

        PlayerData data = getPlayerData(p);

        if (data.isInLobby()) {
            return;
        }

        //double radius = (double)p.getWidth() / 2.0D;
        //AxisAlignedBB bb = temporalBB.setBounds(to.x - radius, to.y, to.z - radius, to.x + radius, to.y + (double)p.getHeight(), to.z + radius);

        AxisAlignedBB bb = p.getBoundingBox().clone();

        AxisAlignedBB bb2 = bb.clone();
        bb2.minY += 0.6;
        bb2.expand(-0.2, 0, -0.2);

        //System.out.println(bb);

        /*int minX = NukkitMath.floorDouble(bb.minX);
        int minY = NukkitMath.floorDouble(bb.minY);
        int minZ = NukkitMath.floorDouble(bb.minZ);
        int maxX = NukkitMath.ceilDouble(bb.maxX);
        int maxY = NukkitMath.ceilDouble(bb.maxY);
        int maxZ = NukkitMath.ceilDouble(bb.maxZ);

        boolean ground = false;
        boolean liquid = false;

        boolean done = false;

        for (int z = minZ; z <= maxZ; ++z) {
            if(done){
                break;
            }
            for (int x = minX; x <= maxX; ++x) {
                if(done){
                    break;
                }
                for (int y = minY; y <= maxY; ++y) {
                    Block block = p.getLevel().getBlock(p.temporalVector.setComponents(x, y, z));

                    //System.out.println(block.getName()+":"+block.getId()+"   pos: "+x+":"+y+":"+z+"       ppos: "+p.getFloorX()+":"+p.getFloorY()+":"+p.getFloorZ());

                    /*if(block.getBoundingBox() == null){
                        System.out.println("null bb: "+block.getName());
                    }*/

                    /*if(!block.collidesWithBB(bb)){
                        //System.out.println("collides");
                        continue;
                    }*/

                    /*if(block instanceof BlockLiquid){
                        liquid = true;
                        ground = true;
                    } else if( block.getId() == Item.LADDER || block.getId() == Item.VINE){
                        System.out.println("ladder");
                        ground = true;
                        continue;
                    }*/

                    /*if(block instanceof BlockSlab || block instanceof BlockStairs){
                        data.setLastSlab(System.currentTimeMillis());
                        continue;
                    }*/

                    /*if (!block.isTransparent() && block.collidesWithBB(bb2)) {
                        e.setCancelled();
                        //System.out.println("cancelled: "+block.getName());
                        done = true;
                        break;
                    }
                }
            }
        }*/

        /*if(!e.isCancelled()){
            data.setOnGround(ground);
            if(liquid) {
                data.setLastLiquid(to);
            }
        }*/

        for (Block block : p.getCollisionBlocks()) {
            if (!block.isTransparent() && block.collidesWithBB(bb2)) {
                e.setCancelled();
                return;
            }
        }

        long time = System.currentTimeMillis();
        Location pLoc = p.clone();

        if (p.onGround || p.riding != null || !NoCheatTask.isInAir(p, data)) {
            data.setLastOnGround(time);
            data.setLastGroundPos(pLoc);

            /*if(p.onGround && to.y > from.y){
                data.setLastJump(time);
                data.setLastJumpPos(pLoc);
                System.out.println("jump");
            }*/
            //data.setOnGround(true);
            //System.out.println("on ground");
        } else {
            //data.setOnGround(false);

            int inAirTime = (int) (time - data.getLastOnGround());

            if (to.y >= from.y && to.y > 0) {

                Vector3 pos = data.getLastGroundPos();

                //System.out.println("Y: "+(to.y - data.getLastGroundPos().y)+"          time: "+(data.getLastOnGround() - data.getLastJump()));

                if(to.y - data.getLastGroundPos().y <= 1 && data.getLastOnGround() > data.getLastJump()){
                    data.setLastJump(data.getLastOnGround());
                    data.setLastJumpPos(data.getLastGroundPos());
                    //System.out.println("jump2");
                }

                Location lastJumpPos = data.getLastJumpPos();
                double jumpDistance = lastJumpPos.distance(to);

                boolean slab = false;

                for (Block block : p.getGroundBlocks()) {
                    if ((block instanceof BlockSlab || block instanceof BlockStairs) && block.collidesWithBB(bb2)) {
                        data.setLastSlab(to);
                        data.setLastSlabTime(time);
                        //slab = true;
                    }
                }

                //System.out.println(slab ? "!ground" : "!ground slab");
                /*if (jumpDistance > 3.2) {
                    System.out.println("\ndistance: " + jumpDistance);
                }

                if (p.y - lastJumpPos.y > 1.6) {
                    System.out.println("\nhigh: " + (to.y - lastJumpPos.y));
                }*/

                //double groundDistance = Math.sqrt(Math.pow(pos.x - to.x, 2.0D) + Math.pow(pos.z - to.z, 2.0D));
                double groundDistance = pos.distance(to);
                //double groundXZ = Math.sqrt(Math.pow(pos.x - to.x, 2.0D) + Math.pow(pos.z - to.z, 2.0D));

                double slabDistance = Math.sqrt(Math.pow(data.getLastSlab().x - to.x, 2.0D) + Math.pow(data.getLastSlab().z - to.z, 2.0D));

                double slabDistanceY = to.y - data.getLastSlab().y;

                //System.out.println("ground distance: "+groundDistance + "           inAirTime: "+inAirTime);
                //System.out.println("liquid distance: "+data.getLastLiquid().distance(to));
                //System.out.println("slab distance: "+slabDistance);

                double waterDistance = Math.sqrt(Math.pow(data.getLastLiquid().x - to.x, 2.0D) + Math.pow(data.getLastLiquid().z - to.z, 2.0D));
                double waterY = to.y - data.getLastLiquid().y;


                if ((slabDistance > 0.3 || slabDistanceY > 0.6 || time - data.getLastSlabTime() > 500) && groundDistance > 0.6 && (waterDistance > 0.6 || waterY > 0.7)) {

                    //System.out.println("ground distance: "+groundDistance);
                    if (time - data.getLastHit() > 3000 && (time - data.getLastJump() > 3000 || (jumpDistance > 3.2 && to.y >= lastJumpPos.y) || to.y - lastJumpPos.y > 1.45 || (inAirTime > 3000 && data.getLastOnGround() - data.getLastCheck() <= 0))) {
                        /*if (data.getLastGroundPos().y - to.y > 2) {
                            double expectedVelocity = (-0.08) / 0.02 - (-0.08) / 0.02 * Math.exp(-0.02 * inAirTime);
                            p.setMotion(p.temporalVector.setComponents(0, expectedVelocity, 0));
                            System.out.println("motion "+expectedVelocity);
                        } else {*/
                        if(pos.y >= to.y){
                            p.setMotion(p.temporalVector.setComponents(0, -500, 0));
                            p.setAllowFlight(false);
                        } else {
                            e.setTo(data.getLastGroundPos());
                            data.setLastOnGround(time);
                        }
                        //}
                    }
                }
            }
        }

        data.setLastCheck(time);
    }

    public static long getTime() {
        return System.currentTimeMillis() * 1000 + 1400000000;
    }

    public static MTCore getInstance() {
        return instance;
    }

    public PlayerData getPlayerData(Player p) {
        return getPlayerData(p.getName());
    }

    public PlayerData getPlayerData(String p) {
        return players.get(p.toLowerCase());
    }

    @EventHandler(
            priority = EventPriority.NORMAL,
            ignoreCancelled = false
    )
    public void onSprint(PlayerToggleSprintEvent e) {
        PlayerData data = getPlayerData(e.getPlayer());

        if (!e.isSprinting()) {
            data.setLastSpeedChange(System.currentTimeMillis());
        }
    }

    @EventHandler(
            priority = EventPriority.NORMAL,
            ignoreCancelled = false
    )
    public void onSneak(PlayerToggleSneakEvent e) {
        PlayerData data = getPlayerData(e.getPlayer());

        if (e.isSneaking()) {
            data.setLastSpeedChange(System.currentTimeMillis());
        }
    }

    /*@EventHandler(
            priority = EventPriority.NORMAL,
            ignoreCancelled = false
    )
    public void onJump(PlayerJumpEvent e) {
        PlayerData data = getPlayerData(e.getPlayer());

        data.setLastJumpPos(e.getPlayer().getLocation().clone());
        data.setLastJump(System.currentTimeMillis());
    }*/

    @EventHandler(
            priority = EventPriority.NORMAL,
            ignoreCancelled = false
    )
    public void onTeleport(PlayerTeleportEvent e) {

        if (e.getCause() == PlayerTeleportEvent.TeleportCause.PLUGIN) {
            PlayerData data = getPlayerData(e.getPlayer());
            data.setTeleport(true);
        }

    }

    @EventHandler
    public void onRespawn(PlayerRespawnEvent e) {
        Player p = e.getPlayer();
        PlayerData data = getPlayerData(e.getPlayer());

        data.setTeleport(true);

        if (!data.isLoggedIn() || p.getLevel().getId() == level.getId()) {
            e.setRespawnPosition(lobby);
        }
    }

    //private int count = 0;
    //private long time = System.currentTimeMillis();

    //@EventHandler
    public void onDataPacketReceive(DataPacketReceiveEvent e) {
        /*if (e.getPacket().pid() == ProtocolInfo.MOVE_PLAYER_PACKET) {
            long time = System.currentTimeMillis();

            if (time - this.time > 1000) {
                if()
                this.time = time;
                System.out.println("\npackets received: " + count);
                count = 0;
            }

            count++;
        }*/

        if (e.getPacket().pid() == ProtocolInfo.PLAYER_ACTION_PACKET) {
            if (((PlayerActionPacket) e.getPacket()).action == PlayerActionPacket.ACTION_JUMP) {
                PlayerData data = getPlayerData(e.getPlayer());
                long time = System.currentTimeMillis();

                if(time - data.getLastOnGround() < 500){
                    data.setLastJumpPos(e.getPlayer().getLocation().clone());
                    data.setLastJump(time);
                }
            }
        }
    }

    public Player getPlayerExact(String name) {
        name = name.toLowerCase();
        for (Player player : new ArrayList<>(getServer().getOnlinePlayers().values())) {
            if (player.getName() == null) {
                continue;
            }

            if (player.getName().toLowerCase().equals(name)) {
                return player;
            }
        }

        return null;
    }
}
