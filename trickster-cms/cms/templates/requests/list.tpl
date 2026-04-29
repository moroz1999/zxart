<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Requests debug</title>
    <style>
        .requests-table {
            width: 100%;
            table-layout: fixed;
        }

        .requests-table td {
            padding: 5px 8px;
            font-size: 14px;
        }

        .requests-table-start {
            width: 10%;
        }

        .requests-table-duration {
            width: 3%;
        }

        .requests-table-completed {
            width: 3%;
        }

        .requests-table-ip {
            width: 5%;
        }

        .requests-table-url {
            width: 30%;
        }

        .requests-table-url a {
            word-break: break-all;
        }

        .requests-table-ua {
            width: 30%;
        }
    </style>
</head>
<body>
<h2>Top {count($topIpCount)} IPs by Request Count</h2>
<ul>
    {foreach $topIpCount as $ip => $count}
        <li>{$ip} -> {$count}</li>
    {/foreach}
</ul>

<h2>Top {count($topIpDuration)} IPs by Total Duration</h2>
<ul>
    {foreach $topIpDuration as $ip => $duration}
        <li>{$ip} -> {$duration}</li>
    {/foreach}
</ul>
<h2>Top {count($topLongestRequests)} Longest Requests</h2>
<table class="requests-table">
    <thead>
    <tr>
        <th class="requests-table-start">Start Time</th>
        <th class="requests-table-duration">Duration</th>
        <th class="requests-table-completed">Done</th>
        <th class="requests-table-ip">IP</th>
        <th class="requests-table-url">URL</th>
        <th class="requests-table-ua">User Agent</th>
    </tr>
    </thead>
    <tbody>
    {foreach $topLongestRequests as $request}
        <tr>
            <td>{$request->formattedStartTime}</td>
            <td>{$request->formattedDuration}</td>
            <td class="requests-table-completed">{$request->completed}</td>
            <td class="requests-table-ip"><a href="https://ipinfo.io/{$request->ip}" target="_blank">{$request->ip}</a>
            </td>
            <td class="requests-table-url"><a href="{$request->url}" target="_blank">{$request->url}</a></td>
            <td class="requests-table-ua">{$request->userAgent}</td>
        </tr>
    {/foreach}
    </tbody>
</table>
<h2>All Requests</h2>
<table class="requests-table">
    <thead>
    <tr>
        <th class="requests-table-start">Start Time</th>
        <th class="requests-table-duration">Duration</th>
        <th class="requests-table-completed">Done</th>
        <th class="requests-table-ip">IP</th>
        <th class="requests-table-url">URL</th>
        <th class="requests-table-ua">User Agent</th>
    </tr>
    </thead>
    <tbody>
    {foreach $requests as $request}
        <tr>
            <td>{$request->formattedStartTime}</td>
            <td>{$request->formattedDuration}</td>
            <td class="requests-table-completed">{$request->completed}</td>
            <td class="requests-table-ip"><a href="https://ipinfo.io/{$request->ip}" target="_blank">{$request->ip}</a>
            </td>
            <td class="requests-table-url"><a href="{$request->url}" target="_blank">{$request->url}</a></td>
            <td class="requests-table-ua">{$request->userAgent}</td>
        </tr>
    {/foreach}
    </tbody>
</table>
</body>
</html>
