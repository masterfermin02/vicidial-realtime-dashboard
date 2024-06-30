<?php

namespace Phpdominicana\Lightwave\Database;

final readonly class DatabasePDO implements DataBaseInterface
{
    public function __construct(
        public \PDO $pdo,
        public string $table = '',
        public array $columns = ['*'],
        public string $where = '',
    )
    {
    }

    public function query(string $query, array $parameters = []): array
    {
        $statement = $this->pdo->prepare($query);
        $statement->execute($parameters);

        return $statement->fetchAll();
    }

    #[\Override] public function table(string $table): self
    {
        return new self($this->pdo, $table, $this->columns, $this->where);
    }

    #[\Override] public function select(array $columns = ['*']): self
    {
        return new self($this->pdo, $this->table, $columns, $this->where);
    }

    #[\Override] public function where(string $column, ?string $operator = null, ?string $value = null): self
    {
        if (is_null($operator) && is_null($value)) {
            return new self($this->pdo, $this->table, $this->columns, $this->where);
        }

        if (is_null($value)) {
            $value = $operator;
            $operator = '=';
        }

        if (empty($this->where)) {
            return new self($this->pdo, $this->table, $this->columns, "WHERE $column $operator '$value'");
        }

        return new self($this->pdo, $this->table, $this->columns, $this->where . " and $column $operator '$value'");
    }

    #[\Override] public function whereIn(string $column, array $values): self
    {
        if (empty($values)) {
            return new self($this->pdo, $this->table, $this->columns, $this->where);
        }

        $in = "'" . implode("','", array_values($values)) . "'";
        if (empty($where)) {
            $where = "WHERE $column IN ($in)";
        } else {
            $where .= " AND $column IN ($in)";
        }
        return new self($this->pdo, $this->table, $this->columns, $this->where . $where);
    }

    #[\Override] public function get(): array
    {
        $query = "SELECT " . implode(',', $this->columns) . " FROM {$this->table} {$this->where} ";
        return $this->query($query);
    }

    #[\Override] public function first(): array
    {
        return $this->get()[0];
    }

    #[\Override] public function all(): array
    {
        return $this->get();
    }

    #[\Override] public function getStatement(): string
    {
        return "SELECT " . implode(',', $this->columns) . " FROM {$this->table} {$this->where} ";
    }

    #[\Override] public function whereNotIn(string $column, array $values): DataBaseInterface
    {
        if (empty($values)) {
            return new self($this->pdo, $this->table, $this->columns, $this->where);
        }

        $in = "'" . implode("','", array_values($values)) . "'";

        if (empty($where)) {
            $where = "WHERE $column NOT IN ($in)";
        } else {
            $where .= " AND $column NOT IN ($in)";
        }

        return new self($this->pdo, $this->table, $this->columns, $this->where . $where);
    }
}
