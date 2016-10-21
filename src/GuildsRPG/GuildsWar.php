<?php

namespace GuildsRPG;

use pocketmine\scheduler\PluginTask;

class GuildsWar extends PluginTask {
	
	public $plugin;
	public $requester;
	
	public function __construct(GCore $pl, $requester) {
        parent::__construct($pl);
        $this->plugin = $pl;
		$this->requester = $requester;
    }
	
	public function onRun($currentTick) {
		unset($this->plugin->wars[$this->requester]);
		$this->plugin->getServer()->getScheduler()->cancelTask($this->getTaskId());
	}
	
}