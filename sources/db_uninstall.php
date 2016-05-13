<?php
/*******************************************************************************
	This is a simplified script to add settings into SMF.

	ATTENTION: If you are trying to INSTALL this package, please access
	it directly, with a URL like the following:
		http://www.yourdomain.tld/forum/add_settings.php (or similar.)

================================================================================

	This script can be used to add new settings into the database for use
	with SMF's $modSettings array.  It is meant to be run either from the
	package manager or directly by URL.

*******************************************************************************/

// Set the below to true to overwrite already existing settings with the defaults. (not recommended.)
$overwrite_old_settings = false;

// Adding globals
global $db_prefix;

/* * *************************************************************************** */
// If SSI.php is in the same place as this file, and SMF isn't defined, this is being run standalone.
if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF')) {
    require_once(dirname(__FILE__) . '/SSI.php');
}
// Hmm... no SSI.php and no SMF?
elseif (!defined('SMF')) {
    die('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');
}

$dropCharges = "DROP TABLE {$db_prefix}EFTCharges";
echo $dropCharges . "\n";
$result = $smcFunc['db_query']('',$dropCharges);

$dropModules = "DROP TABLE {$db_prefix}EFTmodules";	
echo $dropModules . "\n";
$result = $smcFunc['db_query']('',$dropModules);

$dropShips = "DROP TABLE {$db_prefix}EFTShips";	
echo $dropShips . "\n";
$result = $smcFunc['db_query']('',$dropShips);
?>