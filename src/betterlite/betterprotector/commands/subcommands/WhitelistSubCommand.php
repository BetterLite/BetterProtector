<?php

declare(strict_types=1);

namespace betterlite\betterprotector\commands\subcommands;

use betterlite\betterprotector\BetterProtector;
use betterlite\betterprotector\libs\commandox\argument\EnumArgument;
use betterlite\betterprotector\libs\commandox\argument\StringArgument;
use betterlite\betterprotector\libs\commandox\BaseSubCommand;
use betterlite\betterprotector\libs\commandox\CommandContext;
use betterlite\betterprotector\utils\Messages;
use pocketmine\plugin\Plugin;
use function count;
use function implode;
use function strtolower;

/**
 * Whitelist subcommand: manages area whitelist.
 * Author: BetterLite Team
 */
class WhitelistSubCommand extends BaseSubCommand {

    public function __construct(Plugin $plugin) {
        parent::__construct(
            $plugin,
            "whitelist",
            "Manage members whitelisted in an area",
            ["wl", "member", "members"]
        );
    }

    protected function configure(): void {
        $this->setPermission("betterprotector.command.area.whitelist");
        $this->registerArgument(0, new StringArgument("area"));
        $this->registerArgument(1, new EnumArgument("action", ["add", "remove", "list"]));
        $this->registerArgument(2, new StringArgument("player", true));
    }

    public function onRun(CommandContext $context): void {
        $sender = $context->getSender();
        $areaName = strtolower((string) $context->getArg("area"));
        $plugin = BetterProtector::getInstance();
        $area = $plugin->getAreaManager()->getArea($areaName);

        if ($area === null) {
            Messages::send($sender, "area-delete-not-found", ["name" => $areaName]);
            return;
        }

        $action = strtolower((string) $context->getArg("action"));
        $target = $context->getArg("player");

        switch ($action) {
            case "list":
                $whitelist = $area->getWhitelist();
                if (count($whitelist) === 0) {
                    Messages::send($sender, "area-whitelist-empty", ["name" => $area->getName()]);
                    return;
                }

                Messages::send($sender, "area-whitelist-list", [
                    "name" => $area->getName(),
                    "list" => implode(", ", $whitelist)
                ]);
                break;

            case "add":
                if ($target === null || (string)$target === "") {
                    $sender->sendMessage("§cUsage: /area whitelist " . $area->getName() . " add <player>");
                    return;
                }

                if ($area->addWhitelisted((string)$target)) {
                    $plugin->getAreaManager()->save();
                    Messages::send($sender, "area-whitelist-added", [
                        "player" => (string)$target,
                        "name" => $area->getName()
                    ]);
                } else {
                    Messages::send($sender, "area-whitelist-already", [
                        "player" => (string)$target,
                        "name" => $area->getName()
                    ]);
                }
                break;

            case "remove":
            case "delete":
            case "del":
                if ($target === null || (string)$target === "") {
                    $sender->sendMessage("§cUsage: /area whitelist " . $area->getName() . " remove <player>");
                    return;
                }

                if ($area->removeWhitelisted((string)$target)) {
                    $plugin->getAreaManager()->save();
                    Messages::send($sender, "area-whitelist-removed", [
                        "player" => (string)$target,
                        "name" => $area->getName()
                    ]);
                } else {
                    Messages::send($sender, "area-whitelist-not-found", [
                        "player" => (string)$target,
                        "name" => $area->getName()
                    ]);
                }
                break;

            default:
                $sender->sendMessage("§cUnknown action. Use add, remove, or list.");
                break;
        }
    }
}
