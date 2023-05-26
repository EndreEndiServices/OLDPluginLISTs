package Annihilation.Arena.Manager;

import Annihilation.Annihilation;
import Annihilation.Arena.Arena;
import Annihilation.Arena.Kits.Kit;
import Annihilation.Arena.Object.PlayerData;
import Annihilation.MySQL.NormalQuery;
import cn.nukkit.entity.Entity;
import cn.nukkit.entity.projectile.EntityProjectile;
import cn.nukkit.event.player.PlayerDeathEvent;
import cn.nukkit.event.entity.EntityDamageEvent;
import cn.nukkit.event.entity.EntityDamageByEntityEvent;
import cn.nukkit.event.entity.EntityDamageByChildEntityEvent;
import cn.nukkit.Player;
import cn.nukkit.utils.TextFormat;

public class DeathManager {

    public Arena plugin;

    public DeathManager(Arena plugin) {
        this.plugin = plugin;
    }

    public void onDeath(PlayerDeathEvent e) {
        Player p = e.getEntity();
        EntityDamageEvent lastDmg = p.getLastDamageCause();
        String pColor = this.plugin.getPlayerTeam(p).getColor();
        String dColor = "";
        boolean escape = false;
        if (lastDmg != null) {
            if (lastDmg instanceof EntityDamageByChildEntityEvent) {
                Entity arrow = ((EntityDamageByChildEntityEvent) lastDmg).getChild();
                Entity killer = ((EntityDamageByChildEntityEvent) lastDmg).getDamager();
                if (arrow instanceof EntityProjectile) {
                    this.plugin.messageAllPlayers(pColor + p.getName() + TextFormat.GRAY + " was shot by " + this.plugin.getPlayerTeam((Player) killer).getColor() + killer.getName());
                    new NormalQuery(Annihilation.getInstance(), NormalQuery.KILL, new String[]{killer.getName().toLowerCase()});
                    new NormalQuery(Annihilation.getInstance(), "tokens", new String[]{killer.getName().toLowerCase()}, 10, "freezecraft");
                }
                return;
            } else if (lastDmg instanceof EntityDamageByEntityEvent) {
                Entity killer = ((EntityDamageByEntityEvent) lastDmg).getDamager();
                if (killer instanceof Player) {
                    dColor = this.plugin.getPlayerTeam((Player) killer).getColor();
                    this.plugin.messageAllPlayers(pColor + p.getName() + TextFormat.GRAY + " was slain by " + dColor + killer.getName());
                    new NormalQuery(Annihilation.getInstance(), NormalQuery.KILL, new String[]{killer.getName().toLowerCase()});
                    new NormalQuery(Annihilation.getInstance(), "tokens", new String[]{killer.getName().toLowerCase()}, 10, "freezecraft");
                }
                return;
            }

            String killer = null;

            PlayerData killerData = this.plugin.getPlayerData(p).wasKilled();

            if (killerData != null) {
                escape = true;
                dColor = killerData.getTeam().getColor();
                killer = killerData.getName();
            }
            /*if($lastDmg instanceof EntityDamageByBlockEvent){
                if($escape === true){
                    $this->plugin->messageAllPlayers($pColor."{$p->getName()}".TextFormat::GRAY." walked into a cactus while trying to escape ".$this->plugi->getTeamColor($this->plugin->getPlayerTeam($killer)).$killer->getName());
                    $this->plugin->mysql->addKill($killer->getName());
                    return;
                }
                $this->plugin->messageAllPlayers($pColor."{$p->getName()}".TextFormat::GRAY." was pricked to death");
                return;
            }*/
            if (escape) {
                new NormalQuery(Annihilation.getInstance(), NormalQuery.KILL, new String[]{killer.toLowerCase()});
                new NormalQuery(Annihilation.getInstance(), "tokens", new String[]{killer.toLowerCase()}, 10, "freezecraft");
                Player pl = this.plugin.plugin.getServer().getPlayer(killer);
                if (pl != null) {
                    pl.addExperience(15);
                    if (this.plugin.getPlayerData(pl).getKit() == Kit.BERSERKER && pl.getMaxHealth() < 30) {
                        pl.setMaxHealth(pl.getMaxHealth() + 1);
                    }
                }
            }

            switch (lastDmg.getCause()) {
                case 0:
                    if (escape) {
                        this.plugin.messageAllPlayers(pColor + p.getName() + TextFormat.GRAY + " walked into a cactus while trying to escape " + dColor + killer);
                        return;
                    }
                    this.plugin.messageAllPlayers(pColor + p.getName() + TextFormat.GRAY + " was pricked to death");
                    break;
                case 3:
                    this.plugin.messageAllPlayers(pColor + p.getName() + TextFormat.GRAY + " suffocated in a wall");
                    break;
                case 4:
                    if (escape) {
                        this.plugin.messageAllPlayers(pColor + p.getName() + TextFormat.GRAY + " was doomed to fall by " + dColor + killer);
                        break;
                    }
                    this.plugin.messageAllPlayers(pColor + p.getName() + TextFormat.GRAY + " fell from high place");
                    break;
                case 5:
                    if (escape) {
                        this.plugin.messageAllPlayers(pColor + p.getName() + TextFormat.GRAY + " walked into a fire whilst fighting " + dColor + killer);
                        break;
                    }
                    this.plugin.messageAllPlayers(pColor + p.getName() + TextFormat.GRAY + " went up in flames");
                    break;
                case 6:
                    if (escape) {
                        this.plugin.messageAllPlayers(pColor + p.getName() + TextFormat.GRAY + " was burnt to a crisp whilst fighting " + dColor + killer);
                        break;
                    }
                    this.plugin.messageAllPlayers(pColor + p.getName() + TextFormat.GRAY + " burned to death");
                    break;
                case 7:
                    if (escape) {
                        this.plugin.messageAllPlayers(pColor + p.getName() + TextFormat.GRAY + " tried to swim in lava while trying to escape " + dColor + killer);
                        break;
                    }
                    this.plugin.messageAllPlayers(pColor + p.getName() + TextFormat.GRAY + " tried to swim in lava");
                    break;
                case 8:
                    if (escape) {
                        this.plugin.messageAllPlayers(pColor + p.getName() + TextFormat.GRAY + " drowned whilst trying to escape " + dColor + killer);
                        break;
                    }
                    this.plugin.messageAllPlayers(pColor + p.getName() + TextFormat.GRAY + " drowned");
                    break;
                case 9:
                    this.plugin.messageAllPlayers(pColor + p.getName() + TextFormat.GRAY + " blew up");
                    break;
                case 10:
                    this.plugin.messageAllPlayers(pColor + p.getName() + TextFormat.GRAY + " blew up");
                    break;
                case 11:
                    if (escape) {
                        this.plugin.messageAllPlayers(pColor + p.getName() + TextFormat.GRAY + " was doomed to fall by " + dColor + killer);
                        break;
                    }
                    this.plugin.messageAllPlayers(pColor + p.getName() + TextFormat.GRAY + " fell out of the world");
                    break;
                case 12:
                    this.plugin.messageAllPlayers(pColor + p.getName() + TextFormat.GRAY + " died");
                    break;
                case 13:
                    this.plugin.messageAllPlayers(pColor + p.getName() + TextFormat.GRAY + " died");
                    break;
                case 14:
                    this.plugin.messageAllPlayers(pColor + p.getName() + TextFormat.GRAY + " died");
                    break;
            }
        }
    }
}