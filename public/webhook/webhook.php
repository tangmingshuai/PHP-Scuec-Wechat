<?php

function checkIP(string $ip) {
	$fields = explode('.', $ip);
	$validIPPrefix1 = ['104', '192', '136', '0'];
	$validIPPrefix2 = ['34', '198', '203', '127'];
	$validIPPrefix3 = ['34', '198', '178', '64'];
	$validIPPrefix4 = ['34', '198', '32', '85'];
	for ($i=0; $i<=1; $i++) { //bitbucket文档中给的ip段貌似有误不能完全匹配，有空验证一下准确ip
		if ($fields[$i] !== $validIPPrefix1[$i] && $fields[$i] !== $validIPPrefix2[$i] && $fields[$i] !== $validIPPrefix3[$i] && $fields[$i] !== $validIPPrefix4[$i] ) {
			return false;
		}
	}
	return true;
}

function checkHeader(string $header, string $assumption) {
	$header = str_replace('-', '_', strtoupper($header));
	$eventKey = isset($_SERVER['HTTP_'.$header]) ? $_SERVER['HTTP_'.$header] : null;
	if ($eventKey && $eventKey === $assumption) {
		return true;
	}
	return false;
}

date_default_timezone_set('Asia/Shanghai');

define("DEPLOY_LOG_PATH", "./log/");
	define("DEPLOY_LOG_FILE_NAME", "webhook_deploy.log");

define("REPOSITORY_FULL_NAME", "zixunminda/iscuecer");
define("REPOSITORY_UUID", "{3c09cefa-e6fd-454d-9001-dafa42b86436}");
define("DEPLOY_KEYWORD", "DEPLOY_NOW");


set_time_limit(200);

if (checkHeader('X-Event-Key', 'repo:push') && checkIP($_SERVER['REMOTE_ADDR']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
	$postData = file_get_contents('php://input');
	$jsonData = json_decode($postData, true);

	//check repository
	if (
		isset($jsonData['repository']['full_name']) &&
		isset($jsonData['repository']['uuid']) &&
		$jsonData['repository']['full_name'] === REPOSITORY_FULL_NAME &&
		$jsonData['repository']['uuid'] === REPOSITORY_UUID
	) {
		// check the push message. The Message that contains "DEPLOY_NOW" will trigger a 'git pull' command
		$changes = isset($jsonData['push']['changes']) ? $jsonData['push']['changes'] : false;
		if ($changes !== false) {
			foreach ($changes as $each) {
				if (isset($each['new']) && isset($each['new']['target'])) {
					if (strpos($each['new']['target']['message'], DEPLOY_KEYWORD) !== false) {
                        file_put_contents(DEPLOY_LOG_PATH.DEPLOY_LOG_FILE_NAME, "\n\n[".date('Y-m-d H:i:s')."]\n", FILE_APPEND | LOCK_EX);
                        exec('git pull  >> '.DEPLOY_LOG_PATH.DEPLOY_LOG_FILE_NAME.' 2>&1 &');
						exec('composer install  >> '.DEPLOY_LOG_PATH.DEPLOY_LOG_FILE_NAME.' 2>&1 &');
					}
				}
			}
		}
	} else {
		http_response_code(404);
	}
} else {
	http_response_code(404);
}