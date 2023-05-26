package BedWars.Arena;

import BedWars.BedWars;
import BedWars.Entity.SpecialItem;
//import BedWars.Entity.TNTShip;
import BedWars.Entity.Villager;
import BedWars.MySQL.NormalQuery;
import BedWars.MySQL.StatQuery;
import BedWars.MySQL.Stat;
import BedWars.Object.BedWarsData;
import BedWars.Object.Team;
import BedWars.Shop.ItemWindow;
import BedWars.Shop.ShopWindow;
import BedWars.Shop.Window;
import BedWars.Task.*;

import BedWars.Utils.Color;
import BedWars.Utils.Items;
import GTCore.MTCore;

import GTCore.Object.PlayerData;
import cn.nukkit.Server;
import cn.nukkit.blockentity.BlockEntity;
import cn.nukkit.blockentity.BlockEntitySign;
import cn.nukkit.entity.Entity;
import cn.nukkit.entity.item.EntityItem;
import cn.nukkit.entity.projectile.EntitySnowball;
import cn.nukkit.event.*;
import cn.nukkit.event.block.BlockBreakEvent;
import cn.nukkit.event.block.BlockBurnEvent;
import cn.nukkit.event.entity.*;
import cn.nukkit.event.inventory.InventoryPickupArrowEvent;
import cn.nukkit.event.inventory.InventoryPickupItemEvent;
import cn.nukkit.event.inventory.InventoryTransactionEvent;
import cn.nukkit.event.player.*;
import cn.nukkit.event.inventory.CraftItemEvent;
import cn.nukkit.event.block.BlockPlaceEvent;
import cn.nukkit.block.Block;
import cn.nukkit.inventory.Inventory;
import cn.nukkit.inventory.PlayerInventory;
import cn.nukkit.inventory.Transaction;
import cn.nukkit.level.Level;
import cn.nukkit.level.Location;
import cn.nukkit.level.sound.AnvilUseSound;
import cn.nukkit.Player;
//import cn.nukkit.entity.passive.EntityVillager;
import cn.nukkit.blockentity.BlockEntityChest;
import cn.nukkit.math.AxisAlignedBB;
import cn.nukkit.nbt.NBTIO;
import cn.nukkit.nbt.tag.CompoundTag;
import cn.nukkit.nbt.tag.DoubleTag;
import cn.nukkit.nbt.tag.FloatTag;
import cn.nukkit.nbt.tag.ListTag;
import cn.nukkit.network.protocol.EntityEventPacket;
import cn.nukkit.utils.TextFormat;
import cn.nukkit.item.Item;
import cn.nukkit.level.Position;
import cn.nukkit.math.Vector3;
import cn.nukkit.network.protocol.ExplodePacket;
import cn.nukkit.level.particle.LargeExplodeParticle;
import cn.nukkit.event.Listener;

import java.util.*;

public class Arena implements Listener {

    public String id;
    public BedWars plugin;
    public HashMap<String, Vector3> data;
    public HashMap<String, Vector3> mainData;
    public Level level;

    public HashMap<String, BedWarsData> playerData = new HashMap<>();
    public HashMap<String, Player> spectators = new HashMap<>();

    public Team[] teams = new Team[5];
    public int game = 0;
    public boolean starting = false;
    public boolean ending = false;
    public ArenaSchedule task;
    public PopupTask popupTask;
    public VotingManager votingManager;
    public DeathManager deathManager;
    public String map = "Voting";
    public int winnerTeam;
    public MTCore mtcore;
    public boolean canJoin = true;

    public Arena(String id, BedWars plugin) {
        this.id = id;
        this.plugin = plugin;
        this.mainData = this.plugin.arenas.get(this.id);
        this.enableScheduler();
        this.votingManager = new VotingManager(this);
        this.deathManager = new DeathManager(this);
        this.votingManager.createVoteTable();
        this.mtcore = this.plugin.mtcore;
        this.writeTeams();
        updateMainSign();
        updateTeamSigns();
    }

    private void enableScheduler() {
        this.plugin.getServer().getScheduler().scheduleRepeatingTask(this.task = new ArenaSchedule(this), 20);
        this.plugin.getServer().getScheduler().scheduleRepeatingTask(this.popupTask = new PopupTask(this), 20);
    }

    private void writeTeams() {
        //this.teams[0] = new Team(this, 0, "lobby", "§5", 0);
        this.teams[1] = new Team(this, 1, "blue", "§9", Color.toDecimal(Color.BLUE));
        this.teams[2] = new Team(this, 2, "red", "§c", Color.toDecimal(Color.RED));
        this.teams[3] = new Team(this, 3, "yellow", "§e", Color.toDecimal(Color.YELLOW) );
        this.teams[4] = new Team(this, 4, "green", "§a", Color.toDecimal(Color.GREEN));
        //this.teams[5] = new Team(this, 5, "invalid", "§7", 0);
    }

