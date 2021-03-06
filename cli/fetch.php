<?php

require_once $_SERVER['DOCUMENT_ROOT'] . 'config/conf';
require_once $_SERVER['DOCUMENT_ROOT'] . 'api/api_queries.php';
require_once $_SERVER['DOCUMENT_ROOT'] . 'db/database_queries.php';

/* Make directory images for storing images */
if (STORE_IMAGES_LOCALLY && !is_dir(rtrim(STORING_LOCATION, '/') . '/images')) {
    mkdir(rtrim(STORING_LOCATION, '/') . '/images');
}

/* Stats */
const NUMBER_OF_RETWEETS = 'NUMBER_OF_RETWEETS';
const NUMBER_OF_ORIGINALS = 'NUMBER_OF_ORIGINALS';
const NUMBER_OF_POSTS_CONTAINING_IMAGES = 'NUMBER_OF_POSTS_CONTAINING_IMAGES';
const NUMBER_OF_UNIQUE_POSTS = 'NUMBER_OF_UNIQUE_POSTS';
const NUMBER_OF_DUPLICATED_POSTS = 'NUMBER_OF_DUPLICATED_POSTS';
const NUMBER_OF_IMAGES_DOWNLOADED = 'NUMBER_OF_IMAGES_DOWNLOADED';

$stats = [
    NUMBER_OF_RETWEETS => 0,
    NUMBER_OF_ORIGINALS => 0,
    NUMBER_OF_POSTS_CONTAINING_IMAGES => 0,
    NUMBER_OF_UNIQUE_POSTS => 0,
    NUMBER_OF_DUPLICATED_POSTS => 0,
    NUMBER_OF_IMAGES_DOWNLOADED => 0,
];

/* API Fetch Each User */
$resources = [];
foreach (USERNAMES as $index => $username) {
    /* Separate Option */
    if (!SEPARATED && !is_null(MAIN_USER) && in_array(MAIN_USER, USERNAMES)) {
        $table = MAIN_USER;
    } else {
        $table = $username;
    }

    /* Fetch Data */
    if (CONSOLE_LOG_RESULTS) echo "Fetching API data + Storing into database \n";
    for ($page = 1; $page <= NUMBER_OF_PAGES; $page++) {
        /* API call */
        try {
            $data = fetchHomeTimeLine([
                'access_token' => ACCESS_TOKENS[$index],
                'count' => COUNT,
                'page' => $page,
                'feature' => FEATURE
            ]);
        } catch (Exception $e) {
            if (CONSOLE_LOG_RESULTS) echo $e->getMessage();
            continue;
        }

        /* Check Response */
        if (!isset($data['statuses'])) {
            continue;
        }

        /* Go through each posts */
        foreach ($data['statuses'] as $post) {
            /* If the post is retweeted post */
            if (isset($post['retweeted_status'])) {
                $stats[NUMBER_OF_RETWEETS] += 1;

                /* Store Retweeted Images */
                if (STORE_IMAGES_LOCALLY && STORE_RETWEET_IMAGES && !empty($post['retweeted_status']['pic_urls'])) {
                    list($dest, $imageLinks) = resourcesGetter($post['retweeted_status']['pic_urls'], $post['retweeted_status']['id']);
                    $resources[$dest] = $imageLinks;
                }

                /* Set Original Post */
                $post = $post['retweeted_status'];
            } else {
                $stats[NUMBER_OF_ORIGINALS] += 1;
            }

            /* Database Values */
            $values = [
                'id' => $post['id'],
                'text' => $post['text'],
                'user_id' => $post['user']['id'],
                'user_name' => $post['user']['screen_name'],
                'created_at' => $post['created_at'],
                'timestamp' => time()
            ];

            /* Has Image */
            if (!empty($post['pic_urls'])) {
                $stats[NUMBER_OF_POSTS_CONTAINING_IMAGES] += 1;
                list($dest, $imageLinks) = resourcesGetter($post['pic_urls'], $post['id']);
                $resources[$dest] = $imageLinks;
                $values['image_qualities'] = IMAGE_QUALITY;
                $values['image_names'] = json_encode(array_keys($imageLinks));
            }

            /* Insert into database */
            try {
                insertIntoTable($table, $values);
                $stats[NUMBER_OF_UNIQUE_POSTS] += 1;
            } catch (Exception $e) {
                if (CONSOLE_LOG_RESULTS) {
                    $message = $e->getMessage();
                    if (!DISABLE_MYSQL_DUPLICATE_MESSAGE || !isDuplicateEntry($message)) {
                        echo $message;
                    }
                }
                $stats[NUMBER_OF_DUPLICATED_POSTS] += 1;
                continue;
            }
        }
    }
}

/* Download Images */
if (CONSOLE_LOG_RESULTS) echo "Downloading images \n";
foreach ($resources as $dest => $resource) {
    $stats[NUMBER_OF_IMAGES_DOWNLOADED] += storeImages($dest, $resource);
}

/* Stats */
if (CONSOLE_LOG_RESULTS) {
    foreach ($stats as $stat => $value) {
        echo $stat . ": " . $value . "\n";
    }
}

/* Finish */
if (CONSOLE_LOG_RESULTS) echo "Finished \n";
