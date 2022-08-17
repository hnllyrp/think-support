<?php

namespace hnllyrp\think\console\migration;

use Doctrine\DBAL\Schema\Column;

class ParseColumns
{
    public const TINY_INT = 'tinyint';

    public const INTEGER = 'int';

    public const SMALLINT = 'smallint';

    public const BIG_INT = 'bigint';

    public const MEDIUMINT = 'mediumint';

    public const VARCHAR = 'varchar';

    public const CHAR = 'char';

    public const TINY_TEXT = 'tinytext';

    public const TEXT = 'text';

    public const MEDIUM_TEXT = 'mediumtext';

    public const LONG_TEXT = 'longtext';

    public const TINY_BLOB = 'tinyblob';

    public const BLOB = 'blob';

    public const MEDIUM_BLOB = 'mediumblob';

    public const LONG_BLOB = 'longblob';

    public const DATE = 'date';

    public const DATETIME = 'datetime';

    public const TIMESTAMP = 'timestamp';

    public const JSON = 'json';

    public const DOUBLE = 'double';

    public const DECIMAL = 'decimal';

    public const FLOAT = 'float';

    public const ENUM = 'enum';

    public const GEOMETRY = 'geometry';

    public const GEOMETRY_COLLECTION = 'geometry_collection';

    public const LINESTRING = 'linestring';

    public const MULTILINESTRING = 'multilinestring';

    public const POINT = 'point';

    public const POLYGON = 'polygon';

    public const YEAR = 'year';

    public const MULTIPOINT = 'multipoint';

    public const MULTIPOLYGON = 'multipolygon';

    public const CURRENT_TIMESTAMP = 'CURRENT_TIMESTAMP';

    /**
     * length for text and blob
     */
    public const TINY_LENGTH = 255;

    public const LENGTH = 65535;

    public const MEDIUM_LENGTH = 1663535;

    public const LONG_LENGTH = 0;

    /**
     * @var Column
     */
    protected $column;

    /**
     * @var array
     */
    protected $originTableInfo;

    public function __construct(Column $column, array $originTableInfo)
    {
        $this->column = $column;

        $this->originTableInfo = $originTableInfo;
    }

    /**
     *
     * @param $type
     * @param $options
     * @return string
     */
    public function getParsedField($type, $options): string
    {
        return sprintf("('%s', '%s', %s)", $this->column->getName(), $type, $options);
    }

    /**
     * get column options
     *
     * @param int $limit
     * @param $precision
     * @param $scale
     * @return string
     */
    public function columnOptions($limit = 0, $precision = null, $scale = null): string
    {
        $options = '';

        if ($limit) {
            $options .= sprintf("'limit' => %s,", $limit);
        }

        if ($precision !== null) {
            $options .= "'precision' => {$precision},";
        }

        if ($scale !== null) {
            $options .= "'scale' => {$scale},";
        }

        return '[' . $options . $this->getNull() . $this->getSigned() . $this->getComment() . ']';
    }

    /**
     * comment
     *
     * @return string
     */
    protected function getComment()
    {
        return sprintf("'comment' => '%s',", $this->column->getComment());
    }

    /**
     * get signed
     *
     * @return string
     */
    protected function getSigned()
    {
        if ($this->isNotSingedValue()) {
            return '';
        }

        return sprintf("'signed' => %s,", !$this->column->getUnsigned() ? 'true' : 'false');
    }

    /**
     * 不需要设置 singed 的字段类型
     * @return bool
     */
    protected function isNotSingedValue()
    {
        if (in_array($this->column->getType()->getName(), ['string', 'char', 'varchar', 'text', 'json'])) {
            return true;
        }

        return false;
    }

    /**
     * get null
     *
     * @return string
     */
    protected function getNull()
    {
        if ($this->column->getNotnull()) {
            $null = "'null' => false,";

            if (!$this->isCanSetDefaultValue()) {
                $null .= sprintf("'default' => %s,", $this->getDefault());
            }

            return $null;
        }

        return "'null' => true,";
    }

    /**
     *
     * @return int|string|null
     */
    protected function getDefault()
    {
        $default = $this->column->getDefault();

        if ($default === null) {
            return 'null';
        }

        if (is_numeric($default)) {
            return (int)$default;
        }

        return "'{$default}'";
    }

    /**
     * @return bool
     */
    protected function isCanSetDefaultValue(): bool
    {
        return in_array($this->column->getType()->getName(), $this->cantHaveDefaultType()) && !$this->column->getDefault();
    }

    /**
     *
     * @return array
     */
    protected function cantHaveDefaultType(): array
    {
        return [
            'blob', 'text', 'date', 'json', 'geometry', 'multigeometry', 'timestamp',
        ];
    }


    public function BigIntType(): string
    {
        return $this->getParsedField('integer', $this->columnOptions('MysqlAdapter::INT_BIG'));
    }

    public function BinaryType(): string
    {
        return $this->getParsedField('binary', $this->columnOptions());
    }

    public function BlobType(): string
    {
        return $this->getParsedField('blob', $this->columnOptions($this->getBlobType()));
    }

    /**
     * blob type
     *
     * @return mixed
     */
    protected function getBlobType()
    {
        $types = [
            self::LENGTH => 'MysqlAdapter::BLOB_REGULAR',
            self::TINY_LENGTH => 'MysqlAdapter::BLOB_TINY',
            self::LONG_LENGTH => 'MysqlAdapter::BLOB_LONG',
        ];

        return $types[$this->column->getLength()] ?? 'MysqlAdapter::BLOB_MEDIUM';
    }

