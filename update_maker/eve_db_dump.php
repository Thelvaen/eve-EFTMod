<?php

/*
 * Todo :
 *
 */

// Script intended to be ran in CLI only
// Connection to the database
$Host = "localhost";
$Base = "evedump";
$User = "root";
$Pass = "";

// Opening MySQL connection
$DB = new mysqli($Host, $User, $Pass, $Base);

if ($DB->connect_error) {
    die('Error connecting to MySQL (' . $DB->connect_errno . ') '
            . $DB->connect_error);
}

// There be dragons
// Requete charge : 
// Initiating vars
$DBarray = array();

$ReqCharges = "SELECT invtypes.typeID, invtypes.typeName FROM invtypes, invgroups WHERE invtypes.groupID = invgroups.groupID and invgroups.categoryID = 8 and invtypes.published = 1";
$ChargesResults = $DB->query($ReqCharges);
while ($Charge = $ChargesResults->fetch_array()) {
    $DBarray["charges"][] = array("typeID" => $Charge["typeID"], "typeName" => htmlentities($Charge["typeName"]));
}
$ChargesResults->free_result();

// 11 = LoSlots,
// 12 = MedSlots,
// 13 = HiSlots,
// 2663 = rigSlots,
// 6306 = serviceSlots

$ReqModules = "SELECT invtypes.typeID, invtypes.typeName, dgmeffects.effectName FROM invtypes, invgroups, dgmtypeeffects, dgmeffects
WHERE invtypes.groupID = invgroups.groupID and invgroups.categoryID in ( 7, 66 ) and invtypes.published = 1
AND dgmeffects.effectID = dgmtypeeffects.effectID AND dgmtypeeffects.typeID = invtypes.typeID AND dgmeffects.effectID IN ( 11, 12, 13, 2663, 6306 )";

$ModulesResults = $DB->query($ReqModules);
while ($Module = $ModulesResults->fetch_array()) {
    $DBarray["modules"][] = array("typeID" => $Module["typeID"], "typeName" => htmlentities($Module["typeName"]), "location" => htmlentities($Module["effectName"]));
}
$ModulesResults->free_result();

$ReqSubSystems = "SELECT invtypes.typeID, invtypes.typeName FROM invtypes, invgroups WHERE invtypes.groupID = invgroups.groupID and invgroups.categoryID = 32 and invtypes.published = 1";
$SubSystemsResults = $DB->query($ReqSubSystems);
while ($SubSystem = $SubSystemsResults->fetch_array()) {
    $DBarray["modules"][] = array("typeID" => $SubSystem["typeID"], "typeName" => htmlentities($SubSystem["typeName"]), "location" => "subSystem");
}
$SubSystemsResults->free_result();

$ReqDrones = "SELECT invtypes.typeID, invtypes.typeName FROM invtypes, invgroups WHERE invtypes.groupID = invgroups.groupID and invgroups.categoryID = 18 and invtypes.published = 1";
$DronesResults = $DB->query($ReqDrones);
while ($Drone = $DronesResults->fetch_array()) {
    $DBarray["modules"][] = array("typeID" => $Drone["typeID"], "typeName" => htmlentities($Drone["typeName"]), "location" => "Drone");
}
$DronesResults->free_result();

