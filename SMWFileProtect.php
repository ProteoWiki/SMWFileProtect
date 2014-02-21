<?php
/**
 * Copyright (C) 2011 Toni Hermoso Pulido <toniher@cau.cat>
 * http://www.cau.cat
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 */


if ( !defined( 'MEDIAWIKI' ) ) {
	echo "Not a valid entry point";
	exit( 1 );
}

if ( !defined( 'SMW_VERSION' ) ) {
	echo 'This extension requires Semantic MediaWiki to be installed.';
	exit( 1 );
}


$wgAutoloadClasses['SMWFileProtect'] = dirname(__FILE__) . '/SMWFileProtect_body.php';

$SMWFileProtectRights = array("sysop"); // We allow sysops always
$SMWFileProtectReferUsers = array("Has User"); // User pages
$SMWFileProtectReferProps = array("Is Visible"); // Booleans


# Informations
$wgExtensionCredits['other'][] = array(
        'path' => __FILE__,
        'name' => 'SMWFileProtect',
        'author' => 'Toni Hermoso',
        'version' => '0.2',
        'url' => 'https://www.mediawiki.org/wiki/User:Toniher',
        'description' => 'Semantic protection of files',
);

$wgHooks['userCan'][] = 'ImageReferuserCan';


# Refer userCan
function ImageReferuserCan( $title, $user, $action, &$result ) {

	$object = new SMWFileProtect;
	$result = $object->executeImageRefer($title, $user);
	return($result);

}

?>
