<p align="center"><img src="https://github.com/senhungwong/weibo-posts-fetcher/blob/master/src/images/logo.png" width="55%"></p>

## Description

The tool is designed for fetching [Weibo](https://weibo.com) (Chinese Twitter) posts, 
filter out the duplicate posts, 
and store them locally.
 
It saves user's time in browsing since most of the content are repeated (people retweet posts).

**The tool requires PHP7 and MySQL.**

API: [Weibo API](http://open.weibo.com/wiki/API)

## Initialize

1. Configuration

```bash
$ php weibo config
```

*Note: you can use your own editor to open it.*

*Rename [conf.example](config/conf.example) to `conf` and fill out the settings*

If you are feeling the configs are too much, just fill in `ACCESS_TOKENS` provided in `conf.example` + database credentials, and keep going! 😉

```
/* Tool Settings */
const SEPARATED = true; /* Separate multiple users' posts into their own table */
const MAIN_USER = null; /* Specify the main username when <SEPARATE> is false */

const STORE_IMAGES_LOCALLY = true; /* Fetching will store the images locally */
const STORING_LOCATION = './data'; /* The local existing storing folder */
const STORE_RETWEET_IMAGES = true; /* Store retweet images as well */
const IMAGE_QUALITY = 'large'; /* Store image quality: large, bmiddle (not guaranteed), thumbnail */

const COUNT = 100; /* The number of posts each api request; Maximum 100 */
const NUMBER_OF_PAGES = 5; /* Request pages, each page contains <COUNT> of posts */
const FEATURE = 0; /* Filter posts; 0: All; 1: Originals; 2: Pictures; 3: Videos; 4: Musics */

const CONSOLE_LOG_RESULTS = true; /* Output the current phrase and the filtering result */
const DISABLE_MYSQL_DUPLICATE_MESSAGE = true; /* Disable the duplicate message error echoing from mysql */
```

```
/* Weibo Settings */
const USERNAMES = ['username']; /* Weibo usernames */
const ACCESS_TOKENS = ['access_token']; /* Access token from http://open.weibo.com/tools/console */
```

```
/* Database Settings */
const DB_HOST = 'localhost'; /* Database Host */
const DB_PORT = 3306; /* Database Port */
const DB_DATABASE = 'weibo'; /* Database Name */
const DB_USER = 'root'; /* User */
const DB_PASSWORD = 'password'; /* Password */
```

2. Create Database

```
MySQL> CREATE DATABASE <DB_DABATASE>
```

3. Initialize

```bash
$ php weibo init
```

## How to Use

You can use it [when is needed](#when-needed) or [set a `crontab` job](#crontab) to fetch posts every day.

### When Needed

```bash
$ php weibo fetch
```

### Crontab

```bash
$ crontab -e
```

Add the following to the file (replace `<directory>` to file directory)

```
0 */8 * * * (php <directory>/fetch) # execute every 8 hours
```

## View Posts

To view posts, host the website using

```bash
$ php weibo serve
```

Then use your browser to open [localhost:8000](http://localhost:8000/)

The page is made with [Materialize CSS](http://materializecss.com/about.html)

## Screenshots

### Database

![Database](docs/screenshots/database.png)

### Fetch Weibo

![Fetch Weibo](docs/screenshots/fetch.png)

### Presenter

![Presenter](docs/screenshots/image-in-present.png)
