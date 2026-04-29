<?php

interface EventsListFilterInterface
{
    public function getEventsElements();

    public function getGroupedEvents($layout = 'month');

    public function getMonthsInfo();

    public function getFilter();
}