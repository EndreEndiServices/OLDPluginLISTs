package Annihilation.Arena;

import Annihilation.Arena.Inventory.EnderBrewingInventory;
import Annihilation.Arena.Inventory.EnderFurnaceInventory;
import Annihilation.Arena.Kits.Handyman;
import Annihilation.Arena.Kits.Kit;
import Annihilation.Arena.Kits.Operative;
import Annihilation.Arena.Kits.Spy;
import Annihilation.Arena.Manager.*;
import Annihilation.Arena.Object.Nexus;
import Annihilation.Arena.Object.PlayerData;
import Annihilation.Arena.Object.Shop;
import Annihilation.Arena.Object.Team;
import Annihilation.Arena.Task.BlockRegenerateTask;
import Annihilation.Arena.Task.WorldCopyTask;
import Annihilation.Entity.FishingHook;
import Annihilation.MySQL.NormalQuery;
import MTCore.MTCore;
import cn.nukkit.entity.Entity;
import cn.nukkit.event.EventHandler;
import cn.nukkit.event.EventPriority;
import cn.nukkit.event.TextContainer;
import cn.nukkit.event.player.*;
import cn.nukkit.inventory.*;
import cn.nukkit.level.Level;
import cn.nukkit.level.Position;
import cn.nukkit.level.particle.LargeExplodeParticle;
import cn.nukkit.level.particle.LavaDripParticle;
import cn.nukkit.level.particle.SmokeParticle;
import cn.nukkit.level.sound.NoteBoxSound;
import cn.nukkit.nbt.tag.CompoundTag;
import cn.nukkit.nbt.tag.DoubleTag;
import cn.nukkit.nbt.tag.FloatTag;
import cn.nukkit.nbt.tag.ListTag;
import cn.nukkit.utils.TextFormat;
import cn.nukkit.Player;
import cn.nukkit.Server;
import cn.nukkit.event.block.BlockPlaceEvent;
import cn.nukkit.event.block.BlockBreakEvent;
import cn.nukkit.event.entity.EntityDamageEvent;
import cn.nukkit.event.entity.EntityDamageByEntityEvent;
import cn.nukkit.event.entity.EntityDamageByChildEntityEvent;
import cn.nukkit.event.Listener;
import cn.nukkit.math.Vector3;
import cn.nukkit.math.AxisAlignedBB;
import cn.nukkit.block.Block;
import cn.nukkit.item.Item;
import cn.nukkit.level.sound.AnvilFallSound;
import Annihilation.Annihilation;
import cn.nukkit.network.protocol.ExplodePacket;
import cn.nukkit.event.inventory.InventoryTransactionEvent;
import org.apache.commons.lang.math.NumberUtils;

import java.util.*;

public class Arena extends ArenaManager implements Listener {

    public Annihilation plugin;

    public String id;

    public ArenaSchedule task;

    public PopupTask popupTask;

    public KitManager kitManager;

    public VotingManager votingManager;

    public WorldManager worldManager;

    public BossManager bossManager;

    public EnderManager enderManager;

    public DeathManager deathManager;

    private static Arena instance;

    public int phase = 0;
    public boolean starting = false;
    public boolean ending = false;

    private int gamesCount = 0;

    public Level level;

    public final HashMap<String, PlayerData> playersData = new HashMap<>();

    public final HashMap<String, Player> players = new HashMap<>();

    public HashMap<String, Object> data;

    public HashMap<String, Vector3> maindata;

    //public MySQLManager mysql;

    public MTCore mtcore;

    public Team winnerteam;

    public String map;

    public Shop shopManager;

    private final static HashMap<Integer, Resource> resources = new HashMap<>();

    public Arena(String id, Annihilation plugin) {
        super();
        super.plugin = this;
        this.plugin = plugin;
        this.id = id;
        this.maindata = this.plugin.arenas.get(id);
        //this.mysql = this.plugin.mysql;
        this.mtcore = this.plugin.mtcore;
        this.kitManager = new KitManager(this);
        this.votingManager = new VotingManager(this);
        this.worldManager = new WorldManager();
        this.bossManager = new BossManager(this);
        this.enderManager = new EnderManager(this);
        this.deathManager = new DeathManager(this);
        this.shopManager = new Shop();
        this.registerTeams();
        this.votingManager.createVoteTable();
        this.plugin.getServer().getScheduler().scheduleRepeatingTask(this.task = new ArenaSchedule(this), 20);
        this.plugin.getServer().getScheduler().scheduleRepeatingTask(this.popupTask = new PopupTask(this), 10);
        Arena.instance = this;
    }

    static {
        resources.put(Item.COAL_ORE, new Resource(Item.COAL, 8, 10, Resource.TYPE_ORE));
        resources.put(Item.IRON_ORE,
                new Resource(Item.IRON_ORE, 10, 20, Resource.TYPE_ORE));
        resources.put(Item.GOLD_ORE,
                new Resource(Item.GOLD_ORE, 10, 20, Resource.TYPE_ORE));
        resources.put(Item.DIAMOND_ORE,
                new Resource(Item.DIAMOND, 12, 30, Resource.TYPE_ORE));
        resources.put(Item.EMERALD_ORE,
                new Resource(Item.EMERALD, 18, 40, Resource.TYPE_ORE));
        resources.put(Item.REDSTONE_ORE,
                new Resource(Item.REDSTONE, 10, 20, Resource.TYPE_ORE));
        resources.put(Item.LAPIS_ORE,
                new Resource(Item.DYE, 10, 20, 4, Resource.TYPE_ORE));
        resources.put(Item.GLOWING_REDSTONE_ORE,
                new Resource(Item.REDSTONE, 10, 20, Resource.TYPE_ORE));
        resources.put(Item.LOG,
                new Resource(Item.LOG, 2, 10, Resource.TYPE_WOOD));
        resources.put(Item.LOG2,
                new Resource(Item.LOG2, 2, 10, Resource.TYPE_WOOD));
        resources.put(Item.GRAVEL,
                new Resource(null, 2, 20, 3));
        resources.put(Item.MELON_BLOCK,
                new Resource(null, 0, 10, 3));
    }

    public static Arena getInstance() {
        return Arena.instance;
    }

    @EventHandler(priority = EventPriority.NORMAL, ignoreCancelled = false)
    public void onPlayerQuit(PlayerQuitEvent e) {
        handlePlayerQuit(e.getPlayer());
    }

    public void handlePlayerQuit(Player p) {
        if (inArena(p)) {
            Team team = getPlayerTeam(p);
            if (team != null) {
                team.removePlayer(p);
            }

            p.setMaxHealth(20);

            if (this.phase >= 1 && team != null) {
                if (p.isAlive() && p.getInventory() != null) {
                    getPlayerData(p).saveInventory(p);
                }
            }
            PlayerData data = this.getPlayerData(p);

            data.setLobby(false);
            players.remove(p.getName().toLowerCase());
            plugin.mtcore.setLobby(p);
            //$this->checkAlive();
        }
    }

