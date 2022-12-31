<?php

/**
 * PHP version of mysqldump cli that comes with MySQL.
 *
 * Tags: mysql mysqldump pdo php7 php8 database php sql mariadb mysql-backup.
 *
 * @category Library
 * @package  Druidfi\Mysqldump
 * @author   Marko Korhonen <marko.korhonen@druid.fi>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     https://github.com/druidfi/mysqldump-php
 * @see      https://github.com/ifsnop/mysqldump-php
 */

// modified. Call me old fashioned. But now it works just fine as a simple library.

/*
namespace Druidfi\Mysqldump;

use Druidfi\Mysqldump\Compress\CompressInterface;
use Druidfi\Mysqldump\Compress\CompressManagerFactory;
use Druidfi\Mysqldump\TypeAdapter\TypeAdapterInterface;
use Druidfi\Mysqldump\TypeAdapter\TypeAdapterMysql;
use Exception;
use PDO;
use PDOException;
*/


class Mysqldump
{
	// Database
	private string $dsn;
	private ?string $user;
	private ?string $pass;
	private string $host;
	private string $dbName;
	private PDO $conn;
	private array $pdoOptions;
	private CompressInterface $io;
	private TypeAdapterInterface $db;

	private static string $adapterClass = TypeAdapterMysql::class;

	private DumpSettings $settings;
	private array $tableColumnTypes = [];
	private $transformTableRowCallable;
	private $transformColumnValueCallable;
	private $infoCallable;

	// Internal data arrays.
	private array $tables = [];
	private array $views = [];
	private array $triggers = [];
	private array $procedures = [];
	private array $functions = [];
	private array $events = [];

	/**
	 * Keyed on table name, with the value as the conditions.
	 * e.g. - 'users' => 'date_registered > NOW() - INTERVAL 6 MONTH'
	 */
	private array $tableWheres = [];
	private array $tableLimits = [];

	/**
	 * Constructor of Mysqldump.
	 *
	 * @param string $dsn PDO DSN connection string
	 * @param string|null $user SQL account username
	 * @param string|null $pass SQL account password
	 * @param array $settings SQL database settings
	 * @param array $pdoOptions PDO configured attributes
	 * @throws Exception
	 */
	public function __construct(
		string  $dsn = '',
		?string $user = null,
		?string $pass = null,
		array   $settings = [],
		array   $pdoOptions = []
	)
	{
		$this->dsn = $this->parseDsn($dsn);
		$this->user = $user;
		$this->pass = $pass;
		$this->settings = new DumpSettings($settings);
		$this->pdoOptions = $pdoOptions;
	}

	/**
	 * Parse DSN string and extract dbname value
	 * Several examples of a DSN string
	 *   mysql:host=localhost;dbname=testdb
	 *   mysql:host=localhost;port=3307;dbname=testdb
	 *   mysql:unix_socket=/tmp/mysql.sock;dbname=testdb
	 *
	 * @param string $dsn dsn string to parse
	 * @throws Exception
	 */
	private function parseDsn(string $dsn): string
	{
		if (empty($dsn) || !($pos = strpos($dsn, ':'))) {
			throw new Exception('Empty DSN string');
		}

		$dbType = strtolower(substr($dsn, 0, $pos));

		if (empty($dbType)) {
			throw new Exception('Missing database type from DSN string');
		}

		$data = [];

		foreach (explode(';', substr($dsn, $pos + 1)) as $kvp) {
			if (strpos($kvp, '=') !== false) {
				list($param, $value) = explode('=', $kvp);
				$data[trim(strtolower($param))] = $value;
			}
		}

		if (empty($data['host']) && empty($data['unix_socket'])) {
			throw new Exception('Missing host from DSN string');
		}

		if (empty($data['dbname'])) {
			throw new Exception('Missing database name from DSN string');
		}

		$this->host = (!empty($data['host'])) ? $data['host'] : $data['unix_socket'];
		$this->dbName = $data['dbname'];

		return $dsn;
	}

	/**
	 * Connect with PDO.
	 *
	 * @throws Exception
	 */
	private function connect()
	{
		try {
			$options = array_replace_recursive([
				PDO::ATTR_PERSISTENT => true,
				PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
				// Don't convert empty strings to SQL NULL values on data fetches.
				PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
				PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => false,
			], $this->pdoOptions);

			$this->conn = new PDO($this->dsn, $this->user, $this->pass, $options);
		} catch (PDOException $e) {
			$message = sprintf("Connection to %s failed with message: %s", $this->host, $e->getMessage());
			throw new Exception($message);
		}

		$this->db = $this->getAdapter();
	}

	public function getAdapter(): TypeAdapterInterface
	{
		return new self::$adapterClass($this->conn, $this->settings);
	}

	private function write(string $data): int
	{
		return $this->io->write($data);
	}

	/**
	 * Primary function, triggers dumping.
	 *
	 * @param string|null $filename Name of file to write sql dump to
	 * @throws Exception
	 */
	public function start(?string $filename = '')
	{
		$destination = 'php://stdout';

		// Output file can be redefined here
		if (!empty($filename)) {
			$destination = $filename;
		}

		// Connect to database
		$this->connect();

		// Create a new compressManager to manage compressed output
		$this->io = CompressManagerFactory::create($this->settings->getCompressMethod());

		// Create output file
		$this->io->open($destination);

		// Write some basic info to output file
		if (!$this->settings->skipComments()) {
			$this->write($this->getDumpFileHeader());
		}

		// Store server settings and use saner defaults to dump
		$this->write($this->db->backupParameters());

		if ($this->settings->isEnabled('databases')) {
			$this->write($this->db->getDatabaseHeader($this->dbName));

			if ($this->settings->isEnabled('add-drop-database')) {
				$this->write($this->db->addDropDatabase($this->dbName));
			}
		}

		// Get table, view, trigger, procedures, functions and events structures from database.
		$this->getDatabaseStructureTables();
		$this->getDatabaseStructureViews();
		$this->getDatabaseStructureTriggers();
		$this->getDatabaseStructureProcedures();
		$this->getDatabaseStructureFunctions();
		$this->getDatabaseStructureEvents();

		if ($this->settings->isEnabled('databases')) {
			$this->write($this->db->databases($this->dbName));
		}

		// If there still are some tables/views in include-tables array, that means that some tables or views weren't
		// found. Give proper error and exit. This check will be removed once include-tables supports regexps.
		if (0 < count($this->settings->getIncludedTables())) {
			$name = implode(',', $this->settings->getIncludedTables());
			$message = sprintf("Table '%s' not found in database", $name);
			throw new Exception($message);
		}

		$this->exportTables();
		$this->exportTriggers();
		$this->exportFunctions();
		$this->exportProcedures();
		$this->exportViews();
		$this->exportEvents();

		// Restore saved parameters.
		$this->write($this->db->restoreParameters());

		// Write some stats to output file.
		if (!$this->settings->skipComments()) {
			$this->write($this->getDumpFileFooter());
		}

		// Close output file.
		$this->io->close();
	}

