<?php

class userQueryFilterConverter extends QueryFilterConverter
{
    protected string $table = 'module_user';

    use SimpleQueryFilterConverterTrait;
}