    @EventHandler
    public void onBlockTouch(PlayerInteractEvent e) {
        Block b = e.getBlock();
        Player p = e.getPlayer();
        Item item = e.getItem();

        PlayerData data1 = mtcore.getPlayerData(p);

        if(!data1.isLoggedIn()){
            e.setCancelled();
            return;
        }

        if (b.equals(mainData.get("sign"))) {
            this.joinToArena(p);
            return;
        }

        if(data1.isInLobby()){
            e.setCancelled();
            return;
        }

        if (e.isCancelled()) {
            return;
        }

        BedWarsData data = getPlayerData(p);

        if (data == null) {
            return;
        }

        int team = this.isJoinSign(b);

        if (team != 0) {
            this.addToTeam(p, team);
            return;
        }


        /*if (e.getAction() == PlayerInteractEvent.RIGHT_CLICK_BLOCK && p.isOp() && item.getId() == Item.SPAWN_EGG && item.getDamage() == TNTShip.NETWORK_ID) {

            CompoundTag nbt = new CompoundTag()
                    .putList(new ListTag<DoubleTag>("Pos")
                            .add(new DoubleTag("", b.getX() + 0.5))
                            .add(new DoubleTag("", b.getY()))
                            .add(new DoubleTag("", b.getZ() + 0.5)))
                    .putList(new ListTag<DoubleTag>("Motion")
                            .add(new DoubleTag("", 0))
                            .add(new DoubleTag("", 0))
                            .add(new DoubleTag("", 0)))
                    .putList(new ListTag<FloatTag>("Rotation")
                            .add(new FloatTag("", new Random().nextFloat() * 360))
                            .add(new FloatTag("", 0)));

            TNTShip ship = (TNTShip) Entity.createEntity("TNTShip", b.getLevel().getChunk((int) b.x >> 4, (int) b.z >> 4), nbt, data.getTeam().getId(), this);
            ship.spawnToAll();

            item.count--;
            p.getInventory().setItemInHand(item);
        }*/
    }

    public void messageAlivePlayers(String msg) {
        for (BedWarsData data : new ArrayList<>(this.playerData.values())) {
            if (data.getPlayer().isOnline()) {
                data.getPlayer().sendMessage(BedWars.getPrefix() + msg);
            }
        }
        this.plugin.getServer().getLogger().info(BedWars.getPrefix() + msg);
    }

    public void joinToArena(Player p) {
        if (this.game >= 1) {
            p.sendMessage(BedWars.getPrefix() + TextFormat.BLUE + "Pripojuji jako spectator...");
            this.setSpectator(p);
            return;
        }

        if (this.playerData.size() >= 16 && !p.hasPermission("gameteam.vip")) {
            p.sendMessage(BedWars.getPrefix() + "§cTato arena je jiz plna");
            return;
        }

        if (!this.canJoin) {
            return;
        }
        BedWarsData pl = new BedWarsData(this, p);
        playerData.put(p.getName().toLowerCase(), pl);

        p.setNameTag(p.getName());
        p.sendMessage(BedWars.getPrefix() + TextFormat.GREEN + "Pripojuji do " + this.id + "...");
        p.teleport(this.mainData.get("lobby"));
        p.setSpawn(this.mainData.get("lobby"));

        PlayerInventory inv = p.getInventory();
        inv.clearAll();
        inv.setItem(0, Item.get(159, 11, 1).setCustomName("§r§7Pripojit do §9Blue"));
        inv.setItem(1, Item.get(159, 14, 1).setCustomName("§r§7Pripojit do §4Red"));
        inv.setItem(2, Item.get(159, 4, 1).setCustomName("§r§7Pripojit do §eYellow"));
        inv.setItem(3, Item.get(159, 5, 1).setCustomName("§r§7Pripojit do §aGreen"));

        for (int i = 0; i < 9; i++) {
            inv.setHotbarSlotIndex(20, i);
        }

        inv.sendContents(p);

        p.setAllowFlight(false);
        p.gamemode = 3;
        p.setGamemode(0);

        this.mtcore.unsetLobby(p);
        this.checkLobby();
        this.updateMainSign();
    }

    public void leaveArena(Player p) {
        BedWarsData data = getPlayerData(p);

        if (data != null && data.getTeam() != null) {
            Team pTeam = data.getTeam();

            if (this.game >= 1) {
                pTeam.messagePlayers(pTeam.getColor() + p.getName() + TextFormat.GRAY + " se odpojil");
                new StatQuery(this.plugin, Stat.LOSSES, p.getName());
                new NormalQuery(p, data.points, 0);
            }

            if (p.isOnline()) {
                p.sendMessage(BedWars.getPrefix() + "Opoustim arenu...");
            }

            updateTeamSigns();
        }

        this.unsetPlayer(p);
        this.unsetSpectator(p);

        if (p.isOnline()) {
            mtcore.setLobby(p);
        }

        if (this.game >= 1) {
            this.checkAlive();
        }

        updateMainSign();
    }

    public boolean isLevelLoaded = false;

    public void startGame() {
        if (!isLevelLoaded) {
            return;
        }

        this.task.startTime = 120;
        this.starting = false;
        isLevelLoaded = false;

        this.plugin.getServer().loadLevel(this.map + "_" + id);
        this.level = this.plugin.getServer().getLevelByName(this.map + "_" + id);

        AnvilUseSound sound = new AnvilUseSound(new Vector3());

        for (BedWarsData data : new ArrayList<>(this.playerData.values())) {
            Player p = data.getPlayer();

            this.level.addSound(sound, p);

            Team team = data.getTeam();

            if (team == null) {
                this.selectTeam(data);
                team = data.getTeam();
            }

            p.getInventory().clearAll();
            sound.setComponents(p.x, p.y, p.z);
            p.setExperience(0, 0);
            p.setHealth(20);
            Vector3 d = this.data.get(team.getId() + "spawn");
            p.teleportImmediate(new Location(d.x, d.y, d.z, 0, 0, this.level));
            p.setSpawn(p.temporalVector.setComponents(d.x, d.y, d.z));
            data.getBaseData().setLastOnGround(System.currentTimeMillis());
            data.getBaseData().setLastGroundPos(p.clone());

            Item bronze = Items.BRONZE.clone();
            bronze.setCount(16);
            Item iron = Items.IRON.clone();
            iron.setCount(3);

            if (p.hasPermission("gameteam.vip")) {
                p.getInventory().addItem(bronze.clone());
                p.getInventory().addItem(iron.clone());
                p.getInventory().addItem(Items.GOLD.clone());
            }

        }

        this.level.setTime(0);
        this.level.stopTime();

        this.game = 1;
        this.messageAllPlayers(BedWars.getPrefix() + TextFormat.AQUA + "Hra zacala!");
        this.updateMainSign();
    }

