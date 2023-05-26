package BedWars.Entity;

import BedWars.Object.Team;
import cn.nukkit.Player;
import cn.nukkit.block.BlockLiquid;
import cn.nukkit.entity.Entity;
import cn.nukkit.entity.EntityCreature;
import cn.nukkit.entity.EntityExplosive;
import cn.nukkit.entity.data.ByteEntityData;
import cn.nukkit.event.entity.EntityDamageByEntityEvent;
import cn.nukkit.item.Item;
import cn.nukkit.level.Explosion;
import cn.nukkit.level.format.FullChunk;
import cn.nukkit.math.NukkitMath;
import cn.nukkit.math.Vector2;
import cn.nukkit.math.Vector3;
import cn.nukkit.nbt.tag.CompoundTag;
import milk.pureentities.entity.animal.Animal;
import milk.pureentities.entity.animal.WalkingAnimal;
import milk.pureentities.util.Utils;

public class TNTShip extends WalkingAnimal implements Animal, EntityExplosive {
    public static final int NETWORK_ID = 13;
    public static final int DATA_POWERED = 19;

    private int bombTime = 0;
    private Team team;

    public TNTShip(FullChunk chunk, CompoundTag nbt, Team team) {
        super(chunk, nbt);
        this.team = team;
    }

    @Override
    public int getNetworkId() {
        return 13;
    }

    @Override
    public float getWidth() {
        return 0.9F;
    }

    @Override
    public float getHeight() {
        return 1.3F;
    }

    @Override
    public void initEntity() {
        super.initEntity();
        this.setMaxHealth(8);
    }

    public boolean isPowered() {
        return this.getDataPropertyBoolean(DATA_POWERED);
    }

    public void setPowered() {
        this.namedTag.putBoolean("powered", true);
        this.setDataProperty(new ByteEntityData(DATA_POWERED, 1));
    }

    public void setPowered(boolean powered) {
        this.namedTag.putBoolean("powered", powered);
        this.setDataProperty(new ByteEntityData(DATA_POWERED, powered ? 1 : 0));
    }

    public int getBombTime() {
        return this.bombTime;
    }

    @Override
    public void explode() {
        Explosion explosion = new Explosion(this, (float) 4, this);
        explosion.explodeB();
        this.close();
    }

    protected void checkTarget() {
        if (this.isKnockback()) {
            return;
        }

        Vector3 target = this.target;
        if (!(target instanceof EntityCreature) || !this.targetOption((EntityCreature) target, this.distanceSquared(target))) {
            double near = Integer.MAX_VALUE;

            for (Entity entity : this.getLevel().getEntities()) {
                Player player = (Player) entity;

                if (player == null || team.arena.getPlayerTeam(player).getId() != team.getId()) {
                    continue;
                }

                double distance = this.distanceSquared(player);
                if (distance > near || !this.targetOption(player, distance)) {
                    continue;
                }
                near = distance;

                this.stayTime = 0;
                this.moveTime = 0;
                this.target = player;
            }
        }

        if (this.target instanceof Player && !((Player) this.target).closed && ((Player) this.target).isAlive()) {
            return;
        }

        int x, z;
        if (this.stayTime > 0) {
            if (Utils.rand(1, 100) > 5) {
                return;
            }
            x = Utils.rand(10, 30);
            z = Utils.rand(10, 30);
            this.target = this.add(Utils.rand() ? x : -x, Utils.rand(-20, 20) / 10, Utils.rand() ? z : -z);
        } else if (Utils.rand(1, 410) == 1) {
            x = Utils.rand(10, 30);
            z = Utils.rand(10, 30);
            this.stayTime = Utils.rand(90, 400);
            this.target = this.add(Utils.rand() ? x : -x, Utils.rand(-20, 20) / 10, Utils.rand() ? z : -z);
        } else if (this.moveTime <= 0 || this.target == null) {
            x = Utils.rand(20, 100);
            z = Utils.rand(20, 100);
            this.stayTime = 0;
            this.moveTime = Utils.rand(300, 1200);
            this.target = this.add(Utils.rand() ? x : -x, 0, Utils.rand() ? z : -z);
        }
    }


