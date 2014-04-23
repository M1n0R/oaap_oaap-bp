<?php
require_once './config.php';
/*
 * Файл создания таблиц в базе данных.
 */
$db = new db();
$query = "CREATE TABLE `oaap-process` (
    `id` int(4) unsigned NOT NULL,
    `time_key` int(4) unsigned NOT NULL,
    `url` varchar(60)  CHARACTER SET 'utf8' COLLATE 'utf8_general_ci' NOT NULL,
    `nonce` int(4) unsigned,
    `algo` varchar(10)  CHARACTER SET 'utf8' COLLATE 'utf8_general_ci' NOT NULL,
    `key` text CHARACTER SET 'utf8' COLLATE 'utf8_general_ci' NOT NULL,
    PRIMARY KEY (`id`)
    )";
$db->mysqli->query($query);
$query = "CREATE TABLE `oaap-users` (
    `id` int(4) unsigned NOT NULL AUTO_INCREMENT,
    `provider_id` int(4) unsigned NOT NULL,
    `time_key` int(4) unsigned NOT NULL,
    `qurl` varchar(100)  CHARACTER SET 'utf8' COLLATE 'utf8_general_ci' NOT NULL,
    `algo` varchar(10)  CHARACTER SET 'utf8' COLLATE 'utf8_general_ci' NOT NULL,
    `key` text CHARACTER SET 'utf8' COLLATE 'utf8_general_ci' NOT NULL,
    PRIMARY KEY (`id`)
    )";
$db->mysqli->query($query);
?>

