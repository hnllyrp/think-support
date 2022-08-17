<?php

namespace hnllyrp\think\console\migration;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Type;

abstract class AbstractMigration
{

    /**
     * @var array
     */
    protected $table;

    /**
     * @var Table
     */
    protected $_table;

    /**
     * @var Column[]
     */
    protected $columns;

    /**
     * @var Column
     */
    protected $column;

    /**
     * @var Type
     */
    protected $columnType;

    /**
     * @var string
     */
    protected $frame;
    /**
     * @var array
     */
    protected $indexes;

    /**
     * @return string
     */
    abstract protected function getMigrationStub(): string;

    /**
     * @return array
     */
    abstract protected function getReplaceContent(): array;

    /**
     * @return array
     */
    abstract protected function replacedString(): array;


    /**
     * set table info
     *
     * @param $table
     * @return $this
     */
    public function setTable(Table $table): self
    {
        $this->_table = $table;

        $this->table = $table->getOptions();

        $this->columns = $table->getColumns();

        $this->indexes = $table->getIndexes();

        return $this;
    }

    /**
     *
     * @return mixed
     */
    protected function getTypeClassName()
    {
        $class = explode('\\', get_class($this->column->getType()));

        return array_pop($class);
    }

    /**
     * replace
     *
     * @return string
     */
    protected function headString(): string
    {
        return '{head}';
    }

    /**
     * eof
     *
     * @return string
     */
    protected function eof(): string
    {
        return "\r\n\t\t\t";
    }


    /**
     * get migration content
     *
     * @return mixed
     * @throws
     */
    public function getMigrationContent()
    {
        $content = '';

        foreach ($this->columns as $column) {

            $this->column = $column;

            $this->columnType = $column->getType();

            $content .= $this->head() . sprintf($this->parseColumn(), $column->getName()) . $this->eof();
        }

        $content .= $this->parseIndexes();

        return $content;
    }

    /**
     * get autoincrement field
     *
     * @return array|null
     */
    protected function getAutoIncrementField(): ?array
    {
        $columns = $this->_table->getColumns();

        foreach ($columns as $key => $column) {
            if ($column->getAutoincrement()) {
                unset($columns[$key]);
                return [$column->getName(), $column->getUnsigned()];
            }
        }

        return null;
    }

    /**
     * output
     *
     * @return mixed
     */
    public function output()
    {
        return str_replace($this->replacedString(), $this->getReplaceContent(), $this->getMigrateStubContent());
    }

    /**
     * get the path of migrate stub
     *
     * @return string
     */
    public function getMigrateStubContent(): string
    {
        return file_get_contents(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . $this->getMigrationStub());
    }

}
