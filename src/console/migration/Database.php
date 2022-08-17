<?php

namespace hnllyrp\think\console\migration;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\PDOMySql\Driver as MysqlDriver;
use Doctrine\DBAL\Driver\PDOOracle\Driver as OracleDriver;
use Doctrine\DBAL\Driver\PDOPgSql\Driver as PgSqlDriver;
use Doctrine\DBAL\Driver\PDOSqlite\Driver as SqliteDriver;
use Doctrine\DBAL\Driver\SQLSrv\Driver as SQLSrvDriver;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\MySQL57Platform;


class Database
{
    protected $doctrineManager;

    protected $doctrineConnection;


    public function __construct()
    {
        $this->doctrineManager = $this->getDoctrineManage();

        $this->doctrineConnection = $this->getDoctrineConnection();
    }

    public function getDoctrineManage()
    {
        if (!$this->doctrineManager) {
            $this->doctrineManager = $this->getDoctrineDriver()->getSchemaManager($this->getDoctrineConnection());
        }

        return $this->doctrineManager;
    }

    /**
     * get doctrine connection
     *
     * @return Connection
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Exception
     */
    public function getDoctrineConnection(): Connection
    {
        if (!$this->doctrineConnection) {

            return new Connection([
                'pdo' => $this->ThinkPHPPdoObject(),
                'platform' => new MySQL57Platform(),
            ], $this->getDoctrineDriver());
        }

        return $this->doctrineConnection;
    }

    /**
     * GET thinkphp Pdo Object
     *
     * @return mixed
     */
    protected function ThinkPHPPdoObject()
    {
        $tables = \think\facade\Db::connect()->getTables();

        if (empty($tables)) {
            return null;
        }

        return \think\facade\Db::table($tables[0])->getConnection()->getPdo();
    }

    /**
     * get doctrine driver
     *
     * @throws \Exception
     */
    protected function getDoctrineDriver()
    {
        switch (config('database.default')) {
            case 'mongo':
                throw new \Exception('not support [mongo] database yet');
            case 'mysql':
                return new MysqlDriver();
            case 'oracle':
                return new OracleDriver();
            case 'pgsql':
                return new PgSqlDriver();
            case 'sqlite':
                return new  SqliteDriver();
            case 'sqlsrv':
                return new SQLSrvDriver();
            default:
                throw new \Exception('not support [' . config('database.default') . '] database yet');
        }
    }


    /**
     * get all tables
     *
     * @return \Doctrine\DBAL\Schema\Table[]
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getAllTables(): array
    {
        $tables = $this->doctrineManager->listTables();

        foreach ($tables as &$table) {
            $table->addOption('name', $table->getName());
            $table->addOption('origin', $this->getOriginTableInformation($table->getName()));
        }

        return $tables;
    }

    /**
     * get databases
     *
     * @return string[]
     */
    public function getDatabases(): array
    {
        return $this->doctrineManager->listDatabases();
    }

    /**
     * get table detail
     *
     * @param $tableName
     * @return mixed[]
     */
    public function getTableDetail($tableName): array
    {
        return $this->doctrineManager->listTableDetails($tableName)->getOptions();
    }

    /**
     * get table indexes
     *
     * @param $tableName
     * @return \Doctrine\DBAL\Schema\Index[]
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getTableIndexes($tableName): array
    {
        return $this->doctrineManager->listTableIndexes($tableName);
    }

    /**
     * get table columns
     *
     * @param $tableName
     * @return \Doctrine\DBAL\Schema\Column[]
     */
    public function getTableColumns($tableName): array
    {
        return $this->doctrineManager->listTableColumns($tableName);
    }

    /**
     * 获取原始信息
     *
     * @param $tableName
     * @return mixed[]
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getOriginTableInformation($tableName): array
    {
        return $this->doctrineConnection->fetchAll($this->doctrineManager->getDatabasePlatform()->getListTableColumnsSQL($tableName));
    }

    /**
     *
     * @param $method
     * @param $arguments
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        return call_user_func_array([$this->doctrineManager, $method], $arguments);
    }
}
