<?php

declare(strict_types=1);

namespace Farmero\warps\Commands;

use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\player\Player;
use pocketmine\plugin\PluginOwned;
use pocketmine\plugin\Plugin;

use Farmero\warps\Warps;
use Farmero\warps\Forms\WarpForms;
use Farmero\warps\Task\TeleportationTask;
use Farmero\warps\API\WarpAPI;

class Warp extends Command implements PluginOwned
{
    private $plugin;
    private $warpAPI;

    public function __construct(Warps $plugin, WarpAPI $warpAPI)
    {
        $command = explode(":", Warps::getConfigValue("warp_cmd"));
        parent::__construct($command[0]);
        if (isset($command[1])) $this->setDescription($command[1]);
        $this->setAliases(Warps::getConfigValue("warp_aliases"));
        $this->setPermission("warps.cmd.warp");
        $this->plugin = $plugin;
        $this->warpAPI = $warpAPI;
    }

    public function getOwningPlugin(): Plugin
    {
        return $this->plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if ($sender instanceof Player) {
            $command = explode(":", Warps::getConfigValue("warp_cmd"));
            if ((isset($command[2])) and (Warps::hasPermissionPlayer($sender, $command[2]))) return;
            if ((isset($args[0])) and ($this->warpAPI->existWarp($args[0]))) {
                if (($sender->hasPermission("warps.cmd.warps"))) {
                    $sender->teleport($this->warpAPI->getWarp($args[0]));
                    $sender->sendMessage(Warps::getConfigReplace("warp_msg_teleport"));
                    $player->sendTitle(Warps::getConfigReplace("warp_title_teleport"));
                    $player->sendSubtitle(Warps::getConfigReplace("warp_subtitle_teleport"));
                } else {
                    $sender->getEffects()->add(new EffectInstance(VanillaEffects::BLINDNESS(), 20 * (Warps::getConfigValue("delay") + 2), 10));
                    new TeleportationTask($sender, $args[0]);
                }
            } else {
                if (Warps::getConfigValue("form")) {
                    $warpForms = new WarpForms($this->warpAPI);
                    $sender->sendForm($warpForms->warpForm());
                    return;
                }

                $warps = implode(", ", $this->warpAPI->getAllWarps());
                $sender->sendMessage(Warps::getConfigReplace("warp_msg_list", ["{warp}"], [$warps]));
            }
        }
    }
}
