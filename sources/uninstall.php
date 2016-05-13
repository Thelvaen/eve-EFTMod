<?php
if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
        require_once(dirname(__FILE__) . '/SSI.php');
 elseif (!defined('SMF'))
        exit('<b>Error:</b> Cannot install - please verify you put this in the same place as SMFs index.php.');

 // removing hooks
 remove_integration_function('integrate_pre_include', $sourcedir . '/Subs-EFTParser.php');
 remove_integration_function('integrate_bbc_codes', 'addEFT_Tag');