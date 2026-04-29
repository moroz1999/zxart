<?php

use Illuminate\Database\Connection;

class pdoTransport extends errorLogger implements transportObject
{
    protected static $instance;
    protected $hostAddress;
    protected $userName;
    protected $password;
    protected $database;
    protected $encodingName;
    protected $tablesPrefix;
    protected $connection;
    protected $debug = false;
    /**
     * @var PDO
     */
    protected $pdo;
    protected $lastOperation;
    protected $columnsString;
    protected $whereString;
    protected $orderString;
    protected $groupString;
    protected $limitString;
    protected $dataLines;
    protected $resourceName;
    protected $queriesHistory = [];
    public $lastInsertedID;

    public static function getInstance($config)
    {
        if (!self::$instance) {
            self::$instance = new self($config);
        }
        return self::$instance;
    }

    /**
     * pdoTransport constructor.
     * @param Config $config
     */
    public function __construct($config)
    {
        $this->hostAddress = $config->get('mysqlHost');
        $this->userName = $config->get('mysqlUser');
        $this->password = $config->get('mysqlPassword');
        $this->encodingName = $config->get('mysqlConnectionEncoding');
        $this->tablesPrefix = $config->get('mysqlTablesPrefix');
        $this->dbConnect();
    }

    protected function dbConnect(): void
    {
        $this->pdo = controller::getInstance()->getContainer()->get(Connection::class)->getPdo();
    }

    protected function setDatabase($database)
    {
        $this->database = $database;
    }

    protected function setNames()
    {
        $query = "SET NAMES '" . $this->encodingName . "'";
        return !!$this->sendQuery($query);
    }

    protected function sendQuery($sqlQuery)
    {
        if ($this->debug) {
            $start = microtime(true);
        }
        try {
            $statement = $this->pdo->prepare($sqlQuery);
            if ($result = $statement->execute()) {
                if ($this->debug) {
                    $end = microtime(true);
                    $this->queriesHistory[] = [
                        'time' => sprintf("%.2f", ($end - $start) * 1000),
                        'query' => $sqlQuery,
                    ];
                }
                return $statement;
            }
        } catch (Exception $e) {
            $this->logError($e->getMessage());
        }
        return false;
    }

    public function setSearchLines($searchLines)
    {
        if (is_array($searchLines)) {
            $fieldsStrings = [];
            foreach ($searchLines as $searchFieldName => &$searchFieldValue) {
                if (is_array($searchFieldValue)) {
                    $searchFieldValue = $this->escapeArray($searchFieldValue);
                    if (count($searchFieldValue) > 1) {
                        $fieldsStrings[] = "`" . $searchFieldName . "` IN ('" . implode("','", $searchFieldValue) . "')";
                    } else {
                        $fieldsStrings[] = "`" . $searchFieldName . "` = '" . reset($searchFieldValue) . "'";
                    }
                } else {
                    $fieldsStrings[] = "`" . $searchFieldName . "`='" . $this->escape($searchFieldValue) . "'";
                }
            }
            if ($fieldsStrings) {
                $selectFieldsString = implode(" AND ", $fieldsStrings);
                $this->whereString = "WHERE (" . $selectFieldsString . ") ";
            } else {
                $this->whereString = "";
            }
        } else {
            $this->logError('Attempted to set an invalid search fields array');
        }
    }

    public function setLimitFields($limitParameters = null)
    {
        $limitString = '';
        if ($limitParameters !== null) {
            if (is_numeric($limitParameters)) {
                $limitString = ' LIMIT ' . $limitParameters;
            } elseif (count($limitParameters) == 2) {
                if (is_numeric($limitParameters[0]) && is_numeric($limitParameters[1])) {
                    $limitString = ' LIMIT ' . $limitParameters[0] . ',' . $limitParameters[1];
                }
            }
        }
        $this->limitString = $limitString;
    }

    public function setGroupFields($groupParameters = null)
    {
        if (is_array($groupParameters) && $groupParameters) {
            $this->groupString = ' GROUP BY `' . implode('`,`', $groupParameters) . '`';
        } elseif ($groupParameters) {
            $this->groupString = ' GROUP BY `' . $groupParameters . '`';
        } else {
            $this->groupString = '';
        }
    }

    public function setOrderFields($orderLines, $literal = false)
    {
        $this->orderString = '';
        if (is_array($orderLines)) {
            if ($orderLines) {
                $strings = [];
                foreach ($orderLines as $column => $order) {
                    if (is_array($order)) {
                        if (count($order) > 1) {
                            $strings[] = ' FIELD(`' . $column . '`,' . implode(',', $order) . ')';
                        }
                    } elseif ($order == '2' || $order === 'rand' || $order === 'RAND') {
                        $strings[] = ' RAND()';
                    } elseif ($order == '1' || $order === 'asc' || $order === 'ASC') {
                        if ($literal) {
                            $strings[] = $column . ' ASC';
                        } else {
                            $strings[] = $this->escape($column) . ' ASC';
                        }
                    } else {
                        if ($literal) {
                            $strings[] = $column . ' DESC';
                        } else {
                            $strings[] = $this->escape($column) . ' DESC';
                        }
                    }
                }
                if ($strings) {
                    $this->orderString = ' ORDER BY ' . implode(',', $strings);
                }
            }
        }
    }

