<?php

class newsListQueryFilterConverter extends QueryFilterConverter
{
    use SimpleQueryFilterConverterTrait;

    protected function getTable(): string
    {
        return 'module_newslist';
    }
}