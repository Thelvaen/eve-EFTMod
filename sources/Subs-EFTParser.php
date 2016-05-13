<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

if (!defined('SMF'))
    die('Hacking attempt...');

function addEFT_Tag(&$codes) {
    $codes[] = array(
        'tag' => 'eft',
        'type' => 'unparsed_content',
        'content' => '$1',
        // !!! Maybe this can be simplified?
        'validate' => create_function('&$tag, &$data, $disabled', '
					global $smcFunc;
					$export = $data;
                                        EFT_Parse($tag, $data, $disabled);
                                        '));
}

function addEFT_Button($bbc_tags) {
    $bbc_tags[] = array(
        'image' => 'eft',
        'code' => 'eft',
        'before' => '[eft]',
        'after' => '[/eft]',
        'description' => $txt['bbc_eft']
    );
}

function EFT_Parse(&$tag, &$data, $disabled) {
    global $smcFunc;
    $export = $data;
    
    $errorReporting = error_reporting();
    error_reporting(0);

    $data = preg_replace('#<br\s*/?>#i', "\n", $data);
    $fitdetails = explode("\n", trim($data));

    foreach ($fitdetails as $key => $row) {
        if ($key == '0') {
            $titlebits = explode(",", preg_replace("/(\[|\]|)/", "", $row));
            $shipType = $titlebits[0];
            $name = trim($titlebits[1]);
            continue;
        }
        // Quantity Drone Patch
        if (preg_match("/\hx\d+$/", trim($row)) == 1) {
            // Multiple drones patch
            $quantite = strripos($row, "x");
            $row = trim(substr($row, 0, $quantite));
        }
        $fitting_array[] = $row;
    }

    //Start fitting
    //$fitting_output = '<div id="fittitle"><h3>'.$name.'</h3><h4>'.$shipType.'</h4></div>';
    //$fitting_output .= '<div id="fitting_container">';
    // Name and Type appended, to when ShipDNA is constructed.
    $fitting_output = '<div id="fitting_container">';
    $fitting_output .= '<div class="fitting_tabs"><ul class="fit-tabs"><li class="fit-tab" onclick="chooseTab(this,\'loadout\');">Loadout</li><li class="fit-tab" onclick="chooseTab(this,\'export\');">EFT Export</li></ul><div style="clear:both;"></div></div>';
    $fitting_output .= '<div id="fittext" style="display:none;">' . $export . '</div>';
    $fitting_output .= '<div title="fitting" id="fitting">';

    //Fitting window
    $fitting_output .= '<div id="fittingwindow"><img border="0" alt="" src="images/fitting/fitting2.png"></div>';

    $sql = 'SELECT * FROM {db_prefix}EFTShips WHERE typeName="' . $shipType . '"';
    $result = $smcFunc['db_query']('', $sql);
    $shipdetails = $smcFunc['db_fetch_assoc']($result);
    $fitting_output .= '<div id="shipicon"><img border="0" alt="" title="' . $shipType . '" src="http://image.eveonline.com/Render/' . $shipdetails["typeID"] . '_512.png"></div>';

    $unknown_items = '';

    $shipDNA_log = '';
    $shipDNA = '';

    $shipDNA_shipId = $shipdetails["typeID"];

    $shipDNA_log .= 'ShipDNA: Setting up new ship "' . $shipDNA_shipId . '"';

    $sql = 'SELECT DISTINCT location FROM {db_prefix}EFTmodules';
    $result = $smcFunc['db_query']('', $sql);
    while ($row = $smcFunc['db_fetch_assoc']($result)) {
        $position[$row['location']] = 1;

        $shipDNA_log .= '<br>ShipDNA: Possible Module Position "' . $row['location'] . '"';
    }

    //Produce fit display
    foreach ($fitting_array as $itemname) {

        //ignore blank lines
        if ($itemname == '') {
            continue;
        }

        if (strpos($itemname, ',')) {
            $details = explode(',', $itemname);
            $itemname = $details[0];
        }

        // It indeed could be an empty slot!
        if (strpos($itemname, '[Empty ') === 0) {
            continue;
        }



        //get the info about the module                
        //$sql = 'SELECT * FROM {db_prefix}EFTmodules where typeName = "' . $itemname . '"';
        //fix for aphostrophe problem
        $itemname = str_replace("&#039;", "'", $itemname);
        $sql = 'SELECT * FROM {db_prefix}EFTmodules where typeName = "' . mysql_real_escape_string($itemname) . '"';
        //$itemname = str_replace("'", "&#039;", $itemname);

        $result = $smcFunc['db_query']('', $sql);
        $row = $smcFunc['db_fetch_assoc']($result);
        // Identify missing items
        if ($row['location'] == '') {
            $unknown_items .= '<br>"' . $itemname . '"';
        } else {
            $fitting_output .= '<div id="' . $row['location'] . $position[$row['location']] . '"><img border="0" title="' . $itemname . '" src="http://image.eveonline.com/Type/' . $row['typeID'] . '_32.png" /></div>';

            if ($row['location'] == 'SubSystem') {
                $shipDNA_arr['SubSystem'][$position[$row['location']] - 1] = $row['typeID'] . ';1';
                $shipDNA_log .= '<br>ShipDNA: Added "' . $row['location'] . '" #' . $position[$row['location']] . ' "' . $row['typeID'] . '" ("' . $itemname . '")';
            } else {
                if ($shipDNA_arr[$row['location']][$row['typeID']] == null) {
                    $shipDNA_arr[$row['location']][$row['typeID']] = 1;
                    $shipDNA_log .= '<br>ShipDNA: Added "' . $row['location'] . '" Module "' . $row['typeID'] . '" ("' . $itemname . '")';
                } else {
                    $shipDNA_arr[$row['location']][$row['typeID']] ++;
                    $shipDNA_log .= '<br>ShipDNA: Increased "' . $row['location'] . '" Module "' . $row['typeID'] . '" ("' . $itemname . '")';
                }
            }

            if (!empty($details[1])) {
                $charges[$row['location']][$position[$row['location']]] = trim($details[1]);
                unset($details);
            }

            //increment the position
            $position[$row['location']] ++;
        }
    }

    if (is_array($charges)) {
        foreach ($charges as $slot => $positions) {
            foreach ($positions as $position => $itemname) {
                if (!empty($itemname)) {
                    $sql = 'SELECT typeID FROM {db_prefix}EFTCharges where typeName = "' . $itemname . '"';
                    $result = $smcFunc['db_query']('', $sql);
                    $row = $smcFunc['db_fetch_assoc']($result);
                    $fitting_output .= '<div id="' . $slot . 'charge' . ($position) . '"><img border="0" title="' . $itemname . '" src="http://image.eveonline.com/InventoryType/' . $row['typeID'] . '_32.png"></div>';

                    if ($shipDNA_arr['Charges'][$row['typeID']] == null) {
                        $shipDNA_arr['Charges'][$row['typeID']] = 1;
                        $shipDNA_log .= '<br>ShipDNA: Added "Charge" "' . $row['typeID'] . '" ("' . $itemname . '")';
                    } else {
                        $shipDNA_arr['Charges'][$row['typeID']] ++;
                        $shipDNA_log .= '<br>ShipDNA: Increased "Charge" "' . $row['typeID'] . '" ("' . $itemname . '")';
                    }
                }
            }
        }
    }

    $sql = 'SELECT DISTINCT location FROM {db_prefix}EFTmodules';
    $result = $smcFunc['db_query']('', $sql);
    while ($row = $smcFunc['db_fetch_assoc']($result)) {
        for ($i = ($position[$row['location']]); $i < $shipDetails[$row['location']]; $i++) {
            $fitting_output .= '<div id="' . $row['location'] . ($i + 1) . '"><img border="0" title="Empty ' . ucfirst($row['location']) . ' Slot" src="images/fitting/' . $row['location'] . '.png"></div>';
        }
    }

    //End fitting
    $fitting_output .= '</div></div>';

    // When there are items unknown, list them separately
    if ($unknown_items != '') {
        $fitting_output .= '<div>&nbsp;<br><strong>Extras:</strong>' . $unknown_items . '</div>';
    }

    if (is_array($shipDNA_arr)) {
        // Possible Keys (Order in ShipDNA in parenthesis)
        // - medPower  (3)
        // - hiPower   (2)
        // - loPower   (4)
        // - rigSlot   (5)
        // - Drone     (6)
        // - SubSystem (1, 5x)
        // - Charges   (7)
        // Set up ship Id
        $shipDNA = $shipDNA_shipId;

        // Flatten location group items
        // SubSystems
        if (is_array($shipDNA_arr['SubSystem'])) {
            $shipDNA .= ':' . implode(':', $shipDNA_arr['SubSystem']);
        }

        // High Power Slot Modules
        if (is_array($shipDNA_arr['hiPower'])) {
            foreach ($shipDNA_arr['hiPower'] as $key => $value) {
                $shipDNA .= ':' . $key . ';' . $value;
            }
        } else {
            $shipDNA .= ':';
        }

        // Medium Power Slot Modules
        if (is_array($shipDNA_arr['medPower'])) {
            foreach ($shipDNA_arr['medPower'] as $key => $value) {
                $shipDNA .= ':' . $key . ';' . $value;
            }
        } else {
            $shipDNA .= ':';
        }

        // Low Power Slot Modules
        if (is_array($shipDNA_arr['loPower'])) {
            foreach ($shipDNA_arr['loPower'] as $key => $value) {
                $shipDNA .= ':' . $key . ';' . $value;
            }
        } else {
            $shipDNA .= ':';
        }

        // Rigs
        if (is_array($shipDNA_arr['rigSlot'])) {
            foreach ($shipDNA_arr['rigSlot'] as $key => $value) {
                $shipDNA .= ':' . $key . ';' . $value;
            }
        } else {
            $shipDNA .= ':';
        }

        // Drones
        if (is_array($shipDNA_arr['Drone'])) {
            foreach ($shipDNA_arr['Drone'] as $key => $value) {
                $shipDNA .= ':' . $key . ';' . $value;
            }
        }

        // Charges
        if (is_array($shipDNA_arr['Charges'])) {
            foreach ($shipDNA_arr['Charges'] as $key => $value) {
                $shipDNA .= ':' . $key . ';' . $value;
            }
        }

        $shipDNA .= '::';
    }

    // Remove comment to see logging of Ship DNA Creation
    //$fitting_output .= '<div><h1>Ship DNA Test:</h1>' . $shipDNA_log . '<hr>' . json_encode($shipDNA_arr) . '</div>';
    //$fitting_output .= '<div>' . $shipDNA . '</div>';

    $fitting_output_header = '<div id="fittitle"><h3>' . $name . '</h3><h4>' . $shipType . '</h4></div>';
    // Test for Ingame Browser and Trust for this site
    //if ($_SERVER['HTTP_EVE_TRUSTED'] == 'Yes') {
    // Hurray!
    if ($name == '') {
        $name = 'Fitting';
    }
    $fitting_output_header = '<div id="fittitle"><h3>' .
            '<a href="javascript:CCPEVE.showFitting(\'' . $shipDNA . '\')">' . $name . '</a> (IGB-Link)' .
            '</h3><h4>' .
            '<a href="javascript:CCPEVE.showInfo(' . $shipDNA_shipId . ')">' . $shipType . '</a> (IGB-Link)' .
            '</h4></div>';
    //} 

    error_reporting($errorReporting);
    $data = $fitting_output_header . $fitting_output;
}
