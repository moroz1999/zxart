<?php

/**
 * Interface for Extraction Procedures
 */
interface ExtractionProcedureInterface
{
    /**
     * Method to set various arguments to the procedure (like limitation, filtering ...)
     * @param $arguments
     * @return void
     */
    public function setProcedureArguments($arguments = null);

    /**
     * Method constructs the xml
     * @return void
     */
    public function run();
}

