package Annihilation.Entity;

import cn.nukkit.Player;
import cn.nukkit.entity.data.ByteEntityData;
import cn.nukkit.entity.passive.EntityAnimal;
import cn.nukkit.event.entity.EntityDamageEvent;
import cn.nukkit.item.Item;
import cn.nukkit.level.format.FullChunk;
import cn.nukkit.nbt.tag.CompoundTag;
import cn.nukkit.network.protocol.AddEntityPacket;

public class IronGolem extends EntityAnimal {

    public static final int NETWORK_ID = 20;

    public float height = 2.688F;
    public float width = 1.625F;
    public float length = 0.906F;

    private Player target;

    public IronGolem(FullChunk chunk, CompoundTag nbt) {
        super(chunk, nbt);

        setDataProperty(new ByteEntityData(DATA_NO_AI, 1));
    }

    public void initEntity() {
        super.initEntity();

        setMaxHealth(200);
        setHealth(200);

        setDataProperty(new ByteEntityData(DATA_NO_AI, 1));
    }

    public int getNetworkId() {
        return NETWORK_ID;
    }

    public String getName() {
        return "Iron Golem";
    }

    public boolean onUpdate(int currentTick){
        if(this.closed) {
            return false;
        } else {
            int tickDiff = currentTick - this.lastUpdate;
            if(tickDiff <= 0 && !this.justCreated) {
                return true;
            } else {
                this.lastUpdate = currentTick;
                boolean hasUpdate = this.entityBaseTick(tickDiff);
                if(this.isAlive()) {
                    if(target != null){
                        if(distance(target) > 13){
                            target = null;
                        }
                    }

                    this.motionY -= (double)this.getGravity();
                    if(this.checkObstruction(this.x, this.y, this.z)) {
                        hasUpdate = true;
                    }

                    this.move(this.motionX, this.motionY, this.motionZ);
                    double friction = (double)(1.0F - this.getDrag());
                    if(this.onGround && (Math.abs(this.motionX) > 1.0E-5D || Math.abs(this.motionZ) > 1.0E-5D)) {
                        friction *= this.getLevel().getBlock(this.temporalVector.setComponents((double)((int)Math.floor(this.x)), (double)((int)Math.floor(this.y - 1.0D)), (double)((int)Math.floor(this.z) - 1))).getFrictionFactor();
                    }

                    this.motionX *= friction;
                    this.motionY *= (double)(1.0F - this.getDrag());
                    this.motionZ *= friction;
                    if(this.onGround) {
                        this.motionY *= -0.5D;
                    }

                    this.updateMovement();
                }

                return hasUpdate || !this.onGround || Math.abs(this.motionX) > 1.0E-5D || Math.abs(this.motionY) > 1.0E-5D || Math.abs(this.motionZ) > 1.0E-5D;
            }
        }
    }

    public void spawnTo(Player player) {
        AddEntityPacket pk = new AddEntityPacket();

        pk.eid = getId();
        pk.x = (float) x;
        pk.y = (float) y;
        pk.z = (float) z;
        pk.speedX = (float) motionX;
        pk.speedY = (float) motionY;
        pk.speedZ = (float) motionZ;
        pk.yaw = (float) yaw;
        pk.pitch = (float) pitch;
        pk.metadata = dataProperties;
        pk.type = IronGolem.NETWORK_ID;
        player.dataPacket(pk);

        super.spawnTo(player);
    }

    public Item[] getDrops() {
        return new Item[]{};
    }

    public void setTarget(Player p){
        target = p;
    }

    public Player getTarget(){
        return target;
    }

    public void attack(EntityDamageEvent e){
        super.attack(e);
    }
}
