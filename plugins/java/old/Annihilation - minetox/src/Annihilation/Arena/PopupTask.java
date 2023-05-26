package Annihilation.Arena;

import Annihilation.Arena.Manager.VotingManager;
import Annihilation.Arena.Object.Team;
import cn.nukkit.Player;
import cn.nukkit.level.sound.FizzSound;
import cn.nukkit.scheduler.Task;
import cn.nukkit.utils.TextFormat;
import cn.nukkit.math.Vector3;

public class PopupTask extends Task{
    
    public Arena plugin;
    public ArenaSchedule task;
    public int ending = 0;
    
    public PopupTask(Arena plugin){
        this.plugin = plugin;
    }
    
    public void onRun(int currentTick){
        if(this.plugin.phase >= 1 && !this.plugin.ending){
            this.sendTeamsStats();
            return;
        }

        if(this.plugin.ending){
            if(this.ending == 30){
                this.plugin.ending = false;
                this.plugin.stopGame();
                this.ending = 0;

                return;
            }
            this.ending++;
            this.sendEnding();
            return;
        }

        if(this.plugin.phase == 0){
            this.sendVotes();
        }
    }
    
    public void sendVotes(){
        VotingManager vm = this.plugin.votingManager;
        //$this->plugin->plugin->getServer()->getLogger()->info("{$vm->stats[1]} {$vm->stats[2]} {$vm->stats[3]}");
        String[] votes = new String[]{vm.currentTable[0], vm.currentTable[1], vm.currentTable[2]};

        String tip = "                                                   §8Voting §f| §6/vote <name>"
                + "\n                                                 §b[1] §8"+votes[0]+" §c» §a"+vm.stats.get(votes[0])+" Votes"
                + "\n                                                 §b[2] §8"+votes[1]+" §c» §a"+vm.stats.get(votes[1])+" Votes"
                + "\n                                                 §b[3] §8"+votes[2]+" §c» §a"+vm.stats.get(votes[2])+" Votes";

        for(Player p : this.plugin.getAllPlayers().values()){
            p.sendTip(tip);
        }                        //    |
    }
    
    public void sendEnding(){
        Team team = this.plugin.winnerteam;

        String name = team.getColor()+team.getName();
        for(Player p : this.plugin.getAllPlayers().values()){
            p.sendTip(TextFormat.GRAY+"                    ================[ "+TextFormat.AQUA+"Progress"+TextFormat.GRAY+" ]================\n"
                                                  + "                               "+TextFormat.BOLD+name+TextFormat.LIGHT_PURPLE+" team won the game\n"
                        +TextFormat.GRAY         + "======================================================");
            this.plugin.level.addSound(new FizzSound(p));
        }
    }
    
    public void sendTeamsStats(){
        int[] nex = new int[]{this.plugin.getTeam(1).getNexus().getHealth(), this.plugin.getTeam(2).getNexus().getHealth(), this.plugin.getTeam(3).getNexus().getHealth(), this.plugin.getTeam(4).getNexus().getHealth()};
        String map = this.plugin.map;
        String phase = this.getDisplayPhase(this.plugin.phase)+TextFormat.GRAY+" | "+TextFormat.WHITE+this.plugin.task.time / 3600 % 60 +":"+this.plugin.task.time / 60 % 60 +":"+this.plugin.task.time % 60;
        String tip = "                                                    §8Map: §6"+ map +"\n"
                      + "                                                §eYellow Nexus  §c"+nex[2]+"\n"
                      + "                                                §cRed Nexus     §c"+nex[1]+"\n"
                      + "                                                §9Blue Nexus    §c"+nex[0]+"\n"
                      + "                                                §aGreen Nexus  §c"+nex[3]+"\n"
                      + "\n\n\n                      " + phase;

        for(Player p : this.plugin.getAllPlayers().values()){
            p.sendTip(tip);
            //$p->sendPopup($popup);
        }                                                        //   |
    }
    
    public int getPhaseTime(){
        int time = this.plugin.task.time;
        switch(this.plugin.phase){
            case 1:
                return time / 30;
            case 2:
                return (time - 600) / 30;
            case 3:
                return (time - 1200) / 30;
            case 4:
                return (time - 1800) / 30;
            case 5:
                return 20;
        }
        return 0;
    }
    
    public String getDisplayPhase(int phase){
        switch(phase){
            case 1:
                return TextFormat.GOLD+"Phase: "+TextFormat.DARK_GREEN+"I";
            case 2:
                return TextFormat.GOLD+"Phase: "+TextFormat.DARK_GREEN+"II";
            case 3:
                return TextFormat.RED+"Phase: "+TextFormat.DARK_PURPLE+"III";
            case 4:
                return TextFormat.RED+"Phase: "+TextFormat.DARK_PURPLE+"IV";
            case 5:
                return TextFormat.RED+"Phase: "+TextFormat.DARK_PURPLE+"V";
        }

        return "";
    }
}