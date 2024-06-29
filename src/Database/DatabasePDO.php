<?php

namespace Phpdominicana\Lightwave\Database;

final readonly class DatabasePDO implements DataBaseInterface
{
    public function __construct(
        protected \PDO $pdo,
        protected string $table = '',
        protected array $columns = ['*'],
        protected string $where = '',
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

    #[\Override] public function where(string $column, string $value = '', string $operator = '='): self
    {
        if (empty($value)) {
            return new self($this->pdo, $this->table, $this->columns, $this->where);
        }

        if (empty($this->where)) {
            return new self($this->pdo, $this->table, $this->columns, "WHERE $column $operator $value");
        }

        return new self($this->pdo, $this->table, $this->columns, $this->where . " and $column $operator $value");
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
}
