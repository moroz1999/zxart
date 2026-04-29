<?php

abstract class ExtractionProcedure implements
    DependencyInjectionContextInterface,
    ExtractionProcedureInterface
{
    use DependencyInjectionContextTrait;
}