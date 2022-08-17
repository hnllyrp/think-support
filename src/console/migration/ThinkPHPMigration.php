<?php

namespace hnllyrp\think\console\migration;

use Doctrine\DBAL\Schema\Index;
use think\helper\Str;

class ThinkPHPMigration extends AbstractMigration
{
    public $prefix = '';
    public $className = '';

    public function getPrefix()
    {
        $this->prefix = config_database('mysql', 'prefix');

        return $this->prefix;
    }

    public function getAllTables()
    {
        $database = new Database();

        return $database->getAllTables();
    }

    protected function getMigrationStub(): string
    {
        return 'TpMigration.stub';
    }

    /**
     * @param string $prefix
     * @return $this
     */
    public function setPrefix(string $prefix)
    {
        $this->prefix = $prefix;

        return $this;
    }

    /**
     * @param string $className
     * @return $this
     */
    public function setClassName(string $className)
    {
        $this->className = $className;

        return $this;
    }

    /*
     * replace content
     *
     * @throws \Doctrine\DBAL\DBALException
     * @return array
     */
    protected function getReplaceContent(): array
    {
        $this->removeAutoincrementColumn();

        // 替换表前缀
        $table_name = str_replace($this->prefix, '', $this->table['name']);

        $this->className = $this->className ? $this->className : ucfirst(Str::camel($table_name));

        return [
            $this->className,
            // table name
            $table_name,
            // phinx table information
            sprintf("['engine' => '%s', 'collation' => '%s', 'comment' => '%s' %s %s]",
                $this->table['engine'], $this->table['collation'], $this->table['comment'], $this->getAutoIncrement(), $this->getPrimaryKeys()),

            '$table' . rtrim($this->getMigrationContent(), $this->eof())
        ];
    }

    /**
     * @return array
     */
    protected function replacedString(): array
    {
        return [
            '{MIGRATOR}', '{TABLE}', '{TABLE_INFORMATION}', '{MIGRATION_CONTENT}'
        ];
    }

    /**
     *
     * @return string
     */
    protected function head(): string
    {
        return '->addColumn';
    }

    /**
     * get the path of migrate stub
     */
    public function getMigrateStubContent(): string
    {
        return file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . $this->getMigrationStub());
    }

    /**
     * parse column
     *
     * @return mixed
     */
    protected function parseColumn()
    {
        $className = $this->getTypeClassName();

        $migrateColumn = new ParseColumns($this->column, $this->table['origin']);

        return $migrateColumn->$className();
    }

    /**
     * @return string
     */
    protected function getAutoIncrement()
    {
        $autoIncrement = '';

        $autoField = $this->getAutoIncrementField();

        if ($autoField) {
            // list
            [$fieldName, $signed] = $autoField;
            $autoIncrement .= ",'id' => '{$fieldName}'";
            if ($signed) {
                $autoIncrement .= ",'signed' => true";
            }
        } else {
            $autoIncrement .= ",'id' => false";
        }

        return $autoIncrement;
    }

    /**
     * get primary keys
     *
     * @return string
     */
    public function getPrimaryKeys(): string
    {
        $primary = '';

        if (!isset($this->indexes['primary'])) {
            return $primary;
        }

        foreach ($this->indexes['primary']->getColumns() as $column) {
            $primary .= "'{$column}',";
        }

        return $primary ? sprintf(",'primary_key' => [%s]", trim($primary, ',')) : '';
    }

    /**
     * parse index
     *
     * @return string
     */
    protected function parseIndexes()
    {
        $indexes = '';
        foreach ($this->indexes as $index) {
            // options 判断防止 foreign key 导致报错
            if (!$index->isPrimary() && $index->hasOption('lengths')) {
                $indexes .= sprintf('->addIndex(%s)', $this->parseIndex($index)) . $this->eof();
            }
        }

        return $indexes;
    }

    /**
     * @param Index $index
     * @return string
     */
    protected function parseIndex(Index $index): string
    {
        $columns = $index->getColumns();

        $indexLengths = $index->getOption('lengths');

        // column
        $_columns = '';
        foreach ($columns as $column) {
            $_columns .= "'{$column}',";
        }
        $options = '';
        // limit
        $options .= count(array_filter($indexLengths)) ? $this->parseLimit($indexLengths, $columns) : '';
        // unique
        $options .= $index->isUnique() ? "'unique' => true," : '';
        // alias name
        $options .= $index->getName() ? "'name' => '{$index->getName()}'," : '';
        // type
        $options .= in_array('fulltext', $index->getFlags()) ? "'type' => 'fulltext'," : '';

        return sprintf('[%s], [%s]', trim($_columns, ','), trim($options, ','));
    }

    /**
     * parse limit
     *
     * @param $indexLengths
     * @param $columns
     * @return string
     */
    protected function parseLimit($indexLengths, $columns)
    {
        if (count($indexLengths) < 2 && $indexLengths[0]) {
            return "'limit' => {$indexLengths['0']},";
        }

        $limit = "'limit' => [";
        foreach ($indexLengths as $key => $indexLength) {
            if ($indexLength) {
                $limit = "'$columns[$key]' => {$indexLength},";
            }
        }
        $limit .= '],';

        return $limit;
    }


    /**
     * remove autoincrement column
     *
     * @return void
     */
    protected function removeAutoincrementColumn()
    {
        foreach ($this->columns as $k => $column) {
            if ($column->getAutoincrement()) {
                unset($this->columns[$k]);
                break;
            }
        }
    }


}
