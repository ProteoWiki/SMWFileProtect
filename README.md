# SMWFileProtect

Extension to protect access to files based on which pages images are embedded and the SMW properties assigned to that page.

Please check http://www.mediawiki.org/wiki/Manual:Image_Authorization for proper setup first.

## Parameters

$SMWFileProtectRights = array("sysop"); // Which roles are always allowed to view files

$SMWFileProtectReferUsers = array("Has User"); // Property that refers to pages that link to other pages, for instance user pages, assuming that a page is created or assigned to a user

$SMWFileProtectReferProps = array("Is Visible"); // Boolean property that can be used to grant permission to the page.