    public void selectTeam(BedWarsData data) {
        Team teamm = null;
        Player p = data.getPlayer();

        for (Team team : teams) {
            if (team == null) {
                continue;
            }

            if (!isTeamFull(team) || isTeamFree(team) || p.hasPermission("gameteam.vip")) {
                teamm = team;
            }
        }

        if (teamm != null) {
            teamm.addPlayer(data);
        }
    }

    @EventHandler
    public void onQuit(PlayerQuitEvent e) {
        if (inArena(e.getPlayer()) || this.isSpectator(e.getPlayer())) {
            this.leaveArena(e.getPlayer());
        }
    }

    public void checkAlive() {
        if (!this.ending) {
            List<Team> aliveTeams = this.getAliveTeams();

            if (aliveTeams.size() == 1) {
                Team team = aliveTeams.get(0);
                winnerTeam = team.getId();

                for (BedWarsData pl : new ArrayList<>(team.getPlayers().values())) {
                    Player p = pl.getPlayer();
                    p.sendMessage(BedWars.getPrefix() + TextFormat.GOLD + "Obdrzel jsi 10 tokenu a 500 xp za vyhru!");
                }

                messageAllPlayers(TextFormat.GRAY + "================[ " + team.getColor() + "Konec Hry" + TextFormat.GRAY + " ]================\n"
                        + TextFormat.GRAY + "Team " + team.getColor() + team.getName() + TextFormat.GRAY + " vyhral BedWars! Probiha restart...\n"
                        + TextFormat.GRAY + new String(new char[42 - 1]).replace("\0", "="));

                ArrayList<String> names = new ArrayList<>();
                team.getPlayers().forEach((n, p) -> names.add(p.getPlayer().getName()));

                new StatQuery(this.plugin, Stat.WINS, names);
                this.ending = true;
            }

        }

        if (this.playerData.size() <= 0) {
            Server.getInstance().getScheduler().scheduleDelayedTask(new Runnable() {
                @Override
                public void run() {
                    stopGame();
                }
            }, 1);
        }

        /*if (this.level != null) {
            if (this.level.getPlayers().size() <= 0) {
                this.stopGame();
            }
        }*/
    }

    public void stopGame() {
        for (Player p : this.spectators.values()) {
            this.mtcore.setLobby(p);
        }

        ArrayList<String> names = new ArrayList<>();

        for (BedWarsData data : this.playerData.values()) {
            this.mtcore.setLobby(data.getPlayer());
            names.add(data.getPlayer().getName());
        }

        new StatQuery(this.plugin, Stat.PLAYED, names);
        new NormalQuery(playerData.values());

        this.unsetAllPlayers();
        this.task.gameTime = 3600;
        this.task.startTime = 120;
        this.task.drop = 0;
        this.task.sign = 0;
        this.popupTask.ending = 0;
        this.votingManager.players.clear();
        this.votingManager.currentTable = new String[4];
        this.votingManager.stats.clear();
        this.votingManager.createVoteTable();
        this.ending = false;
        this.winnerTeam = 0;
        this.game = 0;

        this.level.unload();

        updateMainSign();
    }

    public void unsetAllPlayers() {
        this.spectators.clear();
        this.playerData.clear();
        this.resetTeams();
    }

    //@EventHandler
    public void onRespawn(PlayerRespawnEvent e) {
        Player p = e.getPlayer();
        if (!this.inArena(p)) {
            return;
        }
    }

    public void onDeath(PlayerDeathEvent e) {
        Player p = e.getEntity();

        if (this.game >= 1) {

        }
    }

    @EventHandler
    public void onDropItem(PlayerDropItemEvent e) {
        Player p = e.getPlayer();

        if (!p.isOp() && this.game == 0) {
            e.setCancelled();
        }
    }

    @EventHandler
    public void onHit(EntityDamageEvent e) {
        if (e.isCancelled()) {
            return;
        }

        Entity victim = e.getEntity();

        boolean kill = false;

        BedWarsData data = null;
        BedWarsData kData;

        if (victim instanceof Player) {
            if (!this.inArena((Player) victim)) {
                return;
            }

            if (this.game == 0) {
                if (e.getCause() == EntityDamageEvent.CAUSE_VOID) {
                    victim.teleport(mainData.get("lobby"));
                }

                e.setCancelled();
                return;
            }

            if (e.getFinalDamage() >= victim.getHealth()) {
                kill = true;
                data = playerData.get(victim.getName().toLowerCase());
            }
        }

        if (e instanceof EntityDamageByEntityEvent) {
            if (!(((EntityDamageByEntityEvent) e).getDamager() instanceof Player)) {
                return;
            }

            Player killer = (Player) ((EntityDamageByEntityEvent) e).getDamager();

            if (!inArena(killer)) {
                return;
            }

            if (victim instanceof Player) {
                if (data == null) {
                    data = playerData.get(victim.getName().toLowerCase());
                }
                kData = playerData.get(killer.getName().toLowerCase());

                if (this.game < 1 || data.getTeam().getId() == kData.getTeam().getId()) {
                    e.setCancelled();
                    return;
                }

                if (!kill) {
                    data.setLastHit(System.currentTimeMillis());
                    data.setKiller(killer.getName());
                    data.setKillerColor(kData.getTeam().getColor());
                }
            }

            if (e instanceof EntityDamageByChildEntityEvent) {

            } else if (victim instanceof Villager && killer.getGamemode() == 0) {
                kData = playerData.get(killer.getName().toLowerCase());
                //kData.getTeam().getShop().openWindow(killer);
                ItemWindow inv = kData.getTeam().getShop();

                int id = killer.addWindow(inv);

                if (id >= 0) {
                    inv.onOpen(killer);
                }

                e.setCancelled();

            }
        }

        if (kill) {
            e.setCancelled();

            Player p = (Player) victim;

            EntityEventPacket pk = new EntityEventPacket();
            pk.eid = 0L;
            pk.event = 2;
            p.dataPacket(pk);

            p.setHealth(20);
            p.getFoodData().setFoodSaturationLevel(20);
            p.getFoodData().setLevel(20);
            p.removeAllEffects();

            PlayerDeathEvent event = new PlayerDeathEvent(p, new Item[0], "", 0);

            this.deathManager.onDeath(event);

            new StatQuery(this.plugin, Stat.DEATHS, p.getName());

            p.getInventory().clearAll();

            if (!data.canRespawn()) {
                this.unsetPlayer(p);
                p.sendMessage(BedWars.getPrefix() + TextFormat.YELLOW + "Pripojuji jako spectator...");
                this.setSpectator(p, true);

                new StatQuery(this.plugin, Stat.PLAYED, p.getName());

                checkAlive();
            } else {
                p.teleport(this.data.get(data.getTeam().getId() + "spawn"));
            }
        }
    }

