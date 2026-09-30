# SMWFileProtect

Extension to protect access to files based on which pages they are embedded in and the SMW
properties assigned to those pages.

Requires MediaWiki 1.43 or later, PHP 8.1 or later and Semantic MediaWiki. Version 0.4.0 and
earlier do not protect anything on MediaWiki 1.40 or later.

## Installation

```php
wfLoadExtension( 'SMWFileProtect' );
```

Files must be served through `img_auth.php`, see
https://www.mediawiki.org/wiki/Manual:Image_authorization.

Note that `img_auth.php` only checks read permissions when anonymous users cannot read the
wiki (`$wgGroupPermissions['*']['read'] = false`). On a wiki that anonymous users can read, it
streams every file without asking any extension, so this extension only protects the `File:`
description pages. Since MediaWiki 1.43 that check is `$publicWiki` in
`includes/filerepo/AuthenticatedFileEntryPoint.php`.

## How it works

A user can read a `File:` page, and download the file, unless a page that uses the file (via
`imagelinks`) denies them:

- `$SMWFileProtectReferNS`: the page is in a namespace the user cannot read under
  [Lockdown](https://www.mediawiki.org/wiki/Extension:Lockdown)'s
  `$wgNamespacePermissionLockdown`, resolved the same way Lockdown resolves it.
- `$SMWFileProtectReferSMW`: the page has values for one of `$SMWFileProtectReferUsers` and
  none of them is the user's page, or a value for one of `$SMWFileProtectReferProps` that is not
  true. Pages without these properties do not restrict anything.

Users in `$SMWFileProtectRights` can always read files.

## Parameters

```php
$SMWFileProtectRights = [ 'sysop' ]; // Groups that can always read files, whatever pages use them
$SMWFileProtectReferNS = true; // Namespace protection based on Lockdown
$SMWFileProtectReferSMW = false; // Property-based protection, below
$SMWFileProtectReferUsers = [ 'Has User' ]; // Page or text properties listing the users (User:Name) allowed to read files used on a page
$SMWFileProtectReferProps = [ 'Is Visible' ]; // Boolean properties that must be true for files used on a page to be readable
```
