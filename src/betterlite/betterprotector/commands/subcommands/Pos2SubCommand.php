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
 * Pos2 subcommand: sets position 2.
 * Author: BetterLite Team
 */
class Pos2SubCommand extends BaseSubCommand {

    public function __construct(Plugin $plugin) {
        parent::__construct(
            $plugin,
            "pos2",
            "Set the second position of the area",
            ["2"]
        );
    }

    protected function configure(): void {
        $this->setPermission("betterprotector.command.area.pos2");
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

        if ($x !== null && $y !== null && $z !== null) {
            $pos = new Position((float) $x, (float) $y, (float) $z, $world);
        } else {
            $pos = new Position($playerPos->getFloorX(), $playerPos->getFloorY(), $playerPos->getFloorZ(), $world);
        }

        $session = BetterProtector::getInstance()->getSessionManager()->getSession($sender);
        $session->setPos2($pos);

        Messages::send($sender, "pos2-set", [
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