    public void joinToArena(Player p) {
        if (this.inArena(p)) {
            return;
        }

        boolean wasInGame = false;

        PlayerData data;

        if (this.getPlayerData(p) == null) {
            data = this.createPlayerData(p);
        } else {
            data = this.getPlayerData(p);
            if (data.getTeam() != null) {
                wasInGame = true;
            }
        }

        if (this.phase >= 5 && !wasInGame && !p.isOp()) {
            p.sendMessage(Annihilation.getPrefix() + TextFormat.RED + "You can not join in this phase");
            return;
        }

        if (wasInGame && this.phase >= 1 && !data.getTeam().getNexus().isAlive()) {
            p.sendMessage(Annihilation.getPrefix() + TextFormat.RED + "Your team nexus has been destroyed");
            return;
        }

        this.players.put(p.getName().toLowerCase(), p);
        this.plugin.mtcore.unsetLobby(p);

        if (wasInGame) {
            this.addToTeam(p, data.getTeam());

            if (this.phase >= 1) {
                this.teleportToArena(p);
                return;
            }

            p.setNameTag(p.getName());
            data.setLobby(true);
            this.kitManager.addKitWindow(p);
        } else {
            p.setNameTag(p.getName());
            data.setLobby(true);
            this.kitManager.addKitWindow(p);
        }

        Vector3 newPos = this.maindata.get("lobby");

        //plugin.level.loadChunk((int) newPos.x >> 4, (int) newPos.z >> 4);

        p.teleportImmediate(newPos);
        p.setSpawn(newPos);
        p.sendMessage(Annihilation.getPrefix() + TextFormat.GREEN + "Joining to " + this.id + "...");
        p.sendMessage(Annihilation.getPrefix() + TextFormat.GOLD + "Open your inventory to select kit");
        this.checkLobby();
    }

    @EventHandler(priority = EventPriority.NORMAL, ignoreCancelled = false)
    public void onHurt(EntityDamageEvent e) {
        Entity entity = e.getEntity();

        /*if (entity instanceof IronGolem) {
            if (e.getFinalDamage() >= entity.getHealth()) {
                e.setCancelled();
                entity.close();

                String pname = "";

                if (e instanceof EntityDamageByEntityEvent && ((EntityDamageByEntityEvent) e).getDamager() instanceof Player) {
                    Player damager = (Player) ((EntityDamageByEntityEvent) e).getDamager();
                    pname = this.getPlayerTeam(damager).getColor() + damager.getName();
                }else{
                    e.setCancelled();
                    return;
                }

                if (entity.getNameTag().contains("Celariel")) {
                    this.bossManager.onBossDeath(2, pname);
                } else if (entity.getNameTag().contains("Ferwin")) {
                    this.bossManager.onBossDeath(1, pname);
                }
            }
            return;
        }*/

        if (e.isCancelled() || !(entity instanceof Player) || !this.inArena((Player) entity)) {
            return;
        }

        if (e.getCause() == 4 && this.getPlayerData((Player) entity).getKit() == Kit.ACROBAT) {
            e.setCancelled();
            return;
        }

        if (e instanceof EntityDamageByEntityEvent) {
            Player victim = (Player) e.getEntity();
            Player killer = (Player) ((EntityDamageByEntityEvent) e).getDamager();

            if (killer != null && victim != null) {
                if (this.getPlayerTeam(killer) == null || this.getPlayerTeam(victim) == null) {
                    e.setCancelled();
                    return;
                }

                if (!this.inArena(killer) || this.getPlayerTeam(killer).getId() == this.getPlayerTeam(victim).getId() || this.phase == 0 || victim.getLevel() == this.plugin.level) {
                    e.setCancelled();
                    return;
                }

                PlayerData data = this.getPlayerData(victim);
                PlayerData killerData = this.getPlayerData(killer);

                if (killerData.getKit().equals("warrior")) {
                    e.setDamage(e.getDamage() + 1);
                }

                /*if (e.getFinalDamage() < victim.getHealth()) {
                    killer->spawnTo($victim);
                }*/

                data.setKiller(killerData);
            }
        } else if (e instanceof EntityDamageByChildEntityEvent) {
            Player killer = (Player) ((EntityDamageByChildEntityEvent) e).getDamager();
            Player victim = (Player) e.getEntity();

            if (killer != null && victim != null) {
                if (this.getPlayerTeam(killer).getId() == this.getPlayerTeam(victim).getId() || this.phase == 0 || victim.getLevel() == this.plugin.level) {
                    e.setCancelled();
                    return;
                }
                PlayerData data = this.getPlayerData(victim);

                PlayerData killerData = this.getPlayerData(killer);

                if (killerData.getKit().equals("archer")) {
                    e.setDamage(e.getDamage() + 1);
                }

                data.setKiller(killerData);
            }
        }
    }

    private HashSet<String> cobblestone = new HashSet<>();