    private static HashSet<Integer> allowedBlocks = new HashSet<>(Arrays.asList(Item.SANDSTONE, 92, 30, 42, 54, 89, 121, 19, 92, Item.OBSIDIAN, Item.BRICKS));

    @EventHandler
    public void onBlockBreak(BlockBreakEvent e) {
        Player p = e.getPlayer();
        Block b = e.getBlock();

        if (e.isCancelled() || e.isFastBreak()) {
            e.setCancelled();
            return;
        }

        BedWarsData data = getPlayerData(p);

        if (data == null) {
            return;
        }

        if (this.isSpectator(p)) {
            e.setCancelled();
            return;
        }

        if (this.game == 0) {
            e.setCancelled();
            return;
        }

        Team isBed = isBed(b);

        if (isBed != null) {
            e.setCancelled(!this.onBedBreak(p, isBed, b));
            return;
        }

        if (b.getId() == Item.SPONGE) {
            Random random = new Random();

            Inventory inv = data.getTeam().getShop().getWindow(random.nextInt(data.getTeam().getShop().windows.size()));
            Item randomItem = inv.getItem(random.nextInt(inv.getSize()));

            switch (randomItem.getId()) {
                case Item.SANDSTONE:
                    randomItem.setCount(20);
                    break;
                case Item.END_STONE:
                    randomItem.setCount(7);
                    break;
                case Item.COOKED_PORKCHOP:
                    randomItem.setCount(8);
                    break;
                case Item.APPLE:
                    randomItem.setCount(16);
                    break;
                default:
                    randomItem.setCount(1);
                    break;
            }

            e.setDrops(new Item[]{randomItem});
            return;
        }

        if (!allowedBlocks.contains(b.getId())) {
            e.setCancelled();
        } else {
            data.points++;
        }
        /*if($b->getId() === 19){
            $section = array_rand($this->shopManager->items, 1);
            $r = array_rand($this->shopManager->items[$section], 1);
            $randItem = $this->shopManager->items[$section][$r];
            if ($randItem instanceof Item){
                $e->setDrops([$randItem]);
            }
            else {
                $e->setDrops([Item::get($randItem, 0, 1)]);
            }
         }*/
    }

    @EventHandler
    public void onBlockPlace(BlockPlaceEvent e) {
        if (e.isCancelled()) {
            return;
        }

        Player p = e.getPlayer();
        Block b = e.getBlock();

        BedWarsData data = getPlayerData(p);

        if (data == null) {
            return;
        }

        if (this.isSpectator(p)) {
            e.setCancelled();
            return;
        }

        if (this.game == 0) {
            e.setCancelled();
            return;
        }

        if (!allowedBlocks.contains(b.getId())) {
            e.setCancelled();
        } else {
            data.points++;
        }
    }

    private boolean onBedBreak(Player p, Team bedteam, Block b) {
        Team pTeam = getPlayerTeam(p);

        if (pTeam.getId() == bedteam.getId()) {
            p.sendMessage(BedWars.getPrefix() + TextFormat.RED + "Nemuzes nicit svou vlastni postel!");
            return false;
        }

        if (!bedteam.hasBed()) {
            return false;
        }

        for (BedWarsData pl : new ArrayList<>(bedteam.getPlayers().values())) {
            if (p.isOnline()) {
                pl.getPlayer().setSpawn(this.plugin.mainLobby);
            }
        }

        new StatQuery(this.plugin, Stat.BEDS, p.getName());

        this.level.addParticle(new LargeExplodeParticle(new Vector3(b.x, b.y, b.z)));
        ExplodePacket pk = new ExplodePacket();
        pk.x = (float) b.x;
        pk.y = (float) b.y;
        pk.z = (float) b.z;
        pk.radius = (float) 5;

        for (BedWarsData data : new ArrayList<>(playerData.values())) {
            data.getPlayer().dataPacket(pk);
        }

        for (Player player : new ArrayList<>(spectators.values())) {
            player.dataPacket(pk);
        }

        int team = this.getPlayerTeam(p).getId();
        String color = this.teams[team].getColor();
        String name = this.teams[team].getName();

        this.messageAllPlayers("§7================[ " + bedteam.getColor() + "Postel znicena§7 ]================.\n"
                + color + p.getName() + "§7 z teamu " + color + name + "§7 znicil postel teamu "
                + bedteam.getColor() + bedteam.getName() + "\n"
                + "§7==========================================");
        bedteam.onBedBreak();

        checkAlive();
        return true;
    }

