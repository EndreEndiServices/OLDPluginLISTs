package Annihilation.Arena.Manager;

import Annihilation.Annihilation;
import Annihilation.Arena.Arena;
import Annihilation.Arena.Object.Team;
import cn.nukkit.Player;
import cn.nukkit.utils.TextFormat;
import org.apache.commons.lang.math.NumberUtils;

import java.util.*;

public class VotingManager{
    public Arena plugin;
    
    public HashMap<String, String> players = new HashMap<>();
    public String[] currentTable = new String[4];
    public ArrayList<String> allVotes = new ArrayList<>();  //(Collection<String>{"Canyon", "Amazon", "Hamlet"});
    public HashMap<String, Integer> stats;
    
    public VotingManager(Arena plugin) {
        this.plugin = plugin;
        allVotes.add("Canyon");
        allVotes.add("Amazon");
        allVotes.add("Hamlet");
    }
    
    public void createVoteTable(){
        ArrayList<String> all = (ArrayList<String>) allVotes.clone();
        int i = 0;

        while(i < 3){
            int key = new Random().nextInt(all.size());

            this.currentTable[i] = all.get(key);

            all.remove(key);

            i++;
        }

        this.stats = new HashMap<>();
        this.stats.put(currentTable[0], 0);
        this.stats.put(currentTable[1], 0);
        this.stats.put(currentTable[2], 0);
    }
    
    public void onVote(Player p, String vote){
        if(this.plugin.phase >= 1 || !this.plugin.inArena(p)){
            p.sendMessage(Annihilation.getPrefix()+TextFormat.RED+"You can not vote now");
            return;
        }
        if(NumberUtils.isNumber(vote)){
            if(!(Integer.valueOf(vote) >=1 || Integer.valueOf(vote) <= 3)){
                p.sendMessage(Annihilation.getPrefix()+TextFormat.GRAY+"use /vote [map]");
                return;
            }
            if(this.players.containsKey(p.getName().toLowerCase())){
                this.stats.put(this.players.get(p.getName().toLowerCase()), this.stats.get(this.players.get(p.getName().toLowerCase())) - 1);
            }
            this.stats.put(currentTable[Integer.valueOf(vote) - 1], this.stats.get(currentTable[Integer.valueOf(vote) - 1]) + 1);
            this.players.put(p.getName().toLowerCase(), currentTable[Integer.valueOf(vote) - 1]);
            p.sendMessage(Annihilation.getPrefix()+TextFormat.YELLOW+"voted for "+this.currentTable[Integer.valueOf(vote)-1]);
        } else{
            if(!vote.toLowerCase().equals(this.currentTable[0].toLowerCase()) && !vote.toLowerCase().equals(this.currentTable[1].toLowerCase()) && !vote.toLowerCase().equals(this.currentTable[2].toLowerCase())){
                p.sendMessage(Annihilation.getPrefix()+TextFormat.GRAY+"use /vote [map]");
                return;
            }

            if(this.players.containsKey(p.getName().toLowerCase())){
                this.stats.put(this.players.get(p.getName().toLowerCase()), this.stats.get(this.players.get(p.getName().toLowerCase())) - 1);
            }

            String finall = Character.toUpperCase(vote.charAt(0)) + vote.substring(1).toLowerCase();
            this.stats.put(finall, this.stats.get(finall) + 1);
            this.players.put(p.getName().toLowerCase(), finall);
            p.sendMessage(Annihilation.getPrefix()+TextFormat.GOLD+"voted for " + vote);
        }
    }
}