    @EventHandler(priority = EventPriority.NORMAL, ignoreCancelled = false)
    public void onBlockBreak(BlockBreakEvent e) {
        Block b = e.getBlock();
        Player player = e.getPlayer();
        e.setInstaBreak(true);
        if (!this.inArena(player) && !player.isOp()) {
            e.setCancelled();
            return;
        }
        if (this.phase < 1) {
            return;
        }

        Vector3 corner1 = ((Vector3) this.data.get("corner1"));
        Vector3 corner2 = ((Vector3) this.data.get("corner2"));

        if (!this.contains(corner1, corner2, b)) {
            player.sendMessage(TextFormat.RED + "You haven't permissions for that");
            e.setCancelled();
            return;
        }

        if (this.phase >= 1) {
            Vector3 blueNex = (Vector3) this.data.get("1Nexus");
            Vector3 redNex = (Vector3) this.data.get("2Nexus");
            Vector3 yellowNex = (Vector3) this.data.get("3Nexus");
            Vector3 greenNex = (Vector3) this.data.get("4Nexus");

            if (b.getId() == Item.END_STONE) {
                if (b.equals(blueNex)) {
                    e.setCancelled();
                    this.breakNexus(player, this.getTeam(1));
                    return;
                } else if (b.equals(redNex)) {
                    e.setCancelled();
                    this.breakNexus(player, this.getTeam(2));
                    return;
                } else if (b.equals(yellowNex)) {
                    e.setCancelled();
                    this.breakNexus(player, this.getTeam(3));
                    return;
                } else if (b.equals(greenNex)) {
                    e.setCancelled();
                    this.breakNexus(player, this.getTeam(4));
                    return;
                }
            }

            if (this.contains(blueNex.x - 15, 0, blueNex.z - 15, blueNex.x + 15, 128, blueNex.z + 15, b) || this.contains(redNex.x - 15, 0, redNex.z - 15, redNex.x + 15, 128, redNex.z + 15, b) || this.contains(yellowNex.x - 15, 0, yellowNex.z - 15, yellowNex.x + 15, 128, yellowNex.z + 15, b) || this.contains(greenNex.x - 15, 0, greenNex.z - 15, greenNex.x + 15, 128, greenNex.z + 15, b)) {
                e.setCancelled();
                player.sendMessage(TextFormat.RED + "You can't destroy blocks close to the nexus");
                return;
            }

            //if (b.getId() == 14 || b.getId() == 15 || b.getId() == 16 || b.getId() == 21 || b.getId() == 56 || b.getId() == 73 || b.getId() == 74 || b.getId() == 103 || b.getId() == 129 || b.getId() == 17 || b.getId() == 162) {
            if (resources.containsKey(b.getId())) {
                Resource block = resources.get(b.getId());

                e.setDrops(new Item[0]);

                if (b.getId() == Item.GRAVEL) {
                    e.setCancelled();
                    player.getInventory().addItem(this.getGravelDrops());
                    this.level.setBlock(b, Block.get(4));
                    this.cobblestone.add(b.getLocationHash());
                } else if (b.getId() == Item.MELON_BLOCK) {
                    player.getInventory().addItem(Item.get(Item.MELON_SLICE, 0, new Random().nextInt(5) + 3));
                } else {
                    boolean miner = block.type == Resource.TYPE_ORE;
                    boolean berserker = block.type == Resource.TYPE_WOOD;

                    Integer kit = this.getPlayerData(player).getKit();

                    int count = (kit == Kit.MINER && miner) || (kit == Kit.BERSERKER && berserker) ? 2 : 1;

                    if (miner) {
                        e.setCancelled();
                        this.level.setBlock(b, Block.get(4));
                        this.cobblestone.add(b.getLocationHash());
                    }

                    player.getInventory().addItem(Item.get(block.drop, block.meta, count));
                }

                player.addExperience(block.xp);

                Item item = player.getInventory().getItemInHand();
                if (item.isTool()) {
                    item.setDamage(Math.max(0, item.getDamage() - 1));
                    player.getInventory().sendHeldItem(player);
                }

                this.plugin.getServer().getScheduler().scheduleDelayedTask(new BlockRegenerateTask(b), block.delay * 20);
            } else if (b.getId() == 4 && this.cobblestone.contains(b.getLocationHash())) {
                e.setCancelled();
            } else if (b.getId() == Item.FURNACE || b.getId() == Item.BURNING_FURNACE) {
                /*BlockEntityFurnace tile = (BlockEntityFurnace) this.level.getBlockEntity(b);

                if(tile == null){
                    return;
                }

                String owner = tile.namedTag.getString("owner");

                if(!owner.equals("") && !owner.equals(player.getName().toLowerCase())){
                    player.sendMessage(TextFormat.RED + "You can not break this furnace");
                    e.setCancelled();
                }*/
            }

            player.getInventory().sendContents(player);
        }
    }

    public Item[] getGravelDrops() {
        Random rand = new Random();
        Item arrows = Item.get(Item.ARROW, Math.max(
                rand.nextInt(5) - 2, 0));
        Item flint = Item.get(Item.FLINT, Math.max(
                rand.nextInt(4) - 2, 0));
        Item feathers = Item.get(Item.FEATHER, Math.max(
                rand.nextInt(4) - 2, 0));
        Item string = Item.get(Item.STRING, Math.max(
                rand.nextInt(5) - 3, 0));
        Item bones = Item.get(Item.BONE, Math.max(
                rand.nextInt(4) - 2, 0));

        Item[] items = new Item[]{arrows, flint, feathers, string, bones};

        return items;
    }

    @EventHandler(priority = EventPriority.NORMAL, ignoreCancelled = false)
    public void onBlockPlace(BlockPlaceEvent e) {
        Player player = e.getPlayer();
        Block b = e.getBlock();
        if (!this.inArena(player) || this.inLobby(player)) {
            return;
        }
        if (!(this.phase >= 1)) {
            return;
        }
        if (this.phase >= 1) {
            if (resources.containsKey(b.getId())) {
                e.setCancelled();
                return;
            }
            Vector3 blueNex = (Vector3) this.data.get("1Nexus");
            Vector3 redNex = (Vector3) this.data.get("2Nexus");
            Vector3 yellowNex = (Vector3) this.data.get("3Nexus");
            Vector3 greenNex = (Vector3) this.data.get("4Nexus");

            if (this.contains(blueNex.x - 15, 0, blueNex.z - 15, blueNex.x + 15, 128, blueNex.z + 15, b) || this.contains(redNex.x - 15, 0, redNex.z - 15, redNex.x + 15, 128, redNex.z + 15, b) || this.contains(yellowNex.x - 15, 0, yellowNex.z - 15, yellowNex.x + 15, 128, yellowNex.z + 15, b) || this.contains(greenNex.x - 15, 0, greenNex.z - 15, greenNex.x + 15, 128, greenNex.z + 15, b)) {
                e.setCancelled();
                player.sendMessage(TextFormat.RED + "You can't place blocks close to the nexus");
                return;
            }

            PlayerData data = this.getPlayerData(player);

            if (b.getId() == Item.SOUL_SAND && data.getKit() == Kit.OPERATIVE) {
                if (Operative.placed(player)) {
                    return;
                }
                Operative.onPlace(player, b);
            }
        }
    }

    @EventHandler(priority = EventPriority.NORMAL, ignoreCancelled = false)
    public void onBucketFill(PlayerBucketFillEvent e) {
        Player p = e.getPlayer();
        if (!p.isOp()) {
            e.setCancelled();
        }
    }

    @EventHandler(priority = EventPriority.NORMAL, ignoreCancelled = false)
    public void onAchievement(PlayerAchievementAwardedEvent e) {
        e.setCancelled();
    }