	/**
	 * Returns header for dump file.
	 */
	private function getDumpFileHeader(): string
	{
		// Some info about software, source and time
		$header = sprintf(
			"-- mysqldump-php https://github.com/druidfi/mysqldump-php" . PHP_EOL .
			"--" . PHP_EOL .
			"-- Host: %s\tDatabase: %s" . PHP_EOL .
			"-- ------------------------------------------------------" . PHP_EOL,
			$this->host,
			$this->dbName
		);

		if (!empty($version = $this->db->getVersion())) {
			$header .= "-- Server version \t" . $version . PHP_EOL;
		}

		if (!$this->settings->skipDumpDate()) {
			$header .= "-- Date: " . date('r') . PHP_EOL . PHP_EOL;
		}

		return $header;
	}

	/**
	 * Returns footer for dump file.
	 */
	private function getDumpFileFooter(): string
	{
		$footer = '-- Dump completed';

		if (!$this->settings->skipDumpDate()) {
			$footer .= ' on: ' . date('r');
		}

		$footer .= PHP_EOL;

		return $footer;
	}

	/**
	 * Reads table names from database. Fills $this->tables array so they will be dumped later.
	 */
	private function getDatabaseStructureTables()
	{
		$includedTables = $this->settings->getIncludedTables();

		// Listing all tables from database
		if (empty($includedTables)) {
			// include all tables for now, blacklisting happens later
			foreach ($this->conn->query($this->db->showTables($this->dbName)) as $row) {
				$this->tables[] = current($row);
			}
		} else {
			// include only the tables mentioned in include-tables
			foreach ($this->conn->query($this->db->showTables($this->dbName)) as $row) {
				if (in_array(current($row), $includedTables, true)) {
					$this->tables[] = current($row);
					$elem = array_search(current($row), $includedTables);
					unset($includedTables[$elem]);
					$this->settings->setIncludedTables($includedTables);
				}
			}
		}
	}

	/**
	 * Reads view names from database. Fills $this->tables array so they will be dumped later.
	 */
	private function getDatabaseStructureViews()
	{
		$includedViews = $this->settings->getIncludedViews();

		// Listing all views from database
		if (empty($includedViews)) {
			// include all views for now, blacklisting happens later
			foreach ($this->conn->query($this->db->showViews($this->dbName)) as $row) {
				$this->views[] = current($row);
			}
		} else {
			// include only the tables mentioned in include-tables
			foreach ($this->conn->query($this->db->showViews($this->dbName)) as $row) {
				if (in_array(current($row), $includedViews, true)) {
					$this->views[] = current($row);
					$elem = array_search(current($row), $includedViews);
					unset($includedViews[$elem]);
				}
			}
		}
	}

	/**
	 * Reads trigger names from database. Fills $this->tables array so they will be dumped later.
	 */
	private function getDatabaseStructureTriggers()
	{
		// Listing all triggers from database
		if (!$this->settings->skipTriggers()) {
			foreach ($this->conn->query($this->db->showTriggers($this->dbName)) as $row) {
				$this->triggers[] = $row['Trigger'];
			}
		}
	}

	/**
	 * Reads procedure names from database. Fills $this->tables array so they will be dumped later.
	 */
	private function getDatabaseStructureProcedures()
	{
		// Listing all procedures from database
		if ($this->settings->isEnabled('routines')) {
			foreach ($this->conn->query($this->db->showProcedures($this->dbName)) as $row) {
				$this->procedures[] = $row['procedure_name'];
			}
		}
	}

	/**
	 * Reads functions names from database. Fills $this->tables array so they will be dumped later.
	 */
	private function getDatabaseStructureFunctions()
	{
		// Listing all functions from database
		if ($this->settings->isEnabled('routines')) {
			foreach ($this->conn->query($this->db->showFunctions($this->dbName)) as $row) {
				$this->functions[] = $row['function_name'];
			}
		}
	}

	/**
	 * Reads event names from database. Fills $this->tables array so they will be dumped later.
	 */
	private function getDatabaseStructureEvents()
	{
		// Listing all events from database
		if ($this->settings->isEnabled('events')) {
			foreach ($this->conn->query($this->db->showEvents($this->dbName)) as $row) {
				$this->events[] = $row['event_name'];
			}
		}
	}

	/**
	 * Compare if $table name matches with a definition inside $arr.
	 */
	private function matches(string $table, array $arr): bool
	{
		$match = false;

		foreach ($arr as $pattern) {
			if ('/' != $pattern[0]) {
				continue;
			}

			if (1 == preg_match($pattern, $table)) {
				$match = true;
			}
		}

		return in_array($table, $arr) || $match;
	}

	/**
	 * Exports all the tables selected from database
	 */
	private function exportTables()
	{
		// Exporting tables one by one
		foreach ($this->tables as $table) {
			if ($this->matches($table, $this->settings->getExcludedTables())) {
				continue;
			}

			$this->getTableStructure($table);
			$no_data = $this->settings->isEnabled('no-data');

			if (!$no_data) { // don't break compatibility with old trigger
				$this->listValues($table);
			} elseif ($no_data || $this->matches($table, $this->settings->getNoData())) {
				continue;
			} else {
				$this->listValues($table);
			}
		}
	}

	/**
	 * Exports all the views found in database.
	 */
	private function exportViews()
	{
		if (false === $this->settings->isEnabled('no-create-info')) {
			// Exporting views one by one
			foreach ($this->views as $view) {
				if ($this->matches($view, $this->settings->getExcludedTables())) {
					continue;
				}

				$this->tableColumnTypes[$view] = $this->getTableColumnTypes($view);
				$this->getViewStructureTable($view);
			}

			foreach ($this->views as $view) {
				if ($this->matches($view, $this->settings->getExcludedTables())) {
					continue;
				}

				$this->getViewStructureView($view);
			}
		}
	}

	/**
	 * Exports all the triggers found in database.
	 */
	private function exportTriggers()
	{
		foreach ($this->triggers as $trigger) {
			$this->getTriggerStructure($trigger);
		}
	}

	/**
	 * Exports all the procedures found in database.
	 */
	private function exportProcedures()
	{
		foreach ($this->procedures as $procedure) {
			$this->getProcedureStructure($procedure);
		}
	}

	/**
	 * Exports all the functions found in database.
	 */
	private function exportFunctions()
	{
		foreach ($this->functions as $function) {
			$this->getFunctionStructure($function);
		}
	}

	/**
	 * Exports all the events found in database.
	 * @throws Exception
	 */
	private function exportEvents()
	{
		foreach ($this->events as $event) {
			$this->getEventStructure($event);
		}
	}

	/**
	 * Table structure extractor.
	 *
	 * @param string $tableName Name of table to export
	 */
	private function getTableStructure(string $tableName)
	{
		if (!$this->settings->isEnabled('no-create-info')) {
			$ret = '';

			if (!$this->settings->skipComments()) {
				$ret = sprintf(
					"--" . PHP_EOL .
					"-- Table structure for table `%s`" . PHP_EOL .
					"--" . PHP_EOL . PHP_EOL,
					$tableName
				);
			}

			$stmt = $this->db->showCreateTable($tableName);

			foreach ($this->conn->query($stmt) as $r) {
				$this->write($ret);

				if ($this->settings->isEnabled('add-drop-table')) {
					$this->write($this->db->dropTable($tableName));
				}

				$this->write($this->db->createTable($r));

				break;
			}
		}

		$this->tableColumnTypes[$tableName] = $this->getTableColumnTypes($tableName);
	}