    public int isJoinSign(Block b) {
        if (b.equals(mainData.get("1sign"))) {
            return 1;
        } else if (b.equals(mainData.get("2sign"))) {
            return 2;
        } else if (b.equals(mainData.get("3sign"))) {
            return 3;
        } else if (b.equals(mainData.get("4sign"))) {
            return 4;
        } else {
            return 0;
        }
    }

    public Team isBed(Block b) {
        if (b.getId() != Item.BED_BLOCK) {
            return null;
        }

        Vector3 b1 = this.data.get("1bed");
        Vector3 b12 = this.data.get("1bed2");
        Vector3 b2 = this.data.get("2bed");
        Vector3 b22 = this.data.get("2bed2");
        Vector3 b3 = this.data.get("3bed");
        Vector3 b32 = this.data.get("3bed2");
        Vector3 b4 = this.data.get("4bed");
        Vector3 b42 = this.data.get("4bed2");

        if (b.equals(b1) || b.equals(b12)) {
            return teams[1];
        } else if (b.equals(b2) || b.equals(b22)) {
            return teams[2];
        } else if (b.equals(b3) || b.equals(b32)) {
            return teams[3];
        } else if (b.equals(b4) || b.equals(b42)) {
            return teams[4];
        }

        return null;
    }

    public Team getPlayerTeam(Player p) {
        if (this.playerData.containsKey(p.getName().toLowerCase())) {
            return this.playerData.get(p.getName().toLowerCase()).getTeam();
        }
        return null;
    }

    public int getPlayerColor(Player p) {
        if (this.playerData.containsKey(p.getName())) {
            return this.playerData.get(p.getName()).getTeam().getDecimal();
        }
        return 0;
    }

    public boolean isTeamFree(Team team) {
        ArrayList<Integer> teams = new ArrayList<>();

        for (int i = 1; i < 5; i++) {
            if (i == team.getId()) {
                continue;
            }

            teams.add(this.teams[i].getPlayers().size());
        }

        int minPlayers = Math.min(teams.get(2), Math.min(teams.get(0), teams.get(1)));

        return (team.getPlayers().size() - minPlayers) < 2;
    }

    public void addToTeam(Player p, int team) {
        Team pTeam = teams[team];
        BedWarsData data = playerData.get(p.getName().toLowerCase());

        if ((isTeamFull(pTeam) || !isTeamFree(pTeam)) && !p.hasPermission("gameteam.vip")) {
            p.sendMessage(BedWars.getPrefix() + "§cTento team je plny");
            return;
        }

        Team currentTeam = data.getTeam();

        if (currentTeam != null) {
            if (currentTeam.getId() == pTeam.getId()) {
                p.sendMessage(BedWars.getPrefix() + TextFormat.GRAY + "Jiz jsi v teamu " + pTeam.getColor() + pTeam.getName());
                return;
            }

            if (currentTeam.getId() != 0) {
                currentTeam.removePlayer(data);
            }
        }

        pTeam.addPlayer(data);

        updateTeamSigns();

        p.sendMessage(TextFormat.GRAY + "Pripojil ses k teamu " + pTeam.getColor() + pTeam.getName());
    }

    public boolean isTeamFull(Team team) {
        return team.getPlayers().size() >= 4;
    }

    public void unsetPlayer(Player p) {
        //this.unsetSpectator(p);

        BedWarsData data = playerData.remove(p.getName().toLowerCase());

        if (data != null && data.getTeam() != null) {
            data.getTeam().removePlayer(data);
        }

        //new ChangeDisplayNameTask(this.plugin, p.getName(), 1);
        //p.setGamemode(0);

        /*if (p.isOnline()) {
            mtcore.setLobby(p);
        }*/
    }

    public void dropBronze() {
        /*HashMap<Integer, BlockEntityChest> chests = new HashMap<>();
        chests.put(1, (BlockEntityChest) this.level.getBlockEntity(this.data.get("1bronze")));
        chests.put(2, (BlockEntityChest) this.level.getBlockEntity(this.data.get("2bronze")));
        chests.put(3, (BlockEntityChest) this.level.getBlockEntity(this.data.get("3bronze")));
        chests.put(4, (BlockEntityChest) this.level.getBlockEntity(this.data.get("4bronze")));
        for (BlockEntityChest chest : chests.values()){
            chest.getInventory().addItem(Item.get(336, 0, 1).setCustomName("§r§6Bronz"));
        }*/
        this.dropItem(this.data.get("1bronze"), Items.BRONZE);
        this.dropItem(this.data.get("2bronze"), Items.BRONZE);
        this.dropItem(this.data.get("3bronze"), Items.BRONZE);
        this.dropItem(this.data.get("4bronze"), Items.BRONZE);
    }

    public void dropIron() {
        this.dropItem(this.data.get("1iron"), Items.IRON);
        this.dropItem(this.data.get("2iron"), Items.IRON);
        this.dropItem(this.data.get("3iron"), Items.IRON);
        this.dropItem(this.data.get("4iron"), Items.IRON);
    }

    public void dropGold() {
        this.dropItem(this.data.get("1gold"), Items.GOLD);
        this.dropItem(this.data.get("2gold"), Items.GOLD);
        this.dropItem(this.data.get("3gold"), Items.GOLD);
        this.dropItem(this.data.get("4gold"), Items.GOLD);
    }

