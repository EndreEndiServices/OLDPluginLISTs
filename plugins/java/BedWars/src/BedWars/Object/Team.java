package BedWars.Object;

import BedWars.Arena.Arena;
import BedWars.Entity.TNTShip;
import BedWars.Shop.ItemWindow;
import BedWars.Shop.ShopWindow;
import BedWars.Shop.Window;
import BedWars.Utils.Items;
import cn.nukkit.Player;
import cn.nukkit.block.BlockChest;
import cn.nukkit.block.BlockSandstone;
import cn.nukkit.block.BlockTNT;
import cn.nukkit.inventory.ChestInventory;
import cn.nukkit.item.*;
import cn.nukkit.item.enchantment.Enchantment;
import cn.nukkit.nbt.tag.CompoundTag;
import cn.nukkit.utils.TextFormat;
import lombok.Getter;
import lombok.Setter;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.LinkedHashMap;
import java.util.Map;

public class Team {

    @Getter
    public Arena arena;

    @Setter
    public boolean bed = true;

    @Getter
    public String color;

    @Getter
    public String name;

    @Getter
    public int decimal;

    @Getter
    private String status;

    private ChestInventory enderChest;

    @Getter
    private ItemWindow shop;

    @Getter
    public int id;
    public Map<String, BedWarsData> players = new HashMap<>();

    public Team(Arena arena, int id, String name, String color, int decimal) {
        this.arena = arena;
        this.id = id;
        this.name = name;
        this.color = color;
        this.decimal = decimal;
        recalculateStatus();

        registerShop();
    }

    public boolean hasBed() {
        return this.bed;
    }

    public void messagePlayers(String message) {
        for (BedWarsData p : new ArrayList<>(this.players.values())) {
            p.getPlayer().sendMessage(message);
        }
        this.arena.plugin.getServer().getLogger().info(message);
    }

    public void messagePlayers(String message, BedWarsData data) {
        Player player = data.getPlayer();

        String msg = TextFormat.GRAY + "[" + color + "Team" + TextFormat.GRAY + "]   " + player.getDisplayName() + TextFormat.GRAY + " > " + data.getBaseData().getChatColor() + message;

        for (BedWarsData p : new ArrayList<>(this.players.values())) {
            p.getPlayer().sendMessage(msg);
        }
        this.arena.plugin.getServer().getLogger().info(msg);
    }

    public Map<String, BedWarsData> getPlayers() {
        return this.players;
    }

    public void addPlayer(BedWarsData p) {
        this.players.put(p.getPlayer().getName().toLowerCase(), p);
        p.setTeam(this);
        p.getPlayer().setNameTag(getColor() + p.getPlayer().getName());
        p.getPlayer().setDisplayName(TextFormat.GRAY + "[" + TextFormat.GREEN + p.getBaseData().getLevel() + TextFormat.GRAY + "]" + p.getBaseData().getPrefix() + " " + p.getTeam().getColor() + p.getPlayer().getName() + TextFormat.RESET);
        recalculateStatus();
    }

    public void removePlayer(BedWarsData p) {
        this.players.remove(p.getPlayer().getName().toLowerCase());
        p.setTeam(null);
        p.getPlayer().setNameTag(p.getPlayer().getName());
        recalculateStatus();
    }

    public void onBedBreak() {
        this.bed = false;
        recalculateStatus();
    }

    public void recalculateStatus() {
        int count = this.players.size();
        boolean bed = hasBed();

        if (count >= 1 || bed) {
            this.status = "                                          " + color + name + ": " + (bed ? TextFormat.GREEN + "✔" : TextFormat.RED + "✖") + TextFormat.GRAY + " " + this.players.size() + "\n";
        } else {
            this.status = "";
        }
    }

    /**
     * Shop
     */

