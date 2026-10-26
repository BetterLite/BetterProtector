<?php

declare(strict_types=1);

namespace betterlite\betterprotector\libs\commandox\argument;

use betterlite\betterprotector\libs\commandox\CommandArgument;
use betterlite\betterprotector\libs\commandox\exception\ArgumentParseException;
use pocketmine\command\CommandSender;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\network\mcpe\protocol\types\command\CommandParameter;
use function array_slice;
use function count;
use function implode;

class RawTextArgument implements CommandArgument {

    public function __construct(
        private string $name,
        private bool $optional = false
    ) {}

    public function getName(): string {
        return $this->name;
    }

    public function isOptional(): bool {
        return $this->optional;
    }

    public function getTypeName(): string {
        return "rawtext";
    }

    public function getUsageSyntax(): string {
        $syntax = "{$this->name}...:raw";
        return $this->optional ? "[{$syntax}]" : "<{$syntax}>";
    }

    public function parse(array $words, int &$cursor, CommandSender $sender): mixed {
        $count = count($words);
        if ($cursor >= $count) {
            if ($this->optional) {
                return "";
            }
            throw new ArgumentParseException(
                "Missing argument '{$this->name}'.",
                $this->name
            );
        }

        $rest = array_slice($words, $cursor);
        $cursor = $count;

        return implode(" ", $rest);
    }

    public function getSuggestions(CommandSender $sender): array {
        return [];
    }

    public function getNetworkParameter(): ?CommandParameter {
        return CommandParameter::standard(
            $this->name,
            AvailableCommandsPacket::ARG_FLAG_VALID | AvailableCommandsPacket::ARG_TYPE_RAWTEXT,
            0,
            $this->optional
        );
    }
}
