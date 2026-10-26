<?php

declare(strict_types=1);

namespace betterlite\betterprotector\commands\subcommands;

use betterlite\betterprotector\BetterProtector;
use betterlite\betterprotector\libs\commandox\argument\EnumArgument;
use betterlite\betterprotector\libs\commandox\argument\RawTextArgument;
use betterlite\betterprotector\libs\commandox\argument\StringArgument;
use betterlite\betterprotector\libs\commandox\BaseSubCommand;
use betterlite\betterprotector\libs\commandox\CommandContext;
use betterlite\betterprotector\utils\Messages;
use pocketmine\plugin\Plugin;
use function strtolower;
use function trim;

/**
 * Msg subcommand: sets custom entry and exit messages.
 * Author: BetterLite Team
 */
class MsgSubCommand extends BaseSubCommand {

    public function __construct(Plugin $plugin) {
        parent::__construct(
            $plugin,
            "msg",
            "Set custom enter or leave message for an area",
            ["message"]
        );
    }

    protected function configure(): void {
        $this->setPermission("betterprotector.command.area.msg");
        $this->registerArgument(0, new StringArgument("name"));
        $this->registerArgument(1, new EnumArgument("type", ["enter", "leave", "clear"]));
        $this->registerArgument(2, new RawTextArgument("message", true));
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

        $type = strtolower((string) $context->getArg("type"));
        $message = $context->getArg("message");
        $msgStr = $message !== null ? trim((string)$message) : null;

        switch ($type) {
            case "enter":
            case "entry":
                if ($msgStr === null || $msgStr === "") {
                    $sender->sendMessage("§cPlease specify the enter message.");
                    return;
                }
                $area->setEnterMessage($msgStr);
                $plugin->getAreaManager()->save();
                Messages::send($sender, "area-msg-updated", ["type" => "enter", "name" => $area->getName()]);
                break;

            case "leave":
            case "exit":
                if ($msgStr === null || $msgStr === "") {
                    $sender->sendMessage("§cPlease specify the leave message.");
                    return;
                }
                $area->setLeaveMessage($msgStr);
                $plugin->getAreaManager()->save();
                Messages::send($sender, "area-msg-updated", ["type" => "leave", "name" => $area->getName()]);
                break;

            case "clear":
                $area->setEnterMessage(null);
                $area->setLeaveMessage(null);
                $plugin->getAreaManager()->save();
                Messages::send($sender, "area-msg-cleared", ["type" => "enter/leave", "name" => $area->getName()]);
                break;

            default:
                $sender->sendMessage("§cUnknown type. Use enter, leave, or clear.");
                break;
        }
    }
}
