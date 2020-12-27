<?php

trait ChartDataProviderTrait
{
    protected $chartData = [];

    public function getChartData($type = null)
    {
        if (!isset($this->chartData[$type])) {
            $this->chartData[$type] = false;
            $eventsLog = $this->getService('eventsLog');

            if ($dates = $eventsLog->countEvents(
                $this->getChartDataEventTypes($type),
                $this->getChartDataIds($type),
                null,
                time() - 1 * 30 * 24 * 60 * 60,
                null,
                'time',
                'asc',
                null,
                'day'
            )) {
                if (count($dates) > 1) {
                    $chartData = [
                        'labels' => [],
                        'data' => [],
                    ];
                    reset($dates);
                    $startDate = key($dates);
                    end($dates);
                    $endDate = key($dates);
                    $endObject = new DateTime($endDate);
                    $endObject->modify('+1 day');

                    $period = new DatePeriod(
                        new DateTime($startDate),
                        new DateInterval('P1D'),
                        $endObject
                    );

                    foreach ($period as $date) {
                        $string = $date->format('d.m.Y');
                        $chartData['labels'][] = $string;
                        if (isset($dates[$string])) {
                            $chartData['data'][] = $dates[$string];
                        } else {
                            $chartData['data'][] = 0;
                        }
                    }
                    $this->chartData[$type] = json_encode($chartData);
                }
            }
        }

        return $this->chartData[$type];
    }

    protected function resetChartData()
    {
        $this->chartData = null;
    }

    abstract public function getChartDataIds($type = null);

    abstract public function getChartDataEventTypes($type = null);
}