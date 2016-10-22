<?php

namespace GuildsRPG;

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
use pocketmine\math\Vector3;
use pocketmine\level\level;
use pocketmine\level\Position;
use pocketmine\entity\Effect;
use onebone\economyapi\EconomyAPI;

class GuildsCommands {
    
    public $plugin;

    public function __construct(GCore $pg) {
        $this->plugin = $pg;
    }

    CONST HELP_MESSAGE_ONE = "§dGuilds Help Page §f[§c1§f/§f§c6§f]" . TextFormat::RED . "\n§l§c»§r §e/guilds about §l§b»§r §aShows Any Information You Need To Know!\n§l§c»§r §e/guilds accept §l§b»§r §aAccept A Guilds Request!\n§l§c»§r §e/guilds create <name> §l§b»§r §aCreate Your Desire Guilds!\n§l§c»§r §e/guilds del §l§b»§r §aDelete Your Own Guilds!\n§l§c»§r §e/guilds demote <player> §l§b»§r §aDemote Your Any SecondInCommands To Members!\n§l§c»§r §e/guilds deny §l§b»§r §aDeny A Guilds Request!";
    CONST HELP_MESSAGE_TWO = "§dGuilds Help Page §f[§c2§f/§f§c6§f]" . TextFormat::RED . "\n§l§c»§r §e/guilds help <page> §l§b»§r §aShows A List Of Guilds Help Page!\n§l§c»§r §e/guilds info §l§b»§r §aShows Your Guilds Information!\n§l§c»§r §e/guilds info <guild> §l§b»§r §aShows Targets Guilds Information!\n§l§c»§r §e/guilds invite <player> §l§b»§r §aInvite A Player As A guildsmasters!\n§l§c»§r §e/guilds kick <player> §l§b»§r §aKick/Remove Specific Player From Guilds!\n§l§c»§r §e/guilds guildsmasters <player> §l§b»§r §aMake A Player To Be The New guildsmasters!\n§l§c»§r §e/guilds leave §l§b»§r §aLeave Your Current Guilds!";
    CONST HELP_MESSAGE_THREE = "§dGuilds Help Page §f[§c3§f/§f§c6§f]" . TextFormat::RED . "\n§l§c»§r §e/guilds m - {Members + Statuses} §l§b»§r §aShows Your Guilds MembersList!\n§l§c»§r §e/guilds sic - {SecondInCommands + Statuses} §l§b»§r §aShows Your SecondInCommandsList!\n§l§c»§r §e/guilds ourgm - {guildsmasters + Status} §l§b»§r §aShows Your guildmasterList!\n§l§c»§r §e/guilds alliance §l§b»§r §aShows The Guild YOU ALLIED!\n§l§c»§r §e/guilds claim\n§l§c»§r §e/guilds unclaim\n§l§c»§r §e/guilds pos\n§l§c»§r §e/guilds overclaim\n/guilds say <message>";
    CONST HELP_MESSAGE_FOUR = "§dGuilds Help Page §f[§c4§f/§f§c6§f]" . TextFormat::RED . "\n§l§c»§r §e/guilds desc §l§b»§r §aUpdate The Guilds Description!\n§l§c»§r §e/guilds promote <player> §l§b»§r §aPromote A Members To SecondInCommands!\n§l§c»§r §e/guilds Alliancewith <guilds> §l§b»§r §aRequest An Alliance With A Guilds!\n§l§c»§r §e/guilds breakalliancewith <guilds> §l§b»§r §aBreak The Alliance Contract With A Guilds!\n§l§c»§r §e/guilds Allianceok §l§b»§r §aAccept An Alliance Request!\n§l§c»§r §e/guilds Allianceno §l§b»§r §aDenied An Alliance Request!\n§l§c»§r §e/guilds alliance <guilds> §l§b»§r §aShows A Specific Guilds Alliance!";
    CONST HELP_MESSAGE_FIVE = "§dGuilds Help Page §f[§c5§f/§f§c5§f]" . TextFormat::RED . "\n§l§c»§r §e/guilds mof <guilds> §l§b»§r §aShows The List Of A Specific Guilds Members!\n§l§c»§r §e/guilds sicof <guilds> §l§b»§r §aShows The List Of A Specific Guilds SecondInCommands!\n§l§c»§r §e/guilds gmof <guilds> §l§b»§r §aShows The Guilds guildmaster List!\n§l§c»§r §e/guilds search <player> §l§b»§r §aSearch The Player Guilds!\n§l§c»§r §e/guilds learderboards §l§b»§r §aShows Top Ranking Guilds!\n§l§c»§r §e/guilds setef §l§b»§r §aSet Effects For Guilds!\n§l§c»§r §e/guilds efinfo §l§b»§r §aShows Effects Information!\n§l§c»§r §e/guilds getef §l§b»§r §aGets The Effects You Have Setted!\n§l§c»§r §e/guilds sethome\n§l§c»§r §e/guilds unsethome\n§l§c»§r §e/guilds home";
    CONST HELP_MESSAGE_SIX = "Special OP Commands\n/guilds forcedelete <guilds>\n/guilds addgp\n/guilds forceunclaim <guilds>\n/guilds addmoney";
    CONST ERROR_MESSAGE = TextFormat::RED . "ERROR!error!ERROR!error!ERROR!error!ERROR!error!ERROR!error!ERROR!error!ERROR!error!ERROR!error!ERROR!error!ERROR!error!ERROR!error!";
    CONST GUILDS_COMMAND = 'guilds';
    CONST G_COMMAND = 'g';
    CONST EMPTY_ARGURMENT = "Undentified Error!";
	CONST MAP_WIDTH = 48;
	CONST MAP_HEIGHT = 8;
	CONST MAP_HEIGHT_FULL = 17;
	CONST MAP_KEY_CHARS = "\\/#?ç¬£$%=&^ABCDEFGHJKLMNOPQRSTUVWXYZÄÖÜÆØÅ1234567890abcdeghjmnopqrsuvwxyÿzäöüæøåâêîûô";
	CONST MAP_KEY_WILDERNESS = TextFormat::DARK_GRAY . "+";
	CONST MAP_KEY_SEPARATOR = TextFormat::YELLOW . "+";
	CONST MAP_KEY_OVERFLOW = TextFormat::WHITE . "#" . TextFormat::WHITE;
	CONST MAP_OVERFLOW_MESSAGE = self::MAP_KEY_OVERFLOW . ": Too Many Guilds (>" . 107 . ") on this Map.";

/////??MAP??/////

/// JEZZ =,=

