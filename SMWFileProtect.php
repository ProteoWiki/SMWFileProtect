<?php
/**
 * Copyright (C) 2011-2015 Toni Hermoso Pulido <toniher@cau.cat>
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


call_user_func( function () {

	$GLOBALS['wgAutoloadClasses']['SMWFileProtect'] = dirname(__FILE__) . '/SMWFileProtect_body.php';
	$GLOBALS['wgAutoloadClasses']['SMWNSProtect'] = dirname(__FILE__) . '/SMWNSProtect_body.php';

	$GLOBALS['SMWFileProtectRights'] = array("sysop"); // We allow sysops always
	$GLOBALS['SMWFileProtectReferUsers'] = array("Has User"); // User pages
	$GLOBALS['SMWFileProtectReferProps'] = array("Is Visible"); // Booleans
	$GLOBALS['SMWFileProtectReferNS'] = true; // Take into protection of Namespaces where linked

	// TODO: Whitelist if available in certain pages
	$GLOBALS['SMWFileProtectWhiteListPages'] = array();


	# Informations
	$GLOBALS['wgExtensionCredits']['other'][] = array(
			'path' => __FILE__,
			'name' => 'SMWFileProtect',
			'author' => 'Toni Hermoso',
			'version' => '0.3',
			'url' => 'https://www.mediawiki.org/wiki/User:Toniher',
			'description' => 'Semantic protection of files',
	);
	
	$GLOBALS['wgHooks']['userCan'][] = 'SMWProtectuserCan';
	$GLOBALS['wgHooks']['userCan'][] = 'SMWProtectNSuserCan';

} );

# Refer userCan
function SMWProtectuserCan( $title, $user, $action, &$result ) {

	$object = new SMWFileProtect;
	$result = $object->executeImageRefer($title, $user);
	return($result);

}

function SMWProtectNSuserCan( $title, $user, $action, &$result ) {

	$object = new SMWNSProtect;
	$result = $object->executeNSRefer($title, $user);
	return($result);

}

?>
