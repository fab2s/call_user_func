<?php

function formatDiff($str, $float, $plus='+')
{
    return $float <= 0 ? "<fg=green>$str</>" : "<fg=red;options=bold>$plus$str</>";
}

function calcVs(&$reportBench, $compare)
{
    $diff = $reportBench[$compare]['time'] - $reportBench['call_user_func']['time'];
    $reportBench[$compare]['diff'] = $diff;
    $reportBench[$compare]['pct']  = $diff * 100 / $reportBench['call_user_func']['time'];
}

function sortReportByDiff($a, $b)
{
    global $report, $key;
    return $report[$key][$a]["diff"] >= $report[$key][$b]["diff"];
}