    public function onCommand(CommandSender $sender, Command $command, $label, array $args) {
        if ($sender instanceof Player) {
			$player = $sender->getPlayer()->getName();
			$create = $this->plugin->settings->get("CreateCost");
			$claim = $this->plugin->settings->get("ClaimCost");
			$oclaim = $this->plugin->settings->get("OverClaimCost");
			$Alliancer = $this->plugin->settings->get("AllianceCost");
			$Alliancea = $this->plugin->settings->get("AlliancePrice");
			$home = $this->plugin->settings->get("SetHomeCost");
            $player = $sender->getPlayer()->getName();
            if (strtolower($command->getName(self::GUILDS_COMMAND) or $command->getName(self::G_COMMAND))) {
                if (empty($args)) {
                    $sender->sendMessage("Please use /guilds help for a list of commands");
                    return true;
                }

                if (count($args == 2)) {
/*Help Commands*/
                    if (strtolower($args[0]) == "help") {
                        if (!isset($args[1]) || $args[1] == 1) {
                            $sender->sendMessage(TextFormat::GOLD . self::HELP_MESSAGE_ONE);
                            return true;
                        }
                        if ($args[1] == 2) {
                            $sender->sendMessage(TextFormat::GOLD . self::HELP_MESSAGE_TWO);
                            return true;
                        }
                        if ($args[1] == 3) {
                            $sender->sendMessage(TextFormat::GOLD . self::HELP_MESSAGE_THREE);
                            return true;
                        }
                        if ($args[1] == 4) {
                            $sender->sendMessage(TextFormat::GOLD . self::HELP_MESSAGE_FOUR);
                            return true;
                        }
                        if ($args[1] == 5) {
                            $sender->sendMessage(TextFormat::GOLD . self::HELP_MESSAGE_FIVE);
                            return true;

                        }
                        if ($args[1] == 6){
                            $sender->isOP();
                            $sender->sendMessage(Textformat::GOLD. self::HELP_MESSAGE_SIX);
                            return true;
                        }else{
                            $sender->sendMessage(self::ERROR_MESSAGE);
                            return true;
                        }
                    }

                    /////////////////////////////// CREATE ///////////////////////////////

                    if ($args[0] == "create") {
                        if (!isset($args[1])) {
                            $sender->sendMessage("Usage: /guilds create <guilds name>");
                            return true;
                        }
                        if ($this->plugin->isNameBanned($args[1])) {
                            $sender->sendMessage("This name is not allowed");
                            return true;
                        }
                        if ($this->plugin->guildsExists($args[1])) {
                            $sender->sendMessage("The Guilds already exists");
                            return true;
                        }
                        if (strlen($args[1]) > $this->plugin->settings->get("MaxGuildNameLength")) {
                            $sender->sendMessage("That name is too long, please try again the maximum lenght is ". $this->plugin->settings->get("MaxGuildNameLength"));
                            return true;
                        }
                        if ($this->plugin->isInGuilds($sender->getName())) {
                            $sender->sendMessage("You must leave the guilds first");
                            return true;
                        } else if($r = EconomyAPI::getInstance()->reduceMoney($player, $create)) {
                            $guildName = $args[1];
							$player = strtolower($player);
                            $rank = "GuildsMaster";
                            $stmt = $this->plugin->db->prepare("INSERT OR REPLACE INTO master (player, guild, rank) VALUES (:player, :guild, :rank);");
                            $stmt->bindValue(":player", $player);
                            $stmt->bindValue(":guild", $guildName);
                            $stmt->bindValue(":rank", $rank);
                            $result = $stmt->execute();
                            $this->plugin->updateAlliance($guildName);
                            $this->plugin->updateNemisys($guildName);
                            $this->plugin->setGuildsPoints($guildName, $this->plugin->settings->get("TheDefaultPointEveryGuildStartsWith"));
                            $this->plugin->updateTag($player);
                            $sender->sendMessage($this->plugin->formatMessage("Guilds succesfull created ! use '/guilds desc' now.", true));
							$sender->sendMessage($this->plugin->formatMessage("Guilds successfully created for §6$create" , true));
                            return true;
                        } else {
						
						switch($r){
							case EconomyAPI::RET_INVALID:
	                           $needmorecoins = $create - EconomyAPI::getInstance()->myMoney($player);
								$sender->sendMessage("Not Enough Coins You Need : $needmorecoins");
								break;
							case EconomyAPI::RET_CANCELLED:
						
								$sender->sendMessage(self::ERROR_MESSAGE);
								break;
							case EconomyAPI::RET_NO_ACCOUNT:
								$sender->sendMessage(self::ERROR_MESSAGE);
								break;
						}
					  }
                    }
                    /////////////////////////////// INFO ///////////////////////////////

                    if (strtolower($args[0]) == 'info') {
                        if (isset($args[1])) {
                            if (!(ctype_alnum($args[1])) | !($this->plugin->guildsExists($args[1]))) {
                                $sender->sendMessage("Guilds does not exist");
                                $sender->sendMessage("Make sure the name of the selected guild is ABSOLUTELY EXACT.");
                                return true;
                            }
                            $guild = $args[1];
                            $result = $this->plugin->db->query("SELECT * FROM motd WHERE guild='$guild';");
                            $array = $result->fetchArray(SQLITE3_ASSOC);
                            $point = $this->plugin->getGuildsPoints($guild);
                            $money = $this->plugin->getGuildMoney($guild);
                            $message = $array["message"];
                            $gm = $this->plugin->getGuildsMaster($guild);
                            $numPlayers = $this->plugin->getNumberOfPlayers($guild);
                            $effects = $this->plugin->getEffectOf($guild);
                            $boosters = $this->plugin->getBoosterFrom($guild);
                            $alliancecount = $this->plugin->getAllianceCount($guild);
                            $nemisyscount = $this->plugin->getNemisysCount($guild);
                            $sender->sendMessage(TextFormat::GOLD . "-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-" . TextFormat::RESET);
                            $sender->sendMessage(TextFormat::GOLD . "Information§c: §d$guild" . TextFormat::RESET);
                            $sender->sendMessage(TextFormat::GOLD . "GuildsMaster§c: §d$gm". " §b| " . "§eMember: §d$numPlayers" . TextFormat::RESET);
                            $sender->sendMessage(TextFormat::GOLD . "GuildsPoints : §d$point " . TextFormat::YELLOW . "§b| §eGuildsMoneys : §d$money" . TextFormat::RESET);
                            $sender->sendMessage(TextFormat::GOLD . "Effects : §d$effects " . TextFormat::LIGHT_PURPLE . "§b| §eBoosters : §d$boosters" . TextFormat::RESET);
                            $sender->sendMessage(TextFormat::GOLD . "Alliance : §d$alliancecount " . TextFormat::RED . "§b| §eNemisys : §d$nemisyscount" . " " . TextFormat::RESET);
                            $sender->sendMessage(TextFormat::GOLD . "Description §8: " . TextFormat::UNDERLINE . "§d$message" . TextFormat::RESET);
                            $sender->sendMessage(TextFormat::GOLD . "-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-" . TextFormat::RESET);
                        } else {
                            if (!$this->plugin->isInGuilds($player)) {
                                $sender->sendMessage($this->plugin->formatMessage("You must be in a guilds to use this!"));
                                return true;
                            }
                            $guild = $this->plugin->getPlayerGuild($sender->getName());
                            $result = $this->plugin->db->query("SELECT * FROM motd WHERE guild='$guild';");
                            $array = $result->fetchArray(SQLITE3_ASSOC);
                            $point = $this->plugin->getGuildsPoints($guild);
                            $money = $this->plugin->getGuildMoney($guild);
                            $message = $array["message"];
                            $gm = $this->plugin->getGuildsMaster($guild);
                            $numPlayers = $this->plugin->getNumberOfPlayers($guild);
                            $effects = $this->plugin->getEffectOf($guild);
                            $boosters = $this->plugin->getBoosterFrom($guild);
                            $alliancecount = $this->plugin->getAllianceCount($guild);
                            $nemisyscount = $this->plugin->getNemisysCount($guild);
                            $sender->sendMessage(TextFormat::GOLD . "-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-" . TextFormat::RESET);
                            $sender->sendMessage(TextFormat::GOLD . "Information§c: §d$guild" . TextFormat::RESET);
                            $sender->sendMessage(TextFormat::GOLD . "GuildsMaster§c: §d$gm". " §b| " . "§eMember: §d$numPlayers" . TextFormat::RESET);
                            $sender->sendMessage(TextFormat::GOLD . "GuildsPoints : §d$point " . TextFormat::YELLOW . "§b| §eGuildsMoneys : §d$money" . TextFormat::RESET);
                            $sender->sendMessage(TextFormat::GOLD . "Effects : §d$effects " . TextFormat::LIGHT_PURPLE . "§b| §eBoosters : §d$boosters" . TextFormat::RESET);
                            $sender->sendMessage(TextFormat::GOLD . "Alliance : §d$alliancecount " . TextFormat::RED . "§b| §eNemisys : §d$nemisyscount" . " " . TextFormat::RESET);
                            $sender->sendMessage(TextFormat::GOLD . "Description §8: " . TextFormat::UNDERLINE . "§d$message" . TextFormat::RESET);
                            $sender->sendMessage(TextFormat::GOLD . "-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-" . TextFormat::RESET);
                    return true;
                        }
                    }

                    ///////////////////////////////// WAR /////////////////////////////////

                    if ($args[0] == "war") {
                        if (!isset($args[1])) {
                            $sender->sendMessage("Usage: /guilds war <guilds name:tp>");
                            return true;
                        }
                        if (strtolower($args[1]) == "tp") {
                            foreach ($this->plugin->wars as $r => $f) {
                                $fac = $this->plugin->getPlayerGuild($player);
                                if ($r == $fac) {
                                    $x = mt_rand(0, $this->plugin->getNumberOfPlayers($fac) - 1);
                                    $tper = $this->plugin->war_players[$f][$x];
                                    $sender->teleport($this->plugin->getServer()->getPlayerByName($tper));
                                    return;
                                }
                                if ($f == $fac) {
                                    $x = mt_rand(0, $this->plugin->getNumberOfPlayers($fac) - 1);
                                    $tper = $this->plugin->war_players[$r][$x];
                                    $sender->teleport($this->plugin->getServer()->getPlayer($tper));
                                    return;
                                }
                            }
                            $sender->sendMessage("You must be in a war to do that");
                            return true;
                        }
                        if (!(ctype_alnum($args[1]))) {
                            $sender->sendMessage("You may only use letters and numbers");
                            return true;
                        }
                        if (!$this->plugin->guildsExists($args[1])) {
                            $sender->sendMessage("Guilds does not exist");
                            return true;
                        }
                        if (!$this->plugin->isInGuilds($sender->getName())) {
                            $sender->sendMessage("You must be in a guilds to do this");
                            return true;
                        }
                        if (!$this->plugin->isGuildsMaster($player)) {
                            $sender->sendMessage("Only your guilds guildsmasters may start wars");
                            return true;
                        }
                        if (!$this->plugin->isNemisys($this->plugin->getPlayerGuild($player), $args[1])) {
                            $sender->sendMessage("Your guilds is not an enemy of $args[1]");
                            return true;
                        } else {
                            $guildName = $args[1];
                            $sguild = $this->plugin->getPlayerGuild($player);
                            foreach ($this->plugin->war_req as $r => $f) {
                                if ($r == $args[1] && $f == $sguild) {
                                    foreach ($this->plugin->getServer()->getOnlinePlayers() as $p) {
                                        $task = new guildWar($this->plugin, $r);
                                        $handler = $this->plugin->getServer()->getScheduler()->scheduleDelayedTask($task, 20 * 60 * 2);
                                        $task->setHandler($handler);
                                        $p->sendMessage("The war against $guildName and $sguild has started!");
                                        if ($this->plugin->getPlayerGuild($p->getName()) == $sguild) {
                                            $this->plugin->war_players[$sguild][] = $p->getName();
                                        }
                                        if ($this->plugin->getPlayerGuild($p->getName()) == $guildName) {
                                            $this->plugin->war_players[$guildName][] = $p->getName();
                                        }
                                    }
                                    $this->plugin->wars[$guildName] = $sguild;
                                    unset($this->plugin->war_req[strtolower($args[1])]);
                                    return true;
                                }
                            }
                            $this->plugin->war_req[$sguild] = $guildName;
                            foreach ($this->plugin->getServer()->getOnlinePlayers() as $p) {
                                if ($this->plugin->getPlayerGuild($p->getName()) == $guildName) {
                                    if ($this->plugin->getGuildsMaster($guildName) == $p->getName()) {
                                        $p->sendMessage("$sguild wants to start a war, '/guilds war $sguild' to start!");
                                        $sender->sendMessage("Guilds war requested");
                                        return true;
                                    }
                                }
                            }
                            $sender->sendMessage("Guilds guildsmasters is not online.");
                            return true;
                        }
                    }

                    /////////////////////////////// INVITE ///////////////////////////////

                    if ($args[0] == "invite") {
                        if (!isset($args[1])) {
                            $sender->sendMessage("Usage: /guilds invite <player>");
                            return true;
                        }
                        if ($this->plugin->isGuildFull($this->plugin->getPlayerGuild($player))) {
                            $sender->sendMessage("Guilds is full, please kick players to make room");
                            return true;
                        }
                        $invited = $this->plugin->getServer()->getPlayerExact($args[1]);
                        if (!($invited instanceof Player)) {
                            $sender->sendMessage("Player not online");
                            return true;
                        }
                        if ($this->plugin->isInGuilds($invited) == true) {
                            $sender->sendMessage("Player is currently in a guilds");
                            return true;
                        }
                        if ($this->plugin->settings->get("OnlyGuildsMasterAndSecondInCommandsCanInvite")) {
                            if (!($this->plugin->isSecondInCommands($player) || $this->plugin->isGuildsMaster($player))) {
                                $sender->sendMessage("Only your guilds GuildsMaster/SecondInCommands can invite");
                                return true;
                            }
                        }
                        if ($invited->getName() == $player) {

                            $sender->sendMessage("You can't invite yourself to your own guilds!");
                            return true;
                        }

                        $guildName = $this->plugin->getPlayerGuild($player);
                        $invitedName = $invited->getName();
                        $rank = "Member";

                        $stmt = $this->plugin->db->prepare("INSERT OR REPLACE INTO confirm (player, guild, invitedby, timestamp) VALUES (:player, :guild, :invitedby, :timestamp);");
                        $stmt->bindValue(":player", $invitedName);
                        $stmt->bindValue(":guild", $guildName);
                        $stmt->bindValue(":invitedby", $sender->getName());
                        $stmt->bindValue(":timestamp", time());
                        $result = $stmt->execute();
                        $sender->sendMessage("$invitedName has been invited", true);
                        $invited->sendMessage("You have been invited to $guildName. Type '/guilds accept' or '/guilds deny' into chat to accept or deny!", true);
                    }

                    /////////////////////////////// guildsmasters ///////////////////////////////

                    if ($args[0] == "guildsmasters") {
                        if (!isset($args[1])) {
                            $sender->sendMessage("Usage: /guilds guildsmasters <player>");
                            return true;
                        }
                        if (!$this->plugin->isInGuilds($sender->getName())) {
                            $sender->sendMessage("You must be in a guilds to use this");
                            return true;
                        }
                        if (!$this->plugin->isGuildsMaster($player)) {
                            $sender->sendMessage("You must be guildsmasters to use this");
                            return true;
                        }
                        if ($this->plugin->getPlayerGuild($player) != $this->plugin->getPlayerGuild($args[1])) {
                            $sender->sendMessage("Add player to guilds first!");
                            return true;
                        }
                        if (!($this->plugin->getServer()->getPlayerExact($args[1]) instanceof Player)) {
                            $sender->sendMessage("Player not online");
                            return true;
                        }
                        if ($args[1] == $sender->getName()) {

                            $sender->sendMessage("You can't transfer the GuildsMasters Rank to yourself");
                            return true;
                        }
                        $guildName = $this->plugin->getPlayerGuild($player);

                        $stmt = $this->plugin->db->prepare("INSERT OR REPLACE INTO master (player, guild, rank) VALUES (:player, :guild, :rank);");
                        $stmt->bindValue(":player", $player);
                        $stmt->bindValue(":guild", $guildName);
                        $stmt->bindValue(":rank", "Member");
                        $result = $stmt->execute();

                        $stmt = $this->plugin->db->prepare("INSERT OR REPLACE INTO master (player, guild, rank) VALUES (:player, :guild, :rank);");
                        $stmt->bindValue(":player", $args[1]);
                        $stmt->bindValue(":guild", $guildName);
                        $stmt->bindValue(":rank", "guildsmasters");
                        $result = $stmt->execute();


                        $sender->sendMessage("You are no longer guildsmasters", true);
                        $this->plugin->getServer()->getPlayerExact($args[1])->sendMessage("You are now guildsmasters of $guildName!", true);
                        $this->plugin->updateTag($sender->getName());
                        $this->plugin->updateTag($this->plugin->getServer()->getPlayerExact($args[1])->getName());
                    }

                    /////////////////////////////// PROMOTE ///////////////////////////////

                    if ($args[0] == "promote") {
                        if (!isset($args[1])) {
                            $sender->sendMessage("Usage: /guilds promote <player>");
                            return true;
                        }
                        if (!$this->plugin->isInGuilds($sender->getName())) {
                            $sender->sendMessage("You must be in a guilds to use this");
                            return true;
                        }
                        if (!$this->plugin->isGuildsMaster($player)) {
                            $sender->sendMessage("You must be guildsmasters to use this");
                            return true;
                        }
                        if ($this->plugin->getPlayerGuild($player) != $this->plugin->getPlayerGuild($args[1])) {
                            $sender->sendMessage("Player is not in this guilds!");
                            return true;
                        }
                        if ($args[1] == $sender->getName()) {
                            $sender->sendMessage("You can't promote yourself!");
                            return true;
                        }

                        if ($this->plugin->isSecondInCommands($args[1])) {
                            $sender->sendMessage("Player is already SecondInCommands!");
                            return true;
                        }
                        $guildName = $this->plugin->getPlayerGuild($player);
                        $stmt = $this->plugin->db->prepare("INSERT OR REPLACE INTO master (player, guild, rank) VALUES (:player, :guild, :rank);");
                        $stmt->bindValue(":player", $args[1]);
                        $stmt->bindValue(":guild", $guildName);
                        $stmt->bindValue(":rank", "SecondInCommands");
                        $result = $stmt->execute();
                        $player = $this->plugin->getServer()->getPlayerExact($args[1]);
                        $sender->sendMessage("$args[1] has been promoted to SecondInCommands", true);

                        if ($player instanceof Player) {
                            $player->sendMessage("You were promoted to SecondInCommands of $guildName!", true);
                            $this->plugin->updateTag($this->plugin->getServer()->getPlayerExact($args[1])->getName());
                            return true;
                        }
                    }

                    /////////////////////////////// DEMOTE ///////////////////////////////

                    if ($args[0] == "demote") {
                        if (!isset($args[1])) {
                            $sender->sendMessage("Usage: /guilds demote <player>");
                            return true;
                        }
                        if ($this->plugin->isInGuilds($sender->getName()) == false) {
                            $sender->sendMessage("You must be in a guilds to use this");
                            return true;
                        }
                        if ($this->plugin->isGuildsMaster($player) == false) {
                            $sender->sendMessage("You must be guildsmasters to use this");
                            return true;
                        }
                        if ($this->plugin->getPlayerGuild($player) != $this->plugin->getPlayerGuild($args[1])) {
                            $sender->sendMessage("Player is not in this guilds!");
                            return true;
                        }

                        if ($args[1] == $sender->getName()) {
                            $sender->sendMessage("You can't demote yourself!");
                            return true;
                        }
                        if (!$this->plugin->isSecondInCommands($args[1])) {
                            $sender->sendMessage("Player is already Member!");
                            return true;
                        }
                        $guildName = $this->plugin->getPlayerGuild($player);
                        $stmt = $this->plugin->db->prepare("INSERT OR REPLACE INTO master (player, guild, rank) VALUES (:player, :guild, :rank);");
                        $stmt->bindValue(":player", $args[1]);
                        $stmt->bindValue(":guild", $guildName);
                        $stmt->bindValue(":rank", "Member");
                        $result = $stmt->execute();
                        $player = $this->plugin->getServer()->getPlayerExact($args[1]);
                        $sender->sendMessage("$args[1] has been demoted to Member", true);
                        if ($player instanceof Player) {
                            $player->sendMessage("You were demoted to member of $guildName!", true);
                            $this->plugin->updateTag($this->plugin->getServer()->getPlayerExact($args[1])->getName());
                            return true;
                        }
                    }

                    /////////////////////////////// KICK ///////////////////////////////

                    if ($args[0] == "kick") {
                        if (!isset($args[1])) {
                            $sender->sendMessage("Usage: /guilds kick <player>");
                            return true;
                        }
                        if ($this->plugin->isInGuilds($sender->getName()) == false) {
                            $sender->sendMessage("You must be in a guilds to use this!");
                            return true;
                        }
                        if ($this->plugin->isGuildsMaster($player) == false) {
                            $sender->sendMessage("You must be guildsmasters to use this!");
                            return true;
                        }
                        if ($this->plugin->getPlayerGuild($player) != $this->plugin->getPlayerGuild($args[1])) {
                            $sender->sendMessage("Player is not in this guilds!");
                            return true;
                        }
                        if ($args[1] == $sender->getName()) {
                            $sender->sendMessage("You can't kick yourself!");
                            return true;
                        }
                        $kicked = $this->plugin->getServer()->getPlayerExact($args[1]);
                        $guildName = $this->plugin->getPlayerGuild($player);
                        $this->plugin->db->query("DELETE FROM master WHERE player='$args[1]';");
                        $sender->sendMessage("You successfully kicked $args[1]", true);
                        $this->plugin->subtractGuildsPoints($guildName, $this->plugin->settings->get("PointGainedPerPlayerInGuild"));

                        if ($kicked instanceof Player) {
                            $kicked->sendMessage("You have been kicked from $guildName", true);
                            $this->plugin->updateTag($this->plugin->getServer()->getPlayerExact($args[1])->getName());
                            return true;
                        }
                    }

                }
                if (count($args == 1)) {



					/////////////////////////////// CLAIM ///////////////////////////////
					
					if(strtolower($args[0]) == 'claim') {//
						if(!$this->plugin->isInGuilds($player)) {
							$sender->sendMessage("§cYou must be in a guilds to claim");
							return true;
						}
                        if($this->plugin->settings->get("SecondInCommandsCanClaim")){
                            if(!$this->plugin->isGuildsMaster($player) || !$this->plugin->isSecondInCommands($player)) {
							    $sender->sendMessage("§cOnly GuildsMasters and SecondInCommands can claim");
							    return true;
						    }
                        } else {
                            if(!$this->plugin->isGuildsMaster($player)) {
							    $sender->sendMessage("§cYou must be guildsmasters to use this");
							    return true;
						    }
                        }
                        if (!$this->plugin->isGuildsMaster($player)) {
                            $sender->sendMessage("You must be guildsmasters to use this.");
                            return true;
                        }
                        if (!in_array($sender->getPlayer()->getLevel()->getName(), $this->plugin->settings->get("ClaimWorlds"))) {
                            $sender->sendMessage("You can only claim in Guilds Worlds: " . implode(" ", $this->plugin->settings->get("ClaimWorlds")));
                            return true;
                        }
                        
						if($this->plugin->inOwnPlot($sender)) {
							$sender->sendMessage("§aYour guilds has already claimed this area.");
							return true;
						}
						$guild = $this->plugin->getPlayerGuild($sender->getPlayer()->getName());
                        if($this->plugin->getNumberOfPlayers($guild) < $this->plugin->settings->get("PlayersNeededInGuildToClaimAPlot")){
                           
                           $needed_players =  $this->plugin->settings->get("PlayersNeededInGuildToClaimAPlot") - 
                                               $this->plugin->getNumberOfPlayers($guild);
                           $sender->sendMessage("§bYou need §e$needed_players §bmore players to claim");
				           return true;
                        }
                        if($this->plugin->getGuildsPoints($guild) < $this->plugin->settings->get("PointNeededToClaimAPlot")){
                            $needed_point = $this->plugin->settings->get("PointNeededToClaimAPlot");
                            $guild_point = $this->plugin->getGuildsPoints($guild);
							$sender->sendMessage("§3Your guilds doesn't have enough point to claim");
							$sender->sendMessage("§e"."$needed_point" . " §3point is required. Your guilds only has §a$guild_point §3point.");
                            return true;
                        }
						elseif($r = EconomyAPI::getInstance()->reduceMoney($player, $claim)){
						$x = floor($sender->getX());
						$y = floor($sender->getY());
						$z = floor($sender->getZ());
						if($this->plugin->drawPlot($sender, $guild, $x, $y, $z, $sender->getPlayer()->getLevel(), $this->plugin->settings->get("PlotSize")) == false) {
                            
							return true;
						}
                        
						$sender->sendMessage("§bGetting your coordinates...", true);
                        $plot_size = $this->plugin->settings->get("PlotSize");
                        $guild_point = $this->plugin->getGuildsPoints($guild);
						$sender->sendMessage("§aLand successfully claimed for §6$$claim§a.", true);
					}
					else {
						// $r is an error code
						switch($r){
							case EconomyAPI::RET_INVALID:
								# Invalid $amount
								$sender->sendMessage("§3You do not have enough Money to Claim! Need §6$$claim");
								break;
							case EconomyAPI::RET_CANCELLED:
								# Transaction was cancelled for some reason :/
								$sender->sendMessage(self::ERROR_MESSAGE);
								break;
							case EconomyAPI::RET_NO_ACCOUNT:
								$sender->sendMessage(self::ERROR_MESSAGE);
								break;
						}
					}
					}
                    //position
                    if(strtolower($args[0]) == 'pos'){
                        $x = floor($sender->getX());
						$y = floor($sender->getY());
						$z = floor($sender->getZ());
                        $fac = $this->plugin->guildFromPoint($x,$z);
                        $point = $this->plugin->getGuildsPoints($fac);
                        if(!$this->plugin->isInPlot($sender)){
                            $sender->sendMessage("§bThis area is unclaimed. Use §e/guilds claim §bto claim", true);
							return true;
                        }
                        $sender->sendMessage("§3This plot is claimed by §a$fac §3with §e$point §3point");
                    }
                    
                    if(strtolower($args[0]) == 'overclaim') {
						if(!$this->plugin->isInGuilds($player)) {
							$sender->sendMessage("§cYou must be in a guilds to use this");
							return true;
						}
						if(!$this->plugin->isGuildsMaster($player)) {
							$sender->sendMessage("§cYou must be guildsmasters to use this");
							return true;
						}
                        $guild = $this->plugin->getPlayerGuild($player);
						if($this->plugin->getNumberOfPlayers($guild) < $this->plugin->settings->get("PlayersNeededInGuildToClaimAPlot")){
                           
                           $needed_players =  $this->plugin->settings->get("PlayersNeededInGuildToClaimAPlot") - 
                                               $this->plugin->getNumberOfPlayers($guild);
                           $sender->sendMessage("§3You need §e$needed_players §3more players to overclaim");
				           return true;
                        }
                        if (!in_array($sender->getPlayer()->getLevel()->getName(), $this->plugin->settings->get("ClaimWorlds"))) {
                            $sender->sendMessage("You can only claim in Guilds Worlds: " . implode(" ", $this->plugin->settings->get("ClaimWorlds")));
                            return true;
                        }
                        if($this->plugin->getGuildsPoints($guild) < $this->plugin->settings->get("PointNeededToClaimAPlot")){
                            $needed_point = $this->plugin->settings->get("PointNeededToClaimAPlot");
                            $guild_point = $this->plugin->getGuildsPoints($guild);
							$sender->sendMessage("§3Your guilds does not have enough point to claim! Get point by killing players!");
							$sender->sendMessage("§e$needed_point" . "§3 point is required but your guilds only has §e$guild_point §3point");
                            return true;
                        }
						$sender->sendMessage("§bGetting your coordinates...", true);
						$x = floor($sender->getX());
						$y = floor($sender->getY());
						$z = floor($sender->getZ());
                        if($this->plugin->settings->get("EnableOverClaim")){
                            if($this->plugin->isInPlot($sender)){
                                $guild_victim = $this->plugin->guildFromPoint($x,$z);
                                $guild_victim_point = $this->plugin->getGuildsPoints($guild_victim);
                                $guild_ours = $this->plugin->getPlayerGuild($player);
                                $guild_ours_point = $this->plugin->getGuildsPoints($guild_ours);
                                if($this->plugin->inOwnPlot($sender)){
                                    $sender->sendMessage("§aYour guilds has already claimed this land");
                                    return true;
                                } else {
                                    if($guild_ours_point < $guild_victim_point){
                                        $sender->sendMessage("§3Your point level is too low to over claim §b$guild_victim");
                                        return true;
                                    } elseif($r = EconomyAPI::getInstance()->reduceMoney($player, $oclaim))
									   {
                                        $this->plugin->db->query("DELETE FROM plots WHERE guild='$guild_ours';");
                                        $this->plugin->db->query("DELETE FROM plots WHERE guild='$guild_victim';");
                                        $arm = (($this->plugin->settings->get("PlotSize")) - 1) / 2;
                                        $this->plugin->newPlot($guild_ours,$x+$arm,$z+$arm,$x-$arm,$z-$arm);
					                    $sender->sendMessage("§aYour guilds has successfully overclaimed the land of §b$guild_victim §afor §6$$oclaim", true);
                                        return true;
                                    }
									else {
						// $r is an error code
						    switch($r){
							case EconomyAPI::RET_INVALID:
								# Invalid $amount
								$sender->sendMessage("§3You do not have enough Money to Overclaim! Need §6$oclaim");
								break;
							case EconomyAPI::RET_CANCELLED:
								# Transaction was cancelled for some reason :/
								$sender->sendMessage(self::ERROR_MESSAGE);
								break;
							case EconomyAPI::RET_NO_ACCOUNT:
								$sender->sendMessage(self::ERROR_MESSAGE);
								break;
						}
					}
                                    
                                }
                            } else {
                                $sender->sendMessage("§cYou are not in claimed land");
                                return true;
                            }
                        } else {
                            $sender->sendMessage("§cInsufficient permissions");
                            return true;
                        }
                        
					}
                    
					
					/////////////////////////////// UNCLAIM ///////////////////////////////
					
					if(strtolower($args[0]) == "unclaim") {
                        if(!$this->plugin->isInGuilds($sender->getName())) {
							$sender->sendMessage("§cYou must be in a guilds to use this");
							return true;
						}
						if(!$this->plugin->isGuildsMaster($sender->getName())) {
							$sender->sendMessage("§cYou must be guildsmasters to use this");
							return true;
						}
						$guild = $this->plugin->getPlayerGuild($sender->getName());
						$this->plugin->db->query("DELETE FROM plots WHERE guild='$guild';");
						$sender->sendMessage("§aLand successfully unclaimed", true);
					}
					/////////////////////////////// SETHOME ///////////////////////////////
					
					if(strtolower($args[0] == "sethome")) {
						if(!$this->plugin->isInGuilds($player)) {
							$sender->sendMessage("§cYou must be in a guilds to do this");
							return true;
						}
                        if (!in_array($sender->getPlayer()->getLevel()->getName(), $this->plugin->settings->get("ClaimWorlds"))) {
                            $sender->sendMessage("You can only sethome in Guilds Worlds: " . implode(" ", $this->plugin->settings->get("ClaimWorlds")));
                            return true;
                        }
						if(!$this->plugin->isGuildsMaster($player)) {
							$sender->sendMessage("§cYou must be guildsmasters to set home");
							return true;
						}
                        
                        $guild_point = $this->plugin->getGuildsPoints($this->plugin->getPlayerGuild($player));
                        $needed_point = $this->plugin->settings->get("PointNeededToSetOrUpdateAHome");
                        if($guild_point < $needed_point){
                            $sender->sendMessage("§3Your guilds doesn't have enough point set a home. Get point by killing players!");
                            $sender->sendMessage("§e $needed_point §3point is required to set a home. Your guilds has §e$guild_point §3point.");
							return true;
                        }
						elseif($r = EconomyAPI::getInstance()->reduceMoney($player, $home)){
						$guildName = $this->plugin->getPlayerGuild($sender->getName());
						$stmt = $this->plugin->db->prepare("INSERT OR REPLACE INTO home (guild, x, y, z) VALUES (:guild, :x, :y, :z);");
						$stmt->bindValue(":guild", $guildName);
						$stmt->bindValue(":x", $sender->getX());
						$stmt->bindValue(":y", $sender->getY());
						$stmt->bindValue(":z", $sender->getZ());
						$result = $stmt->execute();
						$sender->sendMessage("Guilds home set for $home Coins", true);
                        }
						else {

						    switch($r){
							case EconomyAPI::RET_INVALID:

								$sender->sendMessage("Error! You Need $home Coins To Set A Home!");
								break;
							case EconomyAPI::RET_CANCELLED:
								$sender->sendMessage(self::ERROR_MESSAGE);
								break;
							case EconomyAPI::RET_NO_ACCOUNT:
								$sender->sendMessage(self::ERROR_MESSAGE);
								break;
						}
					}
					}
					
					/////////////////////////////// UNSETHOME ///////////////////////////////
						
					if(strtolower($args[0] == "unsethome")) {
						if(!$this->plugin->isInGuilds($player)) {
							$sender->sendMessage("§cYou must be in a guilds to do this");
							return true;
						}
						if(!$this->plugin->isGuildsMaster($player)) {
							$sender->sendMessage("§cYou must be guildsmasters to unset home");
							return true;
						}
						$guild = $this->plugin->getPlayerGuild($sender->getName());
						$this->plugin->db->query("DELETE FROM home WHERE guild = '$guild';");
						$sender->sendMessage("§aHome unset succeed", true);
					}
					
					/////////////////////////////// HOME ///////////////////////////////
						
					if(strtolower($args[0] == "home")) {
						if(!$this->plugin->isInGuilds($player)) {
							$sender->sendMessage("§cYou must be in a guilds to do this.");
                            return true;
						}
						$guild = $this->plugin->getPlayerGuild($sender->getName());
						$result = $this->plugin->db->query("SELECT * FROM home WHERE guild = '$guild';");
						$array = $result->fetchArray(SQLITE3_ASSOC);

                        if (!in_array($sender->getPlayer()->getLevel()->getName(), $this->plugin->settings->get("ClaimWorlds"))) {
                            $sender->sendMessage("You can only teleport to home in Guilds Worlds: " . implode(" ", $this->plugin->settings->get("ClaimWorlds")));
                            return true;
                        }

						if(!empty($array)) {
							$sender->getPlayer()->teleport(new Vector3($array['x'], $array['y'], $array['z']));
							$sender->sendMessage("§bTeleported to home.", true);
							return true;
						} else {
							$sender->sendMessage("Guilds home has not been set");
				        }
				    }
                    //TOP10 learderboards
                    if (strtolower($args[0]) == 'learderboards') {
                        $this->plugin->learderboards($sender);
                    }
                    //force unclaim
                    if(strtolower($args[0] == "forceunclaim")){
                        if(!isset($args[1])){
                            $sender->sendMessage("/guilds forceunclaim <guilds>");
                            return true;
                        }
                        if(!$this->plugin->guildsExists($args[1])) {
							$sender->sendMessage("§cThe requested guilds does not exist");
                            return true;
						}
                        if(!($sender->isOp())) {
							$sender->sendMessage("§cInsufficient permissions");
                            return true;
						}
				        $sender->sendMessage("§bLand of §a$args[1]§b unclaimed");
                        $this->plugin->db->query("DELETE FROM plots WHERE guild='$args[1]';");
                        
                    }
                    //forcedelete
                    if (strtolower($args[0]) == 'forcedelete') {
                        if (!isset($args[1])) {
                            $sender->sendMessage("Usage: /guilds forcedelete <guilds>");
                            return true;
                        }
                        if (!$this->plugin->guildsExists($args[1])) {
                            $sender->sendMessage("The requested guilds doesn't exist.");
                            return true;
                        }
                        if (!($sender->isOp())) {
                            $sender->sendMessage("You must be OP to do this.");
                            return true;
                        }
                        $this->plugin->db->query("DELETE FROM master WHERE guild='$args[1]';");
                        $this->plugin->db->query("DELETE FROM plots WHERE guild='$args[1]';");
                        $this->plugin->db->query("DELETE FROM alliance WHERE guild1='$args[1]';");
                        $this->plugin->db->query("DELETE FROM alliance WHERE guild2='$args[1]';");
                        $this->plugin->db->query("DELETE FROM nemisys WHERE guild1='$args[1]';");
                        $this->plugin->db->query("DELETE FROM nemisys WHERE guild2='$args[1]';");
                        $this->plugin->db->query("DELETE FROM gp WHERE guild='$args[1]';");
                        $this->plugin->db->query("DELETE FROM moneys WHERE guild='$args[1]';");
                        $this->plugin->db->query("DELETE FROM motd WHERE guild='$args[1]';");
                        $this->plugin->db->query("DELETE FROM home WHERE guild='$args[1]';");
                        $this->plugin->db->query("DELETE FROM wp WHERE guild='$args[1]';");
                        $this->plugin->db->query("DELETE FROM effects WHERE guild='$args[1]';");
                        $this->plugin->db->query("DELETE FROM boosters WHERE guild='$args[1]';");
                        $sender->sendMessage("Unwanted guilds was successfully deleted and their guilds plot was unclaimed!", true);
                    }
                    //Add Guilds Points
                    if (strtolower($args[0]) == 'addgp') {
                        if (!isset($args[1]) or ! isset($args[2])) {
                            $sender->sendMessage("Usage: /guilds addgp <guilds> <GuildsPoints>");
                            return true;
                        }
                        if (!$this->plugin->guildsExists($args[1])) {
                            $sender->sendMessage("The requested guilds doesn't exist.");
                            return true;
                        }
                        if (!($sender->isOp())) {
                            $sender->sendMessage("You must be OP to do this.");
                            return true;
                        }
                        $this->plugin->addGuildsPoints($args[1], $args[2]);
                        $sender->sendMessage("Successfully added $args[2] GuildsPoints to $args[1]", true);
                    }
                    if (strtolower($args[0]) == 'addmoney') {
                        if (!isset($args[1]) or ! isset($args[2])) {
                            $sender->sendMessage("Usage: /guilds addmoney <guilds> <GuildsMoneys>");
                            return true;
                        }
                        if (!$this->plugin->guildsExists($args[1])) {
                            $sender->sendMessage("The requested guilds doesn't exist.");
                            return true;
                        }
                        if (!($sender->isOp())) {
                            $sender->sendMessage("You must be OP to do this.");
                            return true;
                        }
                        $this->plugin->addGuildMoney6($args[1], $args[2]);
                        $sender->sendMessage("Successfully added $args[2] GuildsMoneys to $args[1]", true);
                    }
                    //Stalk A player
                    if (strtolower($args[0]) == 'search') {
                        if (!isset($args[1])) {
                            $sender->sendMessage("Usage: /guilds search <player>");
                            return true;
                        }
                        if (!$this->plugin->isInGuilds($args[1])) {
                            $sender->sendMessage("The selected player is not in a guilds  or doesn't exist.");
                            $sender->sendMessage("Make sure the name of the selected player is ABSOLUTELY EXACT.");
                            return true;
                        }
                        $guild = $this->plugin->getPlayerGuild($args[1]);
                        $sender->sendMessage("-$args[1] is in $guild-", true);
                    }

                    /////////////////////////////// DESCRIPTION ///////////////////////////////

                    if (strtolower($args[0]) == "desc") {
                        if ($this->plugin->isInGuilds($sender->getName()) == false) {
                            $sender->sendMessage("You must be in a guilds to use this!");
                            return true;
                        }
                        if ($this->plugin->isGuildsMaster($player) == false) {
                            $sender->sendMessage("You must be guildsmasters to use this");
                            return true;
                        }
                        $sender->sendMessage("Type your message in chat. It will not be visible to other players", true);
                        $stmt = $this->plugin->db->prepare("INSERT OR REPLACE INTO motdrcv (player, timestamp) VALUES (:player, :timestamp);");
                        $stmt->bindValue(":player", $sender->getName());
                        $stmt->bindValue(":timestamp", time());
                        $result = $stmt->execute();
                    }

                    /////////////////////////////// ACCEPT ///////////////////////////////

                    if (strtolower($args[0]) == "accept") {
                        $player = $sender->getName();
                        $lowercaseName = $player;
                        $result = $this->plugin->db->query("SELECT * FROM confirm WHERE player='$lowercaseName';");
                        $array = $result->fetchArray(SQLITE3_ASSOC);
                        if (empty($array) == true) {
                            $sender->sendMessage("You have not been invited to any guilds");
                            return true;
                        }
                        $invitedTime = $array["timestamp"];
                        $currentTime = time();
                        if (($currentTime - $invitedTime) <= 60) { //This should be configurable
                            $guild = $array["guild"];
                            $stmt = $this->plugin->db->prepare("INSERT OR REPLACE INTO master (player, guild, rank) VALUES (:player, :guild, :rank);");
                            $stmt->bindValue(":player", $player);
                            $stmt->bindValue(":guild", $guild);
                            $stmt->bindValue(":rank", "Member");
                            $result = $stmt->execute();
                            $this->plugin->db->query("DELETE FROM confirm WHERE player='$lowercaseName';");
                            $sender->sendMessage("You successfully joined $guild", true);
                            $this->plugin->addGuildsPoints($guild, $this->plugin->settings->get("PointGainedPerPlayerInGuild"));
                            $this->plugin->getServer()->getPlayerExact($array["invitedby"]);
                            $this->sendMessage("$player joined the guilds", true);
                            $this->plugin->updateTag($sender->getName());
                        } else {
                            $sender->sendMessage("Invite has timed out");
                            $this->plugin->db->query("DELETE * FROM confirm WHERE player='$player';");
                        }
                    }

                    /////////////////////////////// DENY ///////////////////////////////

                    if (strtolower($args[0]) == "deny") {
                        $player = $sender->getName();
                        $lowercaseName = ($player);
                        $result = $this->plugin->db->query("SELECT * FROM confirm WHERE player='$lowercaseName';");
                        $array = $result->fetchArray(SQLITE3_ASSOC);
                        if (empty($array) == true) {
                            $sender->sendMessage("You have not been invited to any guilds");
                            return true;
                        }
                        $invitedTime = $array["timestamp"];
                        $currentTime = time();
                        if (($currentTime - $invitedTime) <= 60) { //This should be configurable
                            $this->plugin->db->query("DELETE FROM confirm WHERE player='$lowercaseName';");
                            $sender->sendMessage("Invite declined", true);
                            $this->plugin->getServer()->getPlayerExact($array["invitedby"])->sendMessage("$player declined the invitation");
                        } else {
                            $sender->sendMessage("Invite has timed out");
                            $this->plugin->db->query("DELETE * FROM confirm WHERE player='$lowercaseName';");
                        }
                    }

                    /////////////////////////////// DELETE ///////////////////////////////

                    if (strtolower($args[0]) == "del") {
                        if ($this->plugin->isInGuilds($player) == true) {
                            if ($this->plugin->isGuildsMaster($player)) {
                                $guild = $this->plugin->getPlayerGuild($player);
                                $this->plugin->db->query("DELETE FROM master WHERE guild='$args[1]';");
                                $this->plugin->db->query("DELETE FROM plots WHERE guild='$args[1]';");
                                $this->plugin->db->query("DELETE FROM alliance WHERE guild1='$args[1]';");
                                $this->plugin->db->query("DELETE FROM alliance WHERE guild2='$args[1]';");
                                $this->plugin->db->query("DELETE FROM nemisys WHERE guild1='$args[1]';");
                                $this->plugin->db->query("DELETE FROM nemisys WHERE guild2='$args[1]';");
                                $this->plugin->db->query("DELETE FROM gp WHERE guild='$args[1]';");
                                $this->plugin->db->query("DELETE FROM moneys WHERE guild='$args[1]';");
                                $this->plugin->db->query("DELETE FROM motd WHERE guild='$args[1]';");
                                $this->plugin->db->query("DELETE FROM home WHERE guild='$args[1]';");
                                $this->plugin->db->query("DELETE FROM wp WHERE guild='$args[1]';");
                                $this->plugin->db->query("DELETE FROM effects WHERE guild='$args[1]';");
                                $this->plugin->db->query("DELETE FROM boosters WHERE guild='$args[1]';");
                                $sender->sendMessage("Guilds successfully disbanded.", true);
                                $this->plugin->updateTag($sender->getName());
                            } else {
                                $sender->sendMessage("You are not guildsmasters!");
                            }
                        } else {
                            $sender->sendMessage("You are not in a guilds!");
                        }
                    }

                    /////////////////////////////// LEAVE ///////////////////////////////

                    if (strtolower($args[0] == "leave")) {
                        if ($this->plugin->isGuildsMaster($player) == false) {
                            $remove = $sender->getPlayer()->getNameTag();
                            $guild = $this->plugin->getPlayerGuild($player);
                            $name = $sender->getName();
                            $this->plugin->db->query("DELETE FROM master WHERE player='$name';");
                            $sender->sendMessage("You successfully left $guild", true);

                            $this->plugin->subtractGuildsPoints($guild, $this->plugin->settings->get("PointGainedPerPlayerInGuild"));
                            $this->plugin->updateTag($sender->getName());
                        } else {
                            $sender->sendMessage("You must delete the guilds or give guildmasterhip to someone else first");
                        }
                    }

                    /////////////////////////////// MEMBERS/SecondInCommandsS/guildsmasters AND THEIR STATUSES ///////////////////////////////
                    if (strtolower($args[0] == "m")) {
                        if (!$this->plugin->isInGuilds($player)) {
                            $sender->sendMessage("You must be in a guilds to do this");
                            return true;
                        }
                        $this->plugin->getPlayersInGuildsByRank($sender, $this->plugin->getPlayerGuild($player), "Member");
                    }
                    if (strtolower($args[0] == "mof")) {
                        if (!isset($args[1])) {
                            $sender->sendMessage("Usage: /guilds mof <guilds>");
                            return true;
                        }
                        if (!$this->plugin->guildsExists($args[1])) {
                            $sender->sendMessage("The requested guilds doesn't exist");
                            return true;
                        }
                        $this->plugin->getPlayersInGuildsByRank($sender, $args[1], "Member");
                    }
                    if (strtolower($args[0] == "sic")) {
                        if (!$this->plugin->isInGuilds($player)) {
                            $sender->sendMessage("You must be in a guilds to do this");
                            return true;
                        }
                        $this->plugin->getPlayersInGuildsByRank($sender, $this->plugin->getPlayerGuild($player), "SecondInCommands");
                    }
                    if (strtolower($args[0] == "sicof")) {
                        if (!isset($args[1])) {
                            $sender->sendMessage("Usage: /guilds sicof <guilds>");
                            return true;
                        }
                        if (!$this->plugin->guildsExists($args[1])) {
                            $sender->sendMessage("The requested guilds doesn't exist");
                            return true;
                        }
                        $this->plugin->getPlayersInGuildsByRank($sender, $args[1], "SecondInCommands");
                    }
                    if (strtolower($args[0] == "ourgm")) {
                        if (!$this->plugin->isInGuilds($player)) {
                            $sender->sendMessage("You must be in a guilds to do this");
                            return true;
                        }
                        $this->plugin->getPlayersInGuildsByRank($sender, $this->plugin->getPlayerGuild($player), "guildsmasters");
                    }
                    if (strtolower($args[0] == "gmof")) {
                        if (!isset($args[1])) {
                            $sender->sendMessage("Usage: /guilds gmof <guilds>");
                            return true;
                        }
                        if (!$this->plugin->guildsExists($args[1])) {
                            $sender->sendMessage("The requested guilds doesn't exist");
                            return true;
                        }
                        $this->plugin->getPlayersInGuildsByRank($sender, $args[1], "guildsmasters");
                    }


                    /////// ?????CHAT??? //////
                    if (strtolower($args[0] == "say")) {
                        if (!($this->plugin->isInGuilds($player))) {

                            $sender->sendMessage("You must be in a guilds to send guild messages");
                            return true;
                        }
                        $r = count($args);
                        $row = array();
                        $rank = "Member";
                        $f = $this->plugin->getPlayerGuild($player);

                        if ($this->plugin->isSecondInCommands($player)) {
                            $rank = "SecondInCommands";
                        }else if($this->plugin->isMember($player)){
                            $rank = "Member";        
                        } else if($this->plugin->isGuildsMaster($player)) {
                            $rank = "GuildsMasters";
                        }
                        $message = " ";
                        for ($i = 0; $i < $r - 1; $i = $i + 1) {
                            $message = $message . $args[$i + 1] . " ";
                        }
                        $result = $this->plugin->db->query("SELECT * FROM master WHERE guild='$f';");
                        for ($i = 0; $resultArr = $result->fetchArray(SQLITE3_ASSOC); $i = $i + 1) {
                            $row[$i]['player'] = $resultArr['player'];
                            $p = $this->plugin->getServer()->getPlayerExact($row[$i]['player']);
                            if ($p instanceof Player) {
                                $p->sendMessage(TextFormat::ITALIC . TextFormat::RED . "" . TextFormat::AQUA . " <$rank> " . TextFormat::GREEN . "<$player> " . "-> " .TextFormat::ITALIC . TextFormat::DARK_AQUA . $message .  TextFormat::RESET);
  
                            }
                        }
                    }


                    ////////////////////////////// Alliance SYSTEM ////////////////////////////////
                    if (strtolower($args[0] == "nemisyswith")) {
                        if (!isset($args[1])) {
                            $sender->sendMessage("Usage: /guilds nemisyswith <guilds>");
                            return true;
                        }
                        if (!$this->plugin->isInGuilds($player)) {
                            $sender->sendMessage("You must be in a guilds to do this");
                            return true;
                        }
                        if (!$this->plugin->isGuildsMaster($player)) {
                            $sender->sendMessage("You must be the guildsmasters to do this");
                            return true;
                        }
                        if (!$this->plugin->guildsExists($args[1])) {
                            $sender->sendMessage("The requested guilds doesn't exist");
                            return true;
                        }
                        if ($this->plugin->getPlayerGuild($player) == $args[1]) {
                            $sender->sendMessage("Your guilds can not nemisys with itself");
                            return true;
                        }
                        if ($this->plugin->isNemisys($this->plugin->getPlayerGuild($player), $args[1])) {
                            $sender->sendMessage("Your guilds is already nemisys with $args[1]");
                            return true;
                        }
                        $fac = $this->plugin->getPlayerGuild($player);
                        $guildsmasters = $this->plugin->getServer()->getPlayerExact($this->plugin->getGuildsMaster($args[1]));
                        $this->plugin->updateNemisys($fac);
                        $this->plugin->updateNemisys($args[1]);

                        if (!($guildsmasters instanceof Player)) {
                            $sender->sendMessage("The guildsmasters of the requested guilds is offline");
                            return true;
                        }
                        if ($this->plugin->getNemisysCount($args[1]) >= $this->plugin->getNemisysLimit()) {
                            $sender->sendMessage("The requested guilds has the maximum amount of nemisys", false);
                            return true;
                        }
                        if ($this->plugin->getNemisysCount($fac) >= $this->plugin->getNemisysLimit()) {
                            $sender->sendMessage("Your guilds has the maximum amount of nemisys", false);
                            return true;
                        }
                        $stmt = $this->plugin->db->prepare("INSERT OR REPLACE INTO nemisys (player, guild, requestedby, timestamp) VALUES (:player, :guild, :requestedby, :timestamp);");
                        $stmt->bindValue(":player", $guildsmasters->getName());
                        $stmt->bindValue(":guild", $args[1]);
                        $stmt->bindValue(":requestedby", $sender->getName());
                        $stmt->bindValue(":timestamp", time());
                        $result = $stmt->execute();
                        $sender->sendMessage("You requested to Nemisys with $args[1]!\nWait for the guildsmasters's response...", true);
                        $guildsmasters->sendMessage("The guildsmasters of $fac requested an alliance.\nType /guilds nemisysok to accept or /guilds nemisysno to deny.", true);
                        $this->plugin->setNemisys($fac, $args[1]);

                        $sender->sendMessage("You are now nemisys with $args[1]!", true);
                        $guildsmasters->sendMessage("The guildsmasters of $fac has declared your guilds as an enemy", true);
                    }
                    if (strtolower($args[0] == "nemisysok")) {
                        if (!$this->plugin->isInGuilds($player)) {
                            $sender->sendMessage("You must be in a guilds to do this");
                            return true;
                        }
                        if (!$this->plugin->isGuildsMaster($player)) {
                            $sender->sendMessage("You must be a guildsmasters to do this");
                            return true;
                        }
                        $lowercaseName = ($player);
                        $result = $this->plugin->db->query("SELECT * FROM nemisys WHERE player='$lowercaseName';");
                        $array = $result->fetchArray(SQLITE3_ASSOC);
                        if (empty($array) == true) {
                            $sender->sendMessage("Your guilds has not been requested to nemisys with any guilds");
                            return true;
                        }
                        $nemisysTime = $array["timestamp"];
                        $currentTime = time();
                        if (($currentTime - $nemisysTime) <= 60) { //This should be configurable
                            $requested_fac = $this->plugin->getPlayerGuild($array["requestedby"]);
                            $sender_fac = $this->plugin->getPlayerGuild($player);
                            $this->plugin->setNemisys($requested_fac, $sender_fac);
                            $this->plugin->setNemisys($sender_fac, $requested_fac);
                            $this->plugin->db->query("DELETE FROM nemisys WHERE player='$lowercaseName';");
                            $this->plugin->updateNemisys($requested_fac);
                            $this->plugin->updateNemisys($sender_fac);
                            $sender->sendMessage("Your guilds has successfully nemisys with $requested_fac", true);
                            $this->plugin->getServer()->getPlayerExact($array["requestedby"])->sendMessage("$player from $sender_fac has accepted the nemisys!", true);
                        } else {
                            $sender->sendMessage("Request has timed out");
                            $this->plugin->db->query("DELETE * FROM alliance WHERE player='$lowercaseName';");
                        }
                    }
                    if (strtolower($args[0]) == "nemisysno") {
                        if (!$this->plugin->isInGuilds($player)) {
                            $sender->sendMessage("You must be in a guilds to do this");
                            return true;
                        }
                        if (!$this->plugin->isGuildsMaster($player)) {
                            $sender->sendMessage("You must be a guildsmasters to do this");
                            return true;
                        }
                        $lowercaseName = ($player);
                        $result = $this->plugin->db->query("SELECT * FROM nemisys WHERE player='$lowercaseName';");
                        $array = $result->fetchArray(SQLITE3_ASSOC);
                        if (empty($array) == true) {
                            $sender->sendMessage("Your guilds has not been requested to nemisys with any guilds");
                            return true;
                        }
                        $nemisysTime = $array["timestamp"];
                        $currentTime = time();
                        if (($currentTime - $nemisysTime) <= 60) { //This should be configurable
                            $requested_fac = $this->plugin->getPlayerGuild($array["requestedby"]);
                            $sender_fac = $this->plugin->getPlayerGuild($player);
                            $this->plugin->db->query("DELETE FROM nemisys WHERE player='$lowercaseName';");
                            $sender->sendMessage("Your guilds has successfully declined the nemisys request.", true);
                            $this->plugin->getServer()->getPlayerExact($array["requestedby"])->sendMessage("$player from $sender_fac has declined the nemisys!");
                        } else {
                            $sender->sendMessage("Request has timed out");
                            $this->plugin->db->query("DELETE * FROM nemisys WHERE player='$lowercaseName';");
                        }
                    }
                    if (strtolower($args[0] == "breaknemisyswith")) {
                        if (!isset($args[1])) {
                            $sender->sendMessage("Usage: /guilds breaknemisyswith <guilds>");
                            return true;
                        }
                        if (!$this->plugin->isInGuilds($player)) {
                            $sender->sendMessage("You must be in a guilds to do this");
                            return true;
                        }
                        if (!$this->plugin->isGuildsMaster($player)) {
                            $sender->sendMessage("You must be the guildsmasters to do this");
                            return true;
                        }
                        if (!$this->plugin->guildsExists($args[1])) {
                            $sender->sendMessage("The requested guilds doesn't exist");
                            return true;
                        }
                        if ($this->plugin->getPlayerGuild($player) == $args[1]) {
                            $sender->sendMessage("Your guilds can not break nemisys with itself");
                            return true;
                        }
                        if (!$this->plugin->areNemisys($this->plugin->getPlayerGuild($player), $args[1])) {
                            $sender->sendMessage("Your guilds is not allied with $args[1]");
                            return true;
                        }

                        $fac = $this->plugin->getPlayerGuild($player);
                        $guildsmasters = $this->plugin->getServer()->getPlayerExact($this->plugin->getGuildsMaster($args[1]));
                        $this->plugin->deleteNemisys($fac, $args[1]);
                        $this->plugin->deleteNemisys($args[1], $fac);
                        $this->plugin->updateNemisys($fac);
                        $this->plugin->updateNemisys($args[1]);
                        $sender->sendMessage("Your guild $fac is no longer nemisys with $args[1]", true);
                        if ($guildsmasters instanceof Player) {
                            $guildsmasters->sendMessage("The guildsmasters of $fac broke the nemisys with your guilds $args[1]", false);
                        }
                    }
                    if (strtolower($args[0] == "nemisys")) {
                        if (!isset($args[1])) {
                            if (!$this->plugin->isInGuilds($player)) {
                                $sender->sendMessage("You must be in a guilds to do this");
                                return true;
                            }
                            $this->plugin->updateNemisys($this->plugin->getPlayerGuild($player));
                            $this->plugin->getAllNemisys($sender, $this->plugin->getPlayerGuild($player));
                        } else {
                            if (!$this->plugin->guildsExists($args[1])) {
                                $sender->sendMessage("The requested guilds doesn't exist");
                                return true;
                            }
                            $this->plugin->updateNemisys($args[1]);
                            $this->plugin->getAllNemisys($sender, $args[1]);
                        }
                    }

                    if (strtolower($args[0] == "Alliancewith")) {
                        if (!isset($args[1])) {
                            $sender->sendMessage("Usage: /guilds Alliancewith <guilds>");
                            return true;
                        }
                        if (!$this->plugin->isInGuilds($player)) {
                            $sender->sendMessage("You must be in a guilds to do this");
                            return true;
                        }
                        if (!$this->plugin->isGuildsMaster($player)) {
                            $sender->sendMessage("You must be the guildsmasters to do this");
                            return true;
                        }
                        if (!$this->plugin->guildsExists($args[1])) {
                            $sender->sendMessage("The requested guilds doesn't exist");
                            return true;
                        }
                        if ($this->plugin->getPlayerGuild($player) == $args[1]) {
                            $sender->sendMessage("Your guilds can not Alliance with itself");
                            return true;
                        }
                        if ($this->plugin->areAlliance($this->plugin->getPlayerGuild($player), $args[1])) {
                            $sender->sendMessage("Your guilds is already allied with $args[1]");
                            return true;
                        }
                        $fac = $this->plugin->getPlayerGuild($player);
                        $guildsmasters = $this->plugin->getServer()->getPlayerExact($this->plugin->getGuildsMaster($args[1]));
                        $this->plugin->updateAlliance($fac);
                        $this->plugin->updateAlliance($args[1]);

                        if (!($guildsmasters instanceof Player)) {
                            $sender->sendMessage("The guildsmasters of the requested guilds is offline");
                            return true;
                        }
                        if ($this->plugin->getAllianceCount($args[1]) >= $this->plugin->getAllianceLimit()) {
                            $sender->sendMessage("The requested guilds has the maximum amount of alliance", false);
                            return true;
                        }
                        if ($this->plugin->getAllianceCount($fac) >= $this->plugin->getAllianceLimit()) {
                            $sender->sendMessage("Your guilds has the maximum amount of alliance", false);
                            return true;
                        }
                        $stmt = $this->plugin->db->prepare("INSERT OR REPLACE INTO alliance (player, guild, requestedby, timestamp) VALUES (:player, :guild, :requestedby, :timestamp);");
                        $stmt->bindValue(":player", $guildsmasters->getName());
                        $stmt->bindValue(":guild", $args[1]);
                        $stmt->bindValue(":requestedby", $sender->getName());
                        $stmt->bindValue(":timestamp", time());
                        $result = $stmt->execute();
                        $sender->sendMessage("You requested to Alliance with $args[1]!\nWait for the guildsmasters's response...", true);
                        $guildsmasters->sendMessage("The guildsmasters of $fac requested an alliance.\nType /guilds Allianceok to accept or /guilds Allianceno to deny.", true);
                    }
                    if (strtolower($args[0] == "breakalliancewith")) {
                        if (!isset($args[1])) {
                            $sender->sendMessage("Usage: /guilds breakalliancewith <guilds>");
                            return true;
                        }
                        if (!$this->plugin->isInGuilds($player)) {
                            $sender->sendMessage("You must be in a guilds to do this");
                            return true;
                        }
                        if (!$this->plugin->isGuildsMaster($player)) {
                            $sender->sendMessage("You must be the guildsmasters to do this");
                            return true;
                        }
                        if (!$this->plugin->guildsExists($args[1])) {
                            $sender->sendMessage("The requested guilds doesn't exist");
                            return true;
                        }
                        if ($this->plugin->getPlayerGuild($player) == $args[1]) {
                            $sender->sendMessage("Your guilds can not break alliance with itself");
                            return true;
                        }
                        if (!$this->plugin->areAlliance($this->plugin->getPlayerGuild($player), $args[1])) {
                            $sender->sendMessage("Your guilds is not allied with $args[1]");
                            return true;
                        }

                        $fac = $this->plugin->getPlayerGuild($player);
                        $guildsmasters = $this->plugin->getServer()->getPlayerExact($this->plugin->getGuildsMaster($args[1]));
                        $this->plugin->deleteAlliance($fac, $args[1]);
                        $this->plugin->deleteAlliance($args[1], $fac);
                        $this->plugin->subtractGuildsPoints($fac, $this->plugin->settings->get("pointGainedPerAlliance"));
                        $this->plugin->subtractGuildsPoints($args[1], $this->plugin->settings->get("pointGainedPerAlliance"));
                        $this->plugin->updateAlliance($fac);
                        $this->plugin->updateAlliance($args[1]);
                        $sender->sendMessage("Your guild $fac is no longer allied with $args[1]", true);
                        if ($guildsmasters instanceof Player) {
                            $guildsmasters->sendMessage("The guildsmasters of $fac broke the alliance with your guilds $args[1]", false);
                        }

                    }
                    if (strtolower($args[0] == "alliance")) {
                        if (!isset($args[1])) {
                            if (!$this->plugin->isInGuilds($player)) {
                                $sender->sendMessage("You must be in a guilds to do this");
                                return true;
                            }

                            $this->plugin->updateAlliance($this->plugin->getPlayerGuild($player));
                            $this->plugin->getAllAlliance($sender, $this->plugin->getPlayerGuild($player));
                        } else {
                            if (!$this->plugin->guildsExists($args[1])) {
                                $sender->sendMessage("The requested guilds doesn't exist");
                                return true;
                            }
                            $this->plugin->updateAlliance($args[1]);
                            $this->plugin->getAllAlliance($sender, $args[1]);
                        }
                    }
                    if (strtolower($args[0] == "allianceok")) {
                        if (!$this->plugin->isInGuilds($player)) {
                            $sender->sendMessage("You must be in a guilds to do this");
                            return true;
                        }
                        if (!$this->plugin->isGuildsMaster($player)) {
                            $sender->sendMessage("You must be a guildsmasters to do this");
                            return true;
                        }
                        $lowercaseName = ($player);
                        $result = $this->plugin->db->query("SELECT * FROM alliance WHERE player='$lowercaseName';");
                        $array = $result->fetchArray(SQLITE3_ASSOC);
                        if (empty($array) == true) {
                            $sender->sendMessage("Your guilds has not been requested to Alliance with any guilds");
                            return true;
                        }
                        $AllianceTime = $array["timestamp"];
                        $currentTime = time();
                        if (($currentTime - $AllianceTime) <= 60) { //This should be configurable
                            $requested_fac = $this->plugin->getPlayerGuild($array["requestedby"]);
                            $sender_fac = $this->plugin->getPlayerGuild($player);
                            $this->plugin->setAlliance($requested_fac, $sender_fac);
                            $this->plugin->setAlliance($sender_fac, $requested_fac);
                            $this->plugin->addGuildsPoints($sender_fac, $this->plugin->settings->get("pointGainedPerAlliance"));
                            $this->plugin->addGuildsPoints($requested_fac, $this->plugin->settings->get("pointGainedPerAlliance"));
                            $this->plugin->db->query("DELETE FROM alliance WHERE player='$lowercaseName';");
                            $this->plugin->updateAlliance($requested_fac);
                            $this->plugin->updateAlliance($sender_fac);
                            $sender->sendMessage("Your guilds has successfully allied with $requested_fac", true);
                            $this->plugin->getServer()->getPlayerExact($array["requestedby"])->sendMessage("$player from $sender_fac has accepted the alliance!", true);
                        } else {
                            $sender->sendMessage("Request has timed out");
                            $this->plugin->db->query("DELETE * FROM alliance WHERE player='$lowercaseName';");
                        }
                    }
                    if (strtolower($args[0]) == "allianceno") {
                        if (!$this->plugin->isInGuilds($player)) {
                            $sender->sendMessage("You must be in a guilds to do this");
                            return true;
                        }
                        if (!$this->plugin->isGuildsMaster($player)) {
                            $sender->sendMessage("You must be a guildsmasters to do this");
                            return true;
                        }
                        $lowercaseName = ($player);
                        $result = $this->plugin->db->query("SELECT * FROM alliance WHERE player='$lowercaseName';");
                        $array = $result->fetchArray(SQLITE3_ASSOC);
                        if (empty($array) == true) {
                            $sender->sendMessage("Your guilds has not been requested to Alliance with any guilds");
                            return true;
                        }
                        $AllianceTime = $array["timestamp"];
                        $currentTime = time();
                        if (($currentTime - $AllianceTime) <= 60) { //This should be configurable
                            $requested_fac = $this->plugin->getPlayerGuild($array["requestedby"]);
                            $sender_fac = $this->plugin->getPlayerGuild($player);
                            $this->plugin->db->query("DELETE FROM alliance WHERE player='$lowercaseName';");
                            $sender->sendMessage("Your guilds has successfully declined the alliance request.", true);
                            $this->plugin->getServer()->getPlayerExact($array["requestedby"])->sendMessage("$player from $sender_fac has declined the alliance!");
                        } else {
                            $sender->sendMessage("Request has timed out");
                            $this->plugin->db->query("DELETE * FROM alliance WHERE player='$lowercaseName';");
                        }
                    }


///////////////////////////////////////
                    ///////////////EFFFECTS?//////////////////////////
                    $amp = 0;
                    $gpperkill = $this->plugin->settings->get("pointGainedPerKillingAnEnemy");
                    $lvl = array($gpperkill*100,$gpperkill*500,$gpperkill*1000,$gpperkill*5000);
                    if(strtolower($args[0]) == 'setef'){
                        if(!isset($args[1])){
                            $sender->sendMessage("/guilds setef <fast:str:jump:haste:res:life>");
							return true;
                        }
                        if(!$this->plugin->isInGuilds($player)){
                            $sender->sendMessage("You must be in a guilds to use this!");
							return true;
                        }
                        if(!$this->plugin->isGuildsMaster($player)) {
							$sender->sendMessage("You must be guildsmasters to use this.");
							return true;
						}
                        $guildname = $this->plugin->getPlayerGuild($player);
                        $Guildgp = $this->plugin->getGuildsPoints($guildname);
                        $gpperkill = $this->plugin->settings->get("pointGainedPerKillingAnEnemy");
                        if($Guildgp < $lvl[0]){
                            $needed_point = $lvl[0];
							$sender->sendMessage("Your guilds doesn't have enough GuildsPoints to select an effect.");
							$sender->sendMessage("$needed_point GuildsPoints is required but your guilds has only $Guildgp GuildsPoints.");
							return true;
                        }
                        if(!(in_array(strtolower($args[1]),array("fast","str","jump","haste","res","life")))){
                            $sender->sendMessage("The '$args[1]' mode is not available.");
                            $sender->sendMessage("/guilds setef <fast:str:jump:haste:res:life>");
							return true;
                        }
                        $this->plugin->addEffectTo($this->plugin->getPlayerGuild($player),strtolower($args[1]));
                        $this->plugin->updateTagsAndEffectsOf($guildname);
                        $sender->sendMessage("Successfully updated your guilds's effect.",true);
                        return true;
                    }
                    if(strtolower($args[0]) == 'efinfo'){
                        for($i=0;$i<4;$i++){
                            $s = $i + 1;
                            $sender->sendMessage("Lvl $s effects unlock at $lvl[$i] GuildsPoints",true);
                        }
                        return true;
                    }
                    if(strtolower($args[0]) == 'getef'){
                        if(!$this->plugin->isInGuilds($player)){
                            $sender->sendMessage("You must be in a guilds to use this!");
							return true;
                        }
                        $guildname = $this->plugin->getPlayerGuild($player);
                        $Guildgp = $this->plugin->getGuildsPoints($guildname);
                        if($this->plugin->getEffectOf($guildname) == "none"){
                            $sender->sendMessage("Your guilds's effect is not set. Set it by typing /guilds setef <effect>");
                            return true;
                        }
                        $sender->removeAllEffects();
                        for($i=0;$i<4;$i++){
                            if($Guildgp >= $lvl[$i]){
                                $amp = $i;
                            }
                        }
                        switch($this->plugin->getEffectOf($guildname)){
                            case "fast":
                                $sender->addEffect(Effect::getEffect(1)->setDuration(PHP_INT_MAX)->setAmplifier($amp)->setVisible(false));
                                break;
                            case "str":
                                $sender->addEffect(Effect::getEffect(5)->setDuration(PHP_INT_MAX)->setAmplifier($amp)->setVisible(false));
                                break;
                            case "jump":
                                $sender->addEffect(Eddect::getEffect(8)->setDuration(PHP_INT_MAX)->setAmplifier($amp)->setVisible(false));
                                break;
                            case "haste":
                                $sender->addEffect(Effect::getEffect(3)->setDuration(PHP_INT_MAX)->setAmplifier($amp)->setVisible(false));
                                break;
                            case "res":
                                $sender->addEffect(Effect::getEffect(11)->setDuration(PHP_INT_MAX)->setAmplifier($amp)->setVisible(false));
                                break;
                            case "life":
                                $sender->addEffect(Effect::getEffect(21)->setDuration(PHP_INT_MAX)->setAmplifier($amp)->setVisible(false));
                                break;
                        }  
                        $sender->sendMessage("Enjoy your effect!", true);
                        return true;
                        }







                    /////////////////////////////// ABOUT ///////////////////////////////

                    if (strtolower($args[0] == 'about')) {
                        $sender->sendMessage(TextFormat::GREEN . "§l§b»§r\n eThis Server Using A Guilds System.\n §eBe The Most pointfull Guilds In This Server!\n §eCreate,Join,Destroy A Guilds!\n §eStart Now By Using This Commands! : /guilds help [page]\n§l§b« ");
                        $sender->sendMessage(TextFormat::GOLD . "\n\n§aDevelop By GamerXzavier.");
                    }
                    //Thanks To The original authors Tethered_
                    //Thank To The Supporter
                    //Big Thanks To NeuroBinds Project Corporation For Helping 64% Of The Code!
                }
            }
        } else {
            $this->plugin->getServer()->getLogger()->warning("Please run command in game");
        }
    }

}

    CONST NEED_MORE_COINS_CLAIM = "You Need " . self::BALANCE_COST_CLAIM . "Coins More!";
    CONST CLAIM_COST_MESSAGE = "Error! Can't Claim A Plot! , You Must Have " . self::CLAIM_COST . " Coins To Claim A Plot.\n" . TextFormat::GREEN . self::NEED_MORE_COINS_CLAIM;
	CONST MAP_WIDTH = 48;
	CONST MAP_HEIGHT = 8;
	CONST MAP_HEIGHT_FULL = 17;
	CONST MAP_KEY_CHARS = "\\/#?ç¬£$%=&^ABCDEFGHJKLMNOPQRSTUVWXYZÄÖÜÆØÅ1234567890abcdeghjmnopqrsuvwxyÿzäöüæøåâêîûô";
	CONST MAP_KEY_WILDERNESS = TextFormat::DARK_GRAY . "+";
	CONST MAP_KEY_SEPARATOR = TextFormat::YELLOW . "+";
	CONST MAP_KEY_OVERFLOW = TextFormat::WHITE . "#" . TextFormat::WHITE;
	CONST MAP_OVERFLOW_MESSAGE = self::MAP_KEY_OVERFLOW . ": Too Many Guilds (>" . 107 . ") on this Map.";

