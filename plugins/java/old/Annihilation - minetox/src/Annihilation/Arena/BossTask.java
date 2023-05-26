package Annihilation.Arena;

import Annihilation.Arena.Manager.BossManager;
import cn.nukkit.scheduler.Task;

public class BossTask extends Task{
    
    public BossManager plugin;
    public int boss;
    
    public BossTask(BossManager plugin, int boss){
        this.plugin = plugin;
        this.boss = boss;
    }

    @Override
    public void onRun(int currentTick){
        plugin.spawnBoss(this.boss);
        if(plugin.task1 == this){
            this.plugin.task1 = null;
            return;
        }
        if(this.plugin.task2 == this){
            this.plugin.task2 = null;
        }
    }
}