SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `#REPLACE_ME#`
--

-- --------------------------------------------------------

--
-- Table structure for table `#REPLACE_ME#_category`
--

DROP TABLE IF EXISTS `#REPLACE_ME#_category`;
CREATE TABLE `#REPLACE_ME#_category` (
  `id` int(10) NOT NULL,
  `name` varchar(128) COLLATE utf8mb4_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin ROW_FORMAT=COMPACT;

--
-- Table structure for table `#REPLACE_ME#_categoryrelation`
--

DROP TABLE IF EXISTS `#REPLACE_ME#_categoryrelation`;
CREATE TABLE `#REPLACE_ME#_categoryrelation` (
  `linkid` int(10) NOT NULL,
  `categoryid` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin ROW_FORMAT=COMPACT;

--
-- Stand-in structure for view `#REPLACE_ME#_combined`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `#REPLACE_ME#_combined`;
CREATE TABLE `#REPLACE_ME#_combined` (
   `id` int(10)
  ,`link` mediumtext
  ,`created` datetime
  ,`status` int(2)
  ,`description` varchar(255)
  ,`title` varchar(255)
  ,`image` varchar(255)
  ,`hash` char(32)
  ,`tag` varchar(64)
  ,`tagId` int(10)
  ,`category` varchar(128)
  ,`categoryId` int(10)
);

-- --------------------------------------------------------

--
-- Table structure for table `#REPLACE_ME#_link`
--

DROP TABLE IF EXISTS `#REPLACE_ME#_link`;
CREATE TABLE `#REPLACE_ME#_link` (
  `id` int(10) NOT NULL,
  `link` mediumtext COLLATE utf8mb4_bin NOT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status` int(2) NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_bin NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_bin NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_bin NOT NULL,
  `hash` char(32) COLLATE utf8mb4_bin NOT NULL,
  `search` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin ROW_FORMAT=COMPACT;

--
-- Table structure for table `#REPLACE_ME#_tag`
--

DROP TABLE IF EXISTS `#REPLACE_ME#_tag`;
CREATE TABLE `#REPLACE_ME#_tag` (
  `id` int(10) NOT NULL,
  `name` varchar(64) COLLATE utf8mb4_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin ROW_FORMAT=COMPACT;

--
-- Table structure for table `#REPLACE_ME#_tagrelation`
--

DROP TABLE IF EXISTS `#REPLACE_ME#_tagrelation`;
CREATE TABLE `#REPLACE_ME#_tagrelation` (
  `linkid` int(10) NOT NULL,
  `tagid` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin ROW_FORMAT=COMPACT;

--
-- Structure for view `#REPLACE_ME#_combined`
--
DROP VIEW IF EXISTS `#REPLACE_ME#_combined`;

CREATE VIEW `#REPLACE_ME#_combined` AS
select `#REPLACE_ME#_link`.`id` AS `id`,
`#REPLACE_ME#_link`.`link` AS `link`,
`#REPLACE_ME#_link`.`created` AS `created`,
`#REPLACE_ME#_link`.`status` AS `status`,
`#REPLACE_ME#_link`.`description` AS `description`,
`#REPLACE_ME#_link`.`title` AS `title`,
`#REPLACE_ME#_link`.`image` AS `image`,
`#REPLACE_ME#_link`.`hash` AS `hash`,
`#REPLACE_ME#_tag`.`name` AS `tag`,
`#REPLACE_ME#_tag`.`id` AS `tagId`,
`#REPLACE_ME#_category`.`name` AS `category`,
`#REPLACE_ME#_category`.`id` AS `categoryId`
from ((((`#REPLACE_ME#_link`
left join `#REPLACE_ME#_tagrelation` on((`#REPLACE_ME#_tagrelation`.`linkid` = `#REPLACE_ME#_link`.`id`)))
left join `#REPLACE_ME#_tag` on((`#REPLACE_ME#_tag`.`id` = `#REPLACE_ME#_tagrelation`.`tagid`)))
left join `#REPLACE_ME#_categoryrelation` on((`#REPLACE_ME#_categoryrelation`.`linkid` = `#REPLACE_ME#_link`.`id`)))
left join `#REPLACE_ME#_category` on((`#REPLACE_ME#_category`.`id` = `#REPLACE_ME#_categoryrelation`.`categoryid`))) ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `#REPLACE_ME#_category`
--
ALTER TABLE `#REPLACE_ME#_category`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `#REPLACE_ME#_categoryrelation`
--
ALTER TABLE `#REPLACE_ME#_categoryrelation`
  ADD UNIQUE KEY `linkid` (`linkid`,`categoryid`);

--
-- Indexes for table `#REPLACE_ME#_link`
--
ALTER TABLE `#REPLACE_ME#_link`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `hash` (`hash`);
ALTER TABLE `#REPLACE_ME#_link` ADD FULLTEXT KEY `search` (`search`);

--
-- Indexes for table `#REPLACE_ME#_tag`
--
ALTER TABLE `#REPLACE_ME#_tag`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `#REPLACE_ME#_tagrelation`
--
ALTER TABLE `#REPLACE_ME#_tagrelation`
  ADD UNIQUE KEY `linkid` (`linkid`,`tagid`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `#REPLACE_ME#_category`
--
ALTER TABLE `#REPLACE_ME#_category`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `#REPLACE_ME#_link`
--
ALTER TABLE `#REPLACE_ME#_link`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `#REPLACE_ME#_tag`
--
ALTER TABLE `#REPLACE_ME#_tag`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
