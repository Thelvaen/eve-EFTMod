<?php

/* * *****************************************************************************
  This is a simplified script to add settings into SMF.

  ATTENTION: If you are trying to INSTALL this package, please access
  it directly, with a URL like the following:
  http://www.yourdomain.tld/forum/add_settings.php (or similar.)

  ================================================================================

  This script can be used to add new settings into the database for use
  with SMF's $modSettings array.  It is meant to be run either from the
  package manager or directly by URL.

 * ***************************************************************************** */

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

// Cleaning DB
$dropCharges = "DROP TABLE {$db_prefix}EFTCharges";
$result = $smcFunc['db_query']('',$dropCharges);

$dropModules = "DROP TABLE {$db_prefix}EFTmodules";	
$result = $smcFunc['db_query']('',$dropModules);

$dropShips = "DROP TABLE {$db_prefix}EFTShips";	
$result = $smcFunc['db_query']('',$dropShips);


// Creating Charges table
$chargeTable = "CREATE TABLE IF NOT EXISTS {$db_prefix}EFTCharges 
			(
				`typeID` int(11) NOT NULL,
				`typeName` varchar(255) NOT NULL
			) ";


$result = $smcFunc['db_query']('', $chargeTable);

// Uh-oh spaghetti-oh!
if ($result === false) {
    echo '<b>Error:</b> Charge table failed!';
} else {
    echo 'succeeded';
}
// Creating Modules table
$modulesTable = "CREATE TABLE IF NOT EXISTS {$db_prefix}EFTmodules
				(
				  `typeID` int(11) NOT NULL,
				  `typeName` varchar(255) NOT NULL,
				  `location` varchar(255) NOT NULL,
				  KEY `TypeName` (`TypeName`)
				) ";

$result = $smcFunc['db_query']('', $modulesTable);

// Uh-oh spaghetti-oh!
if ($result === false) {
    echo '<b>Error:</b> Module create table failed!';
} else {
    echo 'succeeded';
}
// Creating Ships table
$shipTable = "
	CREATE TABLE IF NOT EXISTS {$db_prefix}EFTShips (
	  `typeID` int(11) NOT NULL,
	  `typeName` varchar(255) NOT NULL,
	  `HiSlots` int(11) NOT NULL,
	  `MedSlots` int(11) NOT NULL,
	  `LowSlots` int(11) NOT NULL,
	  `RigSlots` int(11) NOT NULL,
	  `maxSubSystems` int(11) NOT NULL,
          `serviceSlots` int(11) NOT NULL,
	  KEY `TypeName` (`TypeName`)
	) ";

$result = $smcFunc['db_query']('', $shipTable);

// Uh-oh spaghetti-oh!
if ($result === false) {
    echo '<b>Error:</b> Ship table failed!';
} else {
    echo 'succeeded';
}

$EVE_DB_Dump = unserialize(html_entity_decode(file_get_contents($boarddir . "/db_dump.ser")));
//$EVE_DB_Dump = unserialize($data);
unlink($boarddir . "/db_dump.ser");

// Inserting Charges
$count_error = 0;
foreach ($EVE_DB_Dump["charges"] as $Charge) {
    $ReqCharges = "INSERT INTO {$db_prefix}EFTCharges (`typeID`, `typeName`) VALUES";
    $ReqCharges .= "(" . $Charge["typeID"] . ", \"" . $Charge["typeName"] . "\")";
    $result = $smcFunc['db_query']('', $ReqCharges);
    if ($result === false) {
        $count_error ++;
    }
}

// Uh-oh spaghetti-oh!
if ($count_error > 0) {
    echo '<b>Error:</b> Charges INSERT failed!';
}

// Inserting Modules
$count_error = 0;
foreach ($EVE_DB_Dump["modules"] as $Module) {
    $ReqModules = "INSERT INTO {$db_prefix}EFTmodules (`typeID`, `typeName`, `location`) VALUES";
    $ReqModules .= "(" . $Module["typeID"] . ", \"" . $Module["typeName"] . "\", \"" . $Module["location"] . "\")";
    $result = $smcFunc['db_query']('', $ReqModules);
    if ($result === false) {
        $count_error ++;
    }
}

// Uh-oh spaghetti-oh!
if ($count_error > 0) {
    echo '<b>Error:</b> Modules INSERT failed!';
}

// Inserting Ships
$count_error = 0;
foreach ($EVE_DB_Dump["ships"] as $Ship) {
    $ReqShips = "INSERT INTO {$db_prefix}EFTShips (`typeID`, `typeName`, `HiSlots`, `MedSlots`, `LowSlots`, `RigSlots`, `maxSubSystems`, `serviceSlots`) VALUES";
    $ReqShips .= "(" . $Ship["typeID"] . ", \"" . $Ship["typeName"] . "\", " . $Ship["HiSlots"] . ", " . $Ship["MedSlots"] . ", " . $Ship["LowSlots"] . ", " . $Ship["RigSlots"] . ", " . $Ship["maxSubSystems"] . ", " . $Ship["serviceSlots"] . ")";
    $result = $smcFunc['db_query']('', $ReqShips);
    if ($result === false) {
        $count_error ++;
    }
}

// Uh-oh spaghetti-oh!
if ($count_error > 0) {
    echo '<b>Error:</b> Ships INSERT failed!';
}