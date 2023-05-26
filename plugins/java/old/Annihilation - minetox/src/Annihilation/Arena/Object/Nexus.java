package Annihilation.Arena.Object;

import Annihilation.Arena.Arena;
import cn.nukkit.block.Block;
import cn.nukkit.level.particle.CriticalParticle;
import cn.nukkit.level.particle.LargeExplodeParticle;
import cn.nukkit.level.particle.LavaDripParticle;
import cn.nukkit.level.Position;
import cn.nukkit.level.sound.AnvilFallSound;
import cn.nukkit.math.Vector3;
import cn.nukkit.network.protocol.ExplodePacket;
import cn.nukkit.Player;
import cn.nukkit.Server;
import cn.nukkit.utils.TextFormat;

public class Nexus{

    private int health;

    private Position position;

    private Team team;

    private Arena plugin;

    public Nexus(Team team, Position pos){
        this(team, pos, 75);
    }

    public Nexus(Team team, Position pos, int health){
        this.team = team;
        this.position = pos;
        this.health = health;
    }

    public int getHealth(){
        return health;
    }

    public void setHealth(int amount){
        health = amount;
    }

    public Team getTeam(){
        return team;
    }

    public Position getPosition(){
        return position;
    }

    public void damage(){
        this.damage(1);
    }

    public void damage(int damage){
        if(!isAlive()){
            return;
        }
        health -= damage;

        if(getHealth() <= 0){
            getPosition().getLevel().setBlock(getPosition(), Block.get(7), true);
            setHealth(0);
        }
    }

    public boolean isAlive(){
        return getHealth() > 0;
    }
}