	/**
	 * Store column types to create data dumps and for Stand-In tables.
	 *
	 * @param string $tableName Name of table to export
	 * @return array type column types detailed
	 */
	private function getTableColumnTypes(string $tableName): array
	{
		$columnTypes = [];
		$columns = $this->conn->query($this->db->showColumns($tableName));
		$columns->setFetchMode(PDO::FETCH_ASSOC);

		foreach ($columns as $col) {
			$types = $this->db->parseColumnType($col);
			$columnTypes[$col['Field']] = [
				'is_numeric' => $types['is_numeric'],
				'is_blob' => $types['is_blob'],
				'type' => $types['type'],
				'type_sql' => $col['Type'],
				'is_virtual' => $types['is_virtual']
			];
		}

		return $columnTypes;
	}

	/**
	 * View structure extractor, create table (avoids cyclic references).
	 *
	 * @param string $viewName Name of view to export
	 */
	private function getViewStructureTable(string $viewName)
	{
		if (!$this->settings->skipComments()) {
			$ret = (
				'--' . PHP_EOL .
				sprintf('-- Stand-In structure for view `%s`', $viewName) . PHP_EOL .
				'--' . PHP_EOL . PHP_EOL
			);

			$this->write($ret);
		}

		$stmt = $this->db->showCreateView($viewName);

		// create views as tables, to resolve dependencies
		foreach ($this->conn->query($stmt) as $r) {
			if ($this->settings->isEnabled('add-drop-table')) {
				$this->write($this->db->dropView($viewName));
			}

			$this->write($this->createStandInTable($viewName));

			break;
		}
	}

	/**
	 * Write a create table statement for the table Stand-In, show create
	 * table would return a create algorithm when used on a view.
	 *
	 * @param string $viewName Name of view to export
	 * @return string create statement
	 */
	private function createStandInTable(string $viewName): string
	{
		$ret = [];

		foreach ($this->tableColumnTypes[$viewName] as $k => $v) {
			$ret[] = sprintf('`%s` %s', $k, $v['type_sql']);
		}

		$ret = implode(PHP_EOL . ',', $ret);

		return sprintf(
			"CREATE TABLE IF NOT EXISTS `%s` (" . PHP_EOL . "%s" . PHP_EOL . ");" . PHP_EOL,
			$viewName,
			$ret
		);
	}

	/**
	 * View structure extractor, create view.
	 */
	private function getViewStructureView(string $viewName)
	{
		if (!$this->settings->skipComments()) {
			$ret = sprintf(
				"--" . PHP_EOL .
				"-- View structure for view `%s`" . PHP_EOL .
				"--" . PHP_EOL . PHP_EOL,
				$viewName
			);

			$this->write($ret);
		}

		$stmt = $this->db->showCreateView($viewName);

		// Create views, to resolve dependencies replacing tables with views
		foreach ($this->conn->query($stmt) as $r) {
			// Because we must replace table with view, we should delete it
			$this->write($this->db->dropView($viewName));
			$this->write($this->db->createView($r));

			break;
		}
	}

	/**
	 * Trigger structure extractor.
	 *
	 * @param string $triggerName Name of trigger to export
	 */
	private function getTriggerStructure(string $triggerName)
	{
		$stmt = $this->db->showCreateTrigger($triggerName);

		foreach ($this->conn->query($stmt) as $r) {
			if ($this->settings->isEnabled('add-drop-trigger')) {
				$this->write($this->db->addDropTrigger($triggerName));
			}

			$this->write($this->db->createTrigger($r));

			return;
		}
	}

	/**
	 * Procedure structure extractor.
	 *
	 * @param string $procedureName Name of procedure to export
	 */
	private function getProcedureStructure(string $procedureName)
	{
		if (!$this->settings->skipComments()) {
			$ret = "--" . PHP_EOL .
				"-- Dumping routines for database '" . $this->dbName . "'" . PHP_EOL .
				"--" . PHP_EOL . PHP_EOL;
			$this->write($ret);
		}

		$stmt = $this->db->showCreateProcedure($procedureName);

		foreach ($this->conn->query($stmt) as $r) {
			$this->write($this->db->createProcedure($r));

			return;
		}
	}

	/**
	 * Function structure extractor.
	 *
	 * @param string $functionName Name of function to export
	 */
	private function getFunctionStructure(string $functionName)
	{
		if (!$this->settings->skipComments()) {
			$ret = "--" . PHP_EOL .
				"-- Dumping routines for database '" . $this->dbName . "'" . PHP_EOL .
				"--" . PHP_EOL . PHP_EOL;
			$this->write($ret);
		}

		$stmt = $this->db->showCreateFunction($functionName);

		foreach ($this->conn->query($stmt) as $r) {
			$this->write($this->db->createFunction($r));

			return;
		}
	}

	/**
	 * Event structure extractor.
	 *
	 * @param string $eventName Name of event to export
	 * @throws Exception
	 */
	private function getEventStructure(string $eventName)
	{
		if (!$this->settings->skipComments()) {
			$ret = "--" . PHP_EOL .
				"-- Dumping events for database '" . $this->dbName . "'" . PHP_EOL .
				"--" . PHP_EOL . PHP_EOL;
			$this->write($ret);
		}

		$stmt = $this->db->showCreateEvent($eventName);

		foreach ($this->conn->query($stmt) as $r) {
			$this->write($this->db->createEvent($r));

			return;
		}
	}

	/**
	 * Prepare values for output.
	 *
	 * @param string $tableName Name of table which contains rows
	 * @param array $row Associative array of column names and values to be quoted
	 */
	private function prepareColumnValues(string $tableName, array $row): array
	{
		$ret = [];
		$columnTypes = $this->tableColumnTypes[$tableName];

		if ($this->transformTableRowCallable) {
			$row = call_user_func($this->transformTableRowCallable, $tableName, $row);
		}

		foreach ($row as $colName => $colValue) {
			if ($this->transformColumnValueCallable) {
				$colValue = call_user_func($this->transformColumnValueCallable, $tableName, $colName, $colValue, $row);
			}

			$ret[] = $this->escape($colValue, $columnTypes[$colName]);
		}

		return $ret;
	}

	/**
	 * Escape values with quotes when needed.
	 */
	private function escape(?string $colValue, array $colType)
	{
		if (is_null($colValue)) {
			return 'NULL';
		} elseif ($this->settings->isEnabled('hex-blob') && $colType['is_blob']) {
			if ($colType['type'] == 'bit' || !empty($colValue)) {
				return sprintf('0x%s', $colValue);
			} else {
				return "''";
			}
		} elseif ($colType['is_numeric']) {
			return $colValue;
		}

		return $this->conn->quote($colValue);
	}

