//
// Source code recreated from a .class file by IntelliJ IDEA
// (powered by Fernflower decompiler)
//

package MTCore;

import MTCore.Generator.EmptyGenerator;
import MTCore.Mysql.*;
import cn.nukkit.Player;
import cn.nukkit.command.Command;
import cn.nukkit.command.CommandSender;
import cn.nukkit.entity.Entity;
import cn.nukkit.entity.EntityHuman;
import cn.nukkit.event.EventHandler;
import cn.nukkit.event.EventPriority;
import cn.nukkit.event.Listener;
import cn.nukkit.event.TextContainer;
import cn.nukkit.event.block.BlockBreakEvent;
import cn.nukkit.event.block.BlockPlaceEvent;
import cn.nukkit.event.entity.EntityArmorChangeEvent;
import cn.nukkit.event.entity.EntityDamageByEntityEvent;
import cn.nukkit.event.entity.EntityDamageEvent;
import cn.nukkit.event.entity.EntityInventoryChangeEvent;
import cn.nukkit.event.entity.EntityRegainHealthEvent;
import cn.nukkit.event.entity.EntityShootBowEvent;
import cn.nukkit.event.level.WeatherChangeEvent;
import cn.nukkit.event.player.*;
import cn.nukkit.event.server.QueryRegenerateEvent;
import cn.nukkit.item.Item;
import cn.nukkit.level.Level;
import cn.nukkit.level.Position;
import cn.nukkit.level.generator.Generator;
import cn.nukkit.level.sound.FizzSound;
import cn.nukkit.plugin.Plugin;
import cn.nukkit.plugin.PluginBase;
import cn.nukkit.potion.Effect;
import cn.nukkit.utils.TextFormat;

import java.util.*;
import java.util.Map.Entry;

public class MTCore extends PluginBase implements Listener {
    //public MySQLManager mysqlmgr;
    //public Auth authmgr;

    public HashMap<String, Player> players = new HashMap<>();
    public HashMap<String, Integer> chatters = new HashMap<>();
    public HashMap<String, Integer> unauthed = new HashMap<>();
    public HashMap<String, String> chatColors = new HashMap<>();

    public ArrayList<String> vanishers = new ArrayList<>();
    public Map<String, Player> lobbyPlayers = new HashMap<>();
    public Level level;
    public Position lobby;
    public Plugin anni;
    public Plugin bedwars;

    private static MTCore instance;

    public static String serverFullMessage;

    public void onLoad(){
        instance = this;
        serverFullMessage = TextFormat.RED + "Sorry, but this server is full. " + TextFormat.YELLOW + "You can buy a " + TextFormat.AQUA + "VIP rank at " + TextFormat.GREEN + "bit.do/mtBUY";
    }

    public void onEnable() {
        //this.mysqlmgr = new MySQLManager(this);
        //this.authmgr = new Auth(this);
        Generator.addGenerator(EmptyGenerator.class, "emptyworld", 1);
        this.level = this.getServer().getDefaultLevel();
        this.getServer().getPluginManager().registerEvents(this, this);
        this.level.setTime(5000);
        this.level.stopTime();
        this.lobby = this.level.getSpawnLocation();
        this.getServer().getNetwork().setName(TextFormat.GOLD + "MineTox     " + TextFormat.RED + "EXPERIMENTAL");

        for(String s : new String[]{TextFormat.AQUA+"Vote for "+TextFormat.GOLD+"MineTox "+TextFormat.AQUA+"here "+TextFormat.GREEN+"goo.gl/cMTX9s", TextFormat.AQUA+"need help? Ask "+TextFormat.GREEN+"@MineTox_MCPE "+TextFormat.AQUA+"on the twitter", TextFormat.AQUA+"You can buy VIP rank here: "+TextFormat.GREEN+"bit.ly/mtBUY"}){
            MessageTask.messages.add(s);
        }

        this.getServer().getScheduler().scheduleDelayedRepeatingTask(new MessageTask(this), 2400, 2400);
        this.getServer().getScheduler().scheduleDelayedRepeatingTask(new ItemCleanTask(this), 303 * 20, 304 * 20);
    }

