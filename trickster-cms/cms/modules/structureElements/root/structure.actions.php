<?php
$moduleActions = [];
$moduleActions[] = 'showFullList';
$moduleActions[] = 'showPrivileges';
$moduleActions[] = 'receivePrivileges';
$moduleActions[] = 'receivePositions';
$moduleActions[] = 'showPositions';
$moduleActions[] = 'deleteElements';
$moduleActions[] = 'copyElements';
$moduleActions[] = 'moveElements';
$moduleActions[] = 'pasteElements';
$moduleActions[] = 'cloneElements';
// Site-wide editing rights that belong to no single element. Checked directly by
// the SPA data endpoints, so there is no action class behind them.
$moduleActions[] = 'editHardware';
