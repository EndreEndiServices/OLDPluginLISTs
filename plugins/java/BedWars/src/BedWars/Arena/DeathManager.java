
package BedWars.Arena;

import BedWars.BedWars;
import BedWars.MySQL.StatQuery;
import BedWars.MySQL.Stat;
import BedWars.Object.BedWarsData;
import cn.nukkit.entity.Entity;
import cn.nukkit.entity.projectile.EntityProjectile;
import cn.nukkit.event.entity.EntityDamageByBlockEvent;
import cn.nukkit.event.player.PlayerDeathEvent;
import cn.nukkit.event.entity.EntityDamageEvent;
import cn.nukkit.event.entity.EntityDamageByEntityEvent;
import cn.nukkit.event.entity.EntityDamageByChildEntityEvent;
import cn.nukkit.Player;
import cn.nukkit.utils.TextFormat;

import java.util.ArrayList;
import java.util.Arrays;

public class DeathManager {

    public Arena plugin;

    public DeathManager(Arena plugin) {
        this.plugin = plugin;
    }

    public void onDeath(PlayerDeathEvent e) {
        Player p = e.getEntity();
        BedWarsData data = plugin.getPlayerData(p);

        EntityDamageEvent lastDmg = p.getLastDamageCause();
        String pColor = data.getTeam().getColor();
        String dColor = "";
        boolean escape = false;

        if (lastDmg != null) {
            if (lastDmg instanceof EntityDamageByChildEntityEvent) {
                Entity arrow = ((EntityDamageByChildEntityEvent) lastDmg).getChild();
                Entity killer = ((EntityDamageByChildEntityEvent) lastDmg).getDamager();
                if (arrow instanceof EntityProjectile) {
                    this.plugin.messageAllPlayers(pColor + p.getName() + TextFormat.GRAY + " byl zastrelen hracem " + this.plugin.getPlayerTeam((Player) killer).getColor() + killer.getName());
                    new StatQuery(BedWars.getInstance(), Stat.KILLS, killer.getName());
                    //new StatQuery(Annihilation.getInstance(), "tokens", new String[]{killer.getName().toLowerCase()}, 10, "freezecraft");
                }
                return;
            } else if (lastDmg instanceof EntityDamageByEntityEvent) {
                Entity killer = ((EntityDamageByEntityEvent) lastDmg).getDamager();
                if (killer instanceof Player) {
                    dColor = this.plugin.getPlayerTeam((Player) killer).getColor();
                    this.plugin.messageAllPlayers(pColor + p.getName() + TextFormat.GRAY + " byl zabit hracem " + dColor + killer.getName());
                    new StatQuery(BedWars.getInstance(), Stat.KILLS, killer.getName());
                    //new StatQuery(Annihilation.getInstance(), "tokens", new String[]{killer.getName().toLowerCase()}, 10, "freezecraft");
                }
                return;
            }

            String killer = null;

            if (data.wasKilled()) {
                escape = true;
                dColor = data.getKillerColor();
                killer = data.getKiller();
                new StatQuery(BedWars.getInstance(), Stat.KILLS, killer);
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
            if (lastDmg instanceof EntityDamageByBlockEvent) {
                if (escape) {
                    plugin.messageAllPlayers(pColor + p.getName() + TextFormat.GRAY + " vrazil do kaktusu pri pokusu o utek hraci " + dColor + killer);
                    new StatQuery(BedWars.getInstance(), Stat.KILLS, killer);
                }

                plugin.messageAllPlayers(pColor + p.getName() + TextFormat.GRAY + " ");
            }

            switch (lastDmg.getCause()) {
                case 0:
                    if (escape) {
                        this.plugin.messageAllPlayers(pColor + p.getName() + TextFormat.GRAY + " vrazil do kaktusu pri pokusu o utek hraci " + dColor + killer);
                        return;
                    }
                    this.plugin.messageAllPlayers(pColor + p.getName() + TextFormat.GRAY + " se upichal k smrti");
                    break;
                case 3:
                    this.plugin.messageAllPlayers(pColor + p.getName() + TextFormat.GRAY + " se zazdil");
                    break;
                case 4:
                    if (escape) {
                        this.plugin.messageAllPlayers(pColor + p.getName() + TextFormat.GRAY + " byl donucen ke skoku hracem " + dColor + killer);
                        break;
                    }
                    this.plugin.messageAllPlayers(pColor + p.getName() + TextFormat.GRAY + " skocil prilis s vysoka");
                    break;
                case 5:
                    if (escape) {
                        this.plugin.messageAllPlayers(pColor + p.getName() + TextFormat.GRAY + " vkrocil do ohne pri divokem souboji s hracem " + dColor + killer);
                        break;
                    }
                    this.plugin.messageAllPlayers(pColor + p.getName() + TextFormat.GRAY + " shorel");
                    break;
                case 6:
                    if (escape) {
                        this.plugin.messageAllPlayers(pColor + p.getName() + TextFormat.GRAY + " byl spalen na skvarek pri divokem souboji s hracem " + dColor + killer);
                        break;
                    }
                    this.plugin.messageAllPlayers(pColor + p.getName() + TextFormat.GRAY + " uhorel k smrti");
                    break;
                case 7:
                    if (escape) {
                        this.plugin.messageAllPlayers(pColor + p.getName() + TextFormat.GRAY + " se pokusil plavat v lave pri divokem souboji s hracem " + dColor + killer);
                        break;
                    }
                    this.plugin.messageAllPlayers(pColor + p.getName() + TextFormat.GRAY + " se pokusil plavat v lave");
                    break;
                case 8:
                    if (escape) {
                        this.plugin.messageAllPlayers(pColor + p.getName() + TextFormat.GRAY + " se nadechnul pod vodou pri divokem souboji s hracem " + dColor + killer);
                        break;
                    }
                    this.plugin.messageAllPlayers(pColor + p.getName() + TextFormat.GRAY + " se utopil");
                    break;
                case 9:
                    this.plugin.messageAllPlayers(pColor + p.getName() + TextFormat.GRAY + " byl odpalen");
                    break;
                case 10:
                    this.plugin.messageAllPlayers(pColor + p.getName() + TextFormat.GRAY + " byl odpalen");
                    break;
                case 11:
                    if (escape) {
                        this.plugin.messageAllPlayers(pColor + p.getName() + TextFormat.GRAY + " byl donucen ke skoku hracem " + dColor + killer);
                        break;
                    }
                    this.plugin.messageAllPlayers(pColor + p.getName() + TextFormat.GRAY + " vypadl z mapy");
                    break;
                case 12:
                    this.plugin.messageAllPlayers(pColor + p.getName() + TextFormat.GRAY + " zemrel");
                    break;
                case 13:
                    this.plugin.messageAllPlayers(pColor + p.getName() + TextFormat.GRAY + " zemrel");
                    break;
                case 14:
                    this.plugin.messageAllPlayers(pColor + p.getName() + TextFormat.GRAY + " zemrel");
                    break;
            }
        }
    }
}