	/**
	 * Table rows extractor.
	 *
	 * @param string $tableName Name of table to export
	 */
	private function listValues(string $tableName)
	{
		$this->prepareListValues($tableName);

		$onlyOnce = true;
		$lineSize = 0;
		$colNames = [];

		// getting the column statement has side effect, so we backup this setting for consitency
		$completeInsertBackup = $this->settings->isEnabled('complete-insert');

		// colStmt is used to form a query to obtain row values
		$colStmt = $this->getColumnStmt($tableName);

		// colNames is used to get the name of the columns when using complete-insert
		if ($this->settings->isEnabled('complete-insert')) {
			$colNames = $this->getColumnNames($tableName);
		}

		$stmt = "SELECT " . implode(",", $colStmt) . " FROM `$tableName`";

		// Table specific conditions override the default 'where'
		$condition = $this->getTableWhere($tableName);

		if ($condition) {
			$stmt .= sprintf(' WHERE %s', $condition);
		}

		if ($limit = $this->getTableLimit($tableName)) {
			$stmt .= is_numeric($limit) ?
				sprintf(' LIMIT %d', $limit) :
				sprintf(' LIMIT %s', $limit);
		}

		$resultSet = $this->conn->query($stmt);
		$resultSet->setFetchMode(PDO::FETCH_ASSOC);

		$ignore = $this->settings->isEnabled('insert-ignore') ? '  IGNORE' : '';
		$count = 0;

		foreach ($resultSet as $row) {
			$count++;
			$values = $this->prepareColumnValues($tableName, $row);
			$valueList = implode(',', $values);

			if ($onlyOnce || !$this->settings->isEnabled('extended-insert')) {
				if ($this->settings->isEnabled('complete-insert') && count($colNames)) {
					$lineSize += $this->write(sprintf(
						'INSERT%s INTO `%s` (%s) VALUES (%s)',
						$ignore,
						$tableName,
						implode(', ', $colNames),
						$valueList
					));
				} else {
					$lineSize += $this->write(
						sprintf('INSERT%s INTO `%s` VALUES (%s)', $ignore, $tableName, $valueList)
					);
				}
				$onlyOnce = false;
			} else {
				$lineSize += $this->write(sprintf(',(%s)', $valueList));
			}

			if (($lineSize > $this->settings->getNetBufferLength())
				|| !$this->settings->isEnabled('extended-insert')) {
				$onlyOnce = true;
				$lineSize = $this->write(';' . PHP_EOL);
			}
		}

		$resultSet->closeCursor();

		if (!$onlyOnce) {
			$this->write(';' . PHP_EOL);
		}

		$this->endListValues($tableName, $count);

		if ($this->infoCallable && is_callable($this->infoCallable)) {
			call_user_func($this->infoCallable, 'table', ['name' => $tableName, 'rowCount' => $count]);
		}

		$this->settings->setCompleteInsert($completeInsertBackup);
	}

	/**
	 * Table rows extractor, append information prior to dump.
	 *
	 * @param string $tableName Name of table to export
	 */
	private function prepareListValues(string $tableName)
	{
		if (!$this->settings->skipComments()) {
			$this->write(
				"--" . PHP_EOL .
				"-- Dumping data for table `$tableName`" . PHP_EOL .
				"--" . PHP_EOL . PHP_EOL
			);
		}

		if ($this->settings->isEnabled('single-transaction')) {
			$this->conn->exec($this->db->setupTransaction());
			$this->conn->exec($this->db->startTransaction());
		}

		if ($this->settings->isEnabled('lock-tables') && !$this->settings->isEnabled('single-transaction')) {
			$this->db->lockTable($tableName);
		}

		if ($this->settings->isEnabled('add-locks')) {
			$this->write($this->db->startAddLockTable($tableName));
		}

		if ($this->settings->isEnabled('disable-keys')) {
			$this->write($this->db->startAddDisableKeys($tableName));
		}

		// Disable autocommit for faster reload
		if ($this->settings->isEnabled('no-autocommit')) {
			$this->write($this->db->startDisableAutocommit());
		}
	}

	/**
	 * Table rows extractor, close locks and commits after dump.
	 *
	 * @param string $tableName Name of table to export.
	 * @param integer $count Number of rows inserted.
	 */
	private function endListValues(string $tableName, int $count = 0)
	{
		if ($this->settings->isEnabled('disable-keys')) {
			$this->write($this->db->endAddDisableKeys($tableName));
		}

		if ($this->settings->isEnabled('add-locks')) {
			$this->write($this->db->endAddLockTable($tableName));
		}

		if ($this->settings->isEnabled('single-transaction')) {
			$this->conn->exec($this->db->commitTransaction());
		}

		if ($this->settings->isEnabled('lock-tables')
			&& !$this->settings->isEnabled('single-transaction')) {
			$this->db->unlockTable($tableName);
		}

		// Commit to enable autocommit
		if ($this->settings->isEnabled('no-autocommit')) {
			$this->write($this->db->endDisableAutocommit());
		}

		$this->write(PHP_EOL);

		if (!$this->settings->skipComments()) {
			$this->write(
				"-- Dumped table `" . $tableName . "` with $count row(s)" . PHP_EOL .
				'--' . PHP_EOL . PHP_EOL
			);
		}
	}

	/**
	 * Build SQL List of all columns on current table which will be used for selecting.
	 *
	 * @param string $tableName Name of table to get columns
	 *
	 * @return array SQL sentence with columns for select
	 */
	protected function getColumnStmt(string $tableName): array
	{
		$colStmt = [];
		foreach ($this->tableColumnTypes[$tableName] as $colName => $colType) {
			// TODO handle bug where PHP 8.1 returns double field wrong
			if ($colType['is_virtual']) {
				$this->settings->setCompleteInsert();
			} elseif ($colType['type'] == 'double' && PHP_VERSION_ID > 80100) {
				$colStmt[] = sprintf("CONCAT(`%s`) AS `%s`", $colName, $colName);
			} elseif ($colType['type'] === 'bit' && $this->settings->isEnabled('hex-blob')) {
				$colStmt[] = sprintf("LPAD(HEX(`%s`),2,'0') AS `%s`", $colName, $colName);
			} elseif ($colType['is_blob'] && $this->settings->isEnabled('hex-blob')) {
				$colStmt[] = sprintf("HEX(`%s`) AS `%s`", $colName, $colName);
			} else {
				$colStmt[] = sprintf("`%s`", $colName);
			}
		}

		return $colStmt;
	}

	/**
	 * Build SQL List of all columns on current table which will be used for inserting.
	 *
	 * @param string $tableName Name of table to get columns
	 *
	 * @return array columns for sql sentence for insert
	 */
	private function getColumnNames(string $tableName): array
	{
		$colNames = [];

		foreach ($this->tableColumnTypes[$tableName] as $colName => $colType) {
			if ($colType['is_virtual']) {
				$this->settings->setCompleteInsert();
			} else {
				$colNames[] = sprintf('`%s`', $colName);
			}
		}

		return $colNames;
	}

	/**
	 * Get table column types.
	 */
	protected function tableColumnTypes(): array
	{
		return $this->tableColumnTypes;
	}

	/**
	 * Keyed by table name, with the value as the conditions:
	 * e.g. 'users' => 'date_registered > NOW() - INTERVAL 6 MONTH AND deleted=0'
	 */
	public function setTableWheres(array $tableWheres)
	{
		$this->tableWheres = $tableWheres;
	}

	public function getTableWhere(string $tableName)
	{
		if (!empty($this->tableWheres[$tableName])) {
			return $this->tableWheres[$tableName];
		} elseif ($this->settings->get('where')) {
			return $this->settings->get('where');
		}

		return false;
	}

	/**
	 * Keyed by table name, with the value as the numeric limit: e.g. 'users' => 3000
	 */
	public function setTableLimits(array $tableLimits)
	{
		$this->tableLimits = $tableLimits;
	}

