# ************************************************************
# Sequel Pro SQL dump
# Version 4499
#
# http://www.sequelpro.com/
# https://github.com/sequelpro/sequelpro
#
# Host: 127.0.0.1 (MySQL 5.1.73)
# Database: aware
# Generation Time: 2015-12-02 11:51:38 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

# Dump of table ci_sessions
# ------------------------------------------------------------

DROP TABLE IF EXISTS `ci_sessions`;

CREATE TABLE `ci_sessions` (
  `session_id` varchar(40) NOT NULL DEFAULT '0',
  `ip_address` varchar(16) NOT NULL DEFAULT '0',
  `user_agent` TEXT NOT NULL,
  `last_activity` int(10) unsigned NOT NULL DEFAULT '0',
  `user_data` text NOT NULL,
  PRIMARY KEY (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table configuration_list
# ------------------------------------------------------------

DROP TABLE IF EXISTS `configuration_list`;

CREATE TABLE `configuration_list` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plugin_name` text NOT NULL,
  `setting_name` text NOT NULL,
  `setting_description` text NOT NULL,
  `setting_type` text NOT NULL,
  `setting_default_value` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table developer_plugins
# ------------------------------------------------------------

DROP TABLE IF EXISTS `developer_plugins`;

CREATE TABLE `developer_plugins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` text NOT NULL,
  `desc` text NOT NULL,
  `created_date` double DEFAULT NULL,
  `creator_id` int(11) NOT NULL,
  `lastupdate` double NOT NULL,
  `version` text,
  `iconpath` text NOT NULL,
  `package_path` text,
  `package_name` text,
  `status` int(4) NOT NULL DEFAULT '0',
  `type` int(2) DEFAULT NULL,
  `package` text NOT NULL,
  `state` tinyint(4) DEFAULT '0',
  `repository` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table developer_plugins_broadcastextras
# ------------------------------------------------------------

DROP TABLE IF EXISTS `developer_plugins_broadcastextras`;

CREATE TABLE `developer_plugins_broadcastextras` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `broadcast_id` int(11) NOT NULL,
  `extra` text NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `broadcast_id` (`broadcast_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table developer_plugins_broadcasts
# ------------------------------------------------------------

DROP TABLE IF EXISTS `developer_plugins_broadcasts`;

CREATE TABLE `developer_plugins_broadcasts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plugin_id` int(11) NOT NULL,
  `broadcast` text,
  `desc` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table developer_plugins_permissions
# ------------------------------------------------------------

DROP TABLE IF EXISTS `developer_plugins_permissions`;

CREATE TABLE `developer_plugins_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plugin_id` int(11) NOT NULL,
  `permission` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table developer_plugins_settings
# ------------------------------------------------------------

DROP TABLE IF EXISTS `developer_plugins_settings`;

CREATE TABLE `developer_plugins_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plugin_id` int(11) NOT NULL,
  `setting` text,
  `desc` text,
  `setting_type` text NOT NULL,
  `setting_default_value` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table developer_plugins_studyaccess
# ------------------------------------------------------------

DROP TABLE IF EXISTS `developer_plugins_studyaccess`;

CREATE TABLE `developer_plugins_studyaccess` (
  `plugin_id` int(11) NOT NULL,
  `study_api` text NOT NULL,
  `added` double DEFAULT NULL,
  KEY `plugin_id_idx` (`plugin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table developer_plugins_tablefields
# ------------------------------------------------------------

DROP TABLE IF EXISTS `developer_plugins_tablefields`;

CREATE TABLE `developer_plugins_tablefields` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `column_name` text,
  `type` text,
  `description` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table developer_plugins_tablerefs
# ------------------------------------------------------------

DROP TABLE IF EXISTS `developer_plugins_tablerefs`;

CREATE TABLE `developer_plugins_tablerefs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plugin_id` int(11) NOT NULL,
  `table_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table developer_plugins_tables
# ------------------------------------------------------------

DROP TABLE IF EXISTS `developer_plugins_tables`;

CREATE TABLE `developer_plugins_tables` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `table_name` text NOT NULL,
  `context_uri` text NOT NULL,
  `desc` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table developer_plugins_tables_fields
# ------------------------------------------------------------

DROP TABLE IF EXISTS `developer_plugins_tables_fields`;

CREATE TABLE `developer_plugins_tables_fields` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `table_id` int(11) NOT NULL,
  `field_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table developer_plugins_userfeedback
# ------------------------------------------------------------

DROP TABLE IF EXISTS `developer_plugins_userfeedback`;

CREATE TABLE `developer_plugins_userfeedback` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plugin_id` int(11) NOT NULL,
  `title` text NOT NULL,
  `description` text NOT NULL,
  `timestamp` datetime NOT NULL,
  `contact_information` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table developer_sensors
# ------------------------------------------------------------

DROP TABLE IF EXISTS `developer_sensors`;

CREATE TABLE `developer_sensors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sensor` text,
  `description` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `developer_sensors` WRITE;
/*!40000 ALTER TABLE `developer_sensors` DISABLE KEYS */;

INSERT INTO `developer_sensors` (`id`, `sensor`, `description`)
VALUES
	(1,'Accelerometer',NULL),
	(2,'Applications',NULL),
	(3,'Barometer',NULL),
	(4,'Battery',NULL),
	(5,'Bluetooth',NULL),
	(6,'Communication',NULL),
	(7,'ESM',NULL),
	(8,'Gravity',NULL),
	(9,'Gyroscope',NULL),
	(10,'Installations',NULL),
	(11,'Light',NULL),
	(12,'Linear Accelerometer',NULL),
	(13,'Locations',NULL),
	(14,'Magnetometer',NULL),
	(15,'MQTT',NULL),
	(16,'Network',NULL),
	(17,'Processor',NULL),
	(18,'Proximity',NULL),
	(19,'Rotation',NULL),
	(20,'Screen',NULL),
	(21,'Telephony',NULL),
	(22,'Temperature',NULL),
	(23,'Wi-Fi',NULL),
	(24,'Webservices',NULL),
	(25,'Timezone',NULL);

/*!40000 ALTER TABLE `developer_sensors` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table developer_sensors_settings
# ------------------------------------------------------------

DROP TABLE IF EXISTS `developer_sensors_settings`;

CREATE TABLE `developer_sensors_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sensor_id` int(11) NOT NULL,
  `setting` text NOT NULL,
  `description` text NOT NULL,
  `setting_type` text NOT NULL,
  `setting_default_value` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `developer_sensors_settings` WRITE;
/*!40000 ALTER TABLE `developer_sensors_settings` DISABLE KEYS */;

INSERT INTO `developer_sensors_settings` (`id`, `sensor_id`, `setting`, `description`, `setting_type`, `setting_default_value`)
VALUES
	(1,1,'status_accelerometer','true or false to activate or deactivate accelerometer sensor.','boolean',NULL),
	(2,1,'frequency_accelerometer','non-deterministic frequency in microseconds (dependent of the hardware sensor capabilities and resources), e.g., 200000 (normal), 60000 (UI), 20000 (game), 0 (fastest).','integer','200000'),
	(3,2,'status_applications','true or false to activate or deactivate application usage (e.g., foreground, history).','boolean',NULL),
	(4,2,'status_notifications','true or false to activate or deactivate application notifications sensor.','boolean',NULL),
	(5,2,'status_crashes','true of false to activate or deactivate application crashes sensor.','boolean',NULL),
	(6,3,'status_barometer','true or false to activate or deactivate sensor.','boolean',NULL),
	(7,3,'frequency_barometer','non-deterministic frequency in microseconds (dependent of the hardware sensor capabilities and resources). You can also use a SensorManager sensor delay constant.','integer','200000'),
	(8,4,'status_battery','true or false to activate or deactivate battery usage.','boolean',NULL),
	(9,5,'status_bluetooth',' true or false to activate or deactivate bluetooth sensor.','boolean',NULL),
	(10,5,'frequency_bluetooth','deterministic frequency in seconds (default is 60 seconds).','integer','60'),
	(11,6,'status_communication','true or false to activate or deactivate high-level context of users’ communication usage.','boolean',NULL),
	(12,6,'status_calls','true or false to activate or deactivate calls sensor.','boolean',NULL),
	(13,6,'status_messages','true or false to activate or deactivate messages sensor.','boolean',NULL),
	(14,7,'status_esm','true or false to activate or deactivate ESM sensor.','boolean',NULL),
	(15,8,'status_gravity','true or false to activate or deactivate gravity sensor.','boolean',NULL),
	(16,8,'frequency_gravity','non-deterministic frequency in microseconds (dependent of the hardware sensor capabilities and resources). You can also use a SensorManager sensor delay constant.','integer','200000'),
	(17,9,'status_gyroscope','true or false to activate or deactivate sensor.','boolean',NULL),
	(18,9,'frequency_gyroscope','non-deterministic frequency in microseconds (dependent of the hardware sensor capabilities and resources). You can also use a SensorManager sensor delay constant.','integer','200000'),
	(19,10,'status_installations','true or false to activate or deactivate sensor.','boolean',NULL),
	(20,11,'status_light','true or false to activate or deactivate sensor.','boolean',NULL),
	(21,11,'frequency_light','non-deterministic frequency in microseconds (dependent of the hardware sensor capabilities and resources). You can also use a SensorManager sensor delay constant.','integer','200000'),
	(22,12,'status_linear_accelerometer','true or false to activate or deactivate sensor.','boolean',NULL),
	(23,12,'frequency_linear_accelerometer','non-deterministic frequency in microseconds (dependent of the hardware sensor capabilities and resources). You can also use a SensorManager sensor delay constant.','integer','200000'),
	(24,13,'status_location_gps','true or false to activate or deactivate GPS locations.','boolean',NULL),
	(25,13,'status_location_network','true or false to activate or deactivate Network locations.','boolean',NULL),
	(26,13,'frequency_gps','how frequent to check the GPS location, in seconds. By default, every 180 seconds. Setting to 0 (zero) will keep the GPS location tracking always on.','integer','180'),
	(27,13,'frequency_network','how frequently to check the network location, in seconds. By default, every 300 seconds. Setting to 0 (zero) will keep the network location tracking always on.','integer','300'),
	(28,13,'min_gps_accuracy','the minimum acceptable accuracy of GPS location, in meters. By default, 150 meters. Setting to 0 (zero) will keep the GPS location tracking always on.','integer','150'),
	(29,13,'min_network_accuracy','the minimum acceptable accuracy of network location, in meters. By default, 1500 meters. Setting to 0 (zero) will keep the network location tracking always on.','integer','1500'),
	(30,13,'expiration_time','the amount of elapsed time, in seconds, until the location is considered outdated. By default, 300 seconds.','integer','300'),
	(31,14,'status_magnetometer','true or false to activate or deactivate sensor.','boolean',NULL),
	(32,14,'frequency_magnetometer','non-deterministic frequency in microseconds (dependent of the hardware sensor capabilities and resources). You can also use a SensorManager sensor delay constant.','integer','200000'),
	(33,15,'status_mqtt','true or false to activate or deactivate MQTT client.','boolean',NULL),
	(34,15,'mqtt_server','the URL/IP of the server.','text',NULL),
	(35,15,'mqtt_port','the connection port of the server. The standard TCP/IP ports for MQTT: 1883 (non-SSL, non-secure), 8883 (SSL, secure).','integer',NULL),
	(36,15,'mqtt_username','the username to connect to the MQTT Server.','text',NULL),
	(37,15,'mqtt_password','the password to connect to the MQTT Server.','text',NULL),
	(38,15,'mqtt_keep_alive','how frequently to ping the server to keep alive the connection, in minutes (default is every 5 minutes).','integer','5'),
	(39,15,'mqtt_qos','the QoS expected for message delivery (2=exactly once, 1=at least once, 0=no guarantee).','integer','2'),
	(40,16,'status_network_events','true or false to activate or deactivate sensor.','boolean',NULL),
	(41,16,'status_network_traffic','true or false to activate or deactivate sensor.','boolean',NULL),
	(42,17,'status_processor','true or false to activate or deactivate sensor.','boolean',NULL),
	(43,17,'frequency_processor','frequency in seconds to update the processor load, by default is 10 seconds.','integer','200000'),
	(44,18,'status_proximity','true or false to activate or deactivate sensor.','boolean',NULL),
	(45,18,'frequency_proximity','non-deterministic frequency in microseconds (dependent of the hardware sensor capabilities and resources). You can also use a SensorManager sensor delay constant.','integer','200000'),
	(46,19,'status_rotation','true or false to activate or deactivate sensor.','boolean',NULL),
	(47,19,'frequency_rotation','non-deterministic frequency in microseconds (dependent of the hardware sensor capabilities and resources). You can also use a SensorManager sensor delay constant.','integer','200000'),
	(48,20,'status_screen','true or false to activate or deactivate sensor.','boolean',NULL),
	(49,21,'status_telephony','true or false to activate or deactivate sensor.','boolean',NULL),
	(50,22,'status_temperature','true or false to activate or deactivate sensor.','boolean',NULL),
	(51,22,'frequency_temperature','non-deterministic frequency in microseconds (dependent of the hardware sensor capabilities and resources). You can also use a SensorManager sensor delay constant.','integer','200000'),
	(52,23,'status_wifi','true or false to activate or deactivate sensor.','boolean',NULL),
	(53,23,'frequency_wifi','how often to scan for devices, in seconds (default = 60 seconds).','integer','60'),
	(54,24,'webservice_wifi_only','Upload data to webservices only when connected to Wi-Fi','boolean',NULL),
	(55,24,'frequency_webservice','How frequently the data is synced to server? (every 1-59 minutes, 0 to disable)','integer','30'),
	(56,24,'frequency_clean_old_data','How frequently to clean old data? (0 = never, 1 = weekly, 2 = monthly, 3 = daily, 4 = always)','integer','0'),
	(57,2,'frequency_applications','How frequently to check updates on background applications and services statuses.','integer','30'),
	(58,2,'status_keyboard','log keyboard input.','boolean',NULL),
	(60,26,'status_timezone','log user\'s current timezone.','boolean',NULL),
	(61,26,'frequency_timezone','frequency in seconds to check for changes in timezone.','integer',NULL),
	(62,24,'webservice_charging','sync data only if charging.','boolean',NULL);

/*!40000 ALTER TABLE `developer_sensors_settings` ENABLE KEYS */;
UNLOCK TABLES;



# Dump of table logins
# ------------------------------------------------------------

DROP TABLE IF EXISTS `logins`;

CREATE TABLE `logins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `timestamp` double DEFAULT NULL,
  `ip` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table mosquitto_permissions
# ------------------------------------------------------------

DROP TABLE IF EXISTS `mosquitto_permissions`;

CREATE TABLE `mosquitto_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(128) NOT NULL,
  `topic` text NOT NULL,
  `rw` int(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `acls_user_topic` (`username`,`topic`(228))
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table mosquitto_users
# ------------------------------------------------------------

DROP TABLE IF EXISTS `mosquitto_users`;

CREATE TABLE `mosquitto_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(128) NOT NULL,
  `pw` varchar(128) NOT NULL,
  `super` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table studies
# ------------------------------------------------------------

DROP TABLE IF EXISTS `studies`;

CREATE TABLE `studies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `description` text,
  `status` tinyint(4) NOT NULL DEFAULT '1',
  `db_name` text NOT NULL,
  `study_name` text NOT NULL,
  `creator_id` int(11) NOT NULL,
  `created` double NOT NULL DEFAULT '0',
  `api_key` text,
  `mqtt_password` text NOT NULL,
  `db_hostname` text NOT NULL,
  `db_port` text NOT NULL,
  `db_username` text NOT NULL,
  `db_password` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table studies_configurations
# ------------------------------------------------------------

DROP TABLE IF EXISTS `studies_configurations`;

CREATE TABLE `studies_configurations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `study_id` int(11) NOT NULL,
  `config` text NOT NULL,
  `edited` double NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table studies_privileges
# ------------------------------------------------------------

DROP TABLE IF EXISTS `studies_privileges`;

CREATE TABLE `studies_privileges` (
  `study_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `added` double NOT NULL,
  PRIMARY KEY (`study_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table user_levels
# ------------------------------------------------------------

DROP TABLE IF EXISTS `user_levels`;

CREATE TABLE `user_levels` (
  `user_id` int(11) NOT NULL,
  `researcher` tinyint(1) DEFAULT '0',
  `developer` tinyint(1) DEFAULT '1',
  `manager` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table users
# ------------------------------------------------------------

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` text,
  `last_name` text,
  `email` text,
  `google_id` text,
  `activated` tinyint(4) DEFAULT '1',
  `edited` double DEFAULT NULL,
  `acc_created` double DEFAULT NULL,
  `last_login_ip` text,
  `lastlogin` double DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Dump of table chart
# ------------------------------------------------------------

DROP TABLE IF EXISTS `chart`;

CREATE TABLE `chart` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `studies_id` int(11) NOT NULL,
  `placement` int(11) NOT NULL,
  `public` tinyint(1) NOT NULL,
  `type` text NOT NULL,
  `description` text,
  `path` text,
  `image` mediumblob,
  `timestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_table1_studies_idx` (`studies_id`),
  CONSTRAINT `fk_table1_studies` FOREIGN KEY (`studies_id`) REFERENCES `studies` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table chart_parameters
# ------------------------------------------------------------

DROP TABLE IF EXISTS `chart_parameters`;

CREATE TABLE `chart_parameters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `chart_id` int(11) NOT NULL,
  `r_key` text NOT NULL,
  `r_value` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_charts_parameters_charts1_idx` (`chart_id`),
  CONSTRAINT `fk_charts_parameters_charts1` FOREIGN KEY (`chart_id`) REFERENCES `chart` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
