CREATE DATABASE if NOT exists `BaruchScheduling`;
USE `BaruchScheduling`;
CREATE TABLE `days_off` (
    date VARCHAR(40) NOT NULL PRIMARY KEY
);
CREATE TABLE `regular_hours` (
    open VARCHAR(30) NOT NULL DEFAULT '08:00-05',
    closed VARCHAR(30) NOT NULL DEFAULT '17:00-05'
);
CREATE TABLE `regular_off_days` (
    day_off_week VARCHAR(12) NOT NULL UNIQUE
);
CREATE TABLE `custom_schedule` (
    day VARCHAR(12) NOT NULL PRIMARY KEY,
    open VARCHAR(25),
    closed VARCHAR(25)
);