/////??MAP??/////

//FINISHED BUT HAVE BUG SO LATER ;-;

    public function onCommand(CommandSender $sender, Command $command, $label, array $args) {
        if ($sender instanceof Player) {
			$player = $sender->getPlayer()->getName();
			$create = $this->plugin->settings->get("CreateCost");
			$claim = $this->plugin->settings->get("ClaimCost");
			$oclaim = $this->plugin->settings->get("OverClaimCost");
			$Alliancer = $this->plugin->settings->get("AllianceCost");
			$Alliancea = $this->plugin->settings->get("AlliancePrice");
			$home = $this->plugin->settings->get("SetHomeCost");
            $player = $sender->getPlayer()->getName();
            if (strtolower($command->getName(self::GUILDS_COMMAND) or $command->getName(self::G_COMMAND))) {
                if (empty($args)) {
                    $sender->sendMessage("Please use /guilds help for a list of commands");
                    return true;
                }

                if (count($args == 2)) {
/*Help Commands*/
                    if (strtolower($args[0]) == "help") {
                        if (!isset($args[1]) || $args[1] == 1) {
                            $sender->sendMessage(TextFormat::GOLD . self::HELP_MESSAGE_ONE);
                            return true;
                        }
                        if ($args[1] == 2) {
                            $sender->sendMessage(TextFormat::GOLD . self::HELP_MESSAGE_TWO);
                            return true;
                        }
                        if ($args[1] == 3) {
                            $sender->sendMessage(TextFormat::GOLD . self::HELP_MESSAGE_THREE);
                            return true;
                        }
                        if ($args[1] == 4) {
                            $sender->sendMessage(TextFormat::GOLD . self::HELP_MESSAGE_FOUR);
                            return true;
                        }
                        if ($args[1] == 5) {
                            $sender->sendMessage(TextFormat::GOLD . self::HELP_MESSAGE_FIVE);
                            return true;

                        }
                        if ($args[1] == 6){
                            $sender->isOP();
                            $sender->sendMessage(Textformat::GOLD. self::HELP_MESSAGE_SIX);
                            return true;
                        }else{
                            $sender->sendMessage(self::ERROR_MESSAGE);
                            return true;
                        }
                    }

                    /////////////////////////////// CREATE ///////////////////////////////

                    if ($args[0] == "create") {
                        if (!isset($args[1])) {
                            $sender->sendMessage("Usage: /guilds create <guilds name>");
                            return true;
                        }
                        if ($this->plugin->isNameBanned($args[1])) {
                            $sender->sendMessage("This name is not allowed");
                            return true;
                        }
                        if ($this->plugin->guildsExists($args[1])) {
                            $sender->sendMessage("The Guilds already exists");
                            return true;
                        }
                        if (strlen($args[1]) > $this->plugin->settings->get("MaxGuildNameLength")) {
                            $sender->sendMessage("That name is too long, please try again the maximum lenght is ". $this->plugin->settings->get("MaxGuildNameLength"));
                            return true;
                        }
                        if ($this->plugin->isInGuilds($sender->getName())) {
                            $sender->sendMessage("You must leave the guilds first");
                            return true;
                        }
                        elseif($r = EconomyAPI::getInstance()->reduceMoney($player, self::CREATE_COST)) {
                            $guildName = $args[1];
							$player = strtolower($player);
                            $rank = "GuildsMaster";
                            $stmt = $this->plugin->db->prepare("INSERT OR REPLACE INTO master (player, guild, rank) VALUES (:player, :guild, :rank);");
                            $stmt->bindValue(":player", $player);
                            $stmt->bindValue(":guild", $guildName);
                            $stmt->bindValue(":rank", $rank);
                            $result = $stmt->execute();
                            $this->plugin->updateAlliance($guildName);
                            $this->plugin->setGuildsPoints($guildName, $this->plugin->settings->get("TheDefaultPowerEveryGuildstartsWith"));
                            $this->plugin->updateTag($sender->getName());
                            $sender->sendMessage("Guilds succesfull created ! use '/guilds desc' now.", true);
							$sender->sendMessage("Guilds successfully created for §6" . self::CREATE_COST , true);
                            return true;
                        } else {
						
						switch($r){
							case EconomyAPI::RET_INVALID:
							
								$sender->sendMessage(self::CREATE_COST_MESSAGE);
								break;
							case EconomyAPI::RET_CANCELLED:
						
								$sender->sendMessage(self::ERROR_MESSAGE);
								break;
							case EconomyAPI::RET_NO_ACCOUNT:
								$sender->sendMessage(self::ERROR_MESSAGE);
								break;
						}
					  }
                    }
                    /////////////////////////////// INFO ///////////////////////////////

                    if (strtolower($args[0]) == 'info') {
                        if (isset($args[1])) {
                            if (!(ctype_alnum($args[1])) | !($this->plugin->guildsExists($args[1]))) {
                                $sender->sendMessage("Guilds does not exist");
                                $sender->sendMessage("Make sure the name of the selected guild is ABSOLUTELY EXACT.");
                                return true;
                            }
                            $guild = $args[1];
                            $result = $this->plugin->db->query("SELECT * FROM motd WHERE guild='$guild';");
                            $array = $result->fetchArray(SQLITE3_ASSOC);
                            $power = $this->plugin->getGuildsPoints($guild);
                            $money = $this->plugin->getGuildMoney($guild);
                            $message = $array["message"];
                            $gm = $this->plugin->getGuildMaster($guild);
                            $numPlayers = $this->plugin->getNumberOfPlayers($guild);
                            $effects = $this->plugin->getEffectOf($guild);
                            $boosters = $this->plugin->getBoosterFrom($guild);
                            $alliancecount = $this->plugin->getAllianceCount($guild);
                            $nemisyscount = $this->plugin->getNemisysCount($guild);
                            $sender->sendMessage(TextFormat::GOLD . "-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-" . TextFormat::RESET);
                            $sender->sendMessage(TextFormat::GOLD . "Information§c: §d$guild" . TextFormat::RESET);
                            $sender->sendMessage(TextFormat::GOLD . "GuildsMaster§c: §d$gm". " §b| " . "§eMember: §d$numPlayers" . TextFormat::RESET);
                            $sender->sendMessage(TextFormat::GOLD . "GuildsPoints : §d$power " . TextFormat::YELLOW . "§b| §eGuildsMoneys : §d$money" . TextFormat::RESET);
                            $sender->sendMessage(TextFormat::GOLD . "Effects : §d$effects" . TextFormat::LIGHT_PURPLE . "§b| §eBoosters : §d$boosters" . TextFormat::RESET);
                            $sender->sendMessage(TextFormat::GOLD . "Alliance : §d$alliancecount " . TextFormat::RED . "§b| Nemisys : §d$nemisyscount" . " " . TextFormat::RESET);
                            $sender->sendMessage(TextFormat::GOLD . "Description §8: " . TextFormat::UNDERLINE . "§d$message" . TextFormat::RESET);
                            $sender->sendMessage(TextFormat::GOLD . "-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-" . TextFormat::RESET);
                        } else {
                            if (!$this->plugin->isInGuilds($player)) {
                                $sender->sendMessage("You must be in a guilds to use this!");
                                return true;
                            }
                            $guild = $this->plugin->getPlayerGuild($sender->getName());
                            $result = $this->plugin->db->query("SELECT * FROM motd WHERE guild='$guild';");
                            $array = $result->fetchArray(SQLITE3_ASSOC);
                            $power = $this->plugin->getGuildsPoints($guild);
                            $money = $this->plugin->getGuildMoney($guild);
                            $message = $array["message"];
                            $gm = $this->plugin->getGuildMaster($guild);
                            $numPlayers = $this->plugin->getNumberOfPlayers($guild);
                            $effects = $this->plugin->getEffectOf($guild);
                            $boosters = $this->plugin->getBoosterFrom($guild);
                            $alliancecount = $this->plugin->getAllianceCount($guild);
                            $nemisyscount = $this->plugin->getNemisysCount($guild);
                            $sender->sendMessage(TextFormat::GOLD . "-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-" . TextFormat::RESET);
                            $sender->sendMessage(TextFormat::GOLD . "Information§c: §d$guild" . TextFormat::RESET);
                            $sender->sendMessage(TextFormat::GOLD . "GuildsMaster§c: §d$gm". " §b| " . "§eMember: §d$numPlayers" . TextFormat::RESET);
                            $sender->sendMessage(TextFormat::GOLD . "GuildsPoints : §d$power " . TextFormat::YELLOW . "§b| §eGuildsMoneys : §d$money" . TextFormat::RESET);
                            $sender->sendMessage(TextFormat::GOLD . "Effects : §d$effects" . TextFormat::LIGHT_PURPLE . "§b| §eBoosters : §d$boosters" . TextFormat::RESET);
                            $sender->sendMessage(TextFormat::GOLD . "Alliance : §d$alliancecount " . TextFormat::RED . "§b| Nemisys : §d$nemisyscount" . " " . TextFormat::RESET);
                            $sender->sendMessage(TextFormat::GOLD . "Description §8: " . TextFormat::UNDERLINE . "§d$message" . TextFormat::RESET);
                            $sender->sendMessage(TextFormat::GOLD . "-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-" . TextFormat::RESET);
                    return true;
                        }
                    }
                    /////??MAP??/////

                  //  FINISHED BUT HAVE BUG SO LATER +-+

                    ///////////////////////////////// WAR /////////////////////////////////

                    if ($args[0] == "war") {
                        if (!isset($args[1])) {
                            $sender->sendMessage("Usage: /guilds war <guilds name:tp>");
                            return true;
                        }
                        if (strtolower($args[1]) == "tp") {
                            foreach ($this->plugin->wars as $r => $f) {
                                $fac = $this->plugin->getPlayerGuild($player);
                                if ($r == $fac) {
                                    $x = mt_rand(0, $this->plugin->getNumberOfPlayers($fac) - 1);
                                    $tper = $this->plugin->war_players[$f][$x];
                                    $sender->teleport($this->plugin->getServer()->getPlayerByName($tper));
                                    return;
                                }
                                if ($f == $fac) {
                                    $x = mt_rand(0, $this->plugin->getNumberOfPlayers($fac) - 1);
                                    $tper = $this->plugin->war_players[$r][$x];
                                    $sender->teleport($this->plugin->getServer()->getPlayer($tper));
                                    return;
                                }
                            }
                            $sender->sendMessage("You must be in a war to do that");
                            return true;
                        }
                        if (!(ctype_alnum($args[1]))) {
                            $sender->sendMessage("You may only use letters and numbers");
                            return true;
                        }
                        if (!$this->plugin->guildsExists($args[1])) {
                            $sender->sendMessage("Guilds does not exist");
                            return true;
                        }
                        if (!$this->plugin->isInGuilds($sender->getName())) {
                            $sender->sendMessage("You must be in a guilds to do this");
                            return true;
                        }
                        if (!$this->plugin->isGuildsMaster($player)) {
                            $sender->sendMessage("Only your guilds leader may start wars");
                            return true;
                        }
                        if (!$this->plugin->isNemisys($this->plugin->getPlayerGuild($player), $args[1])) {
                            $sender->sendMessage("Your guilds is not an enemy of $args[1]");
                            return true;
                        } else {
                            $guildName = $args[1];
                            $sguild = $this->plugin->getPlayerGuild($player);
                            foreach ($this->plugin->war_req as $r => $f) {
                                if ($r == $args[1] && $f == $sguild) {
                                    foreach ($this->plugin->getServer()->getOnlinePlayers() as $p) {
                                        $task = new guildWar($this->plugin, $r);
                                        $handler = $this->plugin->getServer()->getScheduler()->scheduleDelayedTask($task, 20 * 60 * 2);
                                        $task->setHandler($handler);
                                        $p->sendMessage("The war against $guildName and $sguild has started!");
                                        if ($this->plugin->getPlayerGuild($p->getName()) == $sguild) {
                                            $this->plugin->war_players[$sguild][] = $p->getName();
                                        }
                                        if ($this->plugin->getPlayerGuild($p->getName()) == $guildName) {
                                            $this->plugin->war_players[$guildName][] = $p->getName();
                                        }
                                    }
                                    $this->plugin->wars[$guildName] = $sguild;
                                    unset($this->plugin->war_req[strtolower($args[1])]);
                                    return true;
                                }
                            }
                            $this->plugin->war_req[$sguild] = $guildName;
                            foreach ($this->plugin->getServer()->getOnlinePlayers() as $p) {
                                if ($this->plugin->getPlayerGuild($p->getName()) == $guildName) {
                                    if ($this->plugin->getGuildsMaster($guildName) == $p->getName()) {
                                        $p->sendMessage("$sguild wants to start a war, '/guilds war $sguild' to start!");
                                        $sender->sendMessage("Guilds war requested");
                                        return true;
                                    }
                                }
                            }
                            $sender->sendMessage("Guilds leader is not online.");
                            return true;
                        }
                    }

                    /////////////////////////////// INVITE ///////////////////////////////

                    if ($args[0] == "invite") {
                        if (!isset($args[1])) {
                            $sender->sendMessage("Usage: /guilds invite <player>");
                            return true;
                        }
                        if ($this->plugin->isGuildFull($this->plugin->getPlayerGuild($player))) {
                            $sender->sendMessage("Guilds is full, please kick players to make room");
                            return true;
                        }
                        $invited = $this->plugin->getServer()->getPlayerExact($args[1]);
                        if (!($invited instanceof Player)) {
                            $sender->sendMessage("Player not online");
                            return true;
                        }
                        if ($this->plugin->isInGuilds($invited) == true) {
                            $sender->sendMessage("Player is currently in a guilds");
                            return true;
                        }
                        if ($this->plugin->settings->get("OnlyguildmasterAndSecondInCommandssCanInvite")) {
                            if (!($this->plugin->isSecondInCommands($player) || $this->plugin->isGuildsMaster($player))) {
                                $sender->sendMessage("Only your guilds GuildsMaster/SecondInCommands can invite");
                                return true;
                            }
                        }
                        if ($invited->getName() == $player) {

                            $sender->sendMessage("You can't invite yourself to your own guilds!");
                            return true;
                        }

                        $guildName = $this->plugin->getPlayerGuild($player);
                        $invitedName = $invited->getName();
                        $rank = "Member";

                        $stmt = $this->plugin->db->prepare("INSERT OR REPLACE INTO confirm (player, guild, invitedby, timestamp) VALUES (:player, :guild, :invitedby, :timestamp);");
                        $stmt->bindValue(":player", $invitedName);
                        $stmt->bindValue(":guild", $guildName);
                        $stmt->bindValue(":invitedby", $sender->getName());
                        $stmt->bindValue(":timestamp", time());
                        $result = $stmt->execute();
                        $sender->sendMessage("$invitedName has been invited", true);
                        $invited->sendMessage("You have been invited to $guildName. Type '/guilds accept' or '/guilds deny' into chat to accept or deny!", true);
                    }

                    /////////////////////////////// LEADER ///////////////////////////////

                    if ($args[0] == "leader") {
                        if (!isset($args[1])) {
                            $sender->sendMessage("Usage: /guilds leader <player>");
                            return true;
                        }
                        if (!$this->plugin->isInGuilds($sender->getName())) {
                            $sender->sendMessage("You must be in a guilds to use this");
                            return true;
                        }
                        if (!$this->plugin->isGuildsMaster($player)) {
                            $sender->sendMessage("You must be leader to use this");
                            return true;
                        }
                        if ($this->plugin->getPlayerGuild($player) != $this->plugin->getPlayerGuild($args[1])) {
                            $sender->sendMessage("Add player to guilds first!");
                            return true;
                        }
                        if (!($this->plugin->getServer()->getPlayerExact($args[1]) instanceof Player)) {
                            $sender->sendMessage("Player not online");
                            return true;
                        }
                        if ($args[1] == $sender->getName()) {

                            $sender->sendMessage("You can't transfer the guildmasterhip to yourself");
                            return true;
                        }
                        $guildName = $this->plugin->getPlayerGuild($player);

                        $stmt = $this->plugin->db->prepare("INSERT OR REPLACE INTO master (player, guild, rank) VALUES (:player, :guild, :rank);");
                        $stmt->bindValue(":player", $player);
                        $stmt->bindValue(":guild", $guildName);
                        $stmt->bindValue(":rank", "Member");
                        $result = $stmt->execute();

                        $stmt = $this->plugin->db->prepare("INSERT OR REPLACE INTO master (player, guild, rank) VALUES (:player, :guild, :rank);");
                        $stmt->bindValue(":player", $args[1]);
                        $stmt->bindValue(":guild", $guildName);
                        $stmt->bindValue(":rank", "Leader");
                        $result = $stmt->execute();


                        $sender->sendMessage("You are no longer leader", true);
                        $this->plugin->getServer()->getPlayerExact($args[1])->sendMessage("You are now leader of $guildName!", true);
                        $this->plugin->updateTag($sender->getName());
                        $this->plugin->updateTag($this->plugin->getServer()->getPlayerExact($args[1])->getName());
                    }

                    /////////////////////////////// PROMOTE ///////////////////////////////

                    if ($args[0] == "promote") {
                        if (!isset($args[1])) {
                            $sender->sendMessage("Usage: /guilds promote <player>");
                            return true;
                        }
                        if (!$this->plugin->isInGuilds($sender->getName())) {
                            $sender->sendMessage("You must be in a guilds to use this");
                            return true;
                        }
                        if (!$this->plugin->isGuildsMaster($player)) {
                            $sender->sendMessage("You must be leader to use this");
                            return true;
                        }
                        if ($this->plugin->getPlayerGuild($player) != $this->plugin->getPlayerGuild($args[1])) {
                            $sender->sendMessage("Player is not in this guilds!");
                            return true;
                        }
                        if ($args[1] == $sender->getName()) {
                            $sender->sendMessage("You can't promote yourself!");
                            return true;
                        }

                        if ($this->plugin->isSecondInCommands($args[1])) {
                            $sender->sendMessage("Player is already SecondInCommands!");
                            return true;
                        }
                        $guildName = $this->plugin->getPlayerGuild($player);
                        $stmt = $this->plugin->db->prepare("INSERT OR REPLACE INTO master (player, guild, rank) VALUES (:player, :guild, :rank);");
                        $stmt->bindValue(":player", $args[1]);
                        $stmt->bindValue(":guild", $guildName);
                        $stmt->bindValue(":rank", "SecondInCommands");
                        $result = $stmt->execute();
                        $player = $this->plugin->getServer()->getPlayerExact($args[1]);
                        $sender->sendMessage("$args[1] has been promoted to SecondInCommands", true);

                        if ($player instanceof Player) {
                            $player->sendMessage("You were promoted to SecondInCommands of $guildName!", true);
                            $this->plugin->updateTag($this->plugin->getServer()->getPlayerExact($args[1])->getName());
                            return true;
                        }
                    }

                    /////////////////////////////// DEMOTE ///////////////////////////////

                    if ($args[0] == "demote") {
                        if (!isset($args[1])) {
                            $sender->sendMessage("Usage: /guilds demote <player>");
                            return true;
                        }
                        if ($this->plugin->isInGuilds($sender->getName()) == false) {
                            $sender->sendMessage("You must be in a guilds to use this");
                            return true;
                        }
                        if ($this->plugin->isGuildsMaster($player) == false) {
                            $sender->sendMessage("You must be leader to use this");
                            return true;
                        }
                        if ($this->plugin->getPlayerGuild($player) != $this->plugin->getPlayerGuild($args[1])) {
                            $sender->sendMessage("Player is not in this guilds!");
                            return true;
                        }

                        if ($args[1] == $sender->getName()) {
                            $sender->sendMessage("You can't demote yourself!");
                            return true;
                        }
                        if (!$this->plugin->isSecondInCommands($args[1])) {
                            $sender->sendMessage("Player is already Member!");
                            return true;
                        }
                        $guildName = $this->plugin->getPlayerGuild($player);
                        $stmt = $this->plugin->db->prepare("INSERT OR REPLACE INTO master (player, guild, rank) VALUES (:player, :guild, :rank);");
                        $stmt->bindValue(":player", $args[1]);
                        $stmt->bindValue(":guild", $guildName);
                        $stmt->bindValue(":rank", "Member");
                        $result = $stmt->execute();
                        $player = $this->plugin->getServer()->getPlayerExact($args[1]);
                        $sender->sendMessage("$args[1] has been demoted to Member", true);
                        if ($player instanceof Player) {
                            $player->sendMessage("You were demoted to member of $guildName!", true);
                            $this->plugin->updateTag($this->plugin->getServer()->getPlayerExact($args[1])->getName());
                            return true;
                        }
                    }

                    /////////////////////////////// KICK ///////////////////////////////

                    if ($args[0] == "kick") {
                        if (!isset($args[1])) {
                            $sender->sendMessage("Usage: /guilds kick <player>");
                            return true;
                        }
                        if ($this->plugin->isInGuilds($sender->getName()) == false) {
                            $sender->sendMessage("You must be in a guilds to use this!");
                            return true;
                        }
                        if ($this->plugin->isGuildsMaster($player) == false) {
                            $sender->sendMessage("You must be leader to use this!");
                            return true;
                        }
                        if ($this->plugin->getPlayerGuild($player) != $this->plugin->getPlayerGuild($args[1])) {
                            $sender->sendMessage("Player is not in this guilds!");
                            return true;
                        }
                        if ($args[1] == $sender->getName()) {
                            $sender->sendMessage("You can't kick yourself!");
                            return true;
                        }
                        $kicked = $this->plugin->getServer()->getPlayerExact($args[1]);
                        $guildName = $this->plugin->getPlayerGuild($player);
                        $this->plugin->db->query("DELETE FROM master WHERE player='$args[1]';");
                        $sender->sendMessage("You successfully kicked $args[1]", true);
                        $this->plugin->subtractGuildsPoints($guildName, $this->plugin->settings->get("PowerGainedPerPlayerInguild"));

                        if ($kicked instanceof Player) {
                            $kicked->sendMessage("You have been kicked from $guildName", true);
                            $this->plugin->updateTag($this->plugin->getServer()->getPlayerExact($args[1])->getName());
                            return true;
                        }
                    }

                }
                if (count($args == 1)) {



					/////////////////////////////// CLAIM ///////////////////////////////
					
					if(strtolower($args[0]) == 'claim') {//
						if(!$this->plugin->isInGuilds($player)) {
							$sender->sendMessage("§cYou must be in a guilds to claim");
							return true;
						}
                        if($this->plugin->settings->get("SecondInCommandssCanClaim")){
                            if(!$this->plugin->isGuildsMaster($player) || !$this->plugin->isSecondInCommands($player)) {
							    $sender->sendMessage("§cOnly guildmaster and SecondInCommandss can claim");
							    return true;
						    }
                        } else {
                            if(!$this->plugin->isGuildsMaster($player)) {
							    $sender->sendMessage("§cYou must be leader to use this");
							    return true;
						    }
                        }
                        if (!$this->plugin->isGuildsMaster($player)) {
                            $sender->sendMessage("You must be leader to use this.");
                            return true;
                        }
                        if (!in_array($sender->getPlayer()->getLevel()->getName(), $this->plugin->settings->get("ClaimWorlds"))) {
                            $sender->sendMessage("You can only claim in Guilds Worlds: " . implode(" ", $this->plugin->settings->get("ClaimWorlds")));
                            return true;
                        }
                        
						if($this->plugin->inOwnPlot($sender)) {
							$sender->sendMessage("§aYour guilds has already claimed this area.");
							return true;
						}
						$guild = $this->plugin->getPlayerGuild($sender->getPlayer()->getName());
                        if($this->plugin->getNumberOfPlayers($guild) < $this->plugin->settings->get("PlayersNeededInguildToClaimAPlot")){
                           
                           $needed_players =  $this->plugin->settings->get("PlayersNeededInguildToClaimAPlot") - 
                                               $this->plugin->getNumberOfPlayers($guild);
                           $sender->sendMessage("§bYou need §e$needed_players §bmore players to claim");
				           return true;
                        }
                        if($this->plugin->getGuildsPoints($guild) < $this->plugin->settings->get("PowerNeededToClaimAPlot")){
                            $needed_power = $this->plugin->settings->get("PowerNeededToClaimAPlot");
                            $guild_power = $this->plugin->getGuildsPoints($guild);
							$sender->sendMessage("§3Your guilds doesn't have enough power to claim");
							$sender->sendMessage("§e"."$needed_power" . " §3power is required. Your guilds only has §a$guild_power §3power.");
                            return true;
                        }
						elseif($r = EconomyAPI::getInstance()->reduceMoney($player, $claim)){
						$x = floor($sender->getX());
						$y = floor($sender->getY());
						$z = floor($sender->getZ());
						if($this->plugin->drawPlot($sender, $guild, $x, $y, $z, $sender->getPlayer()->getLevel(), $this->plugin->settings->get("PlotSize")) == false) {
                            
							return true;
						}
                        
						$sender->sendMessage("§bGetting your coordinates...", true);
                        $plot_size = $this->plugin->settings->get("PlotSize");
                        $guild_power = $this->plugin->getGuildsPoints($guild);
						$sender->sendMessage("§aLand successfully claimed for §6$$claim§a.", true);
					}
					else {
						// $r is an error code
						switch($r){
							case EconomyAPI::RET_INVALID:
								# Invalid $amount
								$sender->sendMessage("§3You do not have enough Money to Claim! Need §6$$claim");
								break;
							case EconomyAPI::RET_CANCELLED:
								# Transaction was cancelled for some reason :/
								$sender->sendMessage(self::ERROR_MESSAGE);
								break;
							case EconomyAPI::RET_NO_ACCOUNT:
								$sender->sendMessage(self::ERROR_MESSAGE);
								break;
						}
					}
					}
                    //position
                    if(strtolower($args[0]) == 'pos'){
                        $x = floor($sender->getX());
						$y = floor($sender->getY());
						$z = floor($sender->getZ());
                        $fac = $this->plugin->guildFromPoint($x,$z);
                        $power = $this->plugin->getGuildsPoints($fac);
                        if(!$this->plugin->isInPlot($sender)){
                            $sender->sendMessage("§bThis area is unclaimed. Use §e/guilds claim §bto claim", true);
							return true;
                        }
                        $sender->sendMessage("§3This plot is claimed by §a$fac §3with §e$power §3power");
                    }
                    
                    if(strtolower($args[0]) == 'overclaim') {
						if(!$this->plugin->isInGuilds($player)) {
							$sender->sendMessage("§cYou must be in a guilds to use this");
							return true;
						}
						if(!$this->plugin->isGuildsMaster($player)) {
							$sender->sendMessage("§cYou must be leader to use this");
							return true;
						}
                        $guild = $this->plugin->getPlayerGuild($player);
						if($this->plugin->getNumberOfPlayers($guild) < $this->plugin->settings->get("PlayersNeededInguildToClaimAPlot")){
                           
                           $needed_players =  $this->plugin->settings->get("PlayersNeededInguildToClaimAPlot") - 
                                               $this->plugin->getNumberOfPlayers($guild);
                           $sender->sendMessage("§3You need §e$needed_players §3more players to overclaim");
				           return true;
                        }
                        if (!in_array($sender->getPlayer()->getLevel()->getName(), $this->plugin->settings->get("ClaimWorlds"))) {
                            $sender->sendMessage("You can only claim in Guilds Worlds: " . implode(" ", $this->plugin->settings->get("ClaimWorlds")));
                            return true;
                        }
                        if($this->plugin->getGuildsPoints($guild) < $this->plugin->settings->get("PowerNeededToClaimAPlot")){
                            $needed_power = $this->plugin->settings->get("PowerNeededToClaimAPlot");
                            $guild_power = $this->plugin->getGuildsPoints($guild);
							$sender->sendMessage("§3Your guilds does not have enough power to claim! Get power by killing players!");
							$sender->sendMessage("§e$needed_power" . "§3 power is required but your guilds only has §e$guild_power §3power");
                            return true;
                        }
						$sender->sendMessage("§bGetting your coordinates...", true);
						$x = floor($sender->getX());
						$y = floor($sender->getY());
						$z = floor($sender->getZ());
                        if($this->plugin->settings->get("EnableOverClaim")){
                            if($this->plugin->isInPlot($sender)){
                                $guild_victim = $this->plugin->guildFromPoint($x,$z);
                                $guild_victim_power = $this->plugin->getGuildsPoints($guild_victim);
                                $guild_ours = $this->plugin->getPlayerGuild($player);
                                $guild_ours_power = $this->plugin->getGuildsPoints($guild_ours);
                                if($this->plugin->inOwnPlot($sender)){
                                    $sender->sendMessage("§aYour guilds has already claimed this land");
                                    return true;
                                } else {
                                    if($guild_ours_power < $guild_victim_power){
                                        $sender->sendMessage("§3Your power level is too low to over claim §b$guild_victim");
                                        return true;
                                    } elseif($r = EconomyAPI::getInstance()->reduceMoney($player, $oclaim))
									   {
                                        $this->plugin->db->query("DELETE FROM plots WHERE guild='$guild_ours';");
                                        $this->plugin->db->query("DELETE FROM plots WHERE guild='$guild_victim';");
                                        $arm = (($this->plugin->settings->get("PlotSize")) - 1) / 2;
                                        $this->plugin->newPlot($guild_ours,$x+$arm,$z+$arm,$x-$arm,$z-$arm);
					                    $sender->sendMessage("§aYour guilds has successfully overclaimed the land of §b$guild_victim §afor §6$$oclaim", true);
                                        return true;
                                    }
									else {
						// $r is an error code
						    switch($r){
							case EconomyAPI::RET_INVALID:
								# Invalid $amount
								$sender->sendMessage("§3You do not have enough Money to Overclaim! Need §6$oclaim");
								break;
							case EconomyAPI::RET_CANCELLED:
								# Transaction was cancelled for some reason :/
								$sender->sendMessage(self::ERROR_MESSAGE);
								break;
							case EconomyAPI::RET_NO_ACCOUNT:
								$sender->sendMessage(self::ERROR_MESSAGE);
								break;
						}
					}
                                    
                                }
                            } else {
                                $sender->sendMessage("§cYou are not in claimed land");
                                return true;
                            }
                        } else {
                            $sender->sendMessage("§cInsufficient permissions");
                            return true;
                        }
                        
					}
                    
					
					/////////////////////////////// UNCLAIM ///////////////////////////////
					
					if(strtolower($args[0]) == "unclaim") {
                        if(!$this->plugin->isInGuilds($sender->getName())) {
							$sender->sendMessage("§cYou must be in a guilds to use this");
							return true;
						}
						if(!$this->plugin->isGuildsMaster($sender->getName())) {
							$sender->sendMessage("§cYou must be leader to use this");
							return true;
						}
						$guild = $this->plugin->getPlayerGuild($sender->getName());
						$this->plugin->db->query("DELETE FROM plots WHERE guild='$guild';");
						$sender->sendMessage("§aLand successfully unclaimed", true);
					}
					/////////////////////////////// SETHOME ///////////////////////////////
					
					if(strtolower($args[0] == "sethome")) {
						if(!$this->plugin->isInGuilds($player)) {
							$sender->sendMessage("§cYou must be in a guilds to do this");
							return true;
						}
                        if (!in_array($sender->getPlayer()->getLevel()->getName(), $this->plugin->settings->get("ClaimWorlds"))) {
                            $sender->sendMessage("You can only claim in Guilds Worlds: " . implode(" ", $this->plugin->settings->get("ClaimWorlds")));
                            return true;
                        }
						if(!$this->plugin->isGuildsMaster($player)) {
							$sender->sendMessage("§cYou must be leader to set home");
							return true;
						}
                        
                        $guild_power = $this->plugin->getGuildsPoints($this->plugin->getPlayerGuild($player));
                        $needed_power = $this->plugin->settings->get("PowerNeededToSetOrUpdateAHome");
                        if($guild_power < $needed_power){
                            $sender->sendMessage("§3Your guilds doesn't have enough power set a home. Get power by killing players!");
                            $sender->sendMessage("§e $needed_power §3power is required to set a home. Your guilds has §e$guild_power §3power.");
							return true;
                        }
						elseif($r = EconomyAPI::getInstance()->reduceMoney($player, $home)){
						$guildName = $this->plugin->getPlayerGuild($sender->getName());
						$stmt = $this->plugin->db->prepare("INSERT OR REPLACE INTO home (guild, x, y, z) VALUES (:guild, :x, :y, :z);");
						$stmt->bindValue(":guild", $guildName);
						$stmt->bindValue(":x", $sender->getX());
						$stmt->bindValue(":y", $sender->getY());
						$stmt->bindValue(":z", $sender->getZ());
						$result = $stmt->execute();
						$sender->sendMessage("Guilds home set for $home Coins", true);
                        }
						else {

						    switch($r){
							case EconomyAPI::RET_INVALID:

								$sender->sendMessage("Error! You Need $home Coins To Set A Home!");
								break;
							case EconomyAPI::RET_CANCELLED:
								$sender->sendMessage(self::ERROR_MESSAGE);
								break;
							case EconomyAPI::RET_NO_ACCOUNT:
								$sender->sendMessage(self::ERROR_MESSAGE);
								break;
						}
					}
					}
					
					/////////////////////////////// UNSETHOME ///////////////////////////////
						
					if(strtolower($args[0] == "unsethome")) {
						if(!$this->plugin->isInGuilds($player)) {
							$sender->sendMessage("§cYou must be in a guilds to do this");
							return true;
						}
						if(!$this->plugin->isGuildsMaster($player)) {
							$sender->sendMessage("§cYou must be leader to unset home");
							return true;
						}
						$guild = $this->plugin->getPlayerGuild($sender->getName());
						$this->plugin->db->query("DELETE FROM home WHERE guild = '$guild';");
						$sender->sendMessage("§aHome unset succeed", true);
					}
					
					/////////////////////////////// HOME ///////////////////////////////
						
					if(strtolower($args[0] == "home")) {
						if(!$this->plugin->isInGuilds($player)) {
							$sender->sendMessage("§cYou must be in a guilds to do this.");
                            return true;
						}
						$guild = $this->plugin->getPlayerGuild($sender->getName());
						$result = $this->plugin->db->query("SELECT * FROM home WHERE guild = '$guild';");
						$array = $result->fetchArray(SQLITE3_ASSOC);

                        if (!in_array($sender->getPlayer()->getLevel()->getName(), $this->plugin->settings->get("ClaimWorlds"))) {
                            $sender->sendMessage("You can only get to home in Guilds Worlds: " . implode(" ", $this->plugin->settings->get("ClaimWorlds")));
                            return true;
                        }

						if(!empty($array)) {
							$sender->getPlayer()->teleport(new Vector3($array['x'], $array['y'], $array['z']));
							$sender->sendMessage("§bTeleported to home.", true);
							return true;
						} else {
							$sender->sendMessage("Guilds home has not been set");
				        }
				    }
                    //TOP10 Leaderboards
                    if (strtolower($args[0]) == 'leaderboards') {
                        $this->plugin->leaderboards($sender);
                    }
                    //force unclaim
                    if(strtolower($args[0] == "forceunclaim")){
                        if(!isset($args[1])){
                            $sender->sendMessage("/guilds forceunclaim <guilds>");
                            return true;
                        }
                        if(!$this->plugin->guildsExists($args[1])) {
							$sender->sendMessage("§cThe requested guilds does not exist");
                            return true;
						}
                        if(!($sender->isOp())) {
							$sender->sendMessage("§cInsufficient permissions");
                            return true;
						}
				        $sender->sendMessage("§bLand of §a$args[1]§b unclaimed");
                        $this->plugin->db->query("DELETE FROM plots WHERE guild='$args[1]';");
                        
                    }
                    //forcedelete
                    if (strtolower($args[0]) == 'forcedelete') {
                        if (!isset($args[1])) {
                            $sender->sendMessage("Usage: /guilds forcedelete <guilds>");
                            return true;
                        }
                        if (!$this->plugin->guildsExists($args[1])) {
                            $sender->sendMessage("The requested guilds doesn't exist.");
                            return true;
                        }
                        if (!($sender->isOp())) {
                            $sender->sendMessage("You must be OP to do this.");
                            return true;
                        }
                        $this->plugin->db->query("DELETE FROM master WHERE guild='$args[1]';");
                        $this->plugin->db->query("DELETE FROM plots WHERE guild='$args[1]';");
                        $this->plugin->db->query("DELETE FROM alliance WHERE guild1='$args[1]';");
                        $this->plugin->db->query("DELETE FROM alliance WHERE guild2='$args[1]';");
                        $this->plugin->db->query("DELETE FROM nemisys WHERE guild1='$args[1]';");
                        $this->plugin->db->query("DELETE FROM nemisys WHERE guild2='$args[1]';");
                        $this->plugin->db->query("DELETE FROM gp WHERE guild='$args[1]';");
                        $this->plugin->db->query("DELETE FROM moneys WHERE guild='$args[1]';");
                        $this->plugin->db->query("DELETE FROM motd WHERE guild='$args[1]';");
                        $this->plugin->db->query("DELETE FROM home WHERE guild='$args[1]';");
                        $this->plugin->db->query("DELETE FROM wp WHERE guild='$args[1]';");
                        $this->plugin->db->query("DELETE FROM effects WHERE guild='$args[1]';");
                        $this->plugin->db->query("DELETE FROM boosters WHERE guild='$args[1]';");
                        $sender->sendMessage("Unwanted guilds was successfully deleted and their guilds plot was unclaimed!", true);
                    }
                    //Add Guilds Points
                    if (strtolower($args[0]) == 'addgp') {
                        if (!isset($args[1]) or ! isset($args[2])) {
                            $sender->sendMessage("Usage: /guilds addgp <guilds> <GuildsPoints>");
                            return true;
                        }
                        if (!$this->plugin->guildsExists($args[1])) {
                            $sender->sendMessage("The requested guilds doesn't exist.");
                            return true;
                        }
                        if (!($sender->isOp())) {
                            $sender->sendMessage("You must be OP to do this.");
                            return true;
                        }
                        $this->plugin->addGuildsPoints($args[1], $args[2]);
                        $sender->sendMessage("Successfully added $args[2] GuildsPoints to $args[1]", true);
                    }
                    if (strtolower($args[0]) == 'addmoney') {
                        if (!isset($args[1]) or ! isset($args[2])) {
                            $sender->sendMessage("Usage: /guilds addmoney <guilds> <GuildsMoneys>");
                            return true;
                        }
                        if (!$this->plugin->guildsExists($args[1])) {
                            $sender->sendMessage("The requested guilds doesn't exist.");
                            return true;
                        }
                        if (!($sender->isOp())) {
                            $sender->sendMessage("You must be OP to do this.");
                            return true;
                        }
                        $this->plugin->addGuildMoney6($args[1], $args[2]);
                        $sender->sendMessage("Successfully added $args[2] GuildsMoneys to $args[1]", true);
                    }
                    //Stalk A player
                    if (strtolower($args[0]) == 'search') {
                        if (!isset($args[1])) {
                            $sender->sendMessage("Usage: /guilds search <player>");
                            return true;
                        }
                        if (!$this->plugin->isInGuilds($args[1])) {
                            $sender->sendMessage("The selected player is not in a guilds  or doesn't exist.");
                            $sender->sendMessage("Make sure the name of the selected player is ABSOLUTELY EXACT.");
                            return true;
                        }
                        $guild = $this->plugin->getPlayerGuild($args[1]);
                        $sender->sendMessage("-$args[1] is in $guild-", true);
                    }

                    /////////////////////////////// DESCRIPTION ///////////////////////////////

                    if (strtolower($args[0]) == "desc") {
                        if ($this->plugin->isInGuilds($sender->getName()) == false) {
                            $sender->sendMessage("You must be in a guilds to use this!");
                            return true;
                        }
                        if ($this->plugin->isGuildsMaster($player) == false) {
                            $sender->sendMessage("You must be leader to use this");
                            return true;
                        }
                        $sender->sendMessage("Type your message in chat. It will not be visible to other players", true);
                        $stmt = $this->plugin->db->prepare("INSERT OR REPLACE INTO motdrcv (player, timestamp) VALUES (:player, :timestamp);");
                        $stmt->bindValue(":player", $sender->getName());
                        $stmt->bindValue(":timestamp", time());
                        $result = $stmt->execute();
                    }

                    /////////////////////////////// ACCEPT ///////////////////////////////

                    if (strtolower($args[0]) == "accept") {
                        $player = $sender->getName();
                        $lowercaseName = $player;
                        $result = $this->plugin->db->query("SELECT * FROM confirm WHERE player='$lowercaseName';");
                        $array = $result->fetchArray(SQLITE3_ASSOC);
                        if (empty($array) == true) {
                            $sender->sendMessage("You have not been invited to any guilds");
                            return true;
                        }
                        $invitedTime = $array["timestamp"];
                        $currentTime = time();
                        if (($currentTime - $invitedTime) <= 60) { //This should be configurable
                            $guild = $array["guild"];
                            $stmt = $this->plugin->db->prepare("INSERT OR REPLACE INTO master (player, guild, rank) VALUES (:player, :guild, :rank);");
                            $stmt->bindValue(":player", $player);
                            $stmt->bindValue(":guild", $guild);
                            $stmt->bindValue(":rank", "Member");
                            $result = $stmt->execute();
                            $this->plugin->db->query("DELETE FROM confirm WHERE player='$lowercaseName';");
                            $sender->sendMessage("You successfully joined $guild", true);
                            $this->plugin->addGuildsPoints($guild, $this->plugin->settings->get("PowerGainedPerPlayerInguild"));
                            $this->plugin->getServer()->getPlayerExact($array["invitedby"]);
                            $this->sendMessage("$player joined the guilds", true);
                            $this->plugin->updateTag($sender->getName());
                        } else {
                            $sender->sendMessage("Invite has timed out");
                            $this->plugin->db->query("DELETE * FROM confirm WHERE player='$player';");
                        }
                    }

                    /////////////////////////////// DENY ///////////////////////////////

                    if (strtolower($args[0]) == "deny") {
                        $player = $sender->getName();
                        $lowercaseName = ($player);
                        $result = $this->plugin->db->query("SELECT * FROM confirm WHERE player='$lowercaseName';");
                        $array = $result->fetchArray(SQLITE3_ASSOC);
                        if (empty($array) == true) {
                            $sender->sendMessage("You have not been invited to any guilds");
                            return true;
                        }
                        $invitedTime = $array["timestamp"];
                        $currentTime = time();
                        if (($currentTime - $invitedTime) <= 60) { //This should be configurable
                            $this->plugin->db->query("DELETE FROM confirm WHERE player='$lowercaseName';");
                            $sender->sendMessage("Invite declined", true);
                            $this->plugin->getServer()->getPlayerExact($array["invitedby"])->sendMessage("$player declined the invitation");
                        } else {
                            $sender->sendMessage("Invite has timed out");
                            $this->plugin->db->query("DELETE * FROM confirm WHERE player='$lowercaseName';");
                        }
                    }

                    /////////////////////////////// DELETE ///////////////////////////////

                    if (strtolower($args[0]) == "del") {
                        if ($this->plugin->isInGuilds($player) == true) {
                            if ($this->plugin->isGuildsMaster($player)) {
                                $guild = $this->plugin->getPlayerGuild($player);
                                $this->plugin->db->query("DELETE FROM plots WHERE guild='$guild';");
                                $this->plugin->db->query("DELETE FROM master WHERE guild='$guild';");
                                $this->plugin->db->query("DELETE FROM alliance WHERE guild1='$guild';");
                                $this->plugin->db->query("DELETE FROM alliance WHERE guild2='$guild';");
                                $this->plugin->db->query("DELETE FROM gp WHERE guild='$guild';");
                                $this->plugin->db->query("DELETE FROM motd WHERE guild='$guild';");
                                $this->plugin->db->query("DELETE FROM home WHERE guild='$guild';");
                                $sender->sendMessage("Guilds successfully disbanded.", true);
                                $this->plugin->updateTag($sender->getName());
                            } else {
                                $sender->sendMessage("You are not leader!");
                            }
                        } else {
                            $sender->sendMessage("You are not in a guilds!");
                        }
                    }

                    /////////////////////////////// LEAVE ///////////////////////////////

                    if (strtolower($args[0] == "leave")) {
                        if ($this->plugin->isGuildsMaster($player) == false) {
                            $remove = $sender->getPlayer()->getNameTag();
                            $guild = $this->plugin->getPlayerGuild($player);
                            $name = $sender->getName();
                            $this->plugin->db->query("DELETE FROM master WHERE player='$name';");
                            $sender->sendMessage("You successfully left $guild", true);

                            $this->plugin->subtractGuildsPoints($guild, $this->plugin->settings->get("PowerGainedPerPlayerInguild"));
                            $this->plugin->updateTag($sender->getName());
                        } else {
                            $sender->sendMessage("You must delete the guilds or give guildmasterhip to someone else first");
                        }
                    }

                    /////////////////////////////// MEMBERS/SecondInCommandsS/LEADER AND THEIR STATUSES ///////////////////////////////
                    if (strtolower($args[0] == "members")) {
                        if (!$this->plugin->isInGuilds($player)) {
                            $sender->sendMessage("You must be in a guilds to do this");
                            return true;
                        }
                        $this->plugin->getPlayersInGuildsByRank($sender, $this->plugin->getPlayerGuild($player), "Member");
                    }
                    if (strtolower($args[0] == "membersof")) {
                        if (!isset($args[1])) {
                            $sender->sendMessage("Usage: /guilds membersof <guilds>");
                            return true;
                        }
                        if (!$this->plugin->guildsExists($args[1])) {
                            $sender->sendMessage("The requested guilds doesn't exist");
                            return true;
                        }
                        $this->plugin->getPlayersInGuildsByRank($sender, $args[1], "Member");
                    }
                    if (strtolower($args[0] == "SecondInCommands")) {
                        if (!$this->plugin->isInGuilds($player)) {
                            $sender->sendMessage("You must be in a guilds to do this");
                            return true;
                        }
                        $this->plugin->getPlayersInGuildsByRank($sender, $this->plugin->getPlayerGuild($player), "SecondInCommands");
                    }
                    if (strtolower($args[0] == "SecondInCommandsof")) {
                        if (!isset($args[1])) {
                            $sender->sendMessage("Usage: /guilds SecondInCommandsof <guilds>");
                            return true;
                        }
                        if (!$this->plugin->guildsExists($args[1])) {
                            $sender->sendMessage("The requested guilds doesn't exist");
                            return true;
                        }
                        $this->plugin->getPlayersInGuildsByRank($sender, $args[1], "SecondInCommands");
                    }
                    if (strtolower($args[0] == "ourguildmaster")) {
                        if (!$this->plugin->isInGuilds($player)) {
                            $sender->sendMessage("You must be in a guilds to do this");
                            return true;
                        }
                        $this->plugin->getPlayersInGuildsByRank($sender, $this->plugin->getPlayerGuild($player), "Leader");
                    }
                    if (strtolower($args[0] == "guildmasterof")) {
                        if (!isset($args[1])) {
                            $sender->sendMessage("Usage: /guilds guildmasterof <guilds>");
                            return true;
                        }
                        if (!$this->plugin->guildsExists($args[1])) {
                            $sender->sendMessage("The requested guilds doesn't exist");
                            return true;
                        }
                        $this->plugin->getPlayersInGuildsByRank($sender, $args[1], "Leader");
                    }
                    if (strtolower($args[0] == "say")) {
                        if (!($this->plugin->isInGuilds($player))) {

                            $sender->sendMessage("You must be in a guilds to send guild messages");
                            return true;
                        }
                        $r = count($args);
                        $row = array();
                        $rank = "Member";
                        $f = $this->plugin->getPlayerGuild($player);

                        if ($this->plugin->isSecondInCommands($player)) {
                            $rank = "SecondInCommands";
                        } else if ($this->plugin->isGuildsMaster($player)) {
                            $rank = "Leader";
                        }
                        $message = " ";
                        for ($i = 0; $i < $r - 1; $i = $i + 1) {
                            $message = $message . $args[$i + 1] . " ";
                        }
                        $result = $this->plugin->db->query("SELECT * FROM master WHERE guild='$f';");
                        for ($i = 0; $resultArr = $result->fetchArray(SQLITE3_ASSOC); $i = $i + 1) {
                            $row[$i]['player'] = $resultArr['player'];
                            $p = $this->plugin->getServer()->getPlayerExact($row[$i]['player']);
                            if ($p instanceof Player) {
                                $p->sendMessage(TextFormat::ITALIC . TextFormat::RED . "" . TextFormat::AQUA . " <$rank> " . TextFormat::GREEN . "<$player> " . "-> " .TextFormat::ITALIC . TextFormat::DARK_AQUA . $message .  TextFormat::RESET);
  
                            }
                        }
                    }


                    ////////////////////////////// Alliance SYSTEM ////////////////////////////////
                    if (strtolower($args[0] == "enemywith")) {
                        if (!isset($args[1])) {
                            $sender->sendMessage("Usage: /guilds enemywith <guilds>");
                            return true;
                        }
                        if (!$this->plugin->isInGuilds($player)) {
                            $sender->sendMessage("You must be in a guilds to do this");
                            return true;
                        }
                        if (!$this->plugin->isGuildsMaster($player)) {
                            $sender->sendMessage("You must be the leader to do this");
                            return true;
                        }
                        if (!$this->plugin->guildsExists($args[1])) {
                            $sender->sendMessage("The requested guilds doesn't exist");
                            return true;
                        }
                        if ($this->plugin->getPlayerGuild($player) == $args[1]) {
                            $sender->sendMessage("Your guilds can not enemy with itself");
                            return true;
                        }
                        if ($this->plugin->areAlliance($this->plugin->getPlayerGuild($player), $args[1])) {
                            $sender->sendMessage("Your guilds is already enemied with $args[1]");
                            return true;
                        }
                        $fac = $this->plugin->getPlayerGuild($player);
                        $leader = $this->plugin->getServer()->getPlayerExact($this->plugin->getGuildsMaster($args[1]));

                        if (!($leader instanceof Player)) {
                            $sender->sendMessage("The leader of the requested guilds is offline");
                            return true;
                        }
                        $this->plugin->setNemisys($fac, $args[1]);
                        $sender->sendMessage("You are now nemisys with $args[1]!", true);
                        $leader->sendMessage("The leader of $fac has declared your guilds as an enemy", true);
                    }
                    if (strtolower($args[0] == "Alliancewith")) {
                        if (!isset($args[1])) {
                            $sender->sendMessage("Usage: /guilds Alliancewith <guilds>");
                            return true;
                        }
                        if (!$this->plugin->isInGuilds($player)) {
                            $sender->sendMessage("You must be in a guilds to do this");
                            return true;
                        }
                        if (!$this->plugin->isGuildsMaster($player)) {
                            $sender->sendMessage("You must be the leader to do this");
                            return true;
                        }
                        if (!$this->plugin->guildsExists($args[1])) {
                            $sender->sendMessage("The requested guilds doesn't exist");
                            return true;
                        }
                        if ($this->plugin->getPlayerGuild($player) == $args[1]) {
                            $sender->sendMessage("Your guilds can not Alliance with itself");
                            return true;
                        }
                        if ($this->plugin->areAlliance($this->plugin->getPlayerGuild($player), $args[1])) {
                            $sender->sendMessage("Your guilds is already allied with $args[1]");
                            return true;
                        }
                        $fac = $this->plugin->getPlayerGuild($player);
                        $leader = $this->plugin->getServer()->getPlayerExact($this->plugin->getGuildsMaster($args[1]));
                        $this->plugin->updateAlliance($fac);
                        $this->plugin->updateAlliance($args[1]);

                        if (!($leader instanceof Player)) {
                            $sender->sendMessage("The leader of the requested guilds is offline");
                            return true;
                        }
                        if ($this->plugin->getAllianceCount($args[1]) >= $this->plugin->getallianceLimit()) {
                            $sender->sendMessage("The requested guilds has the maximum amount of alliance", false);
                            return true;
                        }
                        if ($this->plugin->getAllianceCount($fac) >= $this->plugin->getallianceLimit()) {
                            $sender->sendMessage("Your guilds has the maximum amount of alliance", false);
                            return true;
                        }
                        $stmt = $this->plugin->db->prepare("INSERT OR REPLACE INTO alliance (player, guild, requestedby, timestamp) VALUES (:player, :guild, :requestedby, :timestamp);");
                        $stmt->bindValue(":player", $leader->getName());
                        $stmt->bindValue(":guild", $args[1]);
                        $stmt->bindValue(":requestedby", $sender->getName());
                        $stmt->bindValue(":timestamp", time());
                        $result = $stmt->execute();
                        $sender->sendMessage("You requested to Alliance with $args[1]!\nWait for the leader's response...", true);
                        $leader->sendMessage("The leader of $fac requested an alliance.\nType /guilds Allianceok to accept or /guilds Allianceno to deny.", true);
                    }
                    if (strtolower($args[0] == "breakalliancewith")) {
                        if (!isset($args[1])) {
                            $sender->sendMessage("Usage: /guilds breakalliancewith <guilds>");
                            return true;
                        }
                        if (!$this->plugin->isInGuilds($player)) {
                            $sender->sendMessage("You must be in a guilds to do this");
                            return true;
                        }
                        if (!$this->plugin->isGuildsMaster($player)) {
                            $sender->sendMessage("You must be the leader to do this");
                            return true;
                        }
                        if (!$this->plugin->guildsExists($args[1])) {
                            $sender->sendMessage("The requested guilds doesn't exist");
                            return true;
                        }
                        if ($this->plugin->getPlayerGuild($player) == $args[1]) {
                            $sender->sendMessage("Your guilds can not break alliance with itself");
                            return true;
                        }
                        if (!$this->plugin->areAlliance($this->plugin->getPlayerGuild($player), $args[1])) {
                            $sender->sendMessage("Your guilds is not allied with $args[1]");
                            return true;
                        }

                        $fac = $this->plugin->getPlayerGuild($player);
                        $leader = $this->plugin->getServer()->getPlayerExact($this->plugin->getGuildsMaster($args[1]));
                        $this->plugin->deleteAlliance($fac, $args[1]);
                        $this->plugin->deleteAlliance($args[1], $fac);
                        $this->plugin->subtractGuildsPoints($fac, $this->plugin->settings->get("PowerGainedPerAlliance"));
                        $this->plugin->subtractGuildsPoints($args[1], $this->plugin->settings->get("PowerGainedPerAlliance"));
                        $this->plugin->updateAlliance($fac);
                        $this->plugin->updateAlliance($args[1]);
                        $sender->sendMessage("Your guild $fac is no longer allied with $args[1]", true);
                        if ($leader instanceof Player) {
                            $leader->sendMessage("The leader of $fac broke the alliance with your guilds $args[1]", false);
                        }

                    }
                    if (strtolower($args[0] == "alliance")) {
                        if (!isset($args[1])) {
                            if (!$this->plugin->isInGuilds($player)) {
                                $sender->sendMessage("You must be in a guilds to do this");
                                return true;
                            }

                            $this->plugin->updateAlliance($this->plugin->getPlayerGuild($player));
                            $this->plugin->getAllalliance($sender, $this->plugin->getPlayerGuild($player));
                        } else {
                            if (!$this->plugin->guildsExists($args[1])) {
                                $sender->sendMessage("The requested guilds doesn't exist");
                                return true;
                            }
                            $this->plugin->updateAlliance($args[1]);
                            $this->plugin->getAllalliance($sender, $args[1]);
                        }
                    }
                    if (strtolower($args[0] == "allianceok")) {
                        if (!$this->plugin->isInGuilds($player)) {
                            $sender->sendMessage("You must be in a guilds to do this");
                            return true;
                        }
                        if (!$this->plugin->isGuildsMaster($player)) {
                            $sender->sendMessage("You must be a leader to do this");
                            return true;
                        }
                        $lowercaseName = ($player);
                        $result = $this->plugin->db->query("SELECT * FROM alliance WHERE player='$lowercaseName';");
                        $array = $result->fetchArray(SQLITE3_ASSOC);
                        if (empty($array) == true) {
                            $sender->sendMessage("Your guilds has not been requested to Alliance with any guilds");
                            return true;
                        }
                        $AllianceTime = $array["timestamp"];
                        $currentTime = time();
                        if (($currentTime - $AllianceTime) <= 60) { //This should be configurable
                            $requested_fac = $this->plugin->getPlayerGuild($array["requestedby"]);
                            $sender_fac = $this->plugin->getPlayerGuild($player);
                            $this->plugin->setAlliance($requested_fac, $sender_fac);
                            $this->plugin->setAlliance($sender_fac, $requested_fac);
                            $this->plugin->addGuildsPoints($sender_fac, $this->plugin->settings->get("PowerGainedPerAlliance"));
                            $this->plugin->addGuildsPoints($requested_fac, $this->plugin->settings->get("PowerGainedPerAlliance"));
                            $this->plugin->db->query("DELETE FROM alliance WHERE player='$lowercaseName';");
                            $this->plugin->updateAlliance($requested_fac);
                            $this->plugin->updateAlliance($sender_fac);
                            $sender->sendMessage("Your guilds has successfully allied with $requested_fac", true);
                            $this->plugin->getServer()->getPlayerExact($array["requestedby"])->sendMessage("$player from $sender_fac has accepted the alliance!", true);
                        } else {
                            $sender->sendMessage("Request has timed out");
                            $this->plugin->db->query("DELETE * FROM alliance WHERE player='$lowercaseName';");
                        }
                    }
                    if (strtolower($args[0]) == "allianceno") {
                        if (!$this->plugin->isInGuilds($player)) {
                            $sender->sendMessage("You must be in a guilds to do this");
                            return true;
                        }
                        if (!$this->plugin->isGuildsMaster($player)) {
                            $sender->sendMessage("You must be a leader to do this");
                            return true;
                        }
                        $lowercaseName = ($player);
                        $result = $this->plugin->db->query("SELECT * FROM alliance WHERE player='$lowercaseName';");
                        $array = $result->fetchArray(SQLITE3_ASSOC);
                        if (empty($array) == true) {
                            $sender->sendMessage("Your guilds has not been requested to Alliance with any guilds");
                            return true;
                        }
                        $AllianceTime = $array["timestamp"];
                        $currentTime = time();
                        if (($currentTime - $AllianceTime) <= 60) { //This should be configurable
                            $requested_fac = $this->plugin->getPlayerGuild($array["requestedby"]);
                            $sender_fac = $this->plugin->getPlayerGuild($player);
                            $this->plugin->db->query("DELETE FROM alliance WHERE player='$lowercaseName';");
                            $sender->sendMessage("Your guilds has successfully declined the alliance request.", true);
                            $this->plugin->getServer()->getPlayerExact($array["requestedby"])->sendMessage("$player from $sender_fac has declined the alliance!");
                        } else {
                            $sender->sendMessage("Request has timed out");
                            $this->plugin->db->query("DELETE * FROM alliance WHERE player='$lowercaseName';");
                        }
                    }


///////////////////////////////////////
                    ///////////////EFFFECTS?//////////////////////////
                    $amp = 0;
                    $gpperkill = $this->plugin->settings->get("PowerGainedPerKillingAnEnemy");
                    $lvl = array($gpperkill*100,$gpperkill*500,$gpperkill*1000,$gpperkill*5000);
                    if(strtolower($args[0]) == 'setef'){
                        if(!isset($args[1])){
                            $sender->sendMessage("/guilds setef <fast:str:jump:haste:res:life>");
							return true;
                        }
                        if(!$this->plugin->isInGuilds($player)){
                            $sender->sendMessage("You must be in a guilds to use this!");
							return true;
                        }
                        if(!$this->plugin->isGuildsMaster($player)) {
							$sender->sendMessage("You must be leader to use this.");
							return true;
						}
                        $guildname = $this->plugin->getPlayerGuild($player);
                        $Guildgp = $this->plugin->getGuildsPoints($guildname);
                        $gpperkill = $this->plugin->settings->get("PowerGainedPerKillingAnEnemy");
                        if($Guildgp < $lvl[0]){
                            $needed_power = $lvl[0];
							$sender->sendMessage("Your guilds doesn't have enough GuildsPoints to select an effect.");
							$sender->sendMessage("$needed_power GuildsPoints is required but your guilds has only $Guildgp GuildsPoints.");
							return true;
                        }
                        if(!(in_array(strtolower($args[1]),array("fast","str","jump","haste","res","life")))){
                            $sender->sendMessage("The '$args[1]' mode is not available.");
                            $sender->sendMessage("/guilds setef <fast:str:jump:haste:res:life>");
							return true;
                        }
                        $this->plugin->addEffectTo($this->plugin->getPlayerGuild($player),strtolower($args[1]));
                        $this->plugin->updateTagsAndEffectsOf($guildname);
                        $sender->sendMessage("Successfully updated your guilds's effect.",true);
                        return true;
                    }
                    if(strtolower($args[0]) == 'efinfo'){
                        for($i=0;$i<4;$i++){
                            $s = $i + 1;
                            $sender->sendMessage("Lvl $s effects unlock at $lvl[$i] GuildsPoints",true);
                        }
                        return true;
                    }
                    if(strtolower($args[0]) == 'getef'){
                        if(!$this->plugin->isInGuilds($player)){
                            $sender->sendMessage("You must be in a guilds to use this!");
							return true;
                        }
                        $guildname = $this->plugin->getPlayerGuild($player);
                        $Guildgp = $this->plugin->getGuildsPoints($guildname);
                        if($this->plugin->getEffectOf($guildname) == "none"){
                            $sender->sendMessage("Your guilds's effect is not set. Set it by typing /guilds setef <effect>");
                            return true;
                        }
                        $sender->removeAllEffects();
                        for($i=0;$i<4;$i++){
                            if($Guildgp >= $lvl[$i]){
                                $amp = $i;
                            }
                        }
                        switch($this->plugin->getEffectOf($guildname)){
                            case "fast":
                                $sender->addEffect(Effect::getEffect(1)->setDuration(PHP_INT_MAX)->setAmplifier($amp)->setVisible(false));
                                break;
                            case "str":
                                $sender->addEffect(Effect::getEffect(5)->setDuration(PHP_INT_MAX)->setAmplifier($amp)->setVisible(false));
                                break;
                            case "jump":
                                $sender->addEffect(Eddect::getEffect(8)->setDuration(PHP_INT_MAX)->setAmplifier($amp)->setVisible(false));
                                break;
                            case "haste":
                                $sender->addEffect(Effect::getEffect(3)->setDuration(PHP_INT_MAX)->setAmplifier($amp)->setVisible(false));
                                break;
                            case "res":
                                $sender->addEffect(Effect::getEffect(11)->setDuration(PHP_INT_MAX)->setAmplifier($amp)->setVisible(false));
                                break;
                            case "life":
                                $sender->addEffect(Effect::getEffect(21)->setDuration(PHP_INT_MAX)->setAmplifier($amp)->setVisible(false));
                                break;
                        }  
                        $sender->sendMessage("Enjoy your effect!", true);
                        return true;
                        }







                    /////////////////////////////// ABOUT ///////////////////////////////

                    if (strtolower($args[0] == 'about')) {
                        $sender->sendMessage(TextFormat::GREEN . "§l§b»§r\n eThis Server Using A Guilds System.\n §eBe The Most Powerfull Guilds In This Server!\n §eCreate,Join,Destroy A Guilds!\n §eStart Now By Using This Commands! : /guilds help [page]\n§l§b« ");
                        $sender->sendMessage(TextFormat::GOLD . "\n\n§aDevelop By GamerXzavier.");
                    }
                    //Thanks To The original authors Tethered_
                    //Thank To The Supporter
                    //Big Thanks To NeuroBinds Project Corporation For Helping 64% Of The Code!
                }
            }
        } else {
            $this->plugin->getServer()->getLogger()->warning("Please run command in game");
        }
    }

}