    public function setReturnColumns($returnColumns = null, $literal = false)
    {
        if ($returnColumns === null || !$returnColumns) {
            $this->columnsString = '*';
        } else {
            if (!is_array($returnColumns)) {
                if (!$literal) {
                    $this->columnsString = '`' . $returnColumns . '`';
                } else {
                    $this->columnsString = $returnColumns;
                }
            } else {
                if (!$literal) {
                    $this->columnsString = '`' . implode('`,`', $returnColumns) . '`';
                } else {
                    $this->columnsString = implode(',', $returnColumns);
                }
            }
        }
    }

    public function setConditions($conditions = false)
    {
        $this->whereString = '';
        if ($strings = $this->parseConditions($conditions)) {
            $this->whereString = 'WHERE ' . implode(' AND ', $strings);
        }
    }

    public function setOrConditions($orConditions = false)
    {
        $this->whereString = '';
        if ($orConditions && count($orConditions)) {
            if (!is_array(reset($orConditions))) {
                $orConditions = [$orConditions];
            }
            $orStrings = [];
            foreach ($orConditions as &$conditions) {
                if ($strings = $this->parseConditions($conditions)) {
                    $orStrings[] = implode(' AND ', $strings);
                }
            }
            if ($orStrings) {
                $this->whereString = 'WHERE (' . implode(') OR (', $orStrings) . ')';
            }
        }
    }

    protected function parseConditions($conditions = false)
    {
        $strings = [];
        if ($conditions) {
            if (!is_array(reset($conditions))) {
                $conditions = [$conditions];
            }

            foreach ($conditions as &$condition) {
                $column = array_shift($condition);
                $action = array_shift($condition);
                $argument = array_shift($condition);
                $literal = array_shift($condition);

                if ($literal) {
                    $strings[] = $column . ' ' . $action . ' ' . $argument;
                } else {
                    if ($action === 'equals' || $action === '=') {
                        $strings[] = '`' . $column . '`="' . $this->escape($argument) . '"';
                    } elseif ($action === 'not equals' || $action === '!=' || $action === '<>') {
                        $strings[] = '`' . $column . '`!="' . $this->escape($argument) . '"';
                    } elseif ($action === 'like') {
                        if (is_array($column)) {
                            $orStrings = [];
                            foreach ($column as &$fieldName) {
                                if (is_array($argument)) {
                                    foreach ($argument as &$argumentValue) {
                                        $orStrings[] = '`' . $fieldName . '` LIKE "' . $this->escape($argumentValue) . '"';
                                    }
                                } else {
                                    $orStrings[] = '`' . $fieldName . '` LIKE "' . $this->escape($argument) . '"';
                                }
                            }
                            $strings[] = '(' . implode(' OR ', $orStrings) . ')';
                        } elseif (is_array($argument)) {
                            $partStrings = [];
                            foreach ($argument as &$argumentItem) {
                                $partStrings[] = '`' . $column . '` LIKE "%' . $this->escape($argumentItem) . '%"';
                            }
                            $strings[] = '(' . implode(' OR ', $partStrings) . ')';
                        } else {
                            $strings[] = '`' . $column . '` LIKE "' . $this->escape($argument) . '"';
                        }
                    } elseif ($action === 'reverselike') {
                        $strings[] = '"' . $this->escape($argument) . '" LIKE concat("%",' . $column . ', "%")';
                    } elseif ($action === '>') {
                        $strings[] = '`' . $column . '`>"' . $this->escape($argument) . '"';
                    } elseif ($action === '<') {
                        $strings[] = '`' . $column . '`<"' . $this->escape($argument) . '"';
                    } elseif ($action === '>=') {
                        $strings[] = '`' . $column . '`>="' . $this->escape($argument) . '"';
                    } elseif ($action === '<=') {
                        $strings[] = '`' . $column . '`<="' . $this->escape($argument) . '"';
                    } elseif ($action === 'in' || $action == 'IN') {
                        $argument = $this->escapeArray($argument);
                        if (count($argument) != 1) {
                            if (is_array($column)) {
                                $orStrings = [];
                                foreach ($column as &$columnName) {
                                    $orStrings[] = '`' . $columnName . '` IN ("' . implode('","', $argument) . '")';
                                }
                                $strings[] = '(' . implode(' OR ', $orStrings) . ')';
                            } else {
                                $strings[] = '`' . $column . '` IN ("' . implode('","', $argument) . '")';
                            }
                        } else {
                            if (is_array($column)) {
                                $orStrings = [];
                                foreach ($column as &$columnName) {
                                    $orStrings[] = '`' . $columnName . '`="' . reset($argument) . '"';
                                }
                                $strings[] = '(' . implode(' OR ', $orStrings) . ')';
                            } else {
                                $strings[] = '`' . $column . '`="' . reset($argument) . '"';
                            }
                        }
                    } elseif ($action === 'not in' || $action == 'NOT IN') {
                        $argument = $this->escapeArray($argument);
                        if (count($argument) > 1) {
                            $strings[] = '`' . $column . '` NOT IN ("' . implode('","', $argument) . '")';
                        } elseif (count($argument) == 1) {
                            $strings[] = '`' . $column . '`!="' . reset($argument) . '"';
                        }
                    }
                }
            }
        }
        return $strings;
    }