    @Override
    public boolean onUpdate(int currentTick) {
        if (this.server.getDifficulty() < 1) {
            this.close();
            return false;
        }

        if (!this.isAlive()) {
            if (++this.deadTicks >= 23) {
                this.close();
                return false;
            }
            return true;
        }

        int tickDiff = currentTick - this.lastUpdate;
        this.lastUpdate = currentTick;
        this.bombTime += tickDiff;

        if (this.bombTime > 800) {
            this.explode();
            return false;
        }

        this.entityBaseTick(tickDiff);

        if (!this.isMovement()) {
            return true;
        }

        if (this.isKnockback()) {
            this.move(this.motionX * tickDiff, this.motionY, this.motionZ * tickDiff);
            this.motionY -= this.getGravity() * tickDiff;
            this.updateMovement();
            return true;
        }

        Vector3 before = this.target;
        this.checkTarget();

        if (this.target instanceof EntityCreature || before != this.target) {
            double x = this.target.x - this.x;
            double y = this.target.y - this.y;
            double z = this.target.z - this.z;

            double diff = Math.abs(x) + Math.abs(z);
            double distance = Math.sqrt(Math.pow(this.x - target.x, 2) + Math.pow(this.z - target.z, 2));
            if (distance <= 4.5) {
                if (target instanceof EntityCreature) {
                    if (this.bombTime >= 200) {
                        this.explode();
                        return false;
                    }
                } else if (Math.pow(this.x - target.x, 2) + Math.pow(this.z - target.z, 2) <= 1) {
                    this.moveTime = 0;
                }
            } else {
                this.motionX = this.getSpeed() * 0.15 * (x / diff);
                this.motionZ = this.getSpeed() * 0.15 * (z / diff);
            }
            this.yaw = Math.toDegrees(-Math.atan2(x / diff, z / diff));
            this.pitch = y == 0 ? 0 : Math.toDegrees(-Math.atan2(y, Math.sqrt(x * x + z * z)));
        }

        double dx = this.motionX * tickDiff;
        double dz = this.motionZ * tickDiff;
        boolean isJump = this.checkJump(dx, dz);
        if (this.stayTime > 0) {
            this.stayTime -= tickDiff;
            this.move(0, this.motionY * tickDiff, 0);
        } else {
            Vector2 be = new Vector2(this.x + dx, this.z + dz);
            this.move(dx, this.motionY * tickDiff, dz);
            Vector2 af = new Vector2(this.x, this.z);

            if ((be.x != af.x || be.y != af.y) && !isJump) {
                this.moveTime -= 90 * tickDiff;
            }
        }

        if (!isJump) {
            if (this.onGround) {
                this.motionY = 0;
            } else if (this.motionY > -this.getGravity() * 4) {
                if (!(this.level.getBlock(new Vector3(NukkitMath.floorDouble(this.x), (int) (this.y + 0.8), NukkitMath.floorDouble(this.z))) instanceof BlockLiquid)) {
                    this.motionY -= this.getGravity() * 1;
                }
            } else {
                this.motionY -= this.getGravity() * tickDiff;
            }
        }
        this.updateMovement();
        return true;
    }

    @Override
    public Vector3 updateMove(int tickDiff) {
        return null;
    }

    public void attackEntity(Entity player) {
    }

    public Item[] getDrops() {
        if (this.lastDamageCause instanceof EntityDamageByEntityEvent) {
            switch (Utils.rand(0, 2)) {
                case 0:
                    return new Item[]{Item.get(Item.FLINT, 0, 1)};
                case 1:
                    return new Item[]{Item.get(Item.GUNPOWDER, 0, 1)};
                case 2:
                    return new Item[]{Item.get(Item.REDSTONE_DUST, 0, 1)};
            }
        }
        return new Item[0];
    }
}
