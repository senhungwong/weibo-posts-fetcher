<?php

require_once $_SERVER['DOCUMENT_ROOT'] . 'config/conf';
require_once $_SERVER['DOCUMENT_ROOT'] . 'db/database_queries.php';

/** -----------------------------------------
 * Create Tables
 * ------------------------------------------
 */
foreach (USERNAMES as $username) {
    try {
        createTableIfNotExists($username);
        if (CONSOLE_LOG_RESULTS) echo $username . " table created successfully. \n";
    } catch (Exception $e) {
        if (CONSOLE_LOG_RESULTS) echo $e->getMessage() . "\n";
        continue;
    }
}

/** -----------------------------------------
 * Create Folder
 * ------------------------------------------
 *      Note: works when <STORING_LOCATION> has only one level
 */
if (STORE_IMAGES_LOCALLY && !is_dir(STORING_LOCATION)) mkdir(STORING_LOCATION);