    private void registerShop() {
        ItemWindow main = new ItemWindow(true);

        ItemWindow blocksW = new ItemWindow();
        ItemWindow armorW = new ItemWindow();
        ItemWindow pickaxeW = new ItemWindow();
        ItemWindow swordW = new ItemWindow();
        ItemWindow bowW = new ItemWindow();
        ItemWindow foodW = new ItemWindow();
        ItemWindow chestW = new ItemWindow();
        ItemWindow potionW = new ItemWindow();
        ItemWindow specialW = new ItemWindow();

        Map<Item, Window> blocks = new LinkedHashMap<>();
        blocks.put(Item.get(Item.SANDSTONE), new ShopWindow(Item.get(Item.SANDSTONE, 0, 2), Items.BRONZE.clone(), blocksW));
        blocks.put(Item.get(Item.SANDSTONE), new ShopWindow(Item.get(Item.SANDSTONE, 0, 16), setCount(Items.BRONZE, 8), blocksW));
        blocks.put(Item.get(Item.END_STONE), new ShopWindow(Item.get(Item.END_STONE), setCount(Items.BRONZE, 7), blocksW));
        blocks.put(Item.get(Item.GLOWSTONE_BLOCK), new ShopWindow(Item.get(Item.GLOWSTONE_BLOCK, 0, 4), setCount(Items.BRONZE, 15), blocksW));
        blocks.put(Item.get(Item.IRON_BLOCK), new ShopWindow(Item.get(Item.IRON_BLOCK), setCount(Items.IRON, 1), blocksW));
        blocks.put(Item.get(Item.GLASS), new ShopWindow(Item.get(Item.GLASS), setCount(Items.BRONZE, 4), blocksW));

        Map<Item, Window> armor = new LinkedHashMap<>();
        armor.put(addEnchantment(new ItemHelmetLeather().setCompoundTag(new CompoundTag().putInt("customColor", getDecimal())), Enchantment.ID_DURABILITY, 1), new ShopWindow(addEnchantment(new ItemHelmetLeather().setCompoundTag(new CompoundTag().putInt("customColor", getDecimal())), Enchantment.ID_DURABILITY, 1), Items.BRONZE.clone(), armorW));
        armor.put(addEnchantment(new ItemLeggingsLeather().setCompoundTag(new CompoundTag().putInt("customColor", getDecimal())), Enchantment.ID_DURABILITY, 1), new ShopWindow(addEnchantment(new ItemLeggingsLeather().setCompoundTag(new CompoundTag().putInt("customColor", getDecimal())), Enchantment.ID_DURABILITY, 1), Items.BRONZE.clone(), armorW));
        armor.put(addEnchantment(new ItemBootsLeather().setCompoundTag(new CompoundTag().putInt("customColor", getDecimal())), Enchantment.ID_DURABILITY, 1), new ShopWindow(addEnchantment(new ItemBootsLeather().setCompoundTag(new CompoundTag().putInt("customColor", getDecimal())), Enchantment.ID_DURABILITY, 1), Items.BRONZE.clone(), armorW));
        armor.put(addEnchantment(addEnchantment(new ItemChestplateChain(), Enchantment.ID_DURABILITY, 1), Enchantment.ID_PROTECTION_ALL, 1).setCustomName("Chestplate lvl I"), new ShopWindow(addEnchantment(addEnchantment(new ItemChestplateChain(), Enchantment.ID_DURABILITY, 1), Enchantment.ID_PROTECTION_ALL, 1).setCustomName("Chestplate lvl I"), setCount(Items.IRON, 1), armorW));
        armor.put(addEnchantment(addEnchantment(new ItemChestplateChain(), Enchantment.ID_DURABILITY, 1), Enchantment.ID_PROTECTION_ALL, 2).setCustomName("Chestplate lvl II"), new ShopWindow(addEnchantment(addEnchantment(new ItemChestplateChain(), Enchantment.ID_DURABILITY, 1), Enchantment.ID_PROTECTION_ALL, 2).setCustomName("Chestplate lvl II"), setCount(Items.IRON, 3), armorW));
        armor.put(addEnchantment(addEnchantment(new ItemChestplateChain(), Enchantment.ID_DURABILITY, 1), Enchantment.ID_PROTECTION_ALL, 3).setCustomName("Chestplate lvl III"), new ShopWindow(addEnchantment(addEnchantment(new ItemChestplateChain(), Enchantment.ID_DURABILITY, 1), Enchantment.ID_PROTECTION_ALL, 3).setCustomName("Chestplate lvl III"), setCount(Items.IRON, 7), armorW));

        Map<Item, Window> pickaxes = new LinkedHashMap<>();
        pickaxes.put(addEnchantment(addEnchantment(new ItemPickaxeWood(), Enchantment.ID_EFFICIENCY, 1), Enchantment.ID_DURABILITY, 1).setCustomName("Pickaxe lvl I"), new ShopWindow(addEnchantment(addEnchantment(new ItemPickaxeWood(), Enchantment.ID_EFFICIENCY, 1), Enchantment.ID_DURABILITY, 1).setCustomName("Pickaxe lvl I"), setCount(Items.BRONZE, 4), pickaxeW));
        pickaxes.put(addEnchantment(addEnchantment(new ItemPickaxeStone(), Enchantment.ID_EFFICIENCY, 1), Enchantment.ID_DURABILITY, 1).setCustomName("Pickaxe lvl II"), new ShopWindow(addEnchantment(addEnchantment(new ItemPickaxeStone(), Enchantment.ID_EFFICIENCY, 1), Enchantment.ID_DURABILITY, 1).setCustomName("Pickaxe lvl II"), setCount(Items.IRON, 2), pickaxeW));
        pickaxes.put(addEnchantment(addEnchantment(new ItemPickaxeIron(), Enchantment.ID_EFFICIENCY, 3), Enchantment.ID_DURABILITY, 1).setCustomName("Pickaxe lvl III"), new ShopWindow(addEnchantment(addEnchantment(new ItemPickaxeIron(), Enchantment.ID_EFFICIENCY, 3), Enchantment.ID_DURABILITY, 1).setCustomName("Pickaxe lvl III"), Items.GOLD.clone(), pickaxeW));

        Map<Item, Window> swords = new LinkedHashMap<>();
        swords.put(addEnchantment(new ItemStick(), Enchantment.ID_KNOCKBACK, 1).setCustomName("Knockback Stick"), new ShopWindow(addEnchantment(new ItemStick(), Enchantment.ID_KNOCKBACK, 1).setCustomName("Knockback Stick"), setCount(Items.BRONZE, 8), swordW));
        swords.put(addEnchantment(addEnchantment(new ItemSwordGold(), Enchantment.ID_DURABILITY, 1), Enchantment.ID_DAMAGE_ALL, 1).setCustomName("Sword lvl I"), new ShopWindow(addEnchantment(addEnchantment(new ItemSwordGold(), Enchantment.ID_DURABILITY, 1), Enchantment.ID_DAMAGE_ALL, 1).setCustomName("Sword lvl I"), Items.IRON.clone(), swordW));
        swords.put(addEnchantment(addEnchantment(new ItemSwordGold(), Enchantment.ID_DURABILITY, 1), Enchantment.ID_DAMAGE_ALL, 2).setCustomName("Sword lvl II"), new ShopWindow(addEnchantment(addEnchantment(new ItemSwordGold(), Enchantment.ID_DURABILITY, 1), Enchantment.ID_DAMAGE_ALL, 2).setCustomName("Sword lvl II"), setCount(Items.IRON, 3), swordW));
        swords.put(addEnchantment(addEnchantment(addEnchantment(new ItemSwordIron(), Enchantment.ID_KNOCKBACK, 1), Enchantment.ID_DAMAGE_ALL, 2).setCustomName("Sword lvl III"), Enchantment.ID_DURABILITY, 2), new ShopWindow(addEnchantment(addEnchantment(addEnchantment(new ItemSwordIron(), Enchantment.ID_KNOCKBACK, 1), Enchantment.ID_DAMAGE_ALL, 2), Enchantment.ID_DURABILITY, 2).setCustomName("Sword lvl III"), setCount(Items.GOLD, 5), swordW));

        Map<Item, Window> bows = new LinkedHashMap<>();
        bows.put(addEnchantment(new ItemBow(), Enchantment.ID_BOW_INFINITY, 1).setCustomName("Bow lvl I"), new ShopWindow(addEnchantment(new ItemBow(), Enchantment.ID_BOW_INFINITY, 1), setCount(Items.GOLD, 3), bowW));
        bows.put(addEnchantment(addEnchantment(new ItemBow(), Enchantment.ID_BOW_INFINITY, 1), Enchantment.ID_BOW_POWER, 1).setCustomName("Bow lvl II"), new ShopWindow(addEnchantment(addEnchantment(new ItemBow(), Enchantment.ID_BOW_INFINITY, 1), Enchantment.ID_BOW_POWER, 1).setCustomName("Bow lvl II"), setCount(Items.GOLD, 7), bowW));
        bows.put(addEnchantment(addEnchantment(addEnchantment(new ItemBow(), Enchantment.ID_BOW_INFINITY, 1), Enchantment.ID_BOW_POWER, 1), Enchantment.ID_BOW_KNOCKBACK, 1).setCustomName("Bow lvl III"), new ShopWindow(addEnchantment(addEnchantment(addEnchantment(new ItemBow(), Enchantment.ID_BOW_INFINITY, 1), Enchantment.ID_BOW_POWER, 1), Enchantment.ID_BOW_KNOCKBACK, 1).setCustomName("Bow lvl III"), setCount(Items.GOLD, 13), bowW));
        bows.put(addEnchantment(new ItemBow(), Enchantment.ID_BOW_INFINITY, 1).setCustomName("Explosive Bow"), new ShopWindow(addEnchantment(new ItemBow(), Enchantment.ID_BOW_INFINITY, 1).setCustomName("Explosive Bow"), setCount(Items.GOLD, 20), bowW));
        bows.put(new ItemArrow(), new ShopWindow(new ItemArrow(), Items.GOLD.clone(), bowW));

        Map<Item, Window> food = new LinkedHashMap<>();
        food.put(new ItemApple(), new ShopWindow(new ItemApple(), Items.BRONZE.clone(), foodW));
        food.put(new ItemPorkchopCooked(), new ShopWindow(new ItemPorkchopCooked(), setCount(Items.BRONZE, 2), foodW));
        food.put(new ItemCake(), new ShopWindow(new ItemCake(), Items.IRON.clone(), foodW));
        food.put(new ItemAppleGold(), new ShopWindow(new ItemAppleGold(), setCount(Items.GOLD, 3), foodW));

        Map<Item, Window> chests = new LinkedHashMap<>();
        chests.put(Item.get(Item.CHEST), new ShopWindow(Item.get(Item.CHEST), Items.IRON.clone(), chestW));

        Map<Item, Window> potions = new LinkedHashMap<>();
        potions.put(new ItemPotion(ItemPotion.INSTANT_HEALTH), new ShopWindow(new ItemPotion(ItemPotion.INSTANT_HEALTH), setCount(Items.IRON, 3), potionW));
        potions.put(new ItemPotion(ItemPotion.INSTANT_HEALTH_II), new ShopWindow(new ItemPotion(ItemPotion.INSTANT_HEALTH_II), setCount(Items.IRON, 5), potionW));
        potions.put(new ItemPotion(ItemPotion.SPEED_LONG), new ShopWindow(new ItemPotion(ItemPotion.SPEED_LONG), setCount(Items.IRON, 7), potionW));
        potions.put(new ItemPotion(ItemPotion.STRENGTH_LONG), new ShopWindow(new ItemPotion(ItemPotion.STRENGTH_LONG), setCount(Items.GOLD, 8), potionW));

        Map<Item, Window> specials = new LinkedHashMap<>();
        specials.put(Item.get(Item.SPONGE), new ShopWindow(Item.get(Item.SPONGE), setCount(Items.IRON, 5), specialW));
        //specials.put(Item.get(Item.SPAWN_EGG, TNTShip.NETWORK_ID), new ShopWindow(Item.get(Item.SPAWN_EGG, TNTShip.NETWORK_ID), setCount(Items.BRONZE, 64), specialW));
        //TODO: MINA
        //TODO: PRUT
        //TODO: ENDER PEARL
        //TODO: WARP DUST

        ////////////////////////////////////////////////////////////////////

        blocksW.setWindows(blocks, main);
        armorW.setWindows(armor, main);
        pickaxeW.setWindows(pickaxes, main);
        swordW.setWindows(swords, main);
        foodW.setWindows(food, main);
        bowW.setWindows(bows, main);
        chestW.setWindows(chests, main);
        potionW.setWindows(potions, main);
        specialW.setWindows(specials, main);

        Map<Item, Window> mainWindow = new LinkedHashMap<>();
        mainWindow.put(new ItemBlock(new BlockSandstone()), blocksW);
        mainWindow.put(new ItemChestplateChain(), armorW);
        mainWindow.put(new ItemSwordGold(), swordW);
        mainWindow.put(new ItemPickaxeStone(), pickaxeW);
        mainWindow.put(new ItemBow(), bowW);
        mainWindow.put(new ItemBlock(new BlockChest()), chestW);
        mainWindow.put(new ItemApple(), foodW);
        mainWindow.put(new ItemPotion(), potionW);
        mainWindow.put(new ItemBlock(new BlockTNT()), specialW);

        main.setWindows(mainWindow);

        this.shop = main;
    }

    private Item setCount(Item i, int count) {
        Item item = i.clone();
        item.setCount(count);
        return item;
    }

    private Item addEnchantment(Item item, int id, int lvl) {
        /*Enchantment e = Enchantment.get(id);
        e.setLevel(lvl, false);
        item.addEnchantment(e);*/

        return item;
    }
}
