package Annihilation.Arena.Task;

import cn.nukkit.block.Block;
import cn.nukkit.scheduler.Task;

public class BlockRegenerateTask extends Task {

    private Block block;

    public BlockRegenerateTask(Block b) {
        this.block = b;
    }

    @Override
    public void onRun(int currentTick) {
        block.getLevel().setBlock(this.block, this.block);
    }
}
