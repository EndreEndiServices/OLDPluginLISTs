package Annihilation.Entity;

import cn.nukkit.Player;
import cn.nukkit.entity.EntityHuman;
import cn.nukkit.event.entity.EntityDamageEvent;
import cn.nukkit.level.format.FullChunk;
import cn.nukkit.nbt.tag.CompoundTag;
import cn.nukkit.network.protocol.AddEntityPacket;
import cn.nukkit.network.protocol.AddPlayerPacket;
import cn.nukkit.network.protocol.RemoveEntityPacket;

import java.util.UUID;

public class SlapperHuman extends EntityHuman {

    public SlapperHuman(FullChunk chunk, CompoundTag nbt) {
        super(chunk, nbt);
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
            getInventory().sendHeldItem(p);*/

            /*AddPlayerPacket pk = new AddPlayerPacket();
            pk.uuid = p.getUniqueId();
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

            this.inventory.sendArmorContents(p);*/
        }
    }

    @Override
    public void despawnFrom(Player player) {
        if (this.hasSpawned.containsKey(player.getLoaderId())) {
            RemoveEntityPacket pk = new RemoveEntityPacket();
            pk.eid = this.getId();
            player.dataPacket(pk);
            this.hasSpawned.remove(player.getLoaderId());
        }

    }

    public void attack(EntityDamageEvent e) {
        e.setCancelled();
        super.attack(e);
    }

    @Override
    public void saveNBT() {

    }
}
