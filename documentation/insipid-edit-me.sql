
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- --------------------------------------------------------

--
-- Table structure for table `#REPLACEME#_category`
--

DROP TABLE IF EXISTS `#REPLACEME#_category`;
CREATE TABLE `#REPLACEME#_category` (
  `id` int(10) NOT NULL,
  `name` varchar(128) COLLATE utf8mb4_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- Table structure for table `#REPLACEME#_categoryrelation`
--

DROP TABLE IF EXISTS `#REPLACEME#_categoryrelation`;
CREATE TABLE `#REPLACEME#_categoryrelation` (
  `linkid` int(10) NOT NULL,
  `categoryid` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- Stand-in structure for view `#REPLACEME#_combined`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `#REPLACEME#_combined`;
CREATE TABLE `#REPLACEME#_combined` (
`id` int(10)
,`link` mediumtext
,`created` datetime
,`status` int(2)
,`description` varchar(255)
,`title` varchar(255)
,`image` varchar(255)
,`hash` char(32)
,`tag` varchar(64)
,`category` varchar(128)
);

-- --------------------------------------------------------

--
-- Table structure for table `#REPLACEME#_link`
--

DROP TABLE IF EXISTS `#REPLACEME#_link`;
CREATE TABLE `#REPLACEME#_link` (
  `id` int(10) NOT NULL,
  `link` mediumtext COLLATE utf8mb4_bin NOT NULL,
  `created` datetime NOT NULL,
  `status` int(2) NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_bin NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_bin NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_bin NOT NULL,
  `hash` char(32) COLLATE utf8mb4_bin NOT NULL,
  `search` text COLLATE utf8mb4_bin NOT NULL,
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- Table structure for table `#REPLACEME#_tag`
--

DROP TABLE IF EXISTS `#REPLACEME#_tag`;
CREATE TABLE `#REPLACEME#_tag` (
  `id` int(10) NOT NULL,
  `name` varchar(64) COLLATE utf8mb4_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- Table structure for table `#REPLACEME#_tagrelation`
--

DROP TABLE IF EXISTS `#REPLACEME#_tagrelation`;
CREATE TABLE `#REPLACEME#_tagrelation` (
  `linkid` int(10) NOT NULL,
  `tagid` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- Structure for view `#REPLACEME#_combined`
--
DROP TABLE IF EXISTS `#REPLACEME#_combined`;

CREATE VIEW `#REPLACEME#_combined`  AS  select `#REPLACEME#_link`.`id` AS `id`,`#REPLACEME#_link`.`link` AS `link`,`#REPLACEME#_link`.`created` AS `created`,`#REPLACEME#_link`.`status` AS `status`,`#REPLACEME#_link`.`description` AS `description`,`#REPLACEME#_link`.`title` AS `title`,`#REPLACEME#_link`.`image` AS `image`,`#REPLACEME#_link`.`hash` AS `hash`,`#REPLACEME#_tag`.`name` AS `tag`,`#REPLACEME#_category`.`name` AS `category` from ((((`#REPLACEME#_link` left join `#REPLACEME#_tagrelation` on((`#REPLACEME#_tagrelation`.`linkid` = `#REPLACEME#_link`.`id`))) left join `#REPLACEME#_tag` on((`#REPLACEME#_tag`.`id` = `#REPLACEME#_tagrelation`.`tagid`))) left join `#REPLACEME#_categoryrelation` on((`#REPLACEME#_categoryrelation`.`linkid` = `#REPLACEME#_link`.`id`))) left join `#REPLACEME#_category` on((`#REPLACEME#_category`.`id` = `#REPLACEME#_categoryrelation`.`categoryid`))) ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `#REPLACEME#_category`
--
ALTER TABLE `#REPLACEME#_category`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `#REPLACEME#_categoryrelation`
--
ALTER TABLE `#REPLACEME#_categoryrelation`
  ADD UNIQUE KEY `linkid` (`linkid`,`categoryid`);

--
-- Indexes for table `#REPLACEME#_link`
--
ALTER TABLE `#REPLACEME#_link`
  ADD PRIMARY KEY (`id`),
  FULLTEXT KEY `search` (`search`),
  ADD UNIQUE KEY `hash` (`hash`);

--
-- Indexes for table `#REPLACEME#_tag`
--
ALTER TABLE `#REPLACEME#_tag`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `#REPLACEME#_tagrelation`
--
ALTER TABLE `#REPLACEME#_tagrelation`
  ADD UNIQUE KEY `linkid` (`linkid`,`tagid`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `#REPLACEME#_category`
--
ALTER TABLE `#REPLACEME#_category`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT for table `#REPLACEME#_link`
--
ALTER TABLE `#REPLACEME#_link`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT for table `#REPLACEME#_tag`
--
ALTER TABLE `#REPLACEME#_tag`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
