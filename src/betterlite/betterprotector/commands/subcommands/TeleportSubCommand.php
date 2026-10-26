<?php

declare(strict_types=1);

namespace betterlite\betterprotector\commands\subcommands;

use betterlite\betterprotector\BetterProtector;
use betterlite\betterprotector\libs\commandox\argument\StringArgument;
use betterlite\betterprotector\libs\commandox\BaseSubCommand;
use betterlite\betterprotector\libs\commandox\CommandContext;
use betterlite\betterprotector\utils\Messages;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\world\Position;
use function strtolower;

/**
 * Teleport subcommand: teleports player to area center.
 * Author: BetterLite Team
 */
class TeleportSubCommand extends BaseSubCommand {

    public function __construct(Plugin $plugin) {
        parent::__construct(
            $plugin,
            "tp",
            "Teleport to the center of a protected area",
            ["teleport", "goto"]
        );
    }

    protected function configure(): void {
        $this->setPermission("betterprotector.command.area.tp");
        $this->registerArgument(0, new StringArgument("name"));
    }

    public function onRun(CommandContext $context): void {
        $sender = $context->getSender();
        if (!($sender instanceof Player)) {
            Messages::send($sender, "error-only-player");
            return;
        }

        $areaName = strtolower((string) $context->getArg("name"));
        $plugin = BetterProtector::getInstance();
        $area = $plugin->getAreaManager()->getArea($areaName);
        if ($area === null) {
            Messages::send($sender, "area-delete-not-found", ["name" => $areaName]);
            return;
        }

        $worldManager = $plugin->getServer()->getWorldManager();
        $worldName = $area->getWorldName();

        if (!$worldManager->isWorldLoaded($worldName)) {
            if (!$worldManager->loadWorld($worldName)) {
                Messages::send($sender, "area-tp-world-not-loaded", ["world" => $worldName]);
                return;
            }
        }

        $world = $worldManager->getWorldByName($worldName);
        if ($world === null) {
            Messages::send($sender, "area-tp-world-not-loaded", ["world" => $worldName]);
            return;
        }

        $center = $area->getBounds()->getCenter();
        $target = new Position($center->getX(), $center->getY(), $center->getZ(), $world);

        $sender->teleport($target);
        Messages::send($sender, "area-tp-success", ["name" => $area->getName()]);
    }
}
