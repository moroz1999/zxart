<?php

interface transportObject
{
    public function setSearchLines($searchLines);

    public function setOrderFields($orderLines);

    public function setDataLines($dataLines);

    public function setResourceName($resourceName);

    public function insertData();

    public function deleteData();

    public function updateData();

    public function selectData();

    public function getQueriesHistory();
}