	/**
	 * Returns the LIMIT for the table. Must be numeric to be returned.
	 */
	public function getTableLimit(string $tableName)
	{
		if (!isset($this->tableLimits[$tableName])) {
			return false;
		}

		$limit = false;

		if (is_numeric($this->tableLimits[$tableName])) {
			$limit = $this->tableLimits[$tableName];
		}

		if (is_array($this->tableLimits[$tableName]) &&
			count($this->tableLimits[$tableName]) === 2 &&
			is_numeric(implode('', $this->tableLimits[$tableName]))
		) {
			$limit = implode(',', $this->tableLimits[$tableName]);
		}

		return $limit;
	}

	/**
	 * Add TypeAdapter
	 *
	 * @throws Exception
	 */
	public function addTypeAdapter(string $adapterClassName)
	{
		if (!is_a($adapterClassName, TypeAdapterInterface::class, true)) {
			$message = sprintf('Adapter %s is not instance of %s', $adapterClassName, TypeAdapterInterface::class);
			throw new Exception($message);
		}

		self::$adapterClass = $adapterClassName;
	}

	/**
	 * Set a callable that will be used to transform table rows.
	 */
	public function setTransformTableRowHook(callable $callable)
	{
		$this->transformTableRowCallable = $callable;
	}

	/**
	 * Set a callable that will be used to report dump information.
	 */
	public function setInfoHook(callable $callable)
	{
		$this->infoCallable = $callable;
	}
}


class DumpSettings
{
	// List of available connection strings.
	const UTF8    = 'utf8';
	const UTF8MB4 = 'utf8mb4';

	private static array $defaults = [
		'include-tables' => [],
		'exclude-tables' => [],
		'include-views' => [],
		'compress' => 'None',
		'init_commands' => [],
		'no-data' => [],
		'if-not-exists' => false,
		'reset-auto-increment' => false,
		'add-drop-database' => false,
		'add-drop-table' => false,
		'add-drop-trigger' => true,
		'add-locks' => true,
		'complete-insert' => false,
		'databases' => false,
		'default-character-set' => self::UTF8,
		'disable-keys' => true,
		'extended-insert' => true,
		'events' => false,
		'hex-blob' => true, /* faster than escaped content */
		'insert-ignore' => false,
		'net_buffer_length' => 1000000,
		'no-autocommit' => true,
		'no-create-info' => false,
		'lock-tables' => true,
		'routines' => false,
		'single-transaction' => true,
		'skip-triggers' => false,
		'skip-tz-utc' => false,
		'skip-comments' => false,
		'skip-dump-date' => false,
		'skip-definer' => false,
		'where' => '',
		/* deprecated */
		'disable-foreign-keys-check' => true
	];
	private array $settings;

	/**
	 * @throws Exception
	 */
	public function __construct(array $settings)
	{
		$this->settings = array_replace_recursive(self::$defaults, $settings);

		$this->settings['init_commands'][] = "SET NAMES " . $this->get('default-character-set');

		if (false === $this->settings['skip-tz-utc']) {
			$this->settings['init_commands'][] = "SET TIME_ZONE='+00:00'";
		}

		$diff = array_diff(array_keys($this->settings), array_keys(self::$defaults));

		if (count($diff) > 0) {
			throw new Exception("Unexpected value in dumpSettings: (" . implode(",", $diff) . ")");
		}

		if (!is_array($this->settings['include-tables']) || !is_array($this->settings['exclude-tables'])) {
			throw new Exception('Include-tables and exclude-tables should be arrays');
		}

		// If no include-views is passed in, dump the same views as tables, mimic mysqldump behaviour.
		if (!isset($settings['include-views'])) {
			$this->settings['include-views'] = $this->settings['include-tables'];
		}
	}

	public function getCompressMethod(): string
	{
		return $this->settings['compress'] ?? CompressManagerFactory::NONE;
	}

	public function getDefaultCharacterSet(): string
	{
		return $this->settings['default-character-set'];
	}

	public static function getDefaults(): array
	{
		return self::$defaults;
	}

	public function getExcludedTables(): array
	{
		return $this->settings['exclude-tables'] ?? [];
	}

	public function getIncludedTables(): array
	{
		return $this->settings['include-tables'] ?? [];
	}

	public function setIncludedTables(array $tables): void
	{
		$this->settings['include-tables'] = $tables;
	}

	public function getIncludedViews(): array
	{
		return $this->settings['include-views'] ?? [];
	}

	public function getInitCommands(): array
	{
		return $this->settings['init_commands'] ?? [];
	}

	public function getNetBufferLength(): int
	{
		return $this->settings['net_buffer_length'];
	}

	public function getNoData(): array
	{
		return $this->settings['no-data'] ?? [];
	}

	public function isEnabled(string $option): bool
	{
		return isset($this->settings[$option]) && $this->settings[$option] === true;
	}

	public function setCompleteInsert(bool $value = true)
	{
		$this->settings['complete-insert'] = $value;
	}

	public function skipComments(): bool
	{
		return $this->isEnabled('skip-comments');
	}

	public function skipDefiner(): bool
	{
		return $this->isEnabled('skip-definer');
	}

	public function skipDumpDate(): bool
	{
		return $this->isEnabled('skip-dump-date');
	}

	public function skipTriggers(): bool
	{
		return $this->isEnabled('skip-triggers');
	}

	public function skipTzUtc(): bool
	{
		return $this->isEnabled('skip-tz-utc');
	}

	public function get(string $option): string
	{
		return (string) $this->settings[$option];
	}
}


interface CompressInterface
{
	public function open(string $filename): bool;

	public function write(string $str): int;

	public function close(): bool;
}

abstract class CompressManagerFactory
{
	// List of available compression methods as constants.
	const GZIP  = 'Gzip';
	const BZIP2 = 'Bzip2';
	const NONE  = 'None';
	const GZIPSTREAM = 'Gzipstream';

	public static array $methods = [
		self::NONE,
		self::GZIP,
		self::BZIP2,
		self::GZIPSTREAM,
	];

	/**
	 * @throws Exception
	 */
	public static function create(string $method): CompressInterface
	{
		$method = ucfirst(strtolower($method));

		if (!in_array($method, self::$methods)) {
			throw new Exception("Compression method ($method) is not defined yet");
		}

		$methodClass = __NAMESPACE__."\\"."Compress".$method;

		return new $methodClass;
	}
}


class CompressNone implements CompressInterface
{
	private $fileHandler;

	/**
	 * @throws Exception
	 */
	public function open(string $filename): bool
	{
		$this->fileHandler = fopen($filename, 'wb');

		if (false === $this->fileHandler) {
			throw new Exception('Output file is not writable');
		}

		return true;
	}

	/**
	 * @throws Exception
	 */
	public function write(string $str): int
	{
		$bytesWritten = fwrite($this->fileHandler, $str);

		if (false === $bytesWritten) {
			throw new Exception('Writing to file failed! Probably, there is no more free space left?');
		}

		return $bytesWritten;
	}

	public function close(): bool
	{
		return fclose($this->fileHandler);
	}
}