    @EventHandler(priority = EventPriority.NORMAL, ignoreCancelled = false)
    public void onDeath(PlayerDeathEvent e) {
        Player p = e.getEntity();
        //$team = $this->getPlayerTeam($p);
        e.setDeathMessage(new TextContainer(""));
        if (!this.inArena(p) || this.inLobby(p)) {
            return;
        }
        if (this.phase >= 1) {
            new NormalQuery(Annihilation.getInstance(), NormalQuery.DEATH, new String[]{p.getName().toLowerCase()});
            this.deathManager.onDeath(e);

            /*
            ArrayList<Item> drops = new ArrayList<>();

            for (Item item : e.getDrops()) {
                System.out.println(item.getId() + " ");
                List<Integer> bl = new ArrayList<>();
                bl.addAll(Arrays.asList(blockedItems));

                if (!bl.contains(item.getId())) {
                    drops.add(item);
                }
            }

            e.setExperience(0);

            e.setDrops(drops.toArray(new Item[drops.size()]));*/

            ArrayList<Item> drops = new ArrayList<>();


            for(Item drop : e.getDrops()){
                if(drop.getName().equals(TextFormat.RESET + TextFormat.GOLD + "SoulBound")){
                    continue;
                }

                drops.add(drop);
            }

            e.setDrops((Item[]) drops.toArray());

            if (this.getPlayerTeam(p).getNexus().getHealth() <= 0) {
                this.handlePlayerQuit(p);
            }
        }
    }

    @EventHandler(priority = EventPriority.NORMAL, ignoreCancelled = false)
    public void onRespawn(PlayerRespawnEvent e) {
        Player p = e.getPlayer();

        if (!this.inArena(p)) {
            return;
        }

        if (!this.inLobby(p) && this.getPlayerTeam(p) != null && this.inArena(p)) {
            this.kitManager.giveKit(p);
            return;
        }

        if (this.getPlayerData(p).getKit().equals("berserker")) {
            p.setMaxHealth(20);
        }
    }

    /*public function onEntitySpawn(EntitySpawnEvent $ev){
        $e = $ev->getEntity();
        if($e instanceof IronGolem && $e->getHealth() === $e->getMaxHealth()){
            $e->setMaxHealth(200);
            $e->setHealth(200);
        }
    }*/

    @EventHandler(priority = EventPriority.NORMAL, ignoreCancelled = false)
    public void onInteract(PlayerInteractEvent e) {
        Block b = e.getBlock();
        Player p = e.getPlayer();
        Item item = e.getItem();

        if (e.isCancelled() || e.getAction() == PlayerInteractEvent.LEFT_CLICK_AIR) {
            /*if(item.getId() == Item.COMPASS && this.phase >= 1 && this.inArena(p)){
                if(!item.hasCustomName()){
                    item.setCustomName("1");
                    //System.out.println("neni custom name");
                }

                String name = item.getCustomName();

                int team = 1;

                switch(name){
                    case "2":
                        team = 2;
                        break;
                    case "3":
                        team = 3;
                        break;
                    case "4":
                        team = 4;
                        break;
                }

                //System.out.println("custom name " + team);

                item.setCustomName(String.valueOf(team));
                p.getInventory().sendHeldItem(p);

                Position spawn = this.getTeam(team).getSpawnLocation();

                SetSpawnPositionPacket pk = new SetSpawnPositionPacket();
                pk.x = (int) spawn.x;
                pk.y = (int) spawn.y;
                pk.z = (int) spawn.z;

                p.dataPacket(pk);
            }*/
            return;
        }

        if (e.getAction() == PlayerInteractEvent.RIGHT_CLICK_AIR && item.getId() == Item.FISHING_ROD) {
            PlayerData data = getPlayerData(p);

            if (data.fishingHook != null) {
                if (data.getKit() != Kit.SCOUT || data.fishingHook.distance(p) > 33) {
                    data.fishingHook.close();
                    data.fishingHook = null;
                } else if (data.fishingHook.motionX == 0 && data.fishingHook.motionZ == 0) {
                    Vector3 diff = new Vector3(data.fishingHook.x - p.x, data.fishingHook.y - p.y, data.fishingHook.z - p.z);

                    double d = p.distance(data.fishingHook);

                    p.setMotion(new Vector3((1.0 + 0.07 * d) * diff.getX() / d, (1.0 + 0.03 * d) * diff.getY() / d + 0.04 * d, (1.0 + 0.07 * d) * diff.getZ() / d));

                    data.fishingHook.close();
                    data.fishingHook = null;
                } else {
                    data.fishingHook.close();
                    data.fishingHook = null;
                }
            } else if (data.getKit() == Kit.SCOUT) {

                CompoundTag nbt = new CompoundTag()
                        .putList(new ListTag<DoubleTag>("Pos")
                                .add(new DoubleTag("", p.x))
                                .add(new DoubleTag("", p.y + p.getEyeHeight()))
                                .add(new DoubleTag("", p.z)))
                        .putList(new ListTag<DoubleTag>("Motion")
                                .add(new DoubleTag("", -Math.sin(p.yaw / 180 * Math.PI) * Math.cos(p.pitch / 180 * Math.PI)))
                                .add(new DoubleTag("", -Math.sin(p.pitch / 180 * Math.PI)))
                                .add(new DoubleTag("", Math.cos(p.yaw / 180 * Math.PI) * Math.cos(p.pitch / 180 * Math.PI))))
                        .putList(new ListTag<FloatTag>("Rotation")
                                .add(new FloatTag("", (float) p.yaw))
                                .add(new FloatTag("", (float) p.pitch)));

                float f = 1.3F;

                data.fishingHook = new FishingHook(p.chunk, nbt, p);
                data.fishingHook.setMotion(data.fishingHook.getMotion().multiply(f));
                data.fishingHook.spawnToAll();
            }
            return;
        }

        if (b.getId() == Item.WALL_SIGN || b.getId() == Item.SIGN_POST) {
            if (b.equals(this.maindata.get("sign"))) {
                this.joinToArena(p);
                return;
            }

            if (!this.inArena(p)) {
                return;
            }

            if (b.equals(this.maindata.get("1sign"))) {
                this.joinTeam(p, 1);
            } else if (b.equals(this.maindata.get("2sign"))) {
                this.joinTeam(p, 2);
            } else if (b.equals(this.maindata.get("3sign"))) {
                this.joinTeam(p, 3);
            } else if (b.equals(this.maindata.get("4sign"))) {
                this.joinTeam(p, 4);
            }

            if (!(this.phase >= 1)) {
                return;
            }

            /*BlockEntitySign sign = (BlockEntitySign) this.level.getBlockEntity(b);

            if (sign != null && sign.getText()[0].toLowerCase().equals("[shop]")) {
                switch (sign.getText()[1].toLowerCase()) {
                    case "weapons":
                        p.addWindow(this.getPlayerTeam(p).getWeaponsShop().getInventory());
                        break;
                    case "brewing":
                        p.addWindow(this.getPlayerTeam(p).getBrewingShop().getInventory());
                        break;
                }
            }*/
            return;
        }

        if (!(this.phase >= 1) || !this.inArena(p)) {
            return;
        }

        if (b.getId() == Item.CHEST) {
            if (isEnderChest(b)) {
                e.setCancelled();
                p.sendMessage(Annihilation.getPrefix() + TextFormat.GRAY + "This is your team's Ender Chest. Any items you store or smelt here are safe from all other players.");

                PlayerData data = this.getPlayerData(p);

                ChestInventory chest = data.getChest();

                if (chest == null) {
                    chest = this.enderManager.createChest(p);
                }

                p.addWindow(chest);
            }
        } else if (b.getId() == Item.FURNACE || b.getId() == Item.BURNING_FURNACE) {
            if (isEnderFurnace(b)) {
                e.setCancelled();
                p.sendMessage(Annihilation.getPrefix() + TextFormat.GRAY + "This is your team's Ender Furnace. Any items you store or smelt here are safe from all other players.");

                PlayerData data = this.getPlayerData(p);

                EnderFurnaceInventory furnace = data.getFurnace();

                if (furnace == null) {
                    furnace = this.enderManager.createFurnace(p);
                }

                p.addWindow(furnace);
            }
        } else if (b.getId() == Item.BREWING_STAND_BLOCK) {
            if (isEnderBrewing(b)) {
                e.setCancelled();
                p.sendMessage(Annihilation.getPrefix() + TextFormat.GRAY + "This is your team's Ender Brewing. Any items you store or brew here are safe from all other players.");

                PlayerData data = this.getPlayerData(p);

                EnderBrewingInventory brewing = data.getBrewing();

                if (brewing == null) {
                    brewing = this.enderManager.createBrewing(p);
                }

                p.addWindow(brewing);
            }
        }
    }

