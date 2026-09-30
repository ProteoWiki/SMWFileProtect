<?php

/*
 * SMWFileProtect Class
 *
 * Copyright (C) 2011-2026  Toni Hermoso Pulido <toniher@cau.cat>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA
 */

use MediaWiki\Config\Config;
use MediaWiki\Title\Title;

class SMWNSProtect
{
    /**
     * False if any of $referers is in a namespace that Lockdown does not let $groups read.
     *
     * @param Title[] $referers
     * @param string[] $groups effective groups of the user
     */
    public static function canRead(array $referers, array $groups, Config $config): bool
    {
        if (!$config->has('NamespacePermissionLockdown')) {
            // Lockdown is not loaded
            return true;
        }
        $lockdown = $config->get('NamespacePermissionLockdown');

        foreach ($referers as $referer) {
            $allowed = self::readGroups($lockdown, $referer->getNamespace());
            if (is_array($allowed) && !array_intersect($groups, $allowed)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Groups allowed to read namespace $ns, resolved like Lockdown's
     * Hooks::namespaceGroups(). Null means unrestricted.
     */
    private static function readGroups(array $lockdown, int $ns): ?array
    {
        $groups = $lockdown[$ns]['read'] ?? $lockdown['*']['read'] ?? $lockdown[$ns]['*'] ?? null;
        return $groups === '*' ? null : $groups;
    }
}
