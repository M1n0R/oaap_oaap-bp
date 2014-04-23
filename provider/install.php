<?php
/*
 * Файл создания таблиц в базе данных
 */
require_once './config.php';
$db = new db();
$query = "CREATE TABLE `oaap-process` (
    `id` int(4) unsigned NOT NULL AUTO_INCREMENT,
    `time_key` int(4) unsigned NOT NULL,
    `key` text CHARACTER SET 'utf8' COLLATE 'utf8_general_ci' NOT NULL,
    PRIMARY KEY (`id`)
    )";
$db->mysqli->query($query);
$query = "CREATE TABLE `oaap-users` (
    `uid` int(4) unsigned NOT NULL,
    `time_key` int(4) unsigned NOT NULL,
    `url` text CHARACTER SET 'utf8' COLLATE 'utf8_general_ci' NOT NULL,
    `functions` text CHARACTER SET 'utf8' COLLATE 'utf8_general_ci' NOT NULL,
    `key` text CHARACTER SET 'utf8' COLLATE 'utf8_general_ci' NOT NULL,
    `autoauth` int(1) unsigned NOT NULL
    )";
$db->mysqli->query($query);
?>

