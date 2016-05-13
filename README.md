# EFT Mod for SMF

Fitting tool for SMF (Simple Machine Forum)

If you're not a dev, you're most likely looking for the latest mod file here :

https://git.raven-ip.com/eve/SMF_EFT_Mod/tree/master/ready_to_install_mod

Already have the mod installed and know how to update the DB with phpmyadmin (or any other tool),
you'll find the updated DB here :

https://git.raven-ip.com/eve/SMF_EFT_Mod/tree/master/db_updates

You're a dev and wants to work on the Mod, just drop me a mail here :

https://forums.eveonline.com/profile/Soressean%20Goldenheart



## Changelog :
### 0.3.4 :
- moved code out of Subs.php modification (use of integration_hooks), WIP
- changing db_install.php to work with XML db for installation, WIP

### 0.3.3 :
* integrating Citadel DB Update,
* correcting a bug where the SMF db_prefix was not correctly used,