interface TypeAdapterInterface
{
	public function addDropDatabase(string $databaseName): string;
	public function addDropTrigger(string $triggerName): string;
	public function backupParameters(): string;
	public function commitTransaction(): string;
	public function createEvent(array $row): string;
	public function createFunction(array $row): string;
	public function createProcedure(array $row): string;
	public function createTable(array $row): string;
	public function createTrigger(array $row): string;
	public function createView(array $row): string;
	public function databases(string $databaseName): string;
	public function dropTable(string $tableName): string;
	public function dropView(string $viewName): string;
	public function endAddDisableKeys(string $tableName): string;
	public function endAddLockTable(string $tableName): string;
	public function endDisableAutocommit(): string;
	public function getDatabaseHeader(string $databaseName): string;
	public function getVersion(): string;
	public function lockTable(string $tableName): string;
	public function parseColumnType(array $colType): array;
	public function restoreParameters(): string;
	public function setupTransaction(): string;
	public function showColumns(string $tableName): string;
	public function showCreateEvent(string $eventName): string;
	public function showCreateFunction(string $functionName): string;
	public function showCreateProcedure(string $procedureName): string;
	public function showCreateTable(string $tableName): string;
	public function showCreateTrigger(string $triggerName): string;
	public function showCreateView(string $viewName): string;
	public function showEvents(string $databaseName): string;
	public function showFunctions(string $databaseName): string;
	public function showProcedures(string $databaseName): string;
	public function showTables(string $databaseName): string;
	public function showTriggers(string $databaseName): string;
	public function showViews(string $databaseName): string;
	public function startAddDisableKeys(string $tableName): string;
	public function startAddLockTable(string $tableName): string;
	public function startDisableAutocommit(): string;
	public function startTransaction(): string;
	public function unlockTable(string $tableName): string;
}

class TypeAdapterMysql implements TypeAdapterInterface
{
	const DEFINER_RE = 'DEFINER=`(?:[^`]|``)*`@`(?:[^`]|``)*`';

	protected PDO $db;
	protected DumpSettings $settings;

	// Numerical Mysql types
	public array $mysqlTypes = [
		'numerical' => [
			'bit',
			'tinyint',
			'smallint',
			'mediumint',
			'int',
			'integer',
			'bigint',
			'real',
			'double',
			'float',
			'decimal',
			'numeric'
		],
		'blob' => [
			'tinyblob',
			'blob',
			'mediumblob',
			'longblob',
			'binary',
			'varbinary',
			'bit',
			'geometry', /* https://bugs.mysql.com/bug.php?id=43544 */
			'point',
			'linestring',
			'polygon',
			'multipoint',
			'multilinestring',
			'multipolygon',
			'geometrycollection',
		]
	];

	public function __construct(PDO $conn, DumpSettings $settings)
	{
		$this->db = $conn;
		$this->settings = $settings;

		// Execute init commands once connected
		foreach ($this->settings->getInitCommands() as $stmt) {
			$this->db->exec($stmt);
		}
	}

	public function databases(string $databaseName): string
	{
		$stmt = $this->db->query("SHOW VARIABLES LIKE 'character_set_database';");
		$characterSet = $stmt->fetchColumn(1);
		$stmt->closeCursor();

		$stmt = $this->db->query("SHOW VARIABLES LIKE 'collation_database';");
		$collation = $stmt->fetchColumn(1);
		$stmt->closeCursor();

		return sprintf(
			"CREATE DATABASE /*!32312 IF NOT EXISTS*/ `%s`" .
			" /*!40100 DEFAULT CHARACTER SET %s " .
			" COLLATE %s */;" . PHP_EOL . PHP_EOL .
			"USE `%s`;" . PHP_EOL . PHP_EOL,
			$databaseName,
			$characterSet,
			$collation,
			$databaseName
		);
	}

	public function showCreateTable(string $tableName): string
	{
		return "SHOW CREATE TABLE `$tableName`";
	}

	public function showCreateView(string $viewName): string
	{
		return "SHOW CREATE VIEW `$viewName`";
	}

	public function showCreateTrigger(string $triggerName): string
	{
		return "SHOW CREATE TRIGGER `$triggerName`";
	}

	public function showCreateProcedure(string $procedureName): string
	{
		return "SHOW CREATE PROCEDURE `$procedureName`";
	}

	public function showCreateFunction(string $functionName): string
	{
		return "SHOW CREATE FUNCTION `$functionName`";
	}

	public function showCreateEvent(string $eventName): string
	{
		return "SHOW CREATE EVENT `$eventName`";
	}

	/**
	 * @throws Exception
	 */
	public function createTable(array $row): string
	{
		if (!isset($row['Create Table'])) {
			throw new Exception("Error getting table code, unknown output");
		}

		$createTable = $row['Create Table'];
		if ($this->settings->isEnabled('reset-auto-increment')) {
			$match = "/AUTO_INCREMENT=[0-9]+/s";
			$replace = "";
			$createTable = preg_replace($match, $replace, $createTable);
		}

		if ($this->settings->isEnabled('if-not-exists')) {
			$createTable = preg_replace('/^CREATE TABLE/', 'CREATE TABLE IF NOT EXISTS', $createTable);
		}

		return "/*!40101 SET @saved_cs_client     = @@character_set_client */;".PHP_EOL.
			"/*!40101 SET character_set_client = ". $this->settings->getDefaultCharacterSet() ." */;".PHP_EOL.
			$createTable.";".PHP_EOL.
			"/*!40101 SET character_set_client = @saved_cs_client */;".PHP_EOL.
			PHP_EOL;
	}

	/**
	 * @throws Exception
	 */
	public function createView(array $row): string
	{
		$ret = "";

		if (!isset($row['Create View'])) {
			throw new Exception("Error getting view structure, unknown output");
		}

		$viewStmt = $row['Create View'];

		$definerStr = $this->settings->skipDefiner() ? '' : '/*!50013 \2 */' . PHP_EOL;

		if ($viewStmtReplaced = preg_replace(
			'/^(CREATE(?:\s+ALGORITHM=(?:UNDEFINED|MERGE|TEMPTABLE))?)\s+('
			.self::DEFINER_RE.'(?:\s+SQL SECURITY DEFINER|INVOKER)?)?\s+(VIEW .+)$/',
			'/*!50001 \1 */'.PHP_EOL.$definerStr.'/*!50001 \3 */',
			$viewStmt,
			1
		)) {
			$viewStmt = $viewStmtReplaced;
		};

		$ret .= $viewStmt.';'.PHP_EOL.PHP_EOL;

		return $ret;
	}

	/**
	 * @throws Exception
	 */
	public function createTrigger(array $row): string
	{
		$ret = "";
		if (!isset($row['SQL Original Statement'])) {
			throw new Exception("Error getting trigger code, unknown output");
		}

		$triggerStmt = $row['SQL Original Statement'];
		$definerStr = $this->settings->skipDefiner() ? '' : '/*!50017 \2*/ ';
		if ($triggerStmtReplaced = preg_replace(
			'/^(CREATE)\s+('.self::DEFINER_RE.')?\s+(TRIGGER\s.*)$/s',
			'/*!50003 \1*/ '.$definerStr.'/*!50003 \3 */',
			$triggerStmt,
			1
		)) {
			$triggerStmt = $triggerStmtReplaced;
		}

		$ret .= "DELIMITER ;;".PHP_EOL.
			$triggerStmt.";;".PHP_EOL.
			"DELIMITER ;".PHP_EOL.PHP_EOL;

		return $ret;
	}

