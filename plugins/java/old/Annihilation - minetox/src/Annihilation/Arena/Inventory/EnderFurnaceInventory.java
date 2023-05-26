package Annihilation.Arena.Inventory;

import Annihilation.Arena.BlockEntity.EnderFurnace;
import cn.nukkit.inventory.ContainerInventory;
import cn.nukkit.inventory.InventoryType;
import cn.nukkit.item.Item;

public class EnderFurnaceInventory extends ContainerInventory {

    private short burnTime = 0;
    private short burnDuration = 0;
    private short cookTime = 0;
    private short maxTime = 0;

    public EnderFurnaceInventory(EnderFurnace tile) {
        super(tile, InventoryType.get(InventoryType.FURNACE));
    }

    @Override
    public EnderFurnace getHolder() {
        return (EnderFurnace) this.holder;
    }

    public Item getResult() {
        return this.getItem(2);
    }

    public Item getFuel() {
        return this.getItem(1);
    }

    public Item getSmelting() {
        return this.getItem(0);
    }

    public boolean setResult(Item item) {
        return this.setItem(2, item);
    }

    public boolean setFuel(Item item) {
        return this.setItem(1, item);
    }

    public boolean setSmelting(Item item) {
        return this.setItem(0, item);
    }

    @Override
    public void onSlotChange(int index, Item before) {
        super.onSlotChange(index, before);

        getHolder().inventoryUpdate(this);
    }

    public short getBurnTime(){
        return burnTime;
    }

    public void setBurnTime(short burnTime){
        this.burnTime = burnTime;
    }

    public short getBurnDuration(){
        return burnDuration;
    }

    public void setBurnDuration(short burnDuration){
        this.burnDuration = burnDuration;
    }

    public short getCookTime(){
        return cookTime;
    }

    public void setCookTime(short cookTime){
        this.cookTime = cookTime;
    }

    public short getMaxTime(){
        return maxTime;
    }

    public void setMaxTime(short maxTime){
        this.maxTime = maxTime;
    }
}