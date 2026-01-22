SET foreign_key_checks = 0;
DELETE FROM `setting` WHERE `id` LIKE 'adminaddon_%';
SET foreign_key_checks = 1;
