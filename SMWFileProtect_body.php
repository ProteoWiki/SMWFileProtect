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
use MediaWiki\User\UserIdentity;
use SMW\DIProperty;
use SMW\DIWikiPage;
use SMW\StoreFactory;

class SMWFileProtect
{
    /**
     * False if any of $referers restricts its files through SMW properties:
     * - a SMWFileProtectReferUsers property has values and none is $user;
     * - a SMWFileProtectReferProps property has a value that is not true.
     * A page without values for these properties does not restrict anything.
     *
     * @param Title[] $referers
     */
    public static function canRead(array $referers, UserIdentity $user, Config $config): bool
    {
        $store = StoreFactory::getStore();
        $userPage = Title::makeTitle(NS_USER, $user->getName());

        foreach ($referers as $referer) {
            $subject = DIWikiPage::newFromTitle($referer);

            foreach ($config->get('SMWFileProtectReferUsers') as $label) {
                $values = $store->getPropertyValues($subject, DIProperty::newFromUserLabel($label));
                if ($values && !self::listsUser($values, $userPage)) {
                    return false;
                }
            }

            foreach ($config->get('SMWFileProtectReferProps') as $label) {
                foreach ($store->getPropertyValues($subject, DIProperty::newFromUserLabel($label)) as $value) {
                    if (!($value instanceof SMWDIBoolean) || !$value->getBoolean()) {
                        return false;
                    }
                }
            }
        }
        return true;
    }

    /**
     * Whether $values (pages, or comma-separated page names) include $userPage.
     */
    private static function listsUser(array $values, Title $userPage): bool
    {
        foreach ($values as $value) {
            if ($value instanceof DIWikiPage) {
                $titles = [$value->getTitle()];
            } elseif ($value instanceof SMWDIBlob) {
                $titles = array_map(
                    static fn ($name) => Title::newFromText(trim($name)),
                    explode(',', $value->getString())
                );
            } else {
                continue;
            }
            foreach ($titles as $title) {
                if ($title && $userPage->equals($title)) {
                    return true;
                }
            }
        }
        return false;
    }
}
