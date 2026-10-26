<?php

declare(strict_types=1);

namespace betterlite\betterprotector\commands\subcommands;

use betterlite\betterprotector\BetterProtector;
use betterlite\betterprotector\libs\commandox\argument\IntegerArgument;
use betterlite\betterprotector\libs\commandox\argument\StringArgument;
use betterlite\betterprotector\libs\commandox\BaseSubCommand;
use betterlite\betterprotector\libs\commandox\CommandContext;
use betterlite\betterprotector\utils\Messages;
use pocketmine\plugin\Plugin;
use function strtolower;

/**
 * Priority subcommand: sets area priority.
 * Author: BetterLite Team
 */
class PrioritySubCommand extends BaseSubCommand {

    public function __construct(Plugin $plugin) {
        parent::__construct(
            $plugin,
            "priority",
            "Set the evaluation priority of an area",
            ["prio"]
        );
    }

    protected function configure(): void {
        $this->setPermission("betterprotector.command.area.priority");
        $this->registerArgument(0, new StringArgument("name"));
        $this->registerArgument(1, new IntegerArgument("priority"));
    }

    public function onRun(CommandContext $context): void {
        $sender = $context->getSender();
        $areaName = strtolower((string) $context->getArg("name"));
        $plugin = BetterProtector::getInstance();
        $area = $plugin->getAreaManager()->getArea($areaName);

        if ($area === null) {
            Messages::send($sender, "area-delete-not-found", ["name" => $areaName]);
            return;
        }

        $priority = (int) $context->getArg("priority");
        $area->setPriority($priority);
        $plugin->getAreaManager()->save();

        Messages::send($sender, "area-priority-updated", [
            "name" => $area->getName(),
            "priority" => $priority
        ]);
    }
}
