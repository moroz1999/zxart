<?php

interface ZxSoftInterface
{
    public function getListImagePreset();

    /**
     * Every hardware code describing what this item runs on: for a production its
     * own codes plus its releases', for a release its own plus its production's.
     *
     * Distinct from the codes the item *stores*, which is what an edit form and a
     * detail page show.
     *
     * @return list<string>
     */
    public function getRunsOnHardwareCodes(): array;
}