$ReqShips = "SELECT invtypes.typeID, invtypes.typeName,
(SELECT dgmtypeattributes.valueFloat FROM dgmtypeattributes WHERE dgmtypeattributes.typeID = invtypes.typeID  AND dgmtypeattributes.attributeID = 14) AS HiSlotsA,
(SELECT dgmtypeattributes.valueInt FROM dgmtypeattributes WHERE dgmtypeattributes.typeID = invtypes.typeID  AND dgmtypeattributes.attributeID = 14) AS HiSlotsB,
(SELECT dgmtypeattributes.valueFloat FROM dgmtypeattributes WHERE dgmtypeattributes.typeID = invtypes.typeID  AND dgmtypeattributes.attributeID = 13) AS MedSlotsA,
(SELECT dgmtypeattributes.valueInt FROM dgmtypeattributes WHERE dgmtypeattributes.typeID = invtypes.typeID  AND dgmtypeattributes.attributeID = 13) AS MedSlotsB,
(SELECT dgmtypeattributes.valueFloat FROM dgmtypeattributes WHERE dgmtypeattributes.typeID = invtypes.typeID  AND dgmtypeattributes.attributeID = 12) AS LowSlotsA,
(SELECT dgmtypeattributes.valueInt FROM dgmtypeattributes WHERE dgmtypeattributes.typeID = invtypes.typeID  AND dgmtypeattributes.attributeID = 12) AS LowSlotsB,
(SELECT dgmtypeattributes.valueFloat FROM dgmtypeattributes WHERE dgmtypeattributes.typeID = invtypes.typeID  AND dgmtypeattributes.attributeID = 1137) AS RigSlotsA,
(SELECT dgmtypeattributes.valueInt FROM dgmtypeattributes WHERE dgmtypeattributes.typeID = invtypes.typeID  AND dgmtypeattributes.attributeID = 1137) AS RigSlotsB,
(SELECT dgmtypeattributes.valueFloat FROM dgmtypeattributes WHERE dgmtypeattributes.typeID = invtypes.typeID  AND dgmtypeattributes.attributeID = 1367) AS maxSubSystemsA,
(SELECT dgmtypeattributes.valueInt FROM dgmtypeattributes WHERE dgmtypeattributes.typeID = invtypes.typeID  AND dgmtypeattributes.attributeID = 1367) AS maxSubSystemsB,
(SELECT dgmtypeattributes.valueFloat FROM dgmtypeattributes WHERE dgmtypeattributes.typeID = invtypes.typeID  AND dgmtypeattributes.attributeID = 2056) AS serviceSlotsA,
(SELECT dgmtypeattributes.valueInt FROM dgmtypeattributes WHERE dgmtypeattributes.typeID = invtypes.typeID  AND dgmtypeattributes.attributeID = 2056) AS serviceSlotsB
FROM invtypes, invgroups
WHERE invtypes.groupID = invgroups.groupID and invgroups.categoryID IN ( 6, 65 ) and invtypes.published = 1";
$ShipsResults = $DB->query($ReqShips);
while ($Ship = $ShipsResults->fetch_array()) {
    $ShipArray = array("typeID" => $Ship["typeID"], "typeName" => htmlentities($Ship["typeName"]), "HiSlots" => 0, "MedSlots" => 0, "LowSlots" => 0, "RigSlots" => 0, "maxSubSystems" => 0, "serviceSlots" => 0);
    if (is_null($Ship["HiSlotsA"])) {
        if (is_null($Ship["HiSlotsB"])) {
            // No HiSlots ==> T3 Cruiser
        } else {
            $ShipArray["HiSlots"] = $Ship["HiSlotsB"];
        }
    } else {
        $ShipArray["HiSlots"] = $Ship["HiSlotsA"];
    }

    if (is_null($Ship["MedSlotsA"])) {
        if (is_null($Ship["MedSlotsB"])) {
            // No MedSlots ==> T3 Cruiser
        } else {
            $ShipArray["MedSlots"] = $Ship["MedSlotsB"];
        }
    } else {
        $ShipArray["MedSlots"] = $Ship["MedSlotsA"];
    }

    if (is_null($Ship["LowSlotsA"])) {
        if (is_null($Ship["LowSlotsB"])) {
            // No LowSlots ==> T3 Cruiser
        } else {
            $ShipArray["LowSlots"] = $Ship["LowSlotsB"];
        }
    } else {
        $ShipArray["LowSlots"] = $Ship["LowSlotsA"];
    }

    if (is_null($Ship["RigSlotsA"])) {
        if (is_null($Ship["RigSlotsB"])) {
            // Null Rigs value stay at 0, should not happened for Rigs
        } else {
            $ShipArray["RigSlots"] = $Ship["RigSlotsB"];
        }
    } else {
        $ShipArray["RigSlotsA"] = $Ship["RigSlotsA"];
    }

    if (is_null($Ship["maxSubSystemsA"])) {
        if (is_null($Ship["maxSubSystemsB"])) {
            // Null SubSystems value stay at 0
        } else {
            $ShipArray["maxSubSystems"] = $Ship["maxSubSystemsB"];
        }
    } else {
        $ShipArray["maxSubSystems"] = $Ship["maxSubSystemsA"];
    }

    if (is_null($Ship["serviceSlotsA"])) {
        if (is_null($Ship["serviceSlotsB"])) {
            // Null SubSystems value stay at 0
        } else {
            $ShipArray["serviceSlots"] = $Ship["serviceSlotsB"];
        }
    } else {
        $ShipArray["serviceSlots"] = $Ship["serviceSlotsA"];
    }

    $DBarray["ships"][] = $ShipArray;
}
$ShipsResults->free_result();

$DB_Dump = fopen("db_dump.ser", "w");
fwrite($DB_Dump, serialize($DBarray));
fclose($DB_Dump);

// Closing MySQL connection
$DB->close();