    public function setDataLines($dataLines)
    {
        if (!is_array($dataLines)) {
            $dataLines = [$dataLines];
        }

        $this->dataLines = $this->escapeArray($dataLines);
    }

    public function setResourceName($resourceName)
    {
        if (strlen($resourceName) > 0) {
            $this->resourceName = $this->tablesPrefix . $resourceName;
        } else {
            $this->logError('Attempted to set an empty/invalid table name');
        }
    }

    public function insertData()
    {
        $insertFieldsString = "(`" . implode("`,`", array_keys($this->dataLines)) . "`) VALUES ('" . implode("','", array_values($this->dataLines)) . "')";
        $sqlQuery = "INSERT INTO `" . $this->resourceName . "` " . $insertFieldsString . ";";

        if (!$this->sendQuery($sqlQuery)) {
            return false;
        }
        $this->lastInsertedID = $this->pdo->lastInsertId();

        return true;
    }

    public function deleteData()
    {
        $sqlQuery = "DELETE FROM `" . $this->resourceName . "` " . $this->whereString . ";";

        return !!$this->sendQuery($sqlQuery);
    }

    public function updateData()
    {
        $fieldsStrings = [];
        foreach ($this->dataLines as $dataFieldName => &$dataFieldValue) {
            $fieldsStrings[] = "`" . $dataFieldName . "`='" . $dataFieldValue . "'";
        }

        $setFieldsStrings = implode(", ", $fieldsStrings);

        $sqlQuery = "UPDATE `" . $this->resourceName . "` " . "SET " . $setFieldsStrings . " " . $this->whereString . ";";

        if (!$this->sendQuery($sqlQuery)) {
            return false;
        }

        return true;
    }

    public function selectData()
    {
        $selectResultRows = false;

        $sqlQuery = "SELECT " . $this->columnsString . " FROM `" . $this->resourceName . "` " . $this->whereString . ' ' . $this->groupString . ' ' . $this->orderString . ' ' . $this->limitString . ";";
        if ($sqlResult = $this->sendQuery($sqlQuery)) {
            $selectResultRows = $sqlResult->fetchAll(PDO::FETCH_ASSOC);
        }
        return $selectResultRows;
    }

    public function loadPrimaryFields()
    {
        $result = false;

        $sqlQuery = 'SHOW INDEXES FROM `' . $this->resourceName . '` WHERE Key_name = "PRIMARY";';
        if ($sqlResult = $this->sendQuery($sqlQuery)) {
            $fields = [];
            while ($sqlRow = $sqlResult->fetch(PDO::FETCH_ASSOC)) {
                $fields[$sqlRow['Column_name']] = $sqlRow['Column_name'];
            }
            $result = $fields;
        }
        return $result;
    }

    public function loadColumnNames()
    {
        $fields = false;

        $sqlQuery = 'SHOW COLUMNS FROM `' . $this->resourceName . '`;';
        if ($sqlResult = $this->sendQuery($sqlQuery)) {
            $fields = [];
            while ($row = $sqlResult->fetch(PDO::FETCH_ASSOC)) {
                $fields[] = $row['Field'];
            }
        }
        return $fields;
    }

    public function sendDirectQuery($sqlQuery)
    {
        $result = [];
        try {
            $queryResult = $this->sendQuery($sqlQuery);
            $result = $queryResult->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $this->logError($e->getMessage());
        }
        return $result;
    }

    protected function escapeArray($array)
    {
        return array_map([$this, 'escape'], $array);
    }

    public function escape($input)
    {
        return substr($this->pdo->quote($input), 1, -1);
    }

    /**
     * @return array
     */
    public function getQueriesHistory()
    {
        return $this->queriesHistory;
    }

    /**
     * @param bool $debug
     */
    public function setDebug($debug)
    {
        $this->debug = $debug;
    }

}
