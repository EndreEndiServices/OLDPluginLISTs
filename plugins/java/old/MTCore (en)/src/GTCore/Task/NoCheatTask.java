package GTCore.Task;

import GTCore.MTCore;
import GTCore.Object.PlayerData;
import cn.nukkit.Player;
import cn.nukkit.block.Block;
import cn.nukkit.block.BlockLiquid;
import cn.nukkit.item.Item;
import cn.nukkit.math.Vector3;
import cn.nukkit.potion.Effect;
import cn.nukkit.scheduler.Task;

import java.util.ArrayList;
import java.util.Map;

public class NoCheatTask extends Task {

    private static final double WALKING_SPEED = 4.33; //4.317
    private static final double SPRINTING_SPEED = 5.64; //5.612
    private static final double SNEAKING_SPEED = 1.33; //1.31

    private MTCore plugin;

    public NoCheatTask(MTCore plugin) {
        this.plugin = plugin;
    }

    @Override
    public void onRun(int currentTick) {
        long time = System.currentTimeMillis();

        for (PlayerData data : new ArrayList<>(plugin.players.values())) {
            if (data.isInLobby()) {
                continue;
            }

            Player p = data.getPlayer();

            if (!p.isSurvival() || !p.isAlive() || p.ticksLived < 40) {
                continue;
            }

            if (data.getLastPos().getLevel().getId() != p.getLevel().getId() || data.isTeleport()) {
                data.setLastGroundPos(p.getLocation().clone());
                data.setLastOnGround(time);
                data.setLastJumpPos(p.getLocation().clone());
                data.setLastJump(time);
                data.setTeleport(false);
            } else {
                double maxDistance = getMaxDistance(p, data, time - data.getLastSpeedCheck());

                if (time - data.getLastHit() <= 1700) {
                    continue;
                }

                Vector3 prev = data.getLastPos();

                if (!p.onGround && prev.y == p.y) {
                    int inAirTime = (int) (time - data.getLastOnGround());
                    if (inAirTime > 1000) {
                        double expectedVelocity = (-0.08) / 0.02 - (-0.08) / 0.02 * Math.exp(-0.02 * inAirTime);
                        p.setMotion(p.temporalVector.setComponents(0, expectedVelocity, 0));
                    }
                }

                double actualDistance = Math.sqrt(Math.abs((prev.x - p.x) * (prev.z - p.z)));
                double diff = maxDistance - actualDistance;

                if (diff < 0) {
                        /*if (diff < -2) {
                            p.kick(TextFormat.YELLOW + "You have been kicked by " + TextFormat.RED + "AntiCheat" + TextFormat.YELLOW + ", reason: " + TextFormat.RED + "speed hack", false);

                            String msg = TextFormat.RED + p.getName() + TextFormat.YELLOW + " kicked by " + TextFormat.RED + "AntiCheat v1.0" + TextFormat.YELLOW + ", reason: " + TextFormat.RED + "speed hack";

                            for (Player pl : new ArrayList<>(plugin.getServer().getOnlinePlayers().values())) {
                                pl.sendMessage(msg);
                            }
                            return;
                        }*/
                    p.teleportImmediate(data.getLastPos());
                }
            }

            data.setLastPos(p.getLocation().clone());
            data.setLastSpeedCheck(time);
        }
    }

    public static boolean isInAir(Player p, PlayerData data) {
        Block block = p.level.getBlock(p.temporalVector.setComponents(Math.floor(p.x), Math.floor(p.y), Math.floor(p.z)));

        if (block instanceof BlockLiquid) {
            data.setLastLiquid(p.clone());
            return false;
        }

        return !(block.getId() == Item.LADDER || block.getId() == Item.VINE);
    }

    private double getMaxDistance(Player p, PlayerData data, long diff) {
        // Speed potions?
        Map<Integer, Effect> effects = p.getEffects();
        int amplifier = 0;
        // Check for speed potions.
        if (!effects.isEmpty()) {
            for (Effect effect : effects.values()) {
                if (effect.getId() == Effect.SPEED) {
                    int a = effect.getAmplifier();
                    // In-case there is more than one speed effect on a player, get the max.
                    if (a > amplifier) {
                        amplifier = a;
                    }
                }
            }
        }

        double speed = ((data.getLastSpeedChange() + diff) > System.currentTimeMillis()) ? SPRINTING_SPEED : (p.isSprinting() ? SPRINTING_SPEED : (p.isSneaking() ? SNEAKING_SPEED : WALKING_SPEED));

        if (System.currentTimeMillis() - data.getLastJump() < 2500) {
            speed += 1;
        }

        double distance = speed + ((amplifier != 0) ? (speed / (0.2 * amplifier)) : 0);

        return distance * (((double) diff) / 1000D);
    }

    /*public static Block[] getBlocksUnder(Player p) {
        AxisAlignedBB bb = p.getBoundingBox().clone();
        bb.maxY = bb.minY + 0.5;
        bb.minY -= 1;

        int minX = NukkitMath.floorDouble(bb.minX);
        int minY = NukkitMath.floorDouble(bb.minY);
        int minZ = NukkitMath.floorDouble(bb.minZ);
        int maxX = NukkitMath.ceilDouble(bb.maxX);
        int maxY = NukkitMath.ceilDouble(bb.maxY);
        int maxZ = NukkitMath.ceilDouble(bb.maxZ);

        for (int z = minZ; z <= maxZ; ++z) {
            for (int x = minX; x <= maxX; ++x) {
                for (int y = minY; y <= maxY; ++y) {
                    Block block = p.getLevel().getBlock(p.temporalVector.setComponents(x, y, z));
                    if (block.getId() != 0 && block.collidesWithBB(bb) && (!block.canPassThrough() || block.getId() == Item.LADDER || block.getId() == Item.VINE || block instanceof BlockLiquid)) {
                        return new Block[]{block};
                    }
                }
            }
        }

        return new Block[]{};
    }*/
}
