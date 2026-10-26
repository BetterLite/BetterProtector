# BetterProtector

**BetterProtector** is a modern, high-performance, and clean area protection plugin for **PocketMine-MP** (BetterLite).

Inspired by the classic *iProtector* plugin by LukeDarling, 
---

## 📦 Code & Package Structure

---

## 🛠️ Commands

Main command: `/area` (aliases: `/bp`, `/betterprotector`, `/guard`)

| Command | Description |
| :--- | :--- |
| `/area help [page]` | Displays available commands and usage instructions. |
| `/area bypass [on\|off]` | Toggle admin bypass mode (allows building/testing areas). |
| `/area wand` | Receive the selection wand tool (wooden axe). |
| `/area pos1 [x y z]` | Set position 1 (using coordinates or current standing position). |
| `/area pos2 [x y z]` | Set position 2 (using coordinates or current standing position). |
| `/area create <name>` | Create a new protected area from the current selection. |
| `/area delete <name>` | Permanently delete an existing protected area. |
| `/area flag <name> [flag] [allow/deny]` | View or modify the flags of an area. |
| `/area whitelist <name> <add\|remove\|list> [player]` | Manage whitelisted members for an area. |
| `/area list [page] [world]` | View a paginated list of created areas with dimensions and world. |
| `/area info [name]` | Show detailed information about an area (or the area you are in). |
| `/area tp <name>` | Teleport to the center of a protected area. |
| `/area priority <name> <priority>` | Set priority for overlapping areas (higher priority takes precedence). |
| `/area msg <name> <enter\|leave\|clear> [msg]` | Set or remove custom entry/exit boundary messages. |
| `/area reload` | Reload configuration files, translations, and area storage. |

---

## 🚩 Available Flags

| Flag | Default Value | Description |
| :--- | :---: | :--- |
| `edit` | `deny` (`false`) | Allows/denies breaking and placing blocks. |
| `touch` | `deny` (`false`) | Allows/denies interacting with chests, doors, trapdoors, buttons, levers, furnaces, etc. |
| `pvp` | `deny` (`false`) | Allows/denies Player vs Player combat. |
| `god` | `deny` (`false`) | God mode: players do not take environmental or mob damage. |
| `explosion` | `deny` (`false`) | Allows/denies block damage caused by explosions (TNT, Creepers). |
| `decay` | `deny` (`false`) | Allows/denies leaf decay. |
| `enter` | `allow` (`true`) | Allows/denies non-whitelisted players from entering the area. |
| `flight` | `allow` (`true`) | Allows/denies player flight within the area. |
| `item-drop` | `allow` (`true`) | Allows/denies dropping items on the ground. |
| `item-pickup` | `allow` (`true`) | Allows/denies picking up items from the ground. |

---

## 🔐 Permissions

- `betterprotector.admin`: Full administrative access to all commands.
- `betterprotector.bypass`: Bypass block edit, interaction, and PvP protections (disabled by default; use `/area bypass` to toggle).
- `betterprotector.command.area`: Permission node for the base `/area` command.
  - `betterprotector.command.area.bypass`
  - `betterprotector.command.area.create`
  - `betterprotector.command.area.delete`
  - `betterprotector.command.area.flag`
  - `betterprotector.command.area.whitelist`
  - `betterprotector.command.area.pos1`
  - `betterprotector.command.area.pos2`
  - `betterprotector.command.area.wand`
  - `betterprotector.command.area.list`
  - `betterprotector.command.area.info`
  - `betterprotector.command.area.tp`
  - `betterprotector.command.area.priority`
  - `betterprotector.command.area.msg`
  - `betterprotector.command.area.reload`

---

## 👥 Authors
- **BetterLite Team**
