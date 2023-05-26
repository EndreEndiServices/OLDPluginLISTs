package Annihilation.Arena.BlockEntity;

import Annihilation.Arena.Inventory.EnderFurnaceInventory;
import cn.nukkit.Player;
import cn.nukkit.blockentity.BlockEntity;
import cn.nukkit.blockentity.BlockEntityContainer;
import cn.nukkit.blockentity.BlockEntityNameable;
import cn.nukkit.blockentity.BlockEntitySpawnable;
import cn.nukkit.inventory.FurnaceRecipe;
import cn.nukkit.inventory.InventoryHolder;
import cn.nukkit.item.Item;
import cn.nukkit.level.format.FullChunk;
import cn.nukkit.nbt.tag.CompoundTag;
import cn.nukkit.nbt.tag.ListTag;
import cn.nukkit.nbt.tag.StringTag;
import cn.nukkit.network.protocol.ContainerSetDataPacket;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.HashSet;

public class EnderFurnace extends BlockEntitySpawnable implements InventoryHolder, BlockEntityContainer, BlockEntityNameable {

    protected EnderFurnaceInventory inventory;

    protected ArrayList<EnderFurnaceInventory> inventories = new ArrayList<>();

    public EnderFurnace(FullChunk chunk, CompoundTag nbt) {
        super(chunk, nbt);
        this.inventory = new EnderFurnaceInventory(this);

        this.scheduleUpdate();
    }

    @Override
    public String getName() {
        return "Ender Furnace";
    }

    @Override
    public boolean hasName() {
        return true;
    }

    @Override
    public void setName(String name) {

    }

    @Override
    public void close() {
        if (!this.closed) {
            for (Player player : this.getInventory().getViewers()) {
                player.removeWindow(this.getInventory());
            }
            super.close();
        }
    }

    @Override
    public void saveNBT() {

    }

    @Override
    public int getSize() {
        return 3;
    }

    protected int getSlotIndex(int index) {
        ListTag<CompoundTag> list = this.namedTag.getList("Items", CompoundTag.class);
        for (int i = 0; i < list.size(); i++) {
            if ((list.get(i).getByte("Slot") & 0xff) == index) {
                return i;
            }
        }

        return -1;
    }

    @Override
    public Item getItem(int index) {
        int i = this.getSlotIndex(index);
        if (i < 0) {
            return Item.get(Item.AIR, 0, 0);
        } else {
            CompoundTag data = (CompoundTag) this.namedTag.getList("Items").get(i);
            return Item.get(data.getShort("id"), data.getShort("Damage"), data.getByte("Count") & 0xff);
        }
    }

    @Override
    public void setItem(int index, Item item) {
        int i = this.getSlotIndex(index);

        CompoundTag d = new CompoundTag()
                .putByte("Count", (byte) item.getCount())
                .putByte("Slot", (byte) index)
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
    public boolean isBlockEntityValid(){
        return true;
    }

    @Override
    public EnderFurnaceInventory getInventory() {
        return inventory;
    }

    protected void checkFuel(Item fuel, EnderFurnaceInventory ei) {
        ei.setMaxTime(fuel.getFuelTime() == null ? 0 : (short) (fuel.getFuelTime() / 10));
        ei.setBurnTime(fuel.getFuelTime() == null ? 0 : (short) (fuel.getFuelTime() / 10));
        ei.setBurnDuration((short) 0);

        if (ei.getBurnTime() > 0) {
            fuel.setCount(fuel.getCount() - 1);
            if (fuel.getCount() == 0) {
                fuel = Item.get(Item.AIR, 0, 0);
            }
            ei.setFuel(fuel);
        }
    }

    public void inventoryUpdate(EnderFurnaceInventory ei){
        if(!inventories.contains(ei)){
            inventories.add(ei);
        }
    }

    private int lastUpdate = 0;

    @Override
    public boolean onUpdate() {
        if (this.closed) {
            return false;
        }

        if(lastUpdate < 10){
            lastUpdate++;
            return true;
        }

        lastUpdate = 0;

        for(int index = 0; index < inventories.size(); index++){

            EnderFurnaceInventory ei = inventories.get(index);

            boolean ret = false;

            Item fuel = ei.getFuel();
            Item raw = ei.getSmelting();
            Item product = ei.getResult();
            FurnaceRecipe smelt = this.server.getCraftingManager().matchFurnaceRecipe(raw);
            boolean canSmelt = (smelt != null && raw.getCount() > 0 && ((smelt.getResult().equals(product, true) && product.getCount() < product.getMaxStackSize()) || product.getId() == Item.AIR));

            if (ei.getBurnTime() <= 0 && canSmelt && fuel.getFuelTime() != null && fuel.getCount() > 0) {
                this.checkFuel(fuel, ei);
            }

            if (ei.getBurnTime() > 0) {
                ei.setBurnTime((short) (ei.getBurnTime() -  1));
                ei.setBurnDuration((short) Math.ceil((double) ei.getBurnTime() / (double) ei.getMaxTime() * 20d));

                if (smelt != null && canSmelt) {
                    ei.setCookTime((short) (ei.getCookTime() + 1));
                    if (ei.getCookTime() >= 20) {
                        product = Item.get(smelt.getResult().getId(), smelt.getResult().getDamage(), product.getCount() + 1);

                        ei.setResult(product);
                        raw.setCount(raw.getCount() - 1);
                        if (raw.getCount() == 0) {
                            raw = Item.get(Item.AIR, 0, 0);
                        }
                        ei.setSmelting(raw);

                        ei.setCookTime((short) (ei.getCookTime() - 20));
                    }
                } else if (ei.getBurnTime() <= 0) {
                    ei.setBurnTime((short) 0);
                    ei.setBurnDuration((short) 0);
                    ei.setCookTime((short) 0);
                } else {
                    ei.setCookTime((short) 0);
                }

                ret = true;
            } else {
                ei.setBurnTime((short) 0);
                ei.setBurnDuration((short) 0);
                ei.setCookTime((short) 0);
            }

            if(!ret){
                inventories.remove(index);
                continue;
            }

            for (Player player : ei.getViewers()) {
                int windowId = player.getWindowId(ei);
                if (windowId > 0) {
                    ContainerSetDataPacket pk = new ContainerSetDataPacket();
                    pk.windowid = (byte) windowId;
                    pk.property = 0;
                    pk.value = ei.getCookTime() * 10;
                    player.dataPacket(pk);

                    pk = new ContainerSetDataPacket();
                    pk.windowid = (byte) windowId;
                    pk.property = 1;
                    pk.value = ei.getBurnDuration() * 10;
                    player.dataPacket(pk);
                }
            }
        }

        super.lastUpdate = System.currentTimeMillis();

        return true;
    }

    @Override
    public CompoundTag getSpawnCompound() {
        CompoundTag c = new CompoundTag()
                .putString("id", BlockEntity.FURNACE)
                .putInt("x", (int) this.x)
                .putInt("y", (int) this.y)
                .putInt("z", (int) this.z)
                .putShort("BurnDuration", 0)
                .putShort("BurnTime", 0)
                .putShort("CookTime", 0)
                .putString("CustomName", "Ender Furnace");

        return c;
    }
}