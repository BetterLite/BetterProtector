<?php

declare(strict_types=1);

namespace betterlite\betterprotector\libs\commandox\argument;

use betterlite\betterprotector\libs\commandox\CommandArgument;
use betterlite\betterprotector\libs\commandox\exception\ArgumentParseException;
use pocketmine\command\CommandSender;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\network\mcpe\protocol\types\command\CommandParameter;
use function preg_match;

class IntegerArgument implements CommandArgument {

    public function __construct(
        private string $name,
        private bool $optional = false,
        private ?int $min = null,
        private ?int $max = null
    ) {}

    public function getName(): string {
        return $this->name;
    }

    public function isOptional(): bool {
        return $this->optional;
    }

    public function getTypeName(): string {
        return "int";
    }

    public function getUsageSyntax(): string {
        $syntax = "{$this->name}:int";
        return $this->optional ? "[{$syntax}]" : "<{$syntax}>";
    }

    public function parse(array $words, int &$cursor, CommandSender $sender): mixed {
        if (!isset($words[$cursor])) {
            if ($this->optional) {
                return null;
            }
            throw new ArgumentParseException("Missing integer '{$this->name}'.", $this->name);
        }

        $raw = $words[$cursor];
        $cursor++;

        if (!preg_match('/^-?\d+$/', $raw)) {
            throw new ArgumentParseException("Argument '{$this->name}' must be an integer.", $this->name);
        }

        $value = (int)$raw;

        if ($this->min !== null && $value < $this->min) {
            throw new ArgumentParseException(
                "Minimum value for '{$this->name}' is {$this->min}.",
                $this->name
            );
        }

        if ($this->max !== null && $value > $this->max) {
            throw new ArgumentParseException(
                "Maximum value for '{$this->name}' is {$this->max}.",
                $this->name
            );
        }

        return $value;
    }

    public function getSuggestions(CommandSender $sender): array {
        return [];
    }

    public function getNetworkParameter(): ?CommandParameter {
        return CommandParameter::standard(
            $this->name,
            AvailableCommandsPacket::ARG_FLAG_VALID | AvailableCommandsPacket::ARG_TYPE_INT,
            0,
            $this->optional
        );
    }
}