	/**
	 * @throws Exception
	 */
	public function createProcedure(array $row): string
	{
		$ret = "";

		if (!isset($row['Create Procedure'])) {
			throw new Exception("Error getting procedure code, unknown output. ".
				"Please check 'https://bugs.mysql.com/bug.php?id=14564'");
		}

		$procedureStmt = $row['Create Procedure'];

		if ($this->settings->skipDefiner()) {
			if ($procedureStmtReplaced = preg_replace(
				'/^(CREATE)\s+('.self::DEFINER_RE.')?\s+(PROCEDURE\s.*)$/s',
				'\1 \3',
				$procedureStmt,
				1
			)) {
				$procedureStmt = $procedureStmtReplaced;
			}
		}

		$ret .= "/*!50003 DROP PROCEDURE IF EXISTS `".
			$row['Procedure']."` */;".PHP_EOL.
			"/*!40101 SET @saved_cs_client     = @@character_set_client */;".PHP_EOL.
			"/*!40101 SET character_set_client = ".$this->settings->getDefaultCharacterSet()." */;".PHP_EOL.
			"DELIMITER ;;".PHP_EOL.
			$procedureStmt." ;;".PHP_EOL.
			"DELIMITER ;".PHP_EOL.
			"/*!40101 SET character_set_client = @saved_cs_client */;".PHP_EOL.PHP_EOL;

		return $ret;
	}

	/**
	 * @throws Exception
	 */
	public function createFunction(array $row): string
	{
		$ret = "";

		if (!isset($row['Create Function'])) {
			throw new Exception("Error getting function code, unknown output. ".
				"Please check 'https://bugs.mysql.com/bug.php?id=14564'");
		}
		$functionStmt = $row['Create Function'];
		$characterSetClient = $row['character_set_client'];
		$collationConnection = $row['collation_connection'];
		$sqlMode = $row['sql_mode'];

		if ($this->settings->skipDefiner()) {
			if ($functionStmtReplaced = preg_replace(
				'/^(CREATE)\s+('.self::DEFINER_RE.')?\s+(FUNCTION\s.*)$/s',
				'\1 \3',
				$functionStmt,
				1
			)) {
				$functionStmt = $functionStmtReplaced;
			}
		}

		$ret .= "/*!50003 DROP FUNCTION IF EXISTS `".
			$row['Function']."` */;".PHP_EOL.
			"/*!40101 SET @saved_cs_client     = @@character_set_client */;".PHP_EOL.
			"/*!50003 SET @saved_cs_results     = @@character_set_results */ ;".PHP_EOL.
			"/*!50003 SET @saved_col_connection = @@collation_connection */ ;".PHP_EOL.
			"/*!40101 SET character_set_client = ".$characterSetClient." */;".PHP_EOL.
			"/*!40101 SET character_set_results = ".$characterSetClient." */;".PHP_EOL.
			"/*!50003 SET collation_connection  = ".$collationConnection." */ ;".PHP_EOL.
			"/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;;".PHP_EOL.
			"/*!50003 SET sql_mode              = '".$sqlMode."' */ ;;".PHP_EOL.
			"/*!50003 SET @saved_time_zone      = @@time_zone */ ;;".PHP_EOL.
			"/*!50003 SET time_zone             = 'SYSTEM' */ ;;".PHP_EOL.
			"DELIMITER ;;".PHP_EOL.
			$functionStmt." ;;".PHP_EOL.
			"DELIMITER ;".PHP_EOL.
			"/*!50003 SET sql_mode              = @saved_sql_mode */ ;".PHP_EOL.
			"/*!50003 SET character_set_client  = @saved_cs_client */ ;".PHP_EOL.
			"/*!50003 SET character_set_results = @saved_cs_results */ ;".PHP_EOL.
			"/*!50003 SET collation_connection  = @saved_col_connection */ ;".PHP_EOL.
			"/*!50106 SET TIME_ZONE= @saved_time_zone */ ;".PHP_EOL.PHP_EOL;


		return $ret;
	}

	/**
	 * @throws Exception
	 */
	public function createEvent(array $row): string
	{
		$ret = "";

		if (!isset($row['Create Event'])) {
			throw new Exception("Error getting event code, unknown output. ".
				"Please check 'https://stackoverflow.com/questions/10853826/mysql-5-5-create-event-gives-syntax-error'");
		}

		$eventName = $row['Event'];
		$eventStmt = $row['Create Event'];
		$sqlMode = $row['sql_mode'];
		$definerStr = $this->settings->skipDefiner() ? '' : '/*!50117 \2*/ ';

		if ($eventStmtReplaced = preg_replace(
			'/^(CREATE)\s+('.self::DEFINER_RE.')?\s+(EVENT .*)$/',
			'/*!50106 \1*/ '.$definerStr.'/*!50106 \3 */',
			$eventStmt,
			1
		)) {
			$eventStmt = $eventStmtReplaced;
		}

		$ret .= "/*!50106 SET @save_time_zone= @@TIME_ZONE */ ;".PHP_EOL.
			"/*!50106 DROP EVENT IF EXISTS `".$eventName."` */;".PHP_EOL.
			"DELIMITER ;;".PHP_EOL.
			"/*!50003 SET @saved_cs_client      = @@character_set_client */ ;;".PHP_EOL.
			"/*!50003 SET @saved_cs_results     = @@character_set_results */ ;;".PHP_EOL.
			"/*!50003 SET @saved_col_connection = @@collation_connection */ ;;".PHP_EOL.
			"/*!50003 SET character_set_client  = utf8 */ ;;".PHP_EOL.
			"/*!50003 SET character_set_results = utf8 */ ;;".PHP_EOL.
			"/*!50003 SET collation_connection  = utf8_general_ci */ ;;".PHP_EOL.
			"/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;;".PHP_EOL.
			"/*!50003 SET sql_mode              = '".$sqlMode."' */ ;;".PHP_EOL.
			"/*!50003 SET @saved_time_zone      = @@time_zone */ ;;".PHP_EOL.
			"/*!50003 SET time_zone             = 'SYSTEM' */ ;;".PHP_EOL.
			$eventStmt." ;;".PHP_EOL.
			"/*!50003 SET time_zone             = @saved_time_zone */ ;;".PHP_EOL.
			"/*!50003 SET sql_mode              = @saved_sql_mode */ ;;".PHP_EOL.
			"/*!50003 SET character_set_client  = @saved_cs_client */ ;;".PHP_EOL.
			"/*!50003 SET character_set_results = @saved_cs_results */ ;;".PHP_EOL.
			"/*!50003 SET collation_connection  = @saved_col_connection */ ;;".PHP_EOL.
			"DELIMITER ;".PHP_EOL.
			"/*!50106 SET TIME_ZONE= @save_time_zone */ ;".PHP_EOL.PHP_EOL;
		// Commented because we are doing this in restore_parameters()
		// "/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;" . PHP_EOL . PHP_EOL;

		return $ret;
	}

	public function showTables(string $databaseName): string
	{
		return sprintf(
			"SELECT TABLE_NAME AS tbl_name ".
			"FROM INFORMATION_SCHEMA.TABLES ".
			"WHERE TABLE_TYPE='BASE TABLE' AND TABLE_SCHEMA='%s' ".
			"ORDER BY TABLE_NAME",
			$databaseName
		);
	}

