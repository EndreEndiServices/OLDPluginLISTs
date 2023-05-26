package Annihilation.Arena.Manager;

import Annihilation.Annihilation;
import Annihilation.Arena.Arena;
import Annihilation.Arena.Object.PlayerData;
import Annihilation.Arena.Object.Team;
import Annihilation.Arena.Utils.Color;
import Annihilation.MySQL.JoinTeamQuery;
import cn.nukkit.level.Position;
import cn.nukkit.Player;
import cn.nukkit.utils.TextFormat;

import java.util.ArrayList;
import java.util.HashMap;

public abstract class ArenaManager{

    protected Arena plugin;

    public Team[] teams;

    public ArenaManager(){

    }

    private Arena getPlugin(){
        return plugin;
    }

    public void getWinningTeam(){

    }

    public void registerTeams(){
        this.teams = new Team[5];

        this.teams[1] = new Team(1, "blue", TextFormat.BLUE, plugin, Color.toDecimal(Color.BLUE));
        this.teams[2] = new Team(2, "red", TextFormat.RED, plugin, Color.toDecimal(Color.RED));
        this.teams[3] = new Team(3, "yellow", TextFormat.YELLOW, plugin, Color.toDecimal(Color.YELLOW));
        this.teams[4] = new Team(4, "green", TextFormat.GREEN, plugin, Color.toDecimal(Color.GREEN));
    }

    public Team getPlayerTeam(Player p){
        return getPlayerData(p).getTeam();
    }

    public void addToTeam(Player player, Team team){
        getPlayerData(player).setTeam(team);
        team.addPlayer(player);

        new JoinTeamQuery(Annihilation.getInstance(), player.getName().toLowerCase(), team.getColor());
        //player.setNameTag(team.getColor() + player.getName() + TextFormat.WHITE);
        //player.setDisplayName(plugin.mtcore.getDisplayRank(player)+" "+team.getColor() + player.getName()+TextFormat.WHITE);
    }

    public HashMap<String, Player> getTeamPlayers(Team team){
        return team.getPlayers();
    }

    public Team getTeam(int id){
        return teams[id];
    }

    public HashMap<String, Player> getAllPlayers(){
        return plugin.players;
    }

    public HashMap<String, Player> getPlayersInTeam(){
        HashMap<String, Player> players = new HashMap<>();
        players.putAll(getTeam(1).getPlayers());
        players.putAll(getTeam(2).getPlayers());
        players.putAll(getTeam(3).getPlayers());
        players.putAll(getTeam(4).getPlayers());

        return players;
    }

    public void messageAllPlayers(String message){
        this.messageAllPlayers(message, null);
    }

    public void messageAllPlayers(String message, Player player){
        if(player != null){
            String msg;

            if (getPlayerTeam(player).getId() == 0) {
                msg = player.getDisplayName() + TextFormat.DARK_AQUA + " > " + message;
            }else{
                String color = getPlayerTeam(player).getColor();
                msg = TextFormat.GRAY + "[" + color + "All" + TextFormat.GRAY + "]   " + player.getDisplayName() + TextFormat.DARK_AQUA + " > " + message.substring(1);
            }

            for (Player p : getPlayersInTeam().values()) {
                p.sendMessage(msg);
            }
            return;
        }

        for (Player p : getPlayersInTeam().values()) {
            p.sendMessage(message);
        }
    }

    public PlayerData getPlayerData(Player p){
        return plugin.playersData.get(p.getName().toLowerCase());
    }

    public PlayerData createPlayerData(Player p){
        PlayerData data = new PlayerData(p.getName());
        plugin.playersData.put(p.getName().toLowerCase(), data);

        return data;
    }

    public boolean inArena(Player p){
        return plugin.players.containsKey(p.getName().toLowerCase());
    }

    public boolean isTeamFree(Team team){
        int players = team.getPlayers().size();

        ArrayList<Integer> teams = new ArrayList<>();

        int i = 0;

        for(int t = 1; t < 5; t++){
            Team teamm = this.getTeam(t);

            if(this.plugin.phase >= 2 && teamm.getNexus().getHealth() <= 0){
                continue;
            }

            if(teamm.getId() != team.getId()){
                teams.add(teamm.getPlayers().size());

                i++;
            }
        }

        switch(teams.size()){
            case 1:
                return (players - teams.get(0)) < 3;
            case 2:
                return (players - Math.min(teams.get(0), teams.get(1))) < 3;
            case 3:
                return (players - Math.min(teams.get(2), Math.min(teams.get(0), teams.get(1)))) < 3;
        }

        return true;
    }
}