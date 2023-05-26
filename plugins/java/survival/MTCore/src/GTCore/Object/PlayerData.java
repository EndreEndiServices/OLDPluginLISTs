package GTCore.Object;

import cn.nukkit.Player;
import cn.nukkit.Server;
import cn.nukkit.level.Location;
import cn.nukkit.math.Vector3;
import lombok.Getter;
import lombok.Setter;

public class PlayerData {

    @Getter
    private Player player;

    @Getter
    @Setter
    private int level;

    @Getter
    @Setter
    private String suffix;

    @Getter
    @Setter
    private String prefix;

    @Getter
    @Setter
    private String chatColor;

    @Getter
    @Setter
    private int playTime;

    @Getter
    @Setter
    private boolean loggedIn = true;

    @Getter
    @Setter
    private long nextChat;

    @Setter
    private boolean inLobby = true;

    @Getter
    @Setter
    private boolean vanish = false;

    @Getter
    @Setter
    private String displayName;

    /**
     * Quiz data
     */

    @Getter
    @Setter
    private String lastAnswer;

    /**
     * Anticheat data
     */

    @Getter
    @Setter
    private Location lastPos;

    @Getter
    @Setter
    private long lastOnGround = 0;

    @Getter
    @Setter
    private Location lastGroundPos;

    @Getter
    @Setter
    private long lastJump = 0;

    @Getter
    @Setter
    private Location lastJumpPos;

    @Getter
    @Setter
    private long lastSpeedCheck = 0;

    @Getter
    @Setter
    private long lastCheck = 0;

    @Getter
    @Setter
    private long lastSpeedChange = 0;

    @Getter
    @Setter
    private boolean onGround;

    @Setter
    @Getter
    private double motionY = 0;

    @Getter
    @Setter
    private boolean teleport = false;

    @Setter
    @Getter
    private long lastHit = 0;

    @Setter
    @Getter
    private Vector3 lastLiquid = new Vector3();

    @Setter
    @Getter
    private Vector3 lastSlab = new Vector3();

    @Setter
    @Getter
    private long lastSlabTime = Long.MAX_VALUE;

    @Setter
    private int money = -1;

    public int moneyDelta = 0;

    public PlayerData(Player p) {
        this.player = p;
        this.setPlayTime((int) (System.currentTimeMillis() / 1000));
        setLastPos(p);
        setLastGroundPos(p);
        setLastJumpPos(p);
    }

    public boolean isInLobby() {
        return !player.closed && player.isOnline() && player.getLevel().getId() == Server.getInstance().getDefaultLevel().getId();
    }

    /*public void setLastGroundPos(Location loc){
        this.lastGroundPos = loc;

        for (StackTraceElement ste : Thread.currentThread().getStackTrace()) {
            System.out.println(ste);
        }
    }*/

    public int getMoney() {
        return money + moneyDelta;
    }

    public void addMoney(int money) {
        moneyDelta += money;
    }
}
