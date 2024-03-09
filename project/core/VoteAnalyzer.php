<?php

class VoteAnalyzer
{
    public function removeAnomalies(array $votes, $threshold = 1.96)
    {
        if (count($votes) <= 1) {
            return $votes;
        }

        $mean = $this->calculateMean($votes);
        $stdDev = $this->calculateStandardDeviation($votes, $mean);

        if ($stdDev == 0) {
            return $votes;
        }

        $filteredVotes = [];
        foreach ($votes as $vote) {
            $zScore = ($vote - $mean) / $stdDev;
            if (abs($zScore) <= $threshold) {
                $filteredVotes[] = $vote;
            }
        }

        return $filteredVotes;
    }

    protected function calculateMean(array $values)
    {
        return array_sum($values) / count($values);
    }

    protected function calculateStandardDeviation(array $values, $mean)
    {
        $squares = array_map(function ($value) use ($mean) {
            return pow($value - $mean, 2);
        }, $values);

        return sqrt(array_sum($squares) / (count($values) - 1));
    }
}