    @EventHandler(priority = EventPriority.NORMAL, ignoreCancelled = false)
    public void onChat(PlayerChatEvent e) {
        if (e.isCancelled()) {
            return;
        }

        e.setCancelled();

        Player p = e.getPlayer();

        if (this.getPlayerTeam(p) == null) {
            return;
        }

        if (e.getMessage().startsWith("!") && e.getMessage().length() > 1) {
            this.messageAllPlayers(e.getMessage(), p);
            return;
        }

        this.getPlayerTeam(p).message(e.getMessage(), p);
    }

    public void teleportToArena(Player p) {
        Team team = this.getPlayerTeam(p);
        p.teleportImmediate(team.getSpawnLocation());
        p.getInventory().clearAll();
        p.setSpawn(team.getSpawnLocation());
        p.setGamemode(0);

        this.plugin.getServer().sendRecipeList(p);

        PlayerData data = this.getPlayerData(p);

        data.setLobby(false);

        VirtualInventory inv = data.getSavedInventory();
        if (inv != null) {
            this.loadInventory(p, inv);
            data.removeInventory();

            if (this.getPlayerData(p).getKit() == Kit.BERSERKER) {
                p.setHealth(14);
                p.setMaxHealth(14);
            }
            return;
        }
        p.setExperience(0, 0);
        p.getFoodData().setLevel(20);
        p.getFoodData().setFoodSaturationLevel(20);

        this.kitManager.giveKit(p);
    }

    public void stopGame() {
        this.phase = 0;

        for (Player p : new ArrayList<>(this.getAllPlayers().values())) {
            p.teleportImmediate(this.plugin.mainLobby);
            p.setSpawn(this.plugin.mainLobby);
            this.mtcore.setLobby(p);
        }

        this.unsetAllPlayers();
        this.registerTeams();
        this.votingManager.createVoteTable();
        this.ending = false;
        this.starting = false;
        this.task.time = 0;
        this.task.time1 = 120;
        this.task.popup = 0;

        this.gamesCount++;

        for (Player pl : new ArrayList<>(this.plugin.getServer().getOnlinePlayers().values())) {
            pl.kick(TextFormat.GOLD + "MineTox" + TextFormat.BLUE + " is restarting.", false);
        }

        Server.getInstance().forceShutdown();

        /*if($this->gamesCount >= 2){
            Server::getInstance()->shutdown();
        }*/
    }

    public boolean isLevelLoaded = false;

    public void startGame() {
        this.startGame(false);
    }

    public void startGame(boolean force) {
        if (!isLevelLoaded) {
            return;
        }

        if (this.getAllPlayers().size() < 1 && !force) {
            this.messageAllPlayers(Annihilation.getPrefix() + TextFormat.RED + "need 16 players to start");
            this.starting = false;
            this.task.time1 = 120;
            return;
        }

        this.starting = false;
        KitManager kits = this.kitManager;

        HashMap<String, Vector3>[] data = new HashMap[5];
        data[1] = new HashMap<>();
        data[2] = new HashMap<>();
        data[3] = new HashMap<>();
        data[4] = new HashMap<>();

        for (Map.Entry<String, Object> entry : this.data.entrySet()) {
            String key1 = entry.getKey().substring(0, 1);
            if (NumberUtils.isNumber(key1)) {
                data[Integer.valueOf(key1)].put(entry.getKey().substring(1).toLowerCase(), (Vector3) entry.getValue());
            }
        }

        for (int i = 1; i < 5; i++) {
            this.getTeam(i).setData(data[i], this);
        }

        for (Player p : this.getPlayersInTeam().values()) {
            p.setHealth(p.getMaxHealth());
            this.teleportToArena(p);
            //$kits->giveKit($p);
            /*$team = $this->getPlayerTeam($p);
            $pk = new SetSpawnPositionPacket();
            $pk->x = $this->data[$team->getId()."Nexus"]->x;
            $pk->y = $this->data[$team->getId()."Nexus"]->y;
            $pk->z = $this->data[$team->getId()."Nexus"]->z;*/
        }

        for (Vector3 v : (Vector3[]) this.data.get("diamonds")) {
            this.level.setBlock(v, Block.get(1), true);
        }

        this.changePhase(1);
    }

    public void broadcastResults(String winner) {
        String tip = TextFormat.GOLD + TextFormat.BOLD + "-----------------------------";
        tip += TextFormat.DARK_RED + TextFormat.BOLD + "\n     * CONGRATULATIONS *";
        tip += "\n     " + TextFormat.BOLD + winner + TextFormat.DARK_RED + " team wins!";
        tip += TextFormat.GOLD + TextFormat.BOLD + "\n-----------------------------";

        for (Player p : this.getAllPlayers().values()) {
            p.sendTip(tip);
        }
    }

    public void spawnDiamonds() {
        for (Vector3 d : (Vector3[]) this.data.get("diamonds")) {
            this.level.setBlock(d, Block.get(56, 0), true);
        }
    }

    public boolean contains(Vector3 first, Vector3 second, Vector3 pos) {
        return this.contains(first.x, first.y, first.z, second.x, second.y, second.z, pos);
    }

