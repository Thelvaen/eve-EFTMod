# EFT Mod for SMF

Fitting tool for SMF (Simple Machine Forum)

If you're not a dev, you're most likely looking for the latest mod file here :

https://github.com/Thelvaen/eve-EFTMod/tree/master/ready_to_install_mod

Already have the mod installed and know how to update the DB with phpmyadmin (or any other tool),
you'll find the updated DB here :

https://github.com/Thelvaen/eve-EFTMod/tree/master/db_updates

You're a dev and wants to work on the Mod, just drop me a mail here :

https://forums.eveonline.com/profile/Soressean%20Goldenheart



## Changelog :
### 0.3.4 :
- Citadel have been integrated, should work except for Service Slots,
- moved code out of Subs.php modification (use of SMF integration_hooks),
- moved the code outside of the "create_function" originaly used, it's easier to maintain now,
- changing db installation to work with Serialized db for installation,
- code refactoring,
- DB has been prunned of unpublished modules/ships/charges,

### 0.3.3 :
* integrating Citadel DB Update,
* correcting a bug where the SMF db_prefix was not correctly used,
