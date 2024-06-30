<?php

namespace Phpdominicana\Lightwave\Database;

interface DataBaseInterface
{
    public function table(string $table): DataBaseInterface;

    public function select(array $columns = ['*']): DataBaseInterface;

    public function where(string $column, string $operator = '=', string $value = ''): DataBaseInterface;

    public function whereIn(string $column, array $values): DataBaseInterface;

    public function whereNotIn(string $column, array $values): DataBaseInterface;

    public function get(): array;

    public function first(): array;

    public function all(): array;

    public function query(string $query, array $parameters = []): array;

    public function getStatement(): string;
}