    public boolean contains(double x, double y, double z, double x1, double y1, double z1, Vector3 pos) {
        AxisAlignedBB axis = new AxisAlignedBB(Math.min(x, x1), Math.min(y, y1), Math.min(z, z1), Math.max(x, x1), Math.max(y, y1), Math.max(z, z1));

        return axis.isVectorInside(pos);
    }

    public void checkAlive() {
        if (this.phase <= 4 || this.ending) {
            return;
        }

        if (this.getPlayersInTeam().size() <= 0) {
            this.stopGame();
        }
    }

    public void breakNexus(Player player, Team damagedTeam) {
        if (!this.inArena(player)) {
            return;
        }

        Nexus nexus = damagedTeam.getNexus();

        if (nexus.getHealth() < 1) {
            return;
        }

        Team team = this.getPlayerTeam(player);

        if (team.getId() == damagedTeam.getId()) {
            player.sendMessage(TextFormat.RED + "You can't break your own nexus");
            return;
        }

        if (this.phase <= 1) {
            player.sendMessage(TextFormat.RED + "You can not break nexus until phase II");
            return;
        }

        Item item = player.getInventory().getItemInHand();

        if (item.isTool()) {
            item.setDamage(Math.max(0, item.getDamage() - 1));
        }

        Vector3 pos = nexus.getPosition().add(0.5, 0, 0.5);

        this.level.addParticle(new LavaDripParticle(pos));
        this.level.addParticle(new SmokeParticle(pos));

        this.level.addSound(new AnvilFallSound(nexus.getPosition()), this.level.getChunkPlayers((int) nexus.getPosition().x >> 4, (int) nexus.getPosition().z >> 4).values());

        for (Player p : team.getPlayers().values()) {
            p.sendMessage(team.getColor() + player.getName() + TextFormat.DARK_GRAY + " damaged " + damagedTeam.getColor() + damagedTeam.getName() + TextFormat.DARK_GRAY + " team's nexus");
        }


        AnvilFallSound sound = new AnvilFallSound(new Vector3());
        for (Player p : damagedTeam.getPlayers().values()) {
            sound.setComponents(p.x, p.y, p.z);

            this.level.addSound(sound, p);
        }

        nexus.damage();

        new NormalQuery(Annihilation.getInstance(), NormalQuery.NEXUS_DAMAGE, new String[]{player.getName().toLowerCase()});

        if (this.getPlayerData(player).getKit().equals("handyman")) {
            if (Handyman.calculateDamage(this.phase)) {
                team.getNexus().setHealth(team.getNexus().getHealth() + 1);
            }
        }

        if (this.phase == 5 && nexus.getHealth() >= 1) {
            nexus.damage();
            new NormalQuery(Annihilation.getInstance(), NormalQuery.NEXUS_DAMAGE, new String[]{player.getName().toLowerCase()});
        }

        if (nexus.getHealth() <= 0) {
            this.onNexusDestroy(player, damagedTeam);
        }
    }

    public void joinTeam(Player p, int team) {
        this.joinTeam(p, team, false);
    }

    public void joinTeam(Player p, int team2, boolean forceJoin) {
        Team team = this.getTeam(team2);

        if (!this.inLobby(p)) {
            return;
        }

        Team pTeam = this.getPlayerTeam(p);

        if (pTeam != null && pTeam.getId() == team.getId()) {
            p.sendPopup(Annihilation.getPrefix() + TextFormat.GRAY + "You are already in " + team.getColor() + team.getName() + " team");
            return;
        }

        if (!this.isTeamFree(team) && !p.isOp()) {
            p.sendPopup(Annihilation.getPrefix() + TextFormat.GRAY + "This team is full");
            return;
        }

        if (this.phase >= 3 && !forceJoin && !p.isOp()) {
            p.sendMessage(Annihilation.getPrefix() + TextFormat.GRAY + "You can't join in this phase");
            p.teleportImmediate(this.plugin.mainLobby);
            return;
        }

        if (pTeam != null) {
            p.sendMessage(Annihilation.getPrefix() + TextFormat.GRAY + " You can not change teams");
            return;
        }

        this.addToTeam(p, team);
        p.sendMessage(Annihilation.getPrefix() + TextFormat.GRAY + "Joined " + team.getColor() + team.getName());

        if (this.phase >= 1) {
            this.teleportToArena(p);
        }
    }

    public void changePhase(int phase) {
        int sound = 0;

        switch (phase) {
            case 1:
                this.phase = 1;
                this.messageAllPlayers(TextFormat.GRAY + "===========[ " + TextFormat.DARK_AQUA + "Progress" + TextFormat.GRAY + " ]===========\n"
                        + TextFormat.BLUE + "Phase I " + TextFormat.GRAY + "has started\n"
                        + TextFormat.GRAY + "Each nexus is invicible until Phase II\n"
                        + TextFormat.GRAY + "==================================");
                break;
            case 2:
                this.phase = 2;
                //this.bossManager.spawnBoss(1);
                //this.bossManager.spawnBoss(2);
                this.messageAllPlayers(TextFormat.GRAY + "===========[ " + TextFormat.DARK_AQUA + "Progress" + TextFormat.GRAY + " ]===========\n"
                        + TextFormat.GREEN + "Phase II " + TextFormat.GRAY + "has started\n"
                        + TextFormat.GRAY + "Each nexus is no longer invicible\n"
                        + TextFormat.GRAY + "Boss Iron Golems will now spawn\n"
                        + TextFormat.GRAY + "==================================");
                sound = 1;
                break;
            case 3:
                this.phase = 3;
                this.messageAllPlayers(TextFormat.GRAY + "===========[ " + TextFormat.DARK_AQUA + "Progress" + TextFormat.GRAY + " ]===========\n"
                        + TextFormat.YELLOW + "Phase III " + TextFormat.GRAY + "has started\n"
                        + TextFormat.GRAY + "Diamonds now spawn in the middle\n"
                        + TextFormat.GRAY + "==================================");
                this.spawnDiamonds();
                sound = 3;
                break;
            case 4:
                this.phase = 4;
                this.messageAllPlayers(TextFormat.GRAY + "===========[ " + TextFormat.DARK_AQUA + "Progress" + TextFormat.GRAY + " ]===========\n"
                        + TextFormat.GOLD + "Phase IV " + TextFormat.GRAY + "has started\n"
                        + TextFormat.GRAY + "Now you can brew strength\n"
                        + TextFormat.GRAY + "==================================");
                sound = 1;
                break;
            case 5:
                this.phase = 5;
                this.messageAllPlayers(TextFormat.GRAY + "===========[ " + TextFormat.DARK_AQUA + "Progress" + TextFormat.GRAY + " ]===========\n"
                        + TextFormat.RED + "Phase V " + TextFormat.GRAY + "has started\n"
                        + TextFormat.RED + "Double nexus damage\n"
                        + TextFormat.GRAY + "==================================");
                sound = 4;
                break;
        }

        for (Player p : this.getAllPlayers().values()) {
            this.level.addSound(new NoteBoxSound(p, sound), new Player[]{p});
        }
    }

