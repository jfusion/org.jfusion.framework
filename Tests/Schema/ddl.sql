--
-- JFusion Unit Test DDL
--

-- --------------------------------------------------------

--
-- Table structure for table `jos_jfusion`
--
CREATE TABLE `jos_jfusion` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `name` TEXT DEFAULT '',
  `params` TEXT DEFAULT '',
  `master` INTEGER DEFAULT '0',
  `slave` INTEGER DEFAULT '0',
  `status` INTEGER DEFAULT '0',
  `dual_login` INTEGER DEFAULT '0',
  `check_encryption` INTEGER DEFAULT '0',
  `original_name` TEXT
);

CREATE TABLE `jos_jfusion_users_plugin` (
  `autoid` INTEGER PRIMARY KEY AUTOINCREMENT,
  `id` INTEGER NOT NULL,
  `email` TEXT DEFAULT NULL,
  `username` TEXT DEFAULT NULL,
  `userid` TEXT NOT NULL,
  `jname` TEXT NOT NULL
);

CREATE TABLE `jos_jfusion_sync_details` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `syncid` TEXT NOT NULL,
  `jname` TEXT NOT NULL,
  `username` TEXT NOT NULL,
  `email` TEXT NOT NULL,
  `action` TEXT NOT NULL,
  `message` TEXT NOT NULL,
  `data` TEXT NOT NULL
);

CREATE TABLE `jos_jfusion_sync` (
  `syncid` TEXT NOT NULL,
  `action` TEXT NOT NULL,
  `active` INTEGER  NOT NULL  DEFAULT 0,
  `syncdata` TEXT NOT NULL,
  `time_start` INTEGER NULL,
  `time_end` INTEGER NULL
);

/*
mockplugin

 */
CREATE TABLE `mockplugin_users` (
  `userid` INTEGER PRIMARY KEY AUTOINCREMENT,
  `username` TEXT NOT NULL,
  `email` TEXT NOT NULL,
  `password` TEXT NOT NULL,
  `activation` TEXT NULL,
  `block` INTEGER NULL,
  `language` TEXT NULL
);

CREATE TABLE `mockplugin_groups` (
  `userid` INTEGER NOT NULL,
  `group` TEXT NOT NULL
);

CREATE TABLE `mockplugin_usergroups` (
  `id` INTEGER NOT NULL,
  `name` TEXT NOT NULL
);


CREATE TABLE `mockplugin_1_users` (
  `userid` INTEGER PRIMARY KEY AUTOINCREMENT,
  `username` TEXT NOT NULL,
  `email` TEXT NOT NULL,
  `password` TEXT NOT NULL,
  `activation` TEXT NULL,
  `block` INTEGER NULL,
  `language` TEXT NULL
);

CREATE TABLE `mockplugin_1_groups` (
  `userid` INTEGER NOT NULL,
  `group` TEXT NOT NULL
);

CREATE TABLE `mockplugin_1_usergroups` (
  `id` INTEGER NOT NULL,
  `name` TEXT NOT NULL
);
