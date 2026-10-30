<?php

declare(strict_types=1);

namespace betterlite\betterprotector\commands\subcommands;

use betterlite\betterprotector\BetterProtector;
use betterlite\betterprotector\libs\commandox\argument\IntegerArgument;
use betterlite\betterprotector\libs\commandox\BaseSubCommand;
use betterlite\betterprotector\libs\commandox\CommandContext;
use betterlite\betterprotector\utils\Messages;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\world\Position;
use function number_format;

/**
 * Pos1 subcommand: sets position 1.
 * Author: BetterLite Team
 */
class Pos1SubCommand extends BaseSubCommand {

    public function __construct(Plugin $plugin) {
        parent::__construct(
            $plugin,
            "pos1",
            "Set the first position of the area",
            ["1"]
        );
    }

    protected function configure(): void {
        $this->setPermission("betterprotector.command.area.pos1");
        $this->registerArgument(0, new IntegerArgument("x", true));
        $this->registerArgument(1, new IntegerArgument("y", true));
        $this->registerArgument(2, new IntegerArgument("z", true));
    }

    public function onRun(CommandContext $context): void {
        $sender = $context->getSender();
        if (!($sender instanceof Player)) {
            Messages::send($sender, "error-only-player");
            return;
        }

        $world = $sender->getWorld();
        $playerPos = $sender->getPosition();

        $x = $context->getArg("x");
        $y = $context->getArg("y");
        $z = $context->getArg("z");

        // Require all three coordinates or none — avoid silently ignoring partial input.
        $givenCount = ($x !== null ? 1 : 0) + ($y !== null ? 1 : 0) + ($z !== null ? 1 : 0);
        if ($givenCount > 0 && $givenCount < 3) {
            $sender->sendMessage("§cYou must provide all three coordinates (x y z) or none at all.");
            $sender->sendMessage("§eCorrect usage: §f/area pos1 §7or §f/area pos1 <x> <y> <z>");
            return;
        }

        if ($x !== null && $y !== null && $z !== null) {
            $pos = new Position((float) $x, (float) $y, (float) $z, $world);
        } else {
            $pos = new Position($playerPos->getFloorX(), $playerPos->getFloorY(), $playerPos->getFloorZ(), $world);
        }

        $session = BetterProtector::getInstance()->getSessionManager()->getSession($sender);
        $session->setPos1($pos);

        Messages::send($sender, "pos1-set", [
            "x" => $pos->getFloorX(),
            "y" => $pos->getFloorY(),
            "z" => $pos->getFloorZ(),
            "world" => $world->getFolderName()
        ]);

        if ($session->hasCompleteSelection()) {
            $box = $session->getBoundingBox();
            if ($box !== null) {
                Messages::send($sender, "pos-volume", [
                    "width" => $box->getWidth(),
                    "height" => $box->getHeight(),
                    "length" => $box->getLength(),
                    "volume" => number_format($box->getVolume())
                ]);
            }
        }
    }
}
