<?php

use Illuminate\Database\Query\Builder;

abstract class QueryFilterConverter extends errorLogger implements DependencyInjectionContextInterface
{
    use DependencyInjectionContextTrait;

    protected ?Builder $correctionQuery = null;
    protected string $table;

    protected function getFields(): array
    {
        return [
            $this->getTable() . '.id'
        ];
    }

    public function getCorrectionQuery(): ?Builder
    {
        return $this->correctionQuery;
    }

    public function setCorrectionQuery(Builder $correctionQuery)
    {
        $this->correctionQuery = $correctionQuery;
    }

    protected string $type;

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type)
    {
        $this->type = $type;
    }

    protected function getTable(): string
    {
        return 'module_' . strtolower($this->getType());
    }

    protected function getStructureTable(): string
    {
        return 'structure_elements';
    }

    abstract public function convert(Builder $sourceData, string $sourceType);
}