    public function BooleanType(): string
    {
        return $this->getParsedField('boolean', $this->columnOptions());
    }

    public function CharType(): string
    {
        return $this->getParsedField('char', $this->columnOptions($this->column->getLength()));
    }

    public function DatetimeType(): string
    {
        return $this->getParsedField('datetime', $this->columnOptions());
    }

    public function DateType(): string
    {
        return $this->getParsedField('date', $this->columnOptions());
    }

    public function DecimalType(): string
    {
        return $this->getParsedField('decimal', $this->columnOptions(0, $this->column->getPrecision(), $this->column->getScale()));
    }

    public function DoubleType(): string
    {
        return $this->getParsedField('decimal', $this->columnOptions(0, $this->column->getPrecision(), $this->column->getScale()));
    }

    public function EnumType(): string
    {
        return $this->getParsedField('enum', sprintf("['values' => %s]", $this->getEnumValue()));
    }

    /**
     * get enum value
     *
     * @return array|string
     */
    protected function getEnumValue()
    {
        $originTable = $this->originTableInfo;

        $columnName = $this->column->getName();

        foreach ($originTable as $column) {
            if ($column['Field'] == $columnName) {
                preg_match('/\((.*?)\)/', $column['Type'], $match);
                break;
            }
        }

        if (!empty($match)) {
            return sprintf('[%s]', $match[1]);
        }

        return '[]';
    }

    public function FloatType(): string
    {
        return $this->getParsedField('float', $this->columnOptions(0, $this->column->getPrecision(), $this->column->getScale()));
    }

    public function GeometrycollectionType(): string
    {
        return $this->getParsedField('geometrycollection', $this->columnOptions());
    }

    public function GeometryType(): string
    {
        return $this->getParsedField('geometry', $this->columnOptions());
    }

    public function IntegerType(): string
    {
        return $this->getParsedField('integer', $this->columnOptions('MysqlAdapter::INT_REGULAR'));
    }

    public function JsonType(): string
    {
        return $this->getParsedField('json', $this->columnOptions());
    }

    public function LinestringType(): string
    {
        return $this->getParsedField('linestring', $this->columnOptions());
    }

    public function MediumIntType(): string
    {
        return $this->getParsedField('integer', $this->columnOptions('MysqlAdapter::INT_MEDIUM'));
    }

    public function MultipointType(): string
    {
        return $this->getParsedField('multipoint', $this->columnOptions());
    }

    public function MultiPolygonType(): string
    {
        return $this->getParsedField('multipolygon', $this->columnOptions());
    }

    public function PointType(): string
    {
        return $this->getParsedField('point', $this->columnOptions());
    }

    public function PolygonType(): string
    {
        return $this->getParsedField('polygon', $this->columnOptions());
    }

    public function SimpleArrayType(): string
    {
        return $this->getParsedField('set', sprintf("['values' => %s]", $this->getSetValue()));
    }

    /**
     * get SimpleArray Type
     *
     * @return array|string
     */
    protected function getSetValue()
    {
        $originTable = $this->originTableInfo;

        $columnName = $this->column->getName();

        foreach ($originTable as $column) {
            if ($column['Field'] == $columnName) {
                preg_match('/\((.*?)\)/', $column['Type'], $match);
                break;
            }
        }

        if (!empty($match)) {
            return sprintf('[%s]', $match[1]);
        }

        return '[]';
    }

    public function SmallIntType(): string
    {
        return $this->getParsedField('integer', $this->columnOptions('MysqlAdapter::INT_SMALL'));
    }

    public function StringType(): string
    {
        return $this->getParsedField('string', $this->columnOptions($this->column->getLength()));
    }

    public function TextType(): string
    {
        return $this->getParsedField('text', $this->columnOptions($this->getTextType()));
    }

    protected function getTextType()
    {
        $types = [
            self::LENGTH => 'MysqlAdapter::TEXT_REGULAR',
            self::TINY_LENGTH => 'MysqlAdapter::TEXT_TINY',
            self::LONG_LENGTH => 'MysqlAdapter::TEXT_LONG',
        ];

        return $types[$this->column->getLength()] ?? 'MysqlAdapter::TEXT_MEDIUM';
    }

    public function TimestampType(): string
    {
        $this->column->setDefault($this->isSetCurrentTimestamp() ? self::CURRENT_TIMESTAMP : '');

        return $this->getParsedField('timestamp', $this->columnOptions());
    }

    protected function isSetCurrentTimestamp(): bool
    {
        $originTable = $this->originTableInfo;

        $columnName = $this->column->getName();

        $isSet = false;

        foreach ($originTable as $column) {
            if ($column['Field'] == $columnName) {
                if (strpos($column['Default'], self::CURRENT_TIMESTAMP) !== false || strpos($column['Extra'], self::CURRENT_TIMESTAMP)) {
                    $isSet = true;
                }
                break;
            }
        }

        return $isSet;
    }

    public function TimeType(): string
    {
        return $this->getParsedField('time', $this->columnOptions());
    }

    public function YearType(): string
    {
        return $this->getParsedField('year', $this->columnOptions());
    }
}
