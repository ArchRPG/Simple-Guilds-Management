<?php

namespace GuildsRPG;

/*
This Is A Plugin For Pocketmine-mp and others software that support this plugin.
*/

use pocketmine\plugin\PluginBase;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\event\Listener;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\Player;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\utils\TextFormat;
use pocketmine\scheduler\PluginTask;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\utils\Config;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\player\PlayerDeathEvent;

class GuildsEvents implements Listener {
	
	public $plugin;
	
	public function __construct(GCore $pg) {
		$this->plugin = $pg;
	}
	
	public function GuildChat(PlayerChatEvent $PCE) {
		$player = $PCE->getPlayer()->getName();
		if($this->plugin->motdWaiting($player)) {
			if(time() - $this->plugin->getMOTDTime($player) > 30) {
				$PCE->getPlayer()->sendMessage($this->plugin->formatMessage("Timed out. Please use /guilds desc again."));
				$this->plugin->db->query("DELETE FROM motdrcv WHERE player='$player';");
				$PCE->setCancelled(true);
				return true;
			} else {
				$motd = $PCE->getMessage();
				$Guild = $this->plugin->getPlayerGuild($player);
				$this->plugin->setMOTD($Guild, $player, $motd);
				$PCE->setCancelled(true);
				$PCE->getPlayer()->sendMessage($this->plugin->formatMessage("Successfully updated the guilds description. Type /guilds info.", true));
			}
			return true;
		}
	}
	
	public function GuildPVP(EntityDamageEvent $GuildDamage) {
		if($GuildDamage instanceof EntityDamageByEntityEvent) {
			if(!($GuildDamage->getEntity() instanceof Player) or !($GuildDamage->getDamager() instanceof Player)) {
				return true;
			}
			if(($this->plugin->isInGuild($GuildDamage->getEntity()->getPlayer()->getName()) == false) or ($this->plugin->isInGuild($GuildDamage->getDamager()->getPlayer()->getName()) == false)) {
				return true;
			}
			if(($GuildDamage->getEntity() instanceof Player) and ($GuildDamage->getDamager() instanceof Player)) {
				$player1 = $GuildDamage->getEntity()->getPlayer()->getName();
				$player2 = $GuildDamage->getDamager()->getPlayer()->getName();
                $f1 = $this->plugin->getPlayerGuild($player1);
                $f2 = $this->plugin->getPlayerGuild($player2);
				if($this->plugin->sameGuild($player1, $player2) == true or $this->plugin->areAlliance($f1,$f2)) {
					$GuildDamage->setCancelled(true);
				}
			}
		}
	}
	public function GuildBlockBreakProtect(BlockBreakEvent $event) {
		if($this->plugin->isInPlot($event->getPlayer())) {
			if($this->plugin->inOwnPlot($event->getPlayer())) {
				return true;
			} else {
				$event->setCancelled(true);
				$event->getPlayer()->sendMessage($this->plugin->formatMessage("You cannot break blocks here. This is already a property of a Guild. Type /guilds plotinfo for details."));
				return true;
			}
		}
	}
	
	public function GuildBlockPlaceProtect(BlockPlaceEvent $event) {
		if($this->plugin->isInPlot($event->getPlayer())) {
			if($this->plugin->inOwnPlot($event->getPlayer())) {
				return true;
			} else {
				$event->setCancelled(true);
				$event->getPlayer()->sendMessage($this->plugin->formatMessage("You cannot place blocks here. This is already a property of a Guild. Type /guilds plotinfo for details."));
				return true;
			}
		}
	}
	public function onKill(PlayerDeathEvent $event){
        $ent = $event->getEntity();
        $cause = $event->getEntity()->getLastDamageCause();
        if($cause instanceof EntityDamageByEntityEvent){
            $killer = $cause->getDamager();
            if($killer instanceof Player){
                $p = $killer->getPlayer()->getName();
                if($this->plugin->isInGuild($p)){
                    $f = $this->plugin->getPlayerGuild($p);
                    $e = $this->plugin->prefs->get("GPGainedPerKillingAnEnemy");
                    $d = $this->plugin->prefs->get("GuildsMoneyGainPerKill");
                    $killer->sendPopup("§6+ ".$e." GuildsPoints/n§6+ ".$d." GuildsMoney");
                    if($ent instanceof Player){
                        if($this->plugin->isInGuild($ent->getPlayer()->getName())){
                           $this->plugin->addGuildsPoints($f,$e);
                           $this->plugin->addGuildMoney($f,$d);
                        } else {
                           $this->plugin->addGuildsPoints($f,$e/2);
                           $this->plugin->addGuildMoney($f,$d/2);
                        }
                    }
                }
            }
        }
        if($ent instanceof Player){
            $e = $ent->getPlayer()->getName();
            if($this->plugin->isInGuild($e)){
                $f = $this->plugin->getPlayerGuild($e);
                $e = $this->plugin->prefs->get("GPReducedPerDeathByAnEnemy");
                $m = $this->plugin->prefs->get("GuildsMoneyLostPerDeath");
                $ent->sendPopup("§c- $e GuildsPoint/n§c- $m GuildsMoney");
                if($ent->getLastDamageCause() instanceof EntityDamageByEntityEvent && $ent->getLastDamageCause()->getDamager() instanceof Player){
                    if($this->plugin->isInGuild($ent->getLastDamageCause()->getDamager()->getPlayer()->getName())){      
                        $this->plugin->subtractGuildsPoints($f,$e*2);
                        $this->plugin->subtractGuildMoney($f,$m*2);
                    } else {
                        $this->plugin->subtractGuildsPoints($f,$e);
                        $this->plugin->subtractGuildMoney($f,$m);
                    }
                }
            }
        }
    }
    /*
	public function onPlayerJoin(PlayerJoinEvent $event) {
		$this->plugin->updateTag($event->getPlayer()->getName());
	}
    */
}
