<?php

function getRedisUnit() {
    return array(
        'host'     => '',       // (str)REDMIN_REDIS_n_HOST
        'port'     => '6379',   // (str)REDMIN_REDIS_n_PORT
        'name'     => '',       // (str)REDMIN_REDIS_n_NAME
        'password' => null,     // (str)REDMIN_REDIS_n_PASSWORD
        'database' => 0,        // (int)REDMIN_REDIS_n_DATABASE
        'max_databases' => 16,  // (int)REDMIN_REDIS_n_MAX_DATABASES
        'stats'    => array(
            'enable'   => 1,    // (int)REDMIN_REDIS_n_STATS_ENABLE
            'database' => 0,    // (int)REDMIN_REDIS_n_STATS_DATABASE
        ),
        'dbNames' => array(),
    );
}

function insertRedisValue($envKey, $envVal, $insertTargetUnit) {

    switch ($envKey) {
        case 'HOST':
            $insertTargetUnit['host'] = (string)$envVal;
            break;
        case 'PORT':
            $insertTargetUnit['port'] = (string)$envVal;
            break;
        case 'PASSWORD':
            $insertTargetUnit['password'] = (string)$envVal;
            break;
        case 'DATABASE':
            $insertTargetUnit['database'] = (int)$envVal;
            break;
        case 'MAX_DATABASES':
            $insertTargetUnit['max_databases'] = (int)$envVal;
            break;
        case 'STATS_ENABLE':
            $insertTargetUnit['stats']['enable'] = (int)$envVal;
            break;
        case 'STATS_DATABASE':
            $insertTargetUnit['stats']['database'] = (int)$envVal;
            break;
    }

    return $insertTargetUnit;

}

function getRedisArrayFromEnvs($envs) {

    $result = [];

    foreach ($envs as $key => $value) {
        if (preg_match('/(REDMIN_REDIS_)(\d+)_([A-Z_]+)/', $key, $regexResult)) {
            if (!array_key_exists($regexResult[2], $result)) {
                $result[$regexResult[2]] = getRedisUnit();
            }
            $result[$regexResult[2]] = insertRedisValue($regexResult[3], $value, $result[$regexResult[2]]);
        }
    }

    foreach ($result as &$_redis) {
        if (!isset($_redis['name']) || empty($_redis['name'])) {
            $_redis['name'] = $_redis['host'].':'.$_redis['port'];
        }
    }
    unset($_redis);

    ksort($result);
    return $result;
}

$redis_array = getRedisArrayFromEnvs($_ENV);

$config = array(
    'default_controller' => 'Welcome',
    'default_action'     => 'Index',
    'production'         => strcmp(getenv('REDMIN_PRODUCTION_FLAG'), 'false') != 0,
    'default_layout'     => 'layout',
    'timezone'           => 'Asia/Tokyo',
    'log' => array(
        'driver'    => 'file',
        'threshold' => (int)getenv('REDMIN_LOG_THRESHOLD') ?: 0, /* 0: Disable Logging 1: Error 2: Notice 3: Info 4: Warning 5: Debug */
        'file'      => array(
            'directory' => 'logs'
        )
    ),
    'database'  => array(
        'driver' => 'redis',
        'redis' => $redis_array,
    ),
    'session' => array(
        'lifetime'       => (int)getenv('REDMIN_SESSION_LIFETIME') ?: 7200,
        'gc_probability' => (int)getenv('REDMIN_SESSION_GC_PROBABILITY') ?: 2,
        'name'           => 'phpredminsession'
    ),
    'gearman' => array(
        'host' => (string)getenv('REDMIN_GEARMAN_HOST') ?: '127.0.0.1',
        'port' => (int)getenv('REDMIN_GEARMAN_PORT') ?: 4730
    ),
    'terminal' => array(
        'enable'  => true,
        'history' => 200
    )
);

return $config;