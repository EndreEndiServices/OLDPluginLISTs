package Annihilation.Arena;

import Annihilation.Arena.Inventory.EnderBrewingInventory;
import Annihilation.Arena.Inventory.EnderFurnaceInventory;
import Annihilation.Arena.Kits.Handyman;
import Annihilation.Arena.Kits.Kit;
import Annihilation.Arena.Kits.KitInventory;
import Annihilation.Arena.Kits.Operative;
import Annihilation.Arena.Shop.ShopInventory;
import Annihilation.Arena.Shop.ShopWindow;
import Annihilation.Entity.IronGolem;
import Annihilation.Stat;
import Annihilation.Arena.Manager.*;
import Annihilation.Arena.Object.Nexus;
import Annihilation.Arena.Object.PlayerData;
import Annihilation.Arena.Shop.Shop;
import Annihilation.Arena.Object.Team;
import Annihilation.Arena.Task.BlockRegenerateTask;
import Annihilation.Arena.Task.WorldCopyTask;
import Annihilation.Entity.FishingHook;
import Annihilation.MySQL.NormalQuery;
import GTCore.MTCore;
import cn.nukkit.block.*;
import cn.nukkit.blockentity.BlockEntitySign;
import cn.nukkit.entity.Entity;
import cn.nukkit.event.EventHandler;
import cn.nukkit.event.EventPriority;
import cn.nukkit.event.TextContainer;
import cn.nukkit.event.entity.*;
import cn.nukkit.event.player.*;
import cn.nukkit.inventory.*;
import cn.nukkit.item.*;
import cn.nukkit.level.Level;
import cn.nukkit.level.Location;
import cn.nukkit.level.Position;
import cn.nukkit.level.particle.LargeExplodeParticle;
import cn.nukkit.level.particle.LavaDripParticle;
import cn.nukkit.level.particle.SmokeParticle;
import cn.nukkit.nbt.tag.CompoundTag;
import cn.nukkit.nbt.tag.DoubleTag;
import cn.nukkit.nbt.tag.FloatTag;
import cn.nukkit.nbt.tag.ListTag;
import cn.nukkit.utils.TextFormat;
import cn.nukkit.Player;
import cn.nukkit.Server;
import cn.nukkit.event.block.BlockPlaceEvent;
import cn.nukkit.event.block.BlockBreakEvent;
import cn.nukkit.event.Listener;
import cn.nukkit.math.Vector3;
import cn.nukkit.math.AxisAlignedBB;
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

    public AxisAlignedBB boundingBox = new AxisAlignedBB(0, 0, 0, 0, 0, 0);

    //public Shop shopManager;

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
        this.kitManager.init();
        this.votingManager = new VotingManager(this);
        this.worldManager = new WorldManager();
        this.bossManager = new BossManager(this);
        this.enderManager = new EnderManager(this);
        this.deathManager = new DeathManager(this);
        //this.shopManager = new Shop();
        this.registerTeams();
        this.votingManager.createVoteTable();
        this.plugin.getServer().getScheduler().scheduleRepeatingTask(this.task = new ArenaSchedule(this), 20);
        this.plugin.getServer().getScheduler().scheduleRepeatingTask(this.popupTask = new PopupTask(this), 10);
        Arena.instance = this;
    }

    static {
        resources.put(Item.COAL_ORE, new Resource(new ItemCoal(), 8, 10, Resource.TYPE_ORE));
        resources.put(Item.IRON_ORE,
                new Resource(new ItemBlock(new BlockOreIron()), 13, 20, Resource.TYPE_ORE));
        resources.put(Item.GOLD_ORE,
                new Resource(new ItemBlock(new BlockOreGold()), 20, 20, Resource.TYPE_ORE));
        resources.put(Item.DIAMOND_ORE,
                new Resource(new ItemDiamond(), 25, 30, Resource.TYPE_ORE));
        resources.put(Item.EMERALD_ORE,
                new Resource(new ItemEmerald(), 30, 40, Resource.TYPE_ORE));
        resources.put(Item.REDSTONE_ORE,
                new Resource(new ItemRedstone(), 20, 20, Resource.TYPE_ORE));
        resources.put(Item.LAPIS_ORE,
                new Resource(new ItemDye(4), 10, 20, Resource.TYPE_ORE));
        resources.put(Item.GLOWING_REDSTONE_ORE,
                new Resource(new ItemRedstone(), 10, 20, Resource.TYPE_ORE));
        resources.put(Item.LOG,
                new Resource(new ItemBlock(new BlockWood()), 5, 10, Resource.TYPE_WOOD));
        resources.put(Item.LOG2,
                new Resource(new ItemBlock(new BlockWood2()), 5, 10, Resource.TYPE_WOOD));
        resources.put(Item.GRAVEL,
                new Resource(null, 5, 20, 3));
        resources.put(Item.MELON_BLOCK,
                new Resource(null, 0, 10, 3));
    }

    public static Arena getInstance() {
        return Arena.instance;
    }

    @EventHandler
    public void onPlayerQuit(PlayerQuitEvent e) {
        handlePlayerQuit(e.getPlayer());
    }

    public void handlePlayerQuit(Player p) {
        if (inArena(p)) {
            PlayerData data = this.getPlayerData(p);

            Team team = data.getTeam();

            if (team != null) {
                team.removePlayer(p);
                if (this.phase >= 1 && p.isAlive() && p.getInventory() != null) {
                    data.saveInventory(p);
                }
            }

            p.setMaxHealth(20);
            data.setInLobby(false);
            players.remove(p.getName().toLowerCase());
            checkAlive();

            if (p.isOnline()) {
                plugin.mtcore.setLobby(p);
            }
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
            data.setBaseData(this.mtcore.getPlayerData(p));

            if (data.getTeam() != null) {
                wasInGame = true;
            }
        }

        if (this.phase >= 5 && !wasInGame && !p.isOp()) {
            p.sendMessage(Annihilation.getPrefix() + TextFormat.RED + "Nemuzes se pripojit do hry v teto fazi");
            return;
        }

        if (wasInGame && this.phase >= 1 && !data.getTeam().getNexus().isAlive()) {
            p.sendMessage(Annihilation.getPrefix() + TextFormat.RED + "Nexus tveho teamu byl uz znicen");
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

            //p.setNameTag(p.getName());
            data.setInLobby(true);
            this.kitManager.addKitWindow(p);
        } else {
            p.setNameTag(p.getName());
            data.setInLobby(true);
            this.kitManager.addKitWindow(p);
        }

        Vector3 newPos = this.maindata.get("inLobby");

        p.teleportImmediate(new Location(newPos.x, newPos.y, newPos.z));
        p.setSpawn(newPos);
        p.sendMessage(Annihilation.getPrefix() + TextFormat.GREEN + "Pripojuji do " + this.id + "...");
        p.sendMessage(Annihilation.getPrefix() + TextFormat.GOLD + "Otevri svu inventar pro vybrani kitu");
        this.checkLobby();
    }

    @EventHandler
    public void onHurt(EntityDamageEvent e) {
        Entity entity = e.getEntity();

        if (e.isCancelled()) {
            return;
        }

        if (entity instanceof IronGolem && e.getFinalDamage() >= entity.getHealth() && entity.getHealth() > 0) {
            String pname = "";
            Entity damager;
            EntityDamageEvent cause = entity.getLastDamageCause();

            if (cause instanceof EntityDamageByEntityEvent && (damager = ((EntityDamageByEntityEvent) cause).getDamager()) instanceof Player) {
                pname = getPlayerTeam((Player) damager).getColor() + damager.getName();
            }

            if (entity.getNameTag().toLowerCase().contains("celariel")) {
                bossManager.onBossDeath(2, pname);
                //e.setDrops(new Item[0]);
            } else if (entity.getNameTag().toLowerCase().contains("ferwin")) {
                bossManager.onBossDeath(1, pname);
                //e.setDrops(new Item[0]);
            }

            return;
        }

        if (!(entity instanceof Player) || !this.inArena((Player) entity)) {
            return;
        }

        PlayerData data = getPlayerData((Player) entity);

        if (data.isInLobby()) {
            e.setCancelled();

            if (e.getCause() == EntityDamageEvent.CAUSE_VOID) {
                entity.teleport(maindata.get("inLobby"));
            }

            return;
        }

        if (e.getCause() == EntityDamageEvent.CAUSE_FALL && data.getKit() == Kit.ACROBAT) {
            e.setCancelled();
            return;
        }

        if (e instanceof EntityDamageByChildEntityEvent) {
            Player killer = (Player) ((EntityDamageByChildEntityEvent) e).getDamager();
            Player victim = (Player) e.getEntity();

            if (killer != null && victim != null) {
                PlayerData killerData = this.getPlayerData(killer);

                if (killerData.getTeam().getId() == data.getTeam().getId() || this.phase == 0) {
                    e.setCancelled();
                    return;
                }
                //PlayerData data = this.getPlayerData(victim);

                if (killerData.getKit() == Kit.ARCHER) {
                    e.setDamage(e.getDamage() + 1);
                }

                data.setKiller(killerData);
            }
        } else if (e instanceof EntityDamageByEntityEvent) {
            Player victim = (Player) e.getEntity();
            Player killer = (Player) ((EntityDamageByEntityEvent) e).getDamager();

            if (killer != null && victim != null) {
                PlayerData killerData = this.getPlayerData(killer);

                if (killerData.getTeam() == null || data.getTeam() == null) {
                    e.setCancelled();
                    return;
                }

                if (!this.inArena(killer) || killerData.getTeam().getId() == data.getTeam().getId() || this.phase == 0) {
                    e.setCancelled();
                    return;
                }

                //PlayerData data = this.getPlayerData(victim);
                //PlayerData killerData = this.getPlayerData(killer);

                if (killerData.getKit() == Kit.WARRIOR) {
                    e.setDamage(e.getDamage() + 1);
                }

                /*if (e.getFinalDamage() < victim.getHealth()) {
                    killer->spawnTo($victim);
                }*/

                data.setKiller(killerData);
            }
        }
    }

    private HashSet<String> cobblestone = new HashSet<>();

    @EventHandler(priority = EventPriority.NORMAL, ignoreCancelled = false)
    public void onBlockBreak(BlockBreakEvent e) {
        Block b = e.getBlock();
        Player player = e.getPlayer();

        if (e.isCancelled() || e.isFastBreak()) {
            return;
        }

        e.setInstaBreak(true);
        if (!this.inArena(player)) {
            //e.setCancelled();
            return;
        }

        if (this.phase < 1) {
            return;
        }

        if (!boundingBox.isVectorInside(b)) {
            player.sendMessage(TextFormat.RED + "Nemas dostatecna opravneni pro tuto akci");
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
                    this.onNexusBreak(player, this.getTeam(1));
                    return;
                } else if (b.equals(redNex)) {
                    e.setCancelled();
                    this.onNexusBreak(player, this.getTeam(2));
                    return;
                } else if (b.equals(yellowNex)) {
                    e.setCancelled();
                    this.onNexusBreak(player, this.getTeam(3));
                    return;
                } else if (b.equals(greenNex)) {
                    e.setCancelled();
                    this.onNexusBreak(player, this.getTeam(4));
                    return;
                }
            }

            if (this.contains(blueNex.x - 15, 0, blueNex.z - 15, blueNex.x + 15, 128, blueNex.z + 15, b) || this.contains(redNex.x - 15, 0, redNex.z - 15, redNex.x + 15, 128, redNex.z + 15, b) || this.contains(yellowNex.x - 15, 0, yellowNex.z - 15, yellowNex.x + 15, 128, yellowNex.z + 15, b) || this.contains(greenNex.x - 15, 0, greenNex.z - 15, greenNex.x + 15, 128, greenNex.z + 15, b)) {
                e.setCancelled();
                player.sendMessage(TextFormat.RED + "Nemuzes nicit bloky pobliz nexusu");
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

                    Kit kit = this.getPlayerData(player).getKit();

                    int count = (kit == Kit.MINER && miner) || (kit == Kit.BERSERKER && berserker) ? 2 : 1;

                    if (miner) {
                        e.setCancelled();
                        this.level.setBlock(b, Block.get(4));
                        this.cobblestone.add(b.getLocationHash());
                    }

                    Item drop = block.drop.clone();

                    if (berserker) {
                        drop.setDamage(b.getDamage());
                    }

                    player.getInventory().addItem(drop.clone());
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

        return new Item[]{arrows, flint, feathers, string, bones};
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
                player.sendMessage(TextFormat.RED + "Nemuzes pokladat bloky pobliz nexusu!");
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
        e.setDeathMessage(new TextContainer(""));
        if (!this.inArena(p) || this.inLobby(p)) {
            return;
        }
        if (this.phase >= 1) {
            new NormalQuery(Annihilation.getInstance(), Stat.DEATHS, p.getName());
            this.deathManager.onDeath(e);

            if (this.getPlayerTeam(p).getNexus().getHealth() <= 0) {
                this.handlePlayerQuit(p);

                e.setDrops(new Item[0]);
                return;
            }

            ArrayList<Item> drops = new ArrayList<>();


            for (Item drop : e.getDrops()) {
                if (drop.getName().equals(TextFormat.RESET + TextFormat.GOLD + "SoulBound")) {
                    continue;
                }

                drops.add(drop);
            }

            e.setDrops(drops.toArray(new Item[drops.size()]));
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

        if (this.getPlayerData(p).getKit() == Kit.BERSERKER) {
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

    @EventHandler
    public void onInteract(PlayerInteractEvent e) {
        Block b = e.getBlock();
        Player p = e.getPlayer();
        Item item = e.getItem();

        if (item.getId() == Item.FLINT_AND_STEEL) {
            e.setCancelled();
            return;
        }

        if (!e.isCancelled() && e.getAction() == PlayerInteractEvent.LEFT_CLICK_AIR) {
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

        if (!e.isCancelled() && e.getAction() == PlayerInteractEvent.RIGHT_CLICK_AIR && item.getId() == Item.FISHING_ROD) {
            PlayerData data = getPlayerData(p);

            FishingHook fishingHook = data.getFishingHook();

            if (fishingHook != null) {

                if (data.getKit() != Kit.SCOUT || fishingHook.distance(p) > 33) {
                    fishingHook.close();
                    data.setFishingHook(null);
                } else if (fishingHook.motionX == 0 && fishingHook.motionZ == 0) {
                    Vector3 diff = new Vector3(fishingHook.x - p.x, fishingHook.y - p.y, fishingHook.z - p.z);

                    double d = p.distance(fishingHook);

                    p.setMotion(new Vector3((1.0 + 0.07 * d) * diff.getX() / d, (1.0 + 0.03 * d) * diff.getY() / d + 0.04 * d, (1.0 + 0.07 * d) * diff.getZ() / d));

                    fishingHook.close();
                    data.setFishingHook(null);
                } else {
                    fishingHook.close();
                    data.setFishingHook(null);
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

                fishingHook = new FishingHook(p.chunk, nbt, p);
                fishingHook.setMotion(fishingHook.getMotion().multiply(f));
                fishingHook.spawnToAll();

                data.setFishingHook(fishingHook);
            }
            return;
        }

        if (b.getId() == Item.WALL_SIGN || b.getId() == Item.SIGN_POST) {
            if (b.equals(this.maindata.get("sign"))) {
                e.setCancelled();
                this.joinToArena(p);
                return;
            }

            if (e.isCancelled() || !this.inArena(p)) {
                return;
            }

            if (b.equals(this.maindata.get("1sign"))) {
                this.joinTeam(p, 1);
                e.setCancelled();
            } else if (b.equals(this.maindata.get("2sign"))) {
                this.joinTeam(p, 2);
                e.setCancelled();
            } else if (b.equals(this.maindata.get("3sign"))) {
                this.joinTeam(p, 3);
                e.setCancelled();
            } else if (b.equals(this.maindata.get("4sign"))) {
                this.joinTeam(p, 4);
                e.setCancelled();
            }

            if (!(this.phase >= 1)) {
                return;
            }

            BlockEntitySign sign = (BlockEntitySign) this.level.getBlockEntity(b);

            if (sign != null && TextFormat.clean(sign.getText()[0].toLowerCase()).contains("[shop]")) {
                String line2 = TextFormat.clean(sign.getText()[1].toLowerCase());

                if (line2.contains("weapon")) {
                    p.addWindow(Shop.WEAPONS.getWindow());
                    e.setCancelled();
                } else if (line2.contains("brewing")) {
                    if (this.phase < 4) {
                        p.sendMessage(TextFormat.RED + "Brewing muzes pouzivat az od faze IV");
                        e.setCancelled();
                        return;
                    }
                    p.addWindow(Shop.BREWING.getWindow());
                    e.setCancelled();
                }
            }
            return;
        }

        if (e.isCancelled() || this.phase < 1 || !this.inArena(p)) {
            return;
        }

        if (b.getId() == Item.CHEST) {
            if (isEnderChest(b)) {
                e.setCancelled();
                p.sendMessage(Annihilation.getPrefix() + TextFormat.GRAY + "Toto je tva Ender Chest. Jakykoli item, ktery sem vlozis bude uschovan pred ostatnimi hraci.");

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
                p.sendMessage(Annihilation.getPrefix() + TextFormat.GRAY + "Toto je tva Ender Furnace. Jakykoli item, ktery sem vlozis bude uschovan pred ostatnimi hraci.");

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
                p.sendMessage(Annihilation.getPrefix() + TextFormat.GRAY + "Toto je tvuj Ender Brewing. Jakykoli item, ktery sem vlozis bude uschovan pred ostatnimi hraci.");

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

        PlayerData data = getPlayerData(p);

        if (data.getTeam() == null || (e.getMessage().startsWith("!") && e.getMessage().length() > 1)) {
            this.messageAllPlayers(e.getMessage(), p, data);
            return;
        }

        data.getTeam().message(e.getMessage(), p, data);
    }

    public void teleportToArena(Player p) {
        Team team = this.getPlayerTeam(p);
        p.teleportImmediate(team.getSpawnLocation().getLocation());
        p.getInventory().clearAll();
        p.setSpawn(team.getSpawnLocation());
        p.setGamemode(0);

        this.plugin.getServer().sendRecipeList(p);

        PlayerData data = this.getPlayerData(p);

        data.setInLobby(false);

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
            p.teleportImmediate(this.plugin.mainLobby.getLocation());
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
        this.bossManager.reset();

        this.gamesCount++;

        this.level.unload();

        if (this.gamesCount < 10) {
            return;
        }

        /*for (Player pl : new ArrayList<>(this.plugin.getServer().getOnlinePlayers().values())) {
            pl.kick(MTCore.getPrefix() + TextFormat.BLUE + " is restarting...", false);
        }*/

        /*this.plugin.getServer().getScheduler().scheduleDelayedTask(new Runnable() {
            @Override
            public void run() {
                System.exit(1);
            }
        }, 5);*/
        this.plugin.getServer().shutdown();
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
            this.messageAllPlayers(Annihilation.getPrefix() + TextFormat.RED + "Je potreba minimalne 16 hracu k odstartovani hry");
            this.starting = false;
            this.task.time1 = 120;
            return;
        }

        this.starting = false;
        //KitManager kits = this.kitManager;

        level.setTime(0);
        level.stopTime();

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

        int count = 0;

        for (Team t : this.teams) {
            if (t == null) continue;
            count += t.getPlayers().size();
        }

        if (count <= 0) {
            this.stopGame();
        }
    }

    public void onNexusBreak(Player player, Team damagedTeam) {
        if (!this.inArena(player)) {
            return;
        }

        PlayerData data = getPlayerData(player);

        Nexus nexus = damagedTeam.getNexus();

        if (nexus.getHealth() < 1) {
            return;
        }

        Team team = data.getTeam();

        if (team.getId() == damagedTeam.getId()) {
            player.sendMessage(TextFormat.RED + "Nemuzes nicit svuj vlastni nexus");
            return;
        }

        if (this.phase <= 1) {
            player.sendMessage(TextFormat.RED + "Nexus muzes nicit az od faze II");
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
            p.sendMessage(team.getColor() + player.getName() + TextFormat.DARK_GRAY + " poskodil nexus teamu " + damagedTeam.getColor() + damagedTeam.getName());
        }


        AnvilFallSound sound = new AnvilFallSound(new Vector3());
        for (Player p : damagedTeam.getPlayers().values()) {
            sound.setComponents(p.x, p.y, p.z);

            this.level.addSound(sound, p);
        }

        nexus.damage();

        new NormalQuery(Annihilation.getInstance(), Stat.NEXUSDMG, player.getName());

        if (team.getNexus().isAlive() && data.getKit() == Kit.HANDYMAN) {
            if (Handyman.calculateDamage(this.phase)) {
                team.getNexus().setHealth(team.getNexus().getHealth() + 1);
            }
        }

        if (this.phase == 5 && nexus.getHealth() >= 1) {
            nexus.damage();
            new NormalQuery(Annihilation.getInstance(), Stat.NEXUSDMG, player.getName());
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
            p.sendMessage(Annihilation.getPrefix() + TextFormat.GRAY + "jiz jsi v " + team.getColor() + team.getName() + " teamu");
            return;
        }

        if (!this.isTeamFree(team) && !p.hasPermission("gameteam.vip")) {
            p.sendMessage(Annihilation.getPrefix() + TextFormat.GRAY + "Tento team je plny");
            return;
        }

        if (this.phase >= 5 && !forceJoin && !p.isOp()) {
            p.sendMessage(Annihilation.getPrefix() + TextFormat.GRAY + "Nemuzes se pripojit do hry v teto fazi");
            p.teleportImmediate(this.plugin.mainLobby.getLocation());
            return;
        }

        if (pTeam != null) {
            p.sendMessage(Annihilation.getPrefix() + TextFormat.GRAY + "Nemuzes menit svuj team");
            return;
        }

        this.addToTeam(p, team);
        p.sendMessage(Annihilation.getPrefix() + TextFormat.GRAY + "Pripojil ses do teamu " + team.getColor() + team.getName());

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
                        + TextFormat.BLUE + "Faze I " + TextFormat.GRAY + "zacala\n"
                        + TextFormat.GRAY + "Vsechny nexusy jsou neznicitelne do faze II\n"
                        + TextFormat.GRAY + "==================================");
                break;
            case 2:
                this.phase = 2;
                this.bossManager.spawnBoss(1);
                this.bossManager.spawnBoss(2);
                this.messageAllPlayers(TextFormat.GRAY + "===========[ " + TextFormat.DARK_AQUA + "Progress" + TextFormat.GRAY + " ]===========\n"
                        + TextFormat.GREEN + "Faze II " + TextFormat.GRAY + "zacala\n"
                        + TextFormat.GRAY + "Nexusy jiz jdou nicit\n"
                        + TextFormat.GRAY + "Bossove se nyni budou spawnovat\n"
                        + TextFormat.GRAY + "==================================");
                sound = 1;
                break;
            case 3:
                this.phase = 3;
                this.messageAllPlayers(TextFormat.GRAY + "===========[ " + TextFormat.DARK_AQUA + "Progress" + TextFormat.GRAY + " ]===========\n"
                        + TextFormat.YELLOW + "Faze III " + TextFormat.GRAY + "zacala\n"
                        + TextFormat.GRAY + "Diamanty se spawnuji ve stredu mapy\n"
                        + TextFormat.GRAY + "==================================");
                this.spawnDiamonds();
                sound = 3;
                break;
            case 4:
                this.phase = 4;
                this.messageAllPlayers(TextFormat.GRAY + "===========[ " + TextFormat.DARK_AQUA + "Progress" + TextFormat.GRAY + " ]===========\n"
                        + TextFormat.GOLD + "Faze IV " + TextFormat.GRAY + "zacala\n"
                        + TextFormat.GRAY + "Nyni muzes varit lektvary\n"
                        + TextFormat.GRAY + "==================================");
                sound = 1;
                break;
            case 5:
                this.phase = 5;
                this.messageAllPlayers(TextFormat.GRAY + "===========[ " + TextFormat.DARK_AQUA + "Progress" + TextFormat.GRAY + " ]===========\n"
                        + TextFormat.RED + "Faze V " + TextFormat.GRAY + "zacala\n"
                        + TextFormat.RED + "Dvojnasobne poskozeni nexusu\n"
                        + TextFormat.GRAY + "==================================");
                sound = 4;
                break;
        }

        /*for (Player p : this.getAllPlayers().values()) {
            this.level.addSound(new NoteBoxSound(p, sound), new Player[]{p});
        }*/
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
            this.messageAllPlayers(Annihilation.getPrefix() + TextFormat.RED + "Je potreba minimalne 16 hracu k odstartovani hry");
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

        this.map = map;
        this.data = this.plugin.maps.get(map);
        this.recalculateBoundingBox();

        for (Player p : this.getAllPlayers().values()) {
            p.sendMessage(TextFormat.GOLD + "Byla vybrana mapa " + TextFormat.BOLD + TextFormat.YELLOW + map);
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

    @EventHandler
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
        new NormalQuery(Annihilation.getInstance(), Stat.NEXUSES, p.getName());

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

        String msg = TextFormat.GRAY + "===========[ " + damagedTeam.getColor() + "Nexus Znicen" + TextFormat.GRAY + " ]===========\n"
                + team.getColor() + p.getName() + TextFormat.GRAY + " z teamu " + team.getColor() + team.getName() + TextFormat.GRAY + " znicil nexus teamu " + damagedTeam.getColor() + damagedTeam.getName() + TextFormat.GRAY + "\n"
                + TextFormat.GRAY + "==================================";

        //jmena nemuzou byt v lowerCase
        ArrayList<String> names = new ArrayList<>();

        for (Player pl : damagedTeam.getPlayers().values()) {
            names.add(pl.getName());
        }

        new NormalQuery(Annihilation.getInstance(), Stat.LOSSES, names);

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

    /*@EventHandler
    public void onEntityDeath(EntityDeathEvent e){
        Entity entity = e.getEntity();
        EntityDamageEvent cause = entity.getLastDamageCause();

        if(entity instanceof IronGolem) {
            String pname = "";
            Entity damager;

            if (cause instanceof EntityDamageByEntityEvent && (damager = ((EntityDamageByEntityEvent) cause).getDamager()) instanceof Player) {
                pname = getPlayerTeam((Player) damager).getColor() + damager.getName();
            }

            if (entity.getNameTag().toLowerCase().contains("celariel")) {
                bossManager.onBossDeath(2, pname);
                e.setDrops(new Item[0]);
            } else if (entity.getNameTag().toLowerCase().contains("ferwin")) {
                bossManager.onBossDeath(1, pname);
                e.setDrops(new Item[0]);
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

    //private static final Integer[] blockedItems = new Integer[]{Item.LEATHER_BOOTS, Item.LEATHER_CAP, Item.LEATHER_PANTS, Item.LEATHER_TUNIC, Item.WOODEN_PICKAXE, Item.WOODEN_SWORD, Item.WOODEN_AXE};

    @EventHandler(priority = EventPriority.NORMAL, ignoreCancelled = false)
    public void onItemDrop(PlayerDropItemEvent e) {
        Player p = e.getPlayer();
        if (!this.inArena(p) || this.inLobby(p)) {
            e.setCancelled(true);
            return;
        }
        Item item = e.getItem();

        if (item.getName().equals(TextFormat.RESET + TextFormat.GOLD + "SoulBound")) {
            e.setCancelled();
            p.getInventory().setItemInHand(Item.get(0, 0, 0));
        }
    }

    @EventHandler(priority = EventPriority.NORMAL, ignoreCancelled = false)
    public void onTransaction(InventoryTransactionEvent e) {
        boolean is = false;

        Player p = null;
        int slot1 = 0;
        Item item1 = null;

        Inventory chest = null;
        //int slot = 0;
        //Item item = null;

        ShopInventory shop = null;
        int slot2 = 0;

        Inventory kitInventory = null;
        int slot3 = 0;

        for (Transaction trans : e.getTransaction().getTransactions()) {
            Inventory inv = trans.getInventory();

            if (inv instanceof PlayerInventory) {
                p = (Player) trans.getInventory().getHolder();
                slot1 = trans.getSlot();
                item1 = trans.getSourceItem();
                is = true;
            }  else if (inv instanceof ShopInventory) {
                if (inv instanceof KitInventory) {
                    kitInventory = inv;
                    slot3 = trans.getSlot();
                } else if (inv instanceof ShopWindow) {
                    shop = (ShopWindow) inv;
                    slot2 = trans.getSlot();
                }
            } else {
                chest = trans.getInventory();
                //slot = trans.getSlot();
                //item = trans.getSourceItem();
            }
        }

        if (!is) {
            return;
        }

        if (!this.inArena(p) || this.inLobby(p)) {
            e.setCancelled();
            return;
        }

        if (chest != null) {

            if (item1.getName().equals(TextFormat.RESET + TextFormat.GOLD + "SoulBound")) {
                e.setCancelled();
                p.getInventory().setItem(slot1, Item.get(0, 0, 0));
                return;
            }
            return;
        }

        if (shop != null) {
            e.setCancelled();

            Item item2 = shop.getItem(slot2);
            Item cost = shop.getItem(slot2 + 1);

            if (!p.getInventory().contains(cost)) {
                p.sendMessage(TextFormat.RED + "Nemas dostatek zlata");
                return;
            }

            p.getInventory().addItem(item2);
            p.getInventory().removeItem(cost);
            p.sendMessage(TextFormat.GRAY + "Koupil jsi " + TextFormat.YELLOW + item2.getName());
            p.getInventory().sendContents(p);
        } else if (kitInventory != null) {
            e.setCancelled();
            kitManager.itemHeld(p, slot3);
            p.removeWindow(kitInventory);
        }
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
            messageAllPlayers(TextFormat.GRAY + "================[ " + winnerteam.getColor() + "Konec Hry" + TextFormat.GRAY + " ]================\n"
                    + TextFormat.GRAY + "Team " + winnerteam.getColor() + winnerteam.getName() + TextFormat.GRAY + " vyhral Annihilation! Probiha restart...\n"
                    + TextFormat.GRAY + new String(new char[42 - 1]).replace("\0", "="));
            this.winnerteam.message(TextFormat.BOLD + TextFormat.GOLD + "Obdrzel jsi 40 tokenu za vyhru!");

            //jmena nemuzou byt v lowerCase
            ArrayList<String> names = new ArrayList<>();

            for (Player pl : this.winnerteam.getPlayers().values()) {
                names.add(pl.getName());
            }

            new NormalQuery(Annihilation.getInstance(), Stat.WINS, names);
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

    @EventHandler
    public void onPortalEnter(EntityPortalEnterEvent e) {
        Entity entity = e.getEntity();

        System.out.println("portal event");

        if (entity instanceof Player) {
            Player p = (Player) entity;

            if (inArena(p)) {
                PlayerData data = getPlayerData(p);

                if (!data.isInLobby()) {
                    p.teleport(data.getTeam().getSpawnLocation());
                    p.getServer().getScheduler().scheduleDelayedTask(new Runnable() {

                        @Override
                        public void run() {
                            p.addWindow(kitManager.kitWindow);
                        }
                    }, 20);
                }
            }
        }
    }

    private static class Resource {

        public final Item drop;
        public final Integer xp;
        public final Integer delay;

        public final Integer type;

        public static final int TYPE_ORE = 0;
        public static final int TYPE_WOOD = 1;

        public Resource(Item drop, Integer xp, Integer delay, Integer type) {
            this.drop = drop;
            this.xp = xp;
            this.delay = delay;
            this.type = type;
        }
    }
}