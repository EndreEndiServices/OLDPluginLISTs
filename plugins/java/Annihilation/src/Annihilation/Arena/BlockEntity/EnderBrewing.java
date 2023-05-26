package Annihilation.Arena.BlockEntity;

import Annihilation.Arena.Inventory.EnderBrewingInventory;
import cn.nukkit.Player;
import cn.nukkit.Server;
import cn.nukkit.block.BlockAir;
import cn.nukkit.blockentity.BlockEntity;
import cn.nukkit.blockentity.BlockEntityContainer;
import cn.nukkit.blockentity.BlockEntityNameable;
import cn.nukkit.blockentity.BlockEntitySpawnable;
import cn.nukkit.inventory.BrewingRecipe;
import cn.nukkit.inventory.InventoryHolder;
import cn.nukkit.item.Item;
import cn.nukkit.item.ItemBlock;
import cn.nukkit.level.format.FullChunk;
import cn.nukkit.nbt.tag.CompoundTag;
import cn.nukkit.nbt.tag.ListTag;
import cn.nukkit.nbt.tag.StringTag;
import cn.nukkit.network.protocol.ContainerSetDataPacket;

import java.util.ArrayList;
import java.util.Arrays;

public class EnderBrewing extends BlockEntitySpawnable implements InventoryHolder, BlockEntityContainer, BlockEntityNameable {

    protected EnderBrewingInventory inventory;

    protected ArrayList<EnderBrewingInventory> inventories = new ArrayList<>();

    public static final int MAX_BREW_TIME = 40;

    public static final ArrayList<Integer> ingredients = new ArrayList<>(Arrays.asList(Item.NETHER_WART, Item.GOLD_NUGGET, Item.GHAST_TEAR, Item.GLOWSTONE_DUST, Item.REDSTONE_DUST, Item.GUNPOWDER, Item.MAGMA_CREAM, Item.BLAZE_POWDER, Item.GOLDEN_CARROT, Item.SPIDER_EYE, Item.FERMENTED_SPIDER_EYE, Item.GLISTERING_MELON, Item.SUGAR, Item.RAW_FISH));

    public EnderBrewing(FullChunk chunk, CompoundTag nbt) {
        super(chunk, nbt);
        inventory = new EnderBrewingInventory(this);

        this.scheduleUpdate();
    }

    @Override
    public String getName() {
        return "Ender Brewing";
    }

    @Override
    public boolean hasName() {
        return namedTag.contains("CustomName");
    }

    @Override
    public void setName(String name) {
        if (name.equals("")) {
            namedTag.remove("CustomName");
            return;
        }

        namedTag.putString("CustomName", name);
    }

    @Override
    public void close() {
        if (!closed) {
            for (Player player : getInventory().getViewers()) {
                player.removeWindow(getInventory());
            }
            super.close();
        }
    }

    @Override
    public void saveNBT() {
    }

    @Override
    public boolean isBlockEntityValid() {
        return true;
    }

    @Override
    public int getSize() {
        return 4;
    }

    protected int getSlotIndex(int index) {
        ListTag<CompoundTag> list = this.namedTag.getList("Items", CompoundTag.class);
        for (int i = 0; i < list.size(); i++) {
            if (list.get(i).getByte("Slot") == index) {
                return i;
            }
        }

        return -1;
    }

    @Override
    public Item getItem(int index) {
        int i = this.getSlotIndex(index);
        if (i < 0) {
            return new ItemBlock(new BlockAir(), 0, 0);
        } else {
            CompoundTag data = (CompoundTag) this.namedTag.getList("Items").get(i);
            return Item.get(data.getShort("id"), data.getShort("Damage"), data.getByte("Count"));
        }
    }

    @Override
    public void setItem(int index, Item item) {
        int i = this.getSlotIndex(index);

        CompoundTag d = new CompoundTag()
                .putByte("Count", item.getCount())
                .putByte("Slot", index)
                .putShort("id", item.getId())
                .putShort("Damage", item.getDamage());

        if (item.getId() == Item.AIR || item.getCount() <= 0) {
            if (i >= 0) {
                this.namedTag.getList("Items").getAll().remove(i);
            }
        } else if (i < 0) {
            (this.namedTag.getList("Items", CompoundTag.class)).add(d);
        } else {
            (this.namedTag.getList("Items", CompoundTag.class)).add(i, d);
        }
    }

    @Override
    public EnderBrewingInventory getInventory() {
        return inventory;
    }

    protected boolean checkIngredient(Item ingredient) {
        return ingredients.contains(ingredient.getId());
    }

    private int lastUpdate = 0;

    @Override
    public boolean onUpdate() {
        if (closed) {
            return false;
        }

        if (lastUpdate < 10) {
            lastUpdate++;
            return true;
        }

        lastUpdate = 0;

        for (int index = 0; index < inventories.size(); index++) {
            inventory = inventories.get(index);

            Item ingredient = inventory.getIngredient();
            boolean canBrew = false;

            for (int i = 1; i <= 3; i++) {
                if (inventory.getItem(i).getId() == Item.POTION) {
                    canBrew = true;
                }
            }

            if (inventory.brewTime <= MAX_BREW_TIME && canBrew && ingredient.getCount() > 0) {
                if (!this.checkIngredient(ingredient)) {
                    canBrew = false;
                }
            } else {
                canBrew = false;
            }

            if (canBrew) {
                inventory.brewTime--;

                if (inventory.brewTime <= 0) { //20 seconds
                    for (int i = 1; i <= 3; i++) {
                        Item potion = inventory.getItem(i);
                        BrewingRecipe recipe = Server.getInstance().getCraftingManager().matchBrewingRecipe(ingredient, potion);

                        if (recipe != null) {
                            inventory.setItem(i, recipe.getResult());
                        }
                    }

                    ingredient.count--;
                    inventory.setIngredient(ingredient);

                    inventory.brewTime = MAX_BREW_TIME;
                }

                for (Player player : inventory.getViewers()) {
                    int windowId = player.getWindowId(inventory);
                    if (windowId > 0) {
                        ContainerSetDataPacket pk = new ContainerSetDataPacket();
                        pk.windowid = (byte) windowId;
                        pk.property = 0;
                        pk.value = inventory.brewTime * 10;
                        player.dataPacket(pk);
                    }

                }

            } else {
                namedTag.putShort("CookTime", MAX_BREW_TIME);

                this.inventories.remove(index);
            }

        }

        super.lastUpdate = System.currentTimeMillis();

        return true;
    }

    public void inventoryUpdate(EnderBrewingInventory inv) {
        inventories.add(inv);
    }

    @Override
    public CompoundTag getSpawnCompound() {
        CompoundTag nbt = new CompoundTag()
                .putString("id", BlockEntity.BREWING_STAND)
                .putInt("x", (int) this.x)
                .putInt("y", (int) this.y)
                .putInt("z", (int) this.z)
                .putShort("CookTime", MAX_BREW_TIME)
                .putString("CustomName", "Ender Brewing");

        return nbt;
    }

}