	public function showViews(string $databaseName): string
	{
		return sprintf(
			"SELECT TABLE_NAME AS tbl_name ".
			"FROM INFORMATION_SCHEMA.TABLES ".
			"WHERE TABLE_TYPE='VIEW' AND TABLE_SCHEMA='%s' ".
			"ORDER BY TABLE_NAME",
			$databaseName
		);
	}

	public function showTriggers(string $databaseName): string
	{
		return sprintf("SHOW TRIGGERS FROM `%s`;", $databaseName);
	}

	public function showColumns(string $tableName): string
	{
		return sprintf("SHOW COLUMNS FROM `%s`;", $tableName);
	}

	public function showProcedures(string $databaseName): string
	{
		return sprintf(
			"SELECT SPECIFIC_NAME AS procedure_name ".
			"FROM INFORMATION_SCHEMA.ROUTINES ".
			"WHERE ROUTINE_TYPE='PROCEDURE' AND ROUTINE_SCHEMA='%s'",
			$databaseName
		);
	}

	public function showFunctions(string $databaseName): string
	{
		return sprintf(
			"SELECT SPECIFIC_NAME AS function_name ".
			"FROM INFORMATION_SCHEMA.ROUTINES ".
			"WHERE ROUTINE_TYPE='FUNCTION' AND ROUTINE_SCHEMA='%s'",
			$databaseName
		);
	}

	/**
	 * Get query string to ask for names of events from current database.
	 */
	public function showEvents(string $databaseName): string
	{
		return sprintf(
			"SELECT EVENT_NAME AS event_name ".
			"FROM INFORMATION_SCHEMA.EVENTS ".
			"WHERE EVENT_SCHEMA='%s'",
			$databaseName
		);
	}

	public function setupTransaction(): string
	{
		return "SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ";
	}

	public function startTransaction(): string
	{
		return "START TRANSACTION ".
			"/*!40100 WITH CONSISTENT SNAPSHOT */";
	}

	public function commitTransaction(): string
	{
		return "COMMIT";
	}

	public function lockTable(string $tableName): string
	{
		return $this->db->exec(sprintf("LOCK TABLES `%s` READ LOCAL", $tableName));
	}

	public function unlockTable(string $tableName): string
	{
		return $this->db->exec("UNLOCK TABLES");
	}

	public function startAddLockTable(string $tableName): string
	{
		return sprintf("LOCK TABLES `%s` WRITE;" . PHP_EOL, $tableName);
	}

	public function endAddLockTable(string $tableName): string
	{
		return "UNLOCK TABLES;".PHP_EOL;
	}

	public function startAddDisableKeys(string $tableName): string
	{
		return sprintf("/*!40000 ALTER TABLE `%s` DISABLE KEYS */;". PHP_EOL, $tableName);
	}

	public function endAddDisableKeys(string $tableName): string
	{
		return sprintf("/*!40000 ALTER TABLE `%s` ENABLE KEYS */;". PHP_EOL, $tableName);
	}

	public function startDisableAutocommit(): string
	{
		return "SET autocommit=0;".PHP_EOL;
	}

	public function endDisableAutocommit(): string
	{
		return "COMMIT;".PHP_EOL;
	}

	public function addDropDatabase(string $databaseName): string
	{
		return sprintf("/*!40000 DROP DATABASE IF EXISTS `%s`*/;". PHP_EOL.PHP_EOL, $databaseName);
	}

	public function addDropTrigger(string $triggerName): string
	{
		return sprintf("DROP TRIGGER IF EXISTS `%s`;".PHP_EOL, $triggerName);
	}

	public function dropTable(string $tableName): string
	{
		return sprintf("DROP TABLE IF EXISTS `%s`;".PHP_EOL, $tableName);
	}

	public function dropView(string $viewName): string
	{
		return sprintf(
			"DROP TABLE IF EXISTS `%s`;".PHP_EOL.
			"/*!50001 DROP VIEW IF EXISTS `%s`*/;".PHP_EOL,
			$viewName,
			$viewName
		);
	}

	public function getDatabaseHeader(string $databaseName): string
	{
		return sprintf(
			"--".PHP_EOL.
			"-- Current Database: `%s`".PHP_EOL.
			"--".PHP_EOL.PHP_EOL,
			$databaseName
		);
	}

	/**
	 * Decode column metadata and fill info structure.
	 * type, is_numeric and is_blob will always be available.
	 *
	 * @param array $colType Array returned from "SHOW COLUMNS FROM tableName"
	 * @return array
	 */
	public function parseColumnType(array $colType): array
	{
		$colInfo = [];
		$colParts = explode(" ", $colType['Type']);

		if ($fparen = strpos($colParts[0], "(")) {
			$colInfo['type'] = substr($colParts[0], 0, $fparen);
			$colInfo['length'] = str_replace(")", "", substr($colParts[0], $fparen + 1));
			$colInfo['attributes'] = $colParts[1] ?? null;
		} else {
			$colInfo['type'] = $colParts[0];
		}
		$colInfo['is_numeric'] = in_array($colInfo['type'], $this->mysqlTypes['numerical']);
		$colInfo['is_blob'] = in_array($colInfo['type'], $this->mysqlTypes['blob']);
		// for virtual columns that are of type 'Extra', column type
		// could by "STORED GENERATED" or "VIRTUAL GENERATED"
		// MySQL reference: https://dev.mysql.com/doc/refman/5.7/en/create-table-generated-columns.html
		$colInfo['is_virtual'] = strpos($colType['Extra'], "VIRTUAL GENERATED") !== false
			|| strpos($colType['Extra'], "STORED GENERATED") !== false;

		return $colInfo;
	}

	public function backupParameters(): string
	{
		$ret = "/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;".PHP_EOL.
			"/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;".PHP_EOL.
			"/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;".PHP_EOL.
			"/*!40101 SET NAMES ". $this->settings->getDefaultCharacterSet() ." */;".PHP_EOL;

		if (false === $this->settings->skipTzUtc()) {
			$ret .= "/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;".PHP_EOL.
				"/*!40103 SET TIME_ZONE='+00:00' */;".PHP_EOL;
		}

		if ($this->settings->isEnabled('no-autocommit')) {
			$ret .= "/*!40101 SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT */;".PHP_EOL;
		}

		$ret .= "/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;".PHP_EOL.
			"/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;".PHP_EOL.
			"/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;".PHP_EOL.
			"/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;".PHP_EOL.PHP_EOL;

		return $ret;
	}

	public function restoreParameters(): string
	{
		$ret = "";

		if (!$this->settings->skipTzUtc()) {
			$ret .= "/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;".PHP_EOL;
		}

		if ($this->settings->isEnabled('no-autocommit')) {
			$ret .= "/*!40101 SET AUTOCOMMIT=@OLD_AUTOCOMMIT */;".PHP_EOL;
		}

		$ret .= "/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;".PHP_EOL.
			"/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;".PHP_EOL.
			"/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;".PHP_EOL.
			"/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;".PHP_EOL.
			"/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;".PHP_EOL.
			"/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;".PHP_EOL.
			"/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;".PHP_EOL.PHP_EOL;

		return $ret;
	}

	public function getVersion(): string
	{
		return $this->db->getAttribute(PDO::ATTR_SERVER_VERSION);
	}
}
