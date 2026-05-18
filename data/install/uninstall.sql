SET foreign_key_checks = 0;
DELETE FROM `setting` WHERE `id` LIKE 'adminaddon_%';
DELETE FROM `setting` WHERE `id` LIKE 'recaptcha_enable_on_login';
DELETE FROM `setting` WHERE `id` LIKE 'recaptcha_enable_on_forgot_password';
DELETE FROM `setting` WHERE `id` LIKE 'recaptcha_ip_white_list';
SET foreign_key_checks = 1;