    public void checkLobby() {
        if (this.phase >= 1) {
            return;
        }
        if (this.getAllPlayers().size() >= 16) {
            this.starting = true;
        }
    }

    public void selectMap() {
        this.selectMap(false);
    }

    public void selectMap(boolean force) {
        if (this.getAllPlayers().size() < 1 && !force) {
            this.messageAllPlayers(Annihilation.getPrefix() + TextFormat.RED + "Need 16 players to start");
            this.starting = false;
            this.task.time1 = 120;
            return;
        }

        String map = "";
        int points = -10;

        for (Map.Entry<String, Integer> entry : this.votingManager.stats.entrySet()) {
            if (points < entry.getValue()) {
                map = entry.getKey();
                points = entry.getValue();
            }
        }

        if (this.plugin.getServer().isLevelLoaded(map)) {
            this.plugin.getServer().unloadLevel(this.plugin.getServer().getLevelByName(map));
        }

        plugin.getServer().getScheduler().scheduleAsyncTask(new WorldCopyTask(this.id, map, Server.getInstance().getDataPath(), force));

        //this.plugin.getServer().loadLevel(map);
        //this.level = this.plugin.getServer().getLevelByName(map);
        this.map = map;
        this.data = this.plugin.maps.get(map);
        for (Player p : this.getAllPlayers().values()) {
            p.sendMessage(TextFormat.BOLD + TextFormat.YELLOW + map + TextFormat.GOLD + " was chosen");
        }
    }

    public void loadInventory(Player p, VirtualInventory inv) {
        PlayerInventory newInv = p.getInventory();

        newInv.setContents(inv.getContents());
        newInv.setArmorContents(inv.armor);

        for (int i = 0; i < 9; i++) {
            newInv.setHotbarSlotIndex(inv.hotbar[i], i);
        }

        p.setExperience(inv.xp, inv.xplevel);
        p.getFoodData().setLevel(inv.hunger);

        newInv.sendContents(p);
        newInv.sendArmorContents(p);
    }

    public void unsetAllPlayers() {
        this.playersData.clear();
        this.players.clear();
    }

    public int[] getAliveNexuses() {
        int[] nexuses = new int[]{};
        for (int i = 1; i < 5; i++) {
            if (this.getTeam(i).getNexus().getHealth() > 0) {
                nexuses[nexuses.length] = i;
            }
        }
        return nexuses;
    }

    @EventHandler(priority = EventPriority.NORMAL, ignoreCancelled = false)
    public void onItemHeld(PlayerItemHeldEvent e) {
        Player p = e.getPlayer();

        if (!this.inArena(p)) {
            return;
        }

        if (this.inLobby(p)) {
            this.kitManager.itemHeld(e.getPlayer(), e.getInventorySlot());
            e.setCancelled();
        }
    }

    public boolean inLobby(Player p) {
        PlayerData data = this.getPlayerData(p);
        return data != null && data.isInLobby();
    }

    public boolean wasInArena(Player p) {
        return this.getPlayerData(p).wasInGame();
    }

    public void onNexusDestroy(Player p, Team damagedTeam) {
        Nexus nexus = damagedTeam.getNexus();

        Position pos = nexus.getPosition();
        new NormalQuery(Annihilation.getInstance(), NormalQuery.NEXUS_DESTROY, new String[]{p.getName().toLowerCase()});
        new NormalQuery(Annihilation.getInstance(), "tokens", new String[]{p.getName().toLowerCase()}, 40, "freezecraft");
        //this.mtcore.mysqlmgr.addTokens(p.getName(), 40);

        ExplodePacket pk = new ExplodePacket();
        pk.radius = 1;
        ExplodePacket explode = new ExplodePacket();
        explode.x = (float) pos.x;
        explode.y = (float) pos.y;
        explode.z = (float) pos.z;
        explode.radius = 5;

        Server.broadcastPacket(this.level.getChunkPlayers((int) pos.x >> 4, (int) pos.z >> 4).values(), explode);

        this.level.addParticle(new LargeExplodeParticle(pos.add(0.5, 0, 0.5)));

        Team team = this.getPlayerTeam(p);

        String msg = TextFormat.GRAY + "===========[ " + TextFormat.DARK_AQUA + "Progress" + TextFormat.GRAY + " ]===========\n"
                + team.getColor() + p.getName() + TextFormat.GRAY + " from " + team.getColor() + team.getName() + TextFormat.GRAY + " team destroyed " + damagedTeam.getColor() + damagedTeam.getName() + TextFormat.GRAY + " team's nexus\n"
                + TextFormat.GRAY + "==================================";

        new NormalQuery(Annihilation.getInstance(), NormalQuery.LOSE, damagedTeam.getPlayers().keySet().toArray(new String[damagedTeam.getPlayers().size()]));

        for (Player pl : this.getPlayersInTeam().values()) {

            if (team.getId() == damagedTeam.getId()) {
                pl.setSpawn(this.plugin.mainLobby);
            }

            pk.x = (float) pl.getX();
            pk.y = (float) pl.getY();
            pk.z = (float) pl.getZ();
            pl.dataPacket(pk);

            pl.sendMessage(msg);
        }

        this.checkNexuses();
    }

    /*public function onEntityDeath(EntityDeathEvent $e){
        $entity = $e->getEntity();
        $cause = $entity->getLastDamageCause();

        if($entity instanceof IronGolem) {
            $pname = "";

            if ($cause instanceof EntityDamageByEntityEvent && ($damager = $cause->getDamager()) instanceof Player) {
                $pname = $this->getPlayerTeam($damager)->getColor() . $damager->getName();
            }

            if (strpos($entity->getNameTag(), "Celariel")) {
                $this->bossManager->onBossDeath(2, $pname);
                $e->setDrops([]);
            } elseif (strpos($entity->getNameTag(), "Ferwin")) {
                $this->bossManager->onBossDeath(1, $pname);
                $e->setDrops([]);
            }
        }
    }*/

    public boolean isEnderChest(Vector3 b) {
        return b.equals((Vector3) this.data.get("1Chest")) || b.equals((Vector3) this.data.get("2Chest")) || b.equals((Vector3) this.data.get("3Chest")) || b.equals((Vector3) this.data.get("4Chest"));
    }