    private void dropItem(Vector3 v, Item item) {
        Vector3 motion = new Vector3(0/*(new Random()).nextDouble() * 0.2D - 0.1D*/, 0.2D, /*(new Random()).nextDouble() * 0.2D - 0.1D*/0);
        CompoundTag itemTag = NBTIO.putItemHelper(item);
        itemTag.setName("Item");

        Entity[] entities = this.level.getNearbyEntities(new AxisAlignedBB(v.x - 1, v.y - 1, v.z - 1, v.x + 1, v.y + 1, v.z + 1));

        for (Entity entity : entities) {
            if (entity instanceof EntityItem) {
                EntityItem entityItem = (EntityItem) entity;

                if (!entityItem.closed && entityItem.isAlive() && entityItem.getItem().count < 64 && entityItem.getItem().equals(item, true, false)) {
                    entityItem.getItem().count++;
                    return;
                }
            }
        }

        SpecialItem itemEntity = new SpecialItem(this.level.getChunk((int) v.getX() >> 4, (int) v.getZ() >> 4, true), (new CompoundTag()).putList((new ListTag("Pos")).add(new DoubleTag("", v.getX() + 0.5)).add(new DoubleTag("", v.getY())).add(new DoubleTag("", v.getZ() + 0.5))).putList((new ListTag("Motion")).add(new DoubleTag("", motion.x)).add(new DoubleTag("", motion.y)).add(new DoubleTag("", motion.z))).putList((new ListTag("Rotation")).add(new FloatTag("", (new Random()).nextFloat() * 360.0F)).add(new FloatTag("", 0.0F))).putShort("Health", 5).putCompound("Item", itemTag).putShort("PickupDelay", 10));

        if (item.getId() > 0 && item.getCount() > 0) {
            itemEntity.spawnToAll();
        }
    }

    public void messageAllPlayers(String message) {
        for (BedWarsData data : new ArrayList<>(playerData.values())) {
            data.getPlayer().sendMessage(message);
        }

        for (Player p : new ArrayList<>(spectators.values())) {
            p.sendMessage(message);
        }

        this.plugin.getServer().getLogger().info(message);
    }

    public void messageAllPlayers(String message, Player player) {
        BedWarsData pData = playerData.get(player.getName().toLowerCase());
        String color = pData.getTeam().getColor();
        String msg = TextFormat.GRAY + "[" + color + "All" + TextFormat.GRAY + "]   " + player.getDisplayName() + TextFormat.GRAY + " > " + pData.getBaseData().getChatColor() + message.substring(1);

        for (BedWarsData data : new ArrayList<>(playerData.values())) {
            data.getPlayer().sendMessage(msg);
        }

        for (Player p : new ArrayList<>(spectators.values())) {
            p.sendMessage(msg);
        }

        this.plugin.getServer().getLogger().info(TextFormat.GRAY + "[" + color + "] " + player.getDisplayName() + TextFormat.DARK_AQUA + " > " + TextFormat.GRAY + message);
    }

    public String getGameStatus() {
        Team t1 = teams[1];
        Team t2 = teams[2];
        Team t3 = teams[3];
        Team t4 = teams[4];

        return "                                          §8Mapa: §6" + this.map + "\n" + t1.getStatus() + t2.getStatus() + t3.getStatus() + t4.getStatus() + "\n" + "\n";
    }

    public void selectMap() {
        selectMap(false);
    }

    public void selectMap(boolean force) {
        String map = "";
        int points = -1;

        for (Map.Entry<String, Integer> entry : this.votingManager.stats.entrySet()) {
            if (points < entry.getValue()) {
                map = entry.getKey();
                points = entry.getValue();
            }
        }

        if (this.plugin.getServer().isLevelLoaded(map)) {
            this.plugin.getServer().unloadLevel(this.plugin.getServer().getLevelByName(map));
        }

        new WorldCopyTask(this.plugin, map, this.id, force);

        this.map = map;
        this.data = this.plugin.maps.get(map);

        messageAllPlayers(TextFormat.BOLD + TextFormat.GOLD + "Vybrana mapa " + TextFormat.YELLOW + map);
    }

    public void checkLobby() {
        if (this.playerData.size() >= 12 && this.game == 0) {
            this.starting = true;
        } else if (this.playerData.size() >= 16 && this.game == 0 && this.task.startTime > 10) {
            this.task.startTime = 10;
        }
    }

    public ArrayList<Team> getAliveTeams() {
        ArrayList<Team> teams = new ArrayList<>();

        for (Team team : this.teams) {
            if (team == null) {
                continue;
            }

            if (team.hasBed() || team.getPlayers().size() > 0) {
                teams.add(team);
            }
        }

        return teams;
    }

    @EventHandler
    public void onItemHold(PlayerItemHeldEvent e) {
        Player p = e.getPlayer();
        if (!inArena(p)) {
            return;
        }

        if (this.game <= 0 && e.getItem().getId() == 159) {
            switch (e.getItem().getDamage()) {
                case 11:
                    this.addToTeam(p, 1);
                    e.setCancelled();
                    break;
                case 14:
                    this.addToTeam(p, 2);
                    e.setCancelled();
                    break;
                case 4:
                    this.addToTeam(p, 3);
                    e.setCancelled();
                    break;
                case 5:
                    this.addToTeam(p, 4);
                    e.setCancelled();
                    break;
            }
        }
    }

    /*@EventHandler
    public void onItemTake(InventoryPickupItemEvent e) {
        Inventory inv = e.getInventory();
        if (inv.getHolder() instanceof Player) {
            Player p = (Player) inv.getHolder();
        }
    }*/

    @EventHandler
    public void onArrowPickup(InventoryPickupArrowEvent e) {
        e.setCancelled();
    }

    public boolean inArena(Player p) {
        return this.playerData.containsKey(p.getName().toLowerCase());
    }

    @EventHandler
    public void onBucketFill(PlayerBucketFillEvent e) {
        Player p = e.getPlayer();
        if (!p.isOp() || this.inArena(p)) {
            e.setCancelled();
        }
    }

