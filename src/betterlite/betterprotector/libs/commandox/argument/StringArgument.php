<?php

declare(strict_types=1);

namespace betterlite\betterprotector\libs\commandox\argument;

use betterlite\betterprotector\libs\commandox\CommandArgument;
use betterlite\betterprotector\libs\commandox\exception\ArgumentParseException;
use pocketmine\command\CommandSender;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\network\mcpe\protocol\types\command\CommandParameter;

class StringArgument implements CommandArgument {

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
        return "string";
    }

    public function getUsageSyntax(): string {
        $syntax = "{$this->name}:string";
        return $this->optional ? "[{$syntax}]" : "<{$syntax}>";
    }

    public function parse(array $words, int &$cursor, CommandSender $sender): mixed {
        $value = $words[$cursor] ?? "";
        $cursor++;

        if ($value === "" && !$this->optional) {
            throw new ArgumentParseException("Invalid value for '{$this->name}'.", $this->name);
        }

        return $value;
    }

    public function getSuggestions(CommandSender $sender): array {
        return [];
    }

    public function getNetworkParameter(): ?CommandParameter {
        return CommandParameter::standard(
            $this->name,
            AvailableCommandsPacket::ARG_FLAG_VALID | AvailableCommandsPacket::ARG_TYPE_STRING,
            0,
            $this->optional
        );
    }
}