    public boolean isEnderFurnace(Vector3 b) {
        return b.equals((Vector3) this.data.get("1Furnace")) || b.equals((Vector3) this.data.get("2Furnace")) || b.equals((Vector3) this.data.get("3Furnace")) || b.equals((Vector3) this.data.get("4Furnace"));
    }

    public boolean isEnderBrewing(Vector3 b) {
        return b.equals((Vector3) this.data.get("1EnderBrewing")) || b.equals((Vector3) this.data.get("2EnderBrewing")) || b.equals((Vector3) this.data.get("3EnderBrewing")) || b.equals((Vector3) this.data.get("4EnderBrewing"));
    }

    /*@EventHandler(priority = EventPriority.NORMAL, ignoreCancelled = false)
    public void onItemTake(InventoryPickupItemEvent e) {
        Inventory inv = e.getInventory();
        Player p = (Player) inv.getHolder();
        if (p != null) {

        }
    }*/

    private static final Integer[] blockedItems = new Integer[]{Item.LEATHER_BOOTS, Item.LEATHER_CAP, Item.LEATHER_PANTS, Item.LEATHER_TUNIC, Item.WOODEN_PICKAXE, Item.WOODEN_SWORD, Item.WOODEN_AXE};

    @EventHandler(priority = EventPriority.NORMAL, ignoreCancelled = false)
    public void onItemDrop(PlayerDropItemEvent e) {
        Player p = e.getPlayer();
        if (!this.inArena(p) || this.inLobby(p)) {
            e.setCancelled(true);
            return;
        }
        Item item = e.getItem();

        //List<Integer> bl = new ArrayList<>();
        //bl.addAll(Arrays.asList(blockedItems));

        if (item.getName().equals(TextFormat.RESET + TextFormat.GOLD + "SoulBound")) {
            e.setCancelled();
            p.getInventory().setItemInHand(Item.get(0, 0, 0));
        }

        /*if(isset($item->getNamedTag()->Soulbound)){
            $e->setCancelled(true);
            $p->getInventory()->setItemInHand(Item::get(0, 0, 0));
            $this->level->addSound(new BlazeShootSound($p), [$p]);
        }*/
    }

    @EventHandler(priority = EventPriority.NORMAL, ignoreCancelled = false)
    public void onTransaction(InventoryTransactionEvent e) {
        boolean is = false;

        Player p = null;
        int slot1 = 0;
        Item item1 = null;

        ChestInventory chest = null;
        int slot = 0;
        Item item = null;

        for (Transaction trans : e.getTransaction().getTransactions()) {
            Inventory inv = trans.getInventory();

            if (inv instanceof PlayerInventory) {
                p = (Player) trans.getInventory().getHolder();
                slot1 = trans.getSlot();
                item1 = trans.getSourceItem();
                is = true;
                break;
            } else if (inv instanceof ChestInventory) {
                chest = (ChestInventory) trans.getInventory();
                slot = trans.getSlot();
                item = trans.getSourceItem();
            }
        }
        if (!is) {
            return;
        }

        if (!this.inArena(p) || this.inLobby(p) || p == null) {
            e.setCancelled();
            return;
        }

        if (chest != null) {
            //List<Integer> bl = new ArrayList<>();
            //bl.addAll(Arrays.asList(blockedItems));

            if (item1.getName().equals(TextFormat.RESET + TextFormat.GOLD + "SoulBound")) {
                e.setCancelled();
                p.getInventory().setItem(slot1, Item.get(0, 0, 0));
                return;
            }

            if (chest.getHolder().getName().equals("Brewing Shop") || chest.getHolder().getName().equals("Weapons Shop")) {
                this.shopManager.onTransaction(p, slot, item, chest, this.phase);
                e.setCancelled();
            }
        }/*elseif($slot1 > 35){
            $e->setCancelled();
            $p->getInventory()->setItem($slot1, Item::get(0, 0, 0));
        }*/
        /*if(isset($item->getNamedTag()->Soulbound)){
            $e->setCancelled();
            $p->getInventory()->setItem($slot, Item::get($item->getId(), $item->getDamage(), $item->count));
            $this->level->addSound(new BlazeShootSound($p), [$p]);
        }*/
    }

    public void checkNexuses() {
        Set<Integer> alive = new HashSet<>();

        for (int i = 1; i < 5; i++) {
            if (this.getTeam(i).getNexus().isAlive()) {
                alive.add(i);
            }
        }

        if (alive.size() == 1) {
            for (Integer winner : alive) {
                this.winnerteam = this.getTeam(winner);
            }
            this.ending = true;
            this.winnerteam.message(TextFormat.BOLD + TextFormat.GOLD + "Recieved 400 coins for a win!");

            //for (Player p : this.winnerteam.getPlayers().values()) {
            new NormalQuery(Annihilation.getInstance(), "tokens", this.winnerteam.getPlayers().keySet().toArray(new String[this.winnerteam.getPlayers().size()]), 400, "freezecraft");
            //this.mtcore.mysqlmgr.addTokens(p.getName(), 400);
            new NormalQuery(Annihilation.getInstance(), NormalQuery.WIN, this.winnerteam.getPlayers().keySet().toArray(new String[this.winnerteam.getPlayers().size()]));
            //}
        }
    }

    @EventHandler(priority = EventPriority.NORMAL, ignoreCancelled = false)
    public void sneakEvent(PlayerToggleSneakEvent e) {
        Player p = e.getPlayer();

        if (!this.inArena(p)) {
            return;
        }

        if (this.getPlayerData(p).getKit() != Kit.SPY) {
            return;
        }

        if (e.isSneaking()) {
            Spy.onSneak(p);
        } else {
            Spy.onUnsneak(p);
        }
    }

    @EventHandler(priority = EventPriority.NORMAL, ignoreCancelled = false)
    public void onBedEnter(PlayerBedEnterEvent e) {
        e.setCancelled();
    }

    @EventHandler(priority = EventPriority.NORMAL, ignoreCancelled = false)
    public void onFoodChange(PlayerFoodLevelChangeEvent e) {
        Player p = e.getPlayer();

        if (this.inLobby(p)) {
            e.setCancelled();
        }
    }

    private static class Resource {

        public final Integer drop;
        public final Integer xp;
        public final Integer delay;

        public final Integer type;

        public final Integer meta;

        public static final int TYPE_ORE = 0;
        public static final int TYPE_WOOD = 1;

        public Resource(Integer drop, Integer xp, Integer delay, Integer type) {
            this(drop, xp, delay, type, 0);
        }

        public Resource(Integer drop, Integer xp, Integer delay, Integer type, Integer meta) {
            this.drop = drop;
            this.xp = xp;
            this.delay = delay;
            this.meta = meta;
            this.type = type;
        }
    }
}