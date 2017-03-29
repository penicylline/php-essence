<?php
namespace PhpEssence\Component;

use PhpEssence\Exception\DatabaseException;

class Database {
    /**
     * @var \PDO
     */
    protected $pdo;

    protected $fetchMode = \PDO::FETCH_ASSOC;

    public function __construct($config) {
        $options = isset($config['options']) ? $config['options'] : array();
        $this->pdo = new \PDO(
            'mysql:host=' . $config['host'] .';port=' . $config['port'] . ';dbname=' . $config['dbname'],
            $config['username'],
            $config['password'],
            $options
        );
    }

    /**
     * @param string $table
     * @param array $values
     * @return string
     * @throws DatabaseException
     */
    public function add($table, array $values) {
        $sql = 'INSERT INTO ' . $table . '(';
        $params = array();
        foreach ($values as $key => $value) {
            $params[':' . trim($key)] = $value;
        }
        $sql .= implode(', ', array_keys($values)) . ')';
        $sql .= ' VALUES (' . implode(', ', array_keys($params)) . ')';
        $stm = $this->pdo->prepare($sql);
        $res = $stm->execute($params);
        if ($res) {
            return $this->pdo->lastInsertId();
        }
        throw new DatabaseException($sql . '<br/>' . $stm->errorInfo()[2] . '<br/>' . var_export($params, true));
    }

    /**
     * @param string $table
     * @param array $values
     * @param array $conditions
     * @return bool
     * @throws DatabaseException
     */
    public function save($table, array $values, array $conditions) {
        $sql = 'UPDATE ' . $table . ' SET ';
        $needs = array();
        $params = array();
        foreach ($values as $key => $value) {
            $cKey = ':' . trim($key);
            $needs[] = $key . ' = ' . $cKey;
            $params[$cKey] = $value;
        }
        $sql .= implode(', ', $needs);
        $sql .= $this->buildWhereStatement($conditions);
        $params = array_merge($params, $conditions);
        $stm = $this->pdo->prepare($sql);
        $res = $stm->execute($params);
        if ($res) {
            return $res;
        }
        throw new DatabaseException($sql . '<br/>' . $stm->errorInfo()[2] . '<br/>' . var_export($params, true));
    }

    /**
     * @param string $table
     * @param array $options
     * @return array
     * @throws DatabaseException
     */
    public function getList($table, $options = array()) {
        $conditions = isset($options['where']) ? $options['where'] : null;
        $fields = isset($options['fields']) ? $options['fields'] : null;
        $order = isset($options['order']) ? $options['order'] : null;
        $limit = isset($options['limit']) ? $options['limit'] : null;
        $start = isset($options['start']) ? $options['start'] : null;

        $sql = 'SELECT ';
        if ($fields) {
            $sql .= implode(', ', $fields);
        } else {
            $sql .= '*';
        }
        $sql .= ' FROM ' . $table;
        if ($conditions) {
            $sql .= $this->buildWhereStatement($conditions);
        }

        if ($order) {
            $sql .= ' ORDER BY ' . $order;
        }

        if ($limit) {
            $sql .= ' LIMIT ' . intval($limit);
            if ($start) {
                $sql .= ', ' . intval($start);
            }
        }

        $stm = $this->pdo->prepare($sql);
        $res = $stm->execute($conditions);
        if (!$res) {
            throw new DatabaseException($sql . ' >>>>> ' . $stm->errorInfo()[2]);
        }
        return $stm->fetchAll($this->fetchMode);
    }

    /**
     * @param string $table
     * @param null|array $conditions
     * @param null|array $fields
     * @param null|string $order
     * @return bool|mixed
     * @throws DatabaseException
     */
    public function getOne($table, $conditions = null, $fields = null, $order = null) {
        $sql = 'SELECT ';
        if ($fields) {
            $sql .= implode(', ', $fields);
        } else {
            $sql .= '*';
        }
        $sql .= ' FROM ' . $table;
        if ($conditions) {
            $sql .= $this->buildWhereStatement($conditions);
        }
        if ($order) {
            $sql .= ' ORDER BY ' . $order;
        }
        $sql .= ' LIMIT 1';
        $stm = $this->pdo->prepare($sql);
        $res = $stm->execute($conditions);
        if (!$res) {
            throw new DatabaseException($sql . ' >>>>> ' . $stm->errorInfo()[2]);
        }
        $res = $stm->fetch($this->fetchMode);
        $stm->closeCursor();
        return $res;
    }

    /**
     * @param string $table
     * @param null|array $conditions
     * @return mixed
     * @throws DatabaseException
     */
    public function getCount($table, $conditions = null) {
        $sql = 'SELECT count(*) as total ';
        $sql .= ' FROM ' . $table;
        if ($conditions) {
            $sql .= $this->buildWhereStatement($conditions);
        }
        $stm = $this->pdo->prepare($sql);
        $res = $stm->execute($conditions);
        if (!$res) {
            throw new DatabaseException($sql . ' >>>>> ' . $stm->errorInfo()[2]);
        }
        $result = $stm->fetchAll(\PDO::FETCH_ASSOC);
        return $result[0]['total'];
    }

    /**
     * @param string $sql
     * @param array $params
     * @return array
     * @throws DatabaseException
     */
    public function query($sql, $params = []) {
        $stm = $this->pdo->prepare($sql);
        $res = $stm->execute($params);
        if (!$res) {
            throw new DatabaseException($sql . ' >>>>> ' . $stm->errorInfo()[2]);
        }
        return $stm->fetchAll($this->fetchMode);
    }

    /**
     * @param array $conditions
     * @return string
     */
    protected function buildWhereStatement(array &$conditions) {
        $needs = array();
        $keys = array_keys($conditions);
        foreach ($keys as $key) {
            $value = $conditions[$key];
            $space = strpos($key, ' ');
            $trimKey = trim($key);
            if ($space) {
                $cKey = trim(substr($trimKey, 0, $space));
                $needs[] = $trimKey . ' :' . $cKey;
                $conditions[':' . $cKey] = $value;
            } else {
                if ($value === null) {
                    $needs[] = $trimKey . ' IS NULL';
                } else {
                    $needs[] = $trimKey . ' = :' . $trimKey;
                    $conditions[':' . $trimKey] = $value;
                }
            }
            unset($conditions[$key]);
        }
        return ' WHERE ' . implode(' AND ', $needs);
    }

    /**
     * @param string $table
     * @param array $conditions
     * @throws DatabaseException
     */
    public function delete($table, array $conditions) {
        $sql = 'DELETE FROM ' . $table;
        $sql .= $this->buildWhereStatement($conditions);
        $stm = $this->pdo->prepare($sql);
        $res = $stm->execute($conditions);
        if (!$res) {
            throw new DatabaseException($sql . ' >>>>> ' . $stm->errorInfo()[2]);
        }
    }
}