    @EventHandler(
            priority = EventPriority.NORMAL,
            ignoreCancelled = false
    )
    public void onLogin(PlayerLoginEvent e) {
        Player p = e.getPlayer();

        p.addEffect(Effect.getEffect(Effect.BLINDNESS).setDuration(999999999));

        players.put(p.getName().toLowerCase(), p);
        unauthed.put(p.getName().toLowerCase(), 1);

        new LoginDataQuery(this, p.getName().toLowerCase(), p.getUniqueId().toString());
    }

    @EventHandler(
            priority = EventPriority.NORMAL,
            ignoreCancelled = false
    )
    public void onJoin(PlayerJoinEvent e) {
        e.setJoinMessage(new TextContainer(""));
        Player p = e.getPlayer();

        this.getServer().getScheduler().scheduleDelayedTask(new JoinDelay(this, p), 1);

        new JoinQuery(this, p.getName().toLowerCase(), p.getAddress(), p.getUniqueId().toString());

        if(p.getGamemode() != 0) {
            p.setGamemode(0);
        }

        this.setLobby(p, true);
    }

    @Override
    public boolean onCommand(CommandSender sender, Command cmd, String label, String[] args) {
        if(sender instanceof Player) {
            switch(cmd.getName().toLowerCase()) {
                case "tokens":
                case "cash":
                case "coins":
                case "money":
                    new DisplayMoneyQuery(this, sender.getName().toLowerCase());
                    break;
                case "register":
                    if(!unauthed.containsKey(sender.getName().toLowerCase())){
                        sender.sendMessage(MTCore.getPrefix() + "§c" + "You are already logged in");
                    } else if(args.length != 2) {
                        sender.sendMessage(getPrefix() + TextFormat.GOLD + "Use /register [password] [password]");
                    } else if(!args[0].equals(args[1])) {
                        sender.sendMessage(getPrefix() + TextFormat.RED + "Both passwords must be same");
                    } else if(args[1].length() < 4 || args[1].length() > 20){
                        sender.sendMessage(MTCore.getPrefix() + TextFormat.RED + "Password lenght must be between 4 and 20 characters");
                    } else {
                        new RegisterQuery(this, sender.getName().toLowerCase(), args[0], ((Player) sender).getAddress(), ((Player) sender).getUniqueId().toString());
                    }
                    break;
                case "login":
                    if(args.length != 1) {
                        sender.sendMessage(getPrefix() + "§6" + "Use /login [password]");
                    } else if(!unauthed.containsKey(sender.getName().toLowerCase())){
                        sender.sendMessage(MTCore.getPrefix() + "§c" + "You are already logged in");
                    } else {
                        new LoginQuery(this, sender.getName(), args[0], ((Player) sender).getAddress(), ((Player) sender).getUniqueId().toString());
                    }
                    break;
                case "changepassword":
                    if(args.length != 2) {
                        sender.sendMessage(getPrefix() + "§6" + "Use /changepassword [password] [new password]");
                    } else if(args[1].length() < 4 || args[1].length() > 20){
                        sender.sendMessage(MTCore.getPrefix() + TextFormat.RED + "Password lenght must be between 4 and 20 characters");
                    } else {
                        new ChangePasswordQuery(this, sender.getName().toLowerCase(), args[0], args[1]);
                    }
            }
        }

        switch(cmd.getName().toLowerCase()) {
            case "setrank":
                if(!sender.isOp()) {
                    sender.sendMessage(cmd.getPermissionMessage());
                } else if(args.length == 2) {
                    sender.sendMessage(args[0] + "\'s Rank updated");
                    this.setRank(args[0], args[1]);
                }
                break;
            case "addcoins":
                if(!sender.isOp()) {
                    sender.sendMessage(cmd.getPermissionMessage());
                } else if(args.length == 2) {
                    new AddCoinsQuery(this, args[0], Integer.valueOf(args[1]));
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

        switch(rank.toLowerCase()) {
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

        switch(rank.toLowerCase()) {
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
        e.setCancelled(true);
        if(!this.isAuthed(p)) {
            p.sendMessage(getPrefix() + "§c" + "You are not logged in");
        } else if(this.chatters.containsKey(p.getName().toLowerCase()) && this.chatters.get(p.getName().toLowerCase()) > this.getServer().getTick()) {
            p.sendMessage("§cPlease wait " + (this.chatters.get(p.getName().toLowerCase()) - this.getServer().getTick() / 20) + " seconds");
        } else {
            if(!p.isOp()) {
                this.chatters.put(p.getName().toLowerCase(), this.getServer().getTick() + 160);
            }

            //String[] ips = new String[]{"leet.cc", ".tk", "lbsg.net", "inpvp.net", "93.91.250.135", "93.91", "instantmcpe", "cookiebuild", "lbsg", "lifeboat", "rapidpe", "bladestorm", "hypixel", "inpvp"};
            String msg = e.getMessage().replace(" ", "").trim().toLowerCase();
            String[] var5 = ips;
            int var6 = ips.length;

            for(int var7 = 0; var7 < var6; ++var7) {
                String slovo = var5[var7];
                if(msg.contains(slovo)) {
                    p.sendMessage(getPrefix() + "§c" + "Do not advertise!");
                    return;
                }
            }

            if(this.inLobby(p)) {
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
        if(p != null) {

            String msg = p.getDisplayName() + TextFormat.DARK_AQUA + " > " + chatColors.getOrDefault(p.getName().toLowerCase(), "") + message;

            for(Player pl : lobbyPlayers.values()){
                pl.sendMessage(msg);
            }

            getServer().getLogger().info(msg);
        }

    }

    public static String getPrefix() {
        return "§l§4[§r§6MineTox§l§4]§r§f §r§f";
    }

    @EventHandler(
            priority = EventPriority.NORMAL,
            ignoreCancelled = false
    )
    public void onPlayerInteract(PlayerInteractEvent event) {
        Player p = event.getPlayer();
        if(!this.isAuthed(p)) {
            p.sendMessage(getPrefix() + "§c" + "You are not logged in");
            event.setCancelled();
        } else {
            if(this.inLobby(p) && p.getInventory().getItemInHand().getId() == 347 && (event.getAction() == 3 || event.getAction() == 2)) {
                if(this.vanishers.contains(p.getName().toLowerCase())) {
                    this.spawnPlayersTo(p);
                    this.vanishers.remove(p.getName().toLowerCase());
                    p.sendMessage("§eAll players are now visible");
                    level.addSound(new FizzSound(p), new Player[]{p});
                    return;
                }

                this.despawnPlayersFrom(p);
                this.vanishers.add(this.vanishers.size(), p.getName().toLowerCase());
                p.sendMessage("§eVanished all players");
                level.addSound(new FizzSound(p), new Player[]{p});
            }

        }
    }

    @EventHandler(
            priority = EventPriority.NORMAL,
            ignoreCancelled = false
    )
    public void onPlayerDropItem(PlayerDropItemEvent event) {
        if(!this.isAuthed(event.getPlayer())) {
            event.getPlayer().sendMessage(getPrefix() + "§c" + "You are not logged in");
            event.setCancelled();
        }

    }

    @EventHandler(
            priority = EventPriority.NORMAL,
            ignoreCancelled = false
    )
    public void onPlayerItemConsume(PlayerItemConsumeEvent event) {
        if(!this.isAuthed(event.getPlayer())) {
            event.getPlayer().sendMessage(getPrefix() + "§c" + "You are not logged in");
            event.setCancelled();
        }

    }

    @EventHandler(
            priority = EventPriority.NORMAL,
            ignoreCancelled = false
    )
    public void onPlayerItemHeld(PlayerItemHeldEvent event) {
        if(!this.isAuthed(event.getPlayer())) {
            event.getPlayer().sendMessage(getPrefix() + "§c" + "You are not logged in");
            event.setCancelled();
        }

    }

    @EventHandler(
            priority = EventPriority.NORMAL,
            ignoreCancelled = false
    )
    public void onPlayerQuit(PlayerQuitEvent event) {
        this.players.remove(event.getPlayer().getName().toLowerCase());
        event.setQuitMessage(new TextContainer(""));
        event.setAutoSave(false);
    }

    @EventHandler(
            priority = EventPriority.NORMAL,
            ignoreCancelled = false
    )
    public void onPlayerKick(PlayerKickEvent e) {
        e.setQuitMessage(new TextContainer(""));
    }

    @EventHandler(
            priority = EventPriority.NORMAL,
            ignoreCancelled = false
    )
    public void onEntityArmorChange(EntityArmorChangeEvent event) {
        if(event.getEntity() instanceof Player && !this.isAuthed((Player)event.getEntity())) {
            event.setCancelled();
        }

    }

    @EventHandler(
            priority = EventPriority.NORMAL,
            ignoreCancelled = false
    )
    public void onEntityInventoryChange(EntityInventoryChangeEvent event) {
        if(event.getEntity() instanceof Player && !this.isAuthed((Player)event.getEntity())) {
            event.setCancelled();
        }

    }

    @EventHandler(
            priority = EventPriority.NORMAL,
            ignoreCancelled = false
    )
    public void onEntityRegainHealth(EntityRegainHealthEvent event) {
        if(event.getEntity() instanceof Player && !this.isAuthed((Player)event.getEntity())) {
            event.setCancelled();
        }

    }

    @EventHandler(
            priority = EventPriority.NORMAL,
            ignoreCancelled = false
    )
    public void onEntityShootBow(EntityShootBowEvent event) {
        if(event.getEntity() instanceof Player && !this.isAuthed((Player)event.getEntity())) {
            event.setCancelled();
        }

    }

    @EventHandler(
            priority = EventPriority.NORMAL,
            ignoreCancelled = false
    )
    public void onEntityDamage(EntityDamageEvent event) {
        Entity entity = event.getEntity();
        if(event instanceof EntityDamageByEntityEvent) {
            Player attacker = (Player)((EntityDamageByEntityEvent)event).getDamager();
            if(attacker != null) {
                if(!this.isAuthed(attacker)) {
                    event.setCancelled();
                    return;
                }

                if(this.inLobby(attacker) && event.getEntity() instanceof Player) {
                    event.getEntity().despawnFrom(attacker);
                }

                if(!(entity instanceof Player) && entity instanceof EntityHuman) {
                    return;
                }
            }
        }

        if(entity instanceof Player && (!this.isAuthed((Player)entity) || event.getEntity().getLevel().equals(this.getServer().getDefaultLevel()))) {
            event.setCancelled();
        }

    }

    @EventHandler(
            priority = EventPriority.NORMAL,
            ignoreCancelled = false
    )
    public void onBlockPlace(BlockPlaceEvent event) {
        Player p = event.getPlayer();
        if(!this.isAuthed(p)) {
            p.sendMessage(getPrefix() + "§c" + "You are not logged in");
            event.setCancelled();
        }

        if(p.getLevel().equals(this.getServer().getDefaultLevel()) && (!p.isOp() || !p.isCreative())) {
            event.setCancelled();
        }

    }

    @EventHandler(
            priority = EventPriority.NORMAL,
            ignoreCancelled = false
    )
    public void onBlockBreak(BlockBreakEvent event) {
        Player p = event.getPlayer();
        if(!this.isAuthed(p)) {
            p.sendMessage(getPrefix() + "§c" + "You are not logged in");
            event.setCancelled();
        }

        if(p.getLevel().equals(this.getServer().getDefaultLevel()) && (!p.isOp() || !p.isCreative())) {
            event.setCancelled();
        }

    }

    public boolean isAuthed(Player p) {
        return !this.unauthed.containsKey(p.getName().toLowerCase());
    }

    @EventHandler(
            priority = EventPriority.NORMAL,
            ignoreCancelled = false
    )
    public void commandPreprocces(PlayerCommandPreprocessEvent e) {
        Player p = e.getPlayer();
        String msg = e.getMessage().toLowerCase();
        if(!this.isAuthed(p) && !msg.startsWith("/login") && !msg.startsWith("/register")) {
            p.sendMessage(getPrefix() + "§c" + "You are not logged in");
            e.setCancelled();
        } else if(p.isOp() || !msg.startsWith("/me") && !msg.startsWith("/tell")) {
            e.setMessage(msg.replace("&", "§"));
        } else {
            e.setCancelled();
            p.sendMessage("§cUnknown command. Try /help for list of commands");
        }
    }

    public void setRank(String p, String rank) {
        switch(rank.toLowerCase()) {
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

    @EventHandler(
            priority = EventPriority.NORMAL,
            ignoreCancelled = false
    )
    public void onQueryRegenerate(QueryRegenerateEvent e) {
        e.setMaxPlayerCount(50);
    }

    public boolean inLobby(Player p) {
        return this.lobbyPlayers.containsKey(p.getName().toLowerCase());
    }

    public void setLobby(Player p) {
        this.setLobby(p, false);
    }

    public void setLobby(Player p, boolean join) {
        this.vanishers.add(this.vanishers.size(), p.getName().toLowerCase());
        this.lobbyPlayers.put(p.getName().toLowerCase(), p);
        p.setExperience(0, 0);
        p.setHealth(20.0F);
        p.getFoodData().setLevel(20);
        if(!join && p.getInventory() != null) {
            p.getInventory().clearAll();
            p.getInventory().setItem(0, Item.get(347, 0, 1));
            p.getInventory().setItem(1, Item.get(266, 0, 1));
            p.getInventory().setHotbarSlotIndex(0, 0);
            p.getInventory().setHotbarSlotIndex(1, 1);
            p.getInventory().sendContents(p);
            //String name = this.getDisplayRank(p) + " " + p.getName();
            //p.setDisplayName(name);
            //p.setNameTag(name);
        }

    }

    public void unsetLobby(Player p) {
        this.lobbyPlayers.remove(p.getName().toLowerCase());
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

        while(var2.hasNext()) {
            Entry entry = (Entry)var2.next();
            p.despawnFrom((Player)entry.getValue());
        }

    }

    public void spawnPlayersTo(Player p) {
        Iterator var2 = this.level.getPlayers().entrySet().iterator();

        while(var2.hasNext()) {
            Entry entry = (Entry)var2.next();
            p.spawnTo((Player)entry.getValue());
        }

    }

    public void loadPlugins() {
        Plugin bedwars = this.getServer().getPluginManager().getPlugin("BedWars");
        if(bedwars != null && bedwars.isEnabled()) {
            this.bedwars = bedwars;
        }

        Plugin anni = this.getServer().getPluginManager().getPlugin("Annihilation");
        if(anni != null && anni.isEnabled()) {
            this.anni = anni;
        }

    }

    @EventHandler(
            priority = EventPriority.NORMAL,
            ignoreCancelled = false
    )
    public void onFoodChange(PlayerFoodLevelChangeEvent e) {
        Player p = e.getPlayer();
        if(this.inLobby(p)) {
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
    public void onInvalidMove(PlayerInvalidMoveEvent e){
        e.setRevert(false);
    }

    /*@EventHandler(
            priority = EventPriority.NORMAL,
            ignoreCancelled = false
    )
    public void onMove(PlayerMoveEvent e){
        Player p = e.getPlayer();

        if(this.inLobby(p)){
            return;
        }

        AxisAlignedBB bb = p.getBoundingBox();

        for(Block b : p.getLevel().getCollisionBlocks(bb)){
            if(b.isTransparent()){
                continue;
            }

            e.setCancelled();
            break;
        }
    }*/

    public static long getTime(){
        return System.currentTimeMillis() * 1000 + 1400000000;
    }

    public static MTCore getInstance(){
        return instance;
    }
}
