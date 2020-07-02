<?php

class SqliteService {
    const CACHE_TABLE = 'quotes';
    const CACHE_REQUESTS_TABLE = 'requests';
    const CACHE_FILE = 'SQLite';

    private $db;
    public $tables = [];

    public function __construct()
    {
        if (!file_exists(self::CACHE_FILE)) { // Create if not exists
            if (!touch(self::CACHE_FILE)) { // Throw exception if can\'t create the file
                throw new Exception('Cant create the cache file / database');
            }
        }
        $this->db = new SQLite3(self::CACHE_FILE);
        $this->setTables();
        $this->createTables();
    }

    public function createTables() {
        if (!in_array(self::CACHE_TABLE, $this->tables)) {
            $this->createCacheTable();
        }
        if (!in_array(self::CACHE_REQUESTS_TABLE, $this->tables)) {
            $this->createRequestsTable();
        }
    }

    public function rebuildTables() {
        $this->deleteTable(self::CACHE_TABLE);
        $this->deleteTable(self::CACHE_REQUESTS_TABLE);
        $this->createTables();
    }

    private function deleteTable($table) {
        $this->db->query("DROP TABLE $table");
    }

    public function query($sql) {
        return $this->db->query($sql);
    }

    public function getLastUserConnection($uid, $request) {
        $sql = "SELECT * from '" . self::CACHE_REQUESTS_TABLE . "' where uid = '$uid' and request = '$request' ";
        $sql.= " and datetime(created_at) >= datetime('now','-10 minutes') order by created_at desc limit 1";
        $res = $this->db->query($sql);

        if ($user = $res->fetchArray()) {
            $quotes = [];
            foreach ($this->getUserCachedQuotes($user) as $quote) {
                // $quotes[] = $quote; // return this line instead of the followings to see caching data and recognized it in the results
                $quotes[] = [
                    'author' => $quote['quote'],
                    'quote' => $quote['author']
                ];
            }
            return $quotes;
        } else { // false means there is no session cached, get data from a source
            return false;
        }
    }

    public function saveRequestQuotes($quotes, $thisRequest) {
        foreach ($quotes as $quote) {
            $sql = "INSERT INTO " . self::CACHE_TABLE . " (user_id, request, author, quote, created_at) values ";
            $sql.= "($thisRequest[id], '$thisRequest[request]', '$quote[author]', ";
            $sql.= "'$quote[quote]', '$thisRequest[created_at]')";
            $this->db->query($sql);
        }
    }

    public function getUserCachedQuotes($user) {
        // TODO: pending all this
        $sql = "select * from " . self::CACHE_TABLE . "  where user_id = $user[id] and request = '$user[request]'";
        $response = $this->db->query($sql);
        $quotes = [];
        if ($response) {
            while ($quote = $response->fetchArray()) {
                $quotes[] = $quote;
            }
        }
        return $quotes;
    }

    public function registerRequest($uid, $request)
    {
        $dateTime = new DateTime();
        $formattedDateTime = $dateTime->format('Y-m-d H:i:s');
        $sql = "INSERT INTO '" . self::CACHE_REQUESTS_TABLE . "' (uid, request, created_at) values ";
        $sql .= "('$uid', '$request', '$formattedDateTime')";
        $this->db->query($sql); // TODO: Check if worked or throw exception
        return [
            'id' => $this->db->lastInsertRowID(),
            'uid' => $uid,
            'request' => $request,
            'created_at' => $formattedDateTime
        ];
    }

    public function setTables()
    {
        $tablesQuery = $this->query("SELECT name FROM sqlite_master WHERE type='table'");
        $tables = [];
        while($table = $tablesQuery->fetchArray(SQLITE3_ASSOC)) {
            $tables[] = $table['name'];
        }
        $this->tables = $tables;
    }

    public function createRequestsTable () {
        $table = self::CACHE_REQUESTS_TABLE;    // Users
        $this->query("CREATE TABLE $table (id INTEGER PRIMARY KEY"
            . ", uid INTEGER, request VARCHAR(255), created_at DATETIME)");
    }

    public function createCacheTable() { // TODO: request is like a double check, it can be removed in the future
        $table = self::CACHE_TABLE; // cached quotes
        $this->query("CREATE TABLE $table (id INTEGER PRIMARY KEY, user_id VARCHAR(255)"
            . ", request VARCHAR(255), author VARCHAR(255), quote VARCHAR(255), created_at DATETIME)");
    }

}