    @EventHandler
    public void onBucketEmpty(PlayerBucketEmptyEvent e) {
        Player p = e.getPlayer();
        if (!p.isOp() || this.inArena(p)) {
            e.setCancelled();
        }
    }

    @EventHandler
    public void onCraft(CraftItemEvent e) {
        e.setCancelled();
    }

    @EventHandler
    public void onBedEnter(PlayerBedEnterEvent e) {
        e.setCancelled();
    }

    public void resetTeams() {
        this.writeTeams();
    }

    public boolean isSpectator(Player p) {
        return this.spectators.containsKey(p.getName().toLowerCase());
    }

    public void setSpectator(Player p) {
        setSpectator(p, false);
    }

    public void setSpectator(Player p, boolean respawn) {
        if (this.game == 0) {
            return;
        }

        Position tpPos = p;

        if (!respawn) {
            this.mtcore.unsetLobby(p);
            Random random = new Random();
            tpPos = this.playerData.get(new ArrayList<>(playerData.keySet()).get(random.nextInt(playerData.size()))).getPlayer();
        }

        if (tpPos.y < 10) {
            tpPos.y = 10;
        }

        this.spectators.put(p.getName().toLowerCase(), p);

        p.teleport(tpPos);
        p.setSneaking(false);
        p.setGamemode(3);
        //p.getInventory().setItem(0, Item.get(Item.CLOCK).setCustomName(randPlayer.getName().toLowerCase()));
        //p.getInventory().setHotbarSlotIndex(0, 0);
        p.getInventory().clearAll();
        p.getInventory().sendContents(p);

        p.setNameTag(p.getName());

        /*for (Team team : this.teams) {
            for (BedWarsData pla : new ArrayList<>(team.getPlayers().values())) {
                Player pl = pla.getPlayer();
                p.despawnFrom(pl);
            }
        }*/
    }

    /*public void setSpectator(Player p, boolean respawn) {
        if (this.game == 0) {
            return;
        }

        BedWarsData data = getPlayerData(p);

        if (data != null) {
            playerData.remove(p.getName().toLowerCase());
        }


        if (mtcore.inLobby(p)) {
            this.mtcore.unsetLobby(p);
        }

        this.spectators.put(p.getName().toLowerCase(), p);
        p.getInventory().clearAll();
        p.teleport(new Position(p.x, p.y + 10, p.z, this.level));
        p.setSneaking(false);
        p.setGamemode(3);
        p.getInventory().setItem(0, Item.get(Item.CLOCK));
        p.getInventory().setHotbarSlotIndex(0, 0);
        p.getInventory().sendContents(p);

        /*for (Team team : this.teams) {
            for (BedWarsData pla : new ArrayList<>(team.getPlayers().values())) {
                Player pl = pla.getPlayer();
                p.despawnFrom(pl);
            }
        }
    }*/

    public void unsetSpectator(Player p) {
        this.spectators.remove(p.getName().toLowerCase());
    }

    public boolean isChest(Block b) {
        return (b.equals(this.data.get("1bronze")) || b.equals(this.data.get("2bronze")) || b.equals(this.data.get("3bronze")) || b.equals(this.data.get("4bronze")));
    }

    public boolean isEnderChest(Block b) {
        return (b.equals(this.data.get("1chest")) || b.equals(this.data.get("2chest")) || b.equals(this.data.get("3chest")) || b.equals(this.data.get("4chest")));
    }

    @EventHandler
    public void onProjectileHit(ProjectileHitEvent e) {
        Entity ent = e.getEntity();
        if (ent instanceof EntitySnowball) {
            if (((EntitySnowball) ent).shootingEntity instanceof Player) {
                Player p = (Player) ((EntitySnowball) ent).shootingEntity;
                if (this.inArena(p)) {
                    p.teleport(ent.getPosition());
                    EntityDamageEvent ev = new EntityDamageEvent(p, EntityDamageEvent.CAUSE_FALL, 4);
                    p.attack(ev);
                }
            }
        }
    }

    public BlockEntityChest getTeamEnderChest(int team) {
        return (BlockEntityChest) this.level.getBlockEntity(this.data.get(team + "chest"));
    }

    @EventHandler
    public void onChat(PlayerChatEvent e) {
        Player p = e.getPlayer();
        BedWarsData data = getPlayerData(p);
        Player spectator = spectators.get(p.getName().toLowerCase());

        if (e.isCancelled() || (data == null && spectator == null)) {
            return;
        }

        e.setCancelled();


        if (spectator != null) {
            PlayerData data2 = mtcore.getPlayerData(p);
            String msg = TextFormat.GRAY + "[" + TextFormat.BLUE + "SPECTATE" + TextFormat.GRAY + "] " + TextFormat.RESET + TextFormat.WHITE + p.getDisplayName() + TextFormat.GRAY + " > " + data2.getChatColor() + e.getMessage();

            for (Player s : spectators.values()) {
                s.sendMessage(msg);
            }
        } else if (e.getMessage().startsWith("!") && e.getMessage().length() > 1) {
            this.messageAllPlayers(e.getMessage(), p);
        } else if (data.getTeam() != null) {
            data.getTeam().messagePlayers(e.getMessage(), data);
        } else {
            messageAllPlayers(TextFormat.GRAY + "[" + TextFormat.DARK_PURPLE + "Lobby" + TextFormat.GRAY + "] " + p.getDisplayName() + TextFormat.GRAY + " > " + data.getBaseData().getChatColor() + e.getMessage());
        }
    }

