<?php

/*
 * SMWFileProtect Class
 *
 * Copyright (C) 2011-2025  Toni Hermoso Pulido <toniher@cau.cat>
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

use MediaWiki\MediaWikiServices;

class SMWNSProtect
{
    private $dbr;
    private $db_page;
    private $db_page_links;

    /**
    * Class init function defining globals
    **/
    public function __construct()
    {
        $this->dbr = MediaWikiServices::getInstance()
        ->getDBLoadBalancer()
        ->getConnection(DB_REPLICA);

        $config = MediaWikiServices::getInstance()->getMainConfig();

        global $SMWFileProtectReferNS;
        if (is_null($SMWFileProtectReferNS)) {
            $SMWFileProtectReferNS = $config->get('SMWFileProtectReferNS');
        }
        global $SMWFileProtectRights;
        if (is_null($SMWFileProtectRights)) {
            $SMWFileProtectRights = $config->get('SMWFileProtectRights');
        }

    }

    /**
     * The main function call to display the backward navigation element for
     * the page  $pageid
     */
    public function executeNSRefer($title, $user)
    {

        global $wgContLang;

        #Get PageID
        $pageid = $title->getArticleID();

        // First we check
        global $SMWFileProtectReferNS;
        if (! $SMWFileProtectReferNS) {
            return true;
        }

        // Allow to group
        if ($this->groupCheck($user)) {
            return true;
        }

        #Get list of linked page namespaces
        $listReferer = array();
        $listReferer = $this->loadListRefererNS($pageid);

        // Namespace permissions Lockdown
        global $wgNamespacePermissionLockdown;

        if (!isset($wgNamespacePermissionLockdown)) {
            // If no Lockdown, true
            return true;
        }

        //Count linked pages
        $nbReferer = count($listReferer);

        if ($nbReferer > 0) {

            foreach ($listReferer as $NSReferer) {
                // Get namespace
                if (array_key_exists($NSReferer, $wgNamespacePermissionLockdown)) {
                    if (array_key_exists("read", $wgNamespacePermissionLockdown[$NSReferer])) {
                        $detect = $this->groupDetect($wgNamespacePermissionLockdown[$NSReferer]["read"], $user->getGroups());
                        if ($detect == 0) {
                            return false;
                        }
                    } else {
                        if (array_key_exists("*", $wgNamespacePermissionLockdown[$NSReferer])) {
                            $detect = $this->groupDetect($wgNamespacePermissionLockdown[$NSReferer]["*"], $user->getGroups());
                            if ($detect == 0) {
                                return false;
                            }
                        }
                    }
                }
            }

            return true;

        } else {
            return true;
        }


    }

    /**
     * Load the referers list for the article $pageid
     */

    private function loadListRefererNS($pageid)
    {

        if (! is_numeric($pageid)) {
            return array();
        }

        $table = array( 'imagelinks', 'page' );
        $vars = array( 'il_from' );
        $conds = array( 'il_to=page_title', 'page_id='.$pageid );
        $options = array();
        $condoptions = array();

        $result = $this->dbr->select($table, $vars, $conds, 'SMWFileProtect::loadListReferer', $options, $condoptions);

        $listReferer = array();
        $i = 0;

        foreach ($result as $row) {
            $title = Title::newFromId($row->il_from);
            if ((get_class($title) == "Title") && ($title->exists())) {
                $listReferer[$i] = $title->getNamespace();
                $i++;
            }
        }

        return(array_unique($listReferer));
    }

    private function groupDetect($lckgrps, $usergrps)
    {

        $detect = 0;

        if (in_array("*", $lckgrps)) {
            $detect = 1;
        } else {
            foreach ($usergrps as $usergrp) {
                if (in_array($usergrp, $lckgrps)) {
                    $detect = 1;
                }
            }

        }

        return $detect;
    }

    /**
     * Function for checking if user is in one of the allowed groups
     */
    private function groupCheck($user)
    {

        global $SMWFileProtectRights;
        foreach ($SMWFileProtectRights as $grp) {
            if (in_array($grp, $user->getGroups())) {
                return true;
            }
        }
    }

}
