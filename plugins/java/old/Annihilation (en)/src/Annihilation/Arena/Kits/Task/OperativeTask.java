package Annihilation.Arena.Kits.Task;

import cn.nukkit.scheduler.Task;
import cn.nukkit.Player;
import Annihilation.Arena.Kits.Operative;
import cn.nukkit.block.Block;

public class OperativeTask extends Task {

    private Player p;
    private Block b;

    public OperativeTask(Player p, Block b) {
        this.p = p;
        this.b = b;
    }

    @Override
    public void onRun(int currentTick) {
        Operative.execute(this.p, this.b);
    }
}