    @EventHandler
    public void onTransaction(InventoryTransactionEvent e) {
        PlayerInventory inv = null;
        Player p = null;

        Window inv2 = null;
        int slot = -1;

        for (Transaction t : e.getTransaction().getTransactions()) {
            if (t.getInventory() instanceof Window) {
                inv2 = (Window) t.getInventory();
                slot = t.getSlot();
            } else if (t.getInventory() instanceof PlayerInventory) {
                inv = (PlayerInventory) t.getInventory();
                p = (Player) ((PlayerInventory) t.getInventory()).getHolder();
            }
        }

        if (inv != null && inv2 != null) {
            if (!inArena(p)) {
                e.setCancelled();
                return;
            }

            if (inv2 instanceof ShopWindow) {
                if (slot == 0) {
                    Item cost = ((ShopWindow) inv2).getCost();
                    Item item = ((ShopWindow) inv2).getItem();

                    if (!Items.containsItem(inv, cost)) {
                        p.sendMessage(TextFormat.RED + "Ke koupi ti chybi " + cost.getCustomName());
                        return;
                    }

                    if (!inv.canAddItem(item)) {
                        p.sendMessage(TextFormat.RED + "Mas plny inventar!");
                    }

                    Items.removeItem(inv, cost);
                    inv.addItem(item);

                    p.sendMessage(BedWars.getPrefix() + TextFormat.GREEN + "Uspesne sis koupil " + item.getCustomName());
                } else if (inv2.getWindow(slot) != null) {
                    //p.removeWindow(inv2);
                    p.addWindow(inv2.getWindow(slot));

                    inv2.getWindow(slot).onOpen(p);
                }
            } else if (inv2 instanceof ItemWindow) {
                Window newWindow = inv2.getWindow(slot);

                if (newWindow != null) {
                    /*p.removeWindow(inv2);
                    ContainerSetContentPacket pk = new ContainerSetContentPacket();
                    pk.windowid = p.getWindowId(inv2);
                    pk.slots = new Item[inv2.getSize()];
                    Arrays.fill(pk.slots, new ItemBlock(new BlockAir(), null, 0));

                    p.dataPacket(pk);*/

                    //p.removeWindow(inv2);
                    int id = p.addWindow(newWindow);

                    if (id >= 0) {
                        newWindow.onOpen(p);
                    }
                }
            }
        }
    }

    public void updateMainSign() {
        BlockEntity t = this.plugin.level.getBlockEntity(this.mainData.get("sign"));
        if (!(t instanceof BlockEntitySign)) {
            return;
        }
        BlockEntitySign tile = (BlockEntitySign) t;
        String mapname = this.map;
        String map;

        if (this.game <= 0) {
            map = "---";
        } else {
            map = mapname;
        }
        String game = "§aLobby";
        if (this.game == 1) {
            game = "§cIngame";
        }
        if (this.game != 1 && !this.canJoin) {
            game = "§c§lRESTART";
        }
        tile.setText("§4■" + this.id + "■", "§0" + this.playerData.size() + "/16", game, "§l§0" + map);
    }

    public void updateTeamSigns() {
        BlockEntity b = this.plugin.level.getBlockEntity(this.mainData.get("1sign"));
        if (!(b instanceof BlockEntitySign)) {
            return;
        }
        BlockEntitySign blue = (BlockEntitySign) b;
        BlockEntity r = this.plugin.level.getBlockEntity(this.mainData.get("2sign"));
        if (!(r instanceof BlockEntitySign)) {
            return;
        }
        BlockEntitySign red = (BlockEntitySign) r;
        BlockEntity y = this.plugin.level.getBlockEntity(this.mainData.get("3sign"));
        if (!(y instanceof BlockEntitySign)) {
            return;
        }
        BlockEntitySign yellow = (BlockEntitySign) y;
        BlockEntity g = this.plugin.level.getBlockEntity(this.mainData.get("4sign"));
        if (!(g instanceof BlockEntitySign)) {
            return;
        }
        BlockEntitySign green = (BlockEntitySign) g;

        blue.setText("", "§l§9[BLUE]", "§7" + this.teams[1].getPlayers().size() + " players", "");
        red.setText("", "§l§c[RED]", "§7" + this.teams[2].getPlayers().size() + " players", "");
        yellow.setText("", "§l§e[YELLOW]", "§7" + this.teams[3].getPlayers().size() + " players", "");
        green.setText("", "§l§a[GREEN]", "§7" + this.teams[4].getPlayers().size() + " players", "");
    }

    public BedWarsData getPlayerData(Player p) {
        return playerData.get(p.getName().toLowerCase());
    }

    @EventHandler
    public void onHungerChange(PlayerFoodLevelChangeEvent e) {
        if (e.isCancelled()) {
            return;
        }

        Player p = e.getPlayer();

        if (inArena(p) && this.game <= 0) {
            e.setCancelled();
            /*if(p.getFoodData().getLevel() < 20){
                p.getFoodData().setLevel(20);
            }*/
        }
    }

    @EventHandler
    public void onItemPickup(InventoryPickupItemEvent e) {
        EntityItem item = e.getItem();

        Player p = (Player) e.getInventory().getHolder();

        if (p == null) {
            return;
        }

        BedWarsData data = getPlayerData(p);

        if (data != null && this.game >= 1 && item instanceof SpecialItem) {
            data.points++;
        }

        //item.getItem().getNamedTag().print(System.out);
    }

    @EventHandler
    public void onFireSpread(BlockBurnEvent e) {
        e.setCancelled();
    }

    /*@EventHandler
    public void onInventoryClose(InventoryCloseEvent e){
        Inventory inv = e.getInventory();

        if(inv instanceof ItemWindow && ((ItemWindow) inv).isMain()){
            ((ItemWindow) inv).removeWindow(e.getPlayer());
            return;
        }

        if(inv instanceof Window) {
            e.setCancelled(true);

            e.getPlayer().addWindow(inv);
        }
    }*/
}
