package Annihilation.Entity;

import Annihilation.Arena.Inventory.HumanInventory;
import cn.nukkit.Player;
import cn.nukkit.entity.EntityCreature;
import cn.nukkit.entity.data.ByteEntityData;
import cn.nukkit.entity.data.Skin;
import cn.nukkit.event.entity.EntityDamageEvent;
import cn.nukkit.inventory.InventoryHolder;
import cn.nukkit.item.Item;
import cn.nukkit.level.format.FullChunk;
import cn.nukkit.nbt.NBTIO;
import cn.nukkit.nbt.tag.CompoundTag;
import cn.nukkit.nbt.tag.ListTag;
import cn.nukkit.network.protocol.AddEntityPacket;
import cn.nukkit.network.protocol.AddPlayerPacket;
import cn.nukkit.network.protocol.RemoveEntityPacket;
import cn.nukkit.network.protocol.RemovePlayerPacket;
import cn.nukkit.utils.TextFormat;
import cn.nukkit.utils.Utils;
import lombok.Getter;
import lombok.Setter;

import java.nio.charset.StandardCharsets;
import java.util.UUID;

public class AnnihilationNPC extends EntityCreature implements InventoryHolder {

    @Getter
    private HumanInventory inventory;

    @Getter
    @Setter
    private Skin skin;

    @Getter
    private UUID uuid;

    public AnnihilationNPC(FullChunk chunk, CompoundTag nbt) {
        super(chunk, nbt);
    }

    @Override
    public void initEntity() {
        inventory = new HumanInventory(this);

        if (this.namedTag.contains("Skin") && this.namedTag.get("Skin") instanceof CompoundTag) {
            if (!this.namedTag.getCompound("Skin").contains("Transparent")) {
                this.namedTag.getCompound("Skin").putBoolean("Transparent", false);
            }
            this.setSkin(new Skin(this.namedTag.getCompound("Skin").getByteArray("Data"), this.namedTag.getCompound("Skin").getString("ModelId")));
        }

        this.setNameTag(getFullName());

        this.uuid = Utils.dataToUUID(String.valueOf(this.getId()).getBytes(StandardCharsets.UTF_8), this.getSkin()
                .getData(), this.getNameTag().getBytes(StandardCharsets.UTF_8));

        super.initEntity();
    }

    @Override
    public void saveNBT() {
        super.saveNBT();
        this.namedTag.putList(new ListTag<>("Inventory"));
        if (this.inventory != null) {
            for (int slot = 0; slot < 9; ++slot) {
                int hotbarSlot = this.inventory.getHotbarSlotIndex(slot);
                if (hotbarSlot != -1) {
                    Item item = this.inventory.getItem(hotbarSlot);
                    if (item.getId() != 0 && item.getCount() > 0) {
                        this.namedTag.getList("Inventory", CompoundTag.class).add(NBTIO.putItemHelper(item, slot).putByte("TrueSlot", hotbarSlot));
                        continue;
                    }
                }
                this.namedTag.getList("Inventory", CompoundTag.class).add(new CompoundTag()
                        .putByte("Count", 0)
                        .putShort("Damage", 0)
                        .putByte("Slot", slot)
                        .putByte("TrueSlot", -1)
                        .putShort("id", 0)
                );
            }

            int slotCount = Player.SURVIVAL_SLOTS + 9;
            for (int slot = 9; slot < slotCount; ++slot) {
                Item item = this.inventory.getItem(slot - 9);
                this.namedTag.getList("Inventory", CompoundTag.class).add(NBTIO.putItemHelper(item, slot));
            }

            for (int slot = 100; slot < 104; ++slot) {
                Item item = this.inventory.getItem(this.inventory.getSize() + slot - 100);
                if (item != null && item.getId() != Item.AIR) {
                    this.namedTag.getList("Inventory", CompoundTag.class).add(NBTIO.putItemHelper(item, slot));
                }
            }
        }
    }

    @Override
    public void spawnTo(Player p) {
        if (!this.hasSpawned.containsKey(p.getLoaderId())) {
            this.hasSpawned.put(p.getLoaderId(), p);

            /*AddEntityPacket pk = new AddEntityPacket();
            pk.eid = getId();
            pk.type = 32;
            pk.x = (float) x;
            pk.y = (float) y;
            pk.z = (float) z;
            pk.speedX = (float) motionX;
            pk.speedY = (float) motionY;
            pk.speedZ = (float) motionZ;
            pk.yaw = (float) yaw;
            pk.pitch = (float) pitch;
            pk.metadata = dataProperties;
            p.dataPacket(pk);

            getInventory().sendContents(p);
            getInventory().sendArmorContents(p);
            getInventory().sendHeldItem(p);*/

            if (this.skin.getData().length < 64 * 32 * 4) {
                throw new IllegalStateException(this.getClass().getSimpleName() + " must have a valid skin set");
            }

            AddPlayerPacket pk = new AddPlayerPacket();
            pk.uuid = this.getUuid();
            pk.username = this.getName();
            pk.eid = this.getId();
            pk.x = (float) this.x;
            pk.y = (float) this.y;
            pk.z = (float) this.z;
            pk.speedX = (float) this.motionX;
            pk.speedY = (float) this.motionY;
            pk.speedZ = (float) this.motionZ;
            pk.yaw = (float) this.yaw;
            pk.pitch = (float) this.pitch;
            pk.item = this.getInventory().getItemInHand();
            pk.metadata = this.dataProperties;
            p.dataPacket(pk);

            this.inventory.sendArmorContents(p);
        }
    }

    @Override
    public void despawnFrom(Player player) {
        if (this.hasSpawned.containsKey(player.getLoaderId())) {
            RemovePlayerPacket pk = new RemovePlayerPacket();
            pk.eid = this.getId();
            pk.uuid = this.getUuid();
            player.dataPacket(pk);

            this.hasSpawned.remove(player.getLoaderId());
        }

    }

    @Override
    public void attack(EntityDamageEvent e) {
        e.setCancelled();
        super.attack(e);
    }

    public String getFullName() {
        int cost = this.namedTag.getInt("Cost");
        String name = this.namedTag.getString("KitName");

        return TextFormat.GRAY + "[" + (cost > 0 ? TextFormat.DARK_AQUA + cost : TextFormat.GREEN + "FREE") + TextFormat.GRAY + "]  " + TextFormat.YELLOW + name.substring(0, 1).toUpperCase() + name.substring(1);
    }

    public String getKitName() {
        return this.namedTag.getString("KitName");
    }

    public void setCost(int cost) {
        this.namedTag.putInt("Cost", cost);
    }

    public int getCost() {
        return this.namedTag.getInt("Cost");
    }

    public int getNetworkId() {
        return 32;
    }
}