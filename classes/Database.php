<?php

require_once __DIR__ . '/DatabaseDeadlockException.php';

class Database {
    const MYSQL_DEADLOCK_ERROR_CODE = 1213;

    public ?mysqli $con = null;

    public bool $ENABLE_ROW_LOCKING = true;
    public bool $in_db_transaction = false;

    // Variables for query() function to track things
    public ?mysqli_result $result;
    public string $last_query_type;
    public int $last_num_rows = 0;
    public int $last_affected_rows;
    public $last_insert_id;

    public function __construct(
        private string $host,
        private string $username,
        private string $password,
        private string $database,
    ) {}

    /* function connect()
    Connects to a MySQL database and selects a DB. Stores connection resource in $con and returns.
    -Parameters-
    None; Uses @host, @user_name, @password, @database from /secure/vars.php for DB credentials.
*/
    /**
     * @throws RuntimeException
     */
    public function connect(): mysqli {
        if($this->con) {
            return $this->con;
        }

        $con = new mysqli($this->host, $this->username, $this->password) or throw new RuntimeException(mysqli_error($this->con));
        mysqli_select_db($con, $this->database) or throw new RuntimeException(mysqli_error($this->con));

        $this->con = $con;
        return $con;
    }

    /* function clean(raw_input)
        Cleans raw input to be safe for use in queries. Requires $this->con to have a connection for using mysqli_real_escape_string
        -Parameters-
        @raw_input: Input to be sanitized
    */
    public function clean($raw_input): string {
        if(!$this->con) {
            $this->connect();
        }

        $input = trim($raw_input);
        $search_terms = array('&yen;');
        $replace_terms = array('[yen]');
        $input = str_replace($search_terms, $replace_terms, $input);
        $input = htmlspecialchars(
            string: $input,
            flags: ENT_QUOTES,
            double_encode: false
        );

        $input = str_replace($replace_terms, $search_terms, $input);
        $input = mysqli_real_escape_string($this->con, $input);
        return $input;
    }

    /**
     * @throws RuntimeException
     */
    public function query($query): mysqli_result|bool {
        $query = trim($query);

        $expected_query_types = [
            'select',
            'insert',
            'update',
            'delete'
        ];
        $normalized_query = trim(strtolower($query));

        // default to first word
        $this->last_query_type = explode(' ', $normalized_query)[0];

        // double check for expected types in case of weird whitespace
        foreach($expected_query_types as $query_type) {
            if(str_starts_with($normalized_query, $query_type)) {
                $this->last_query_type = $query_type;
            }
        }

        if(!$this->con) {
            $this->connect();
        }

        $result = mysqli_query($this->con, $query);
        if(!$result) {
            $this->handleQueryError();
        }

        if($this->last_query_type == 'select') {
            $this->last_num_rows = mysqli_num_rows($result);
            $this->result = $result;
        }
        else {
            $this->last_affected_rows = mysqli_affected_rows($this->con);
        }

        if($this->last_query_type == 'insert') {
            $this->last_insert_id = mysqli_insert_id($this->con);
        }
        return $result;
    }

    /**
     * @throws DatabaseDeadlockException|Exception
     */
    protected function handleQueryError() {
        $error_code = mysqli_errno($this->con);
        if($error_code == self::MYSQL_DEADLOCK_ERROR_CODE) {
            throw new DatabaseDeadlockException();
        }

        $error_message = mysqli_error($this->con);
        error_log($error_message . ' in ' . System::simpleStackTrace());

        throw new RuntimeException($error_message);
    }

    /* function fetch(result set, return_type)

    */
    public function fetch($result = false, $return_type = 'assoc'): ?array {
        if(!$result) {
            $result = $this->result;
        }

        if($return_type == 'assoc') {
            return mysqli_fetch_assoc($result);
        }
        else {
            return mysqli_fetch_array($result);
        }
    }

    /**
     * @param false       $result
     * @param string|null $id_column
     * @return array
     */
    public function fetch_all($result = false, ?string $id_column = null): array {
        if(!$result) {
            $result = $this->result;
        }

        $entities = [];
        while($row = $this->fetch($result)) {
            if($id_column) {
                $entities[$row[$id_column]] = $row;
            }
            else {
                $entities[] = $row;
            }
        }
        return $entities;
    }

    public function startTransaction(): void {
        if ($this->ENABLE_ROW_LOCKING) {
            $this->query("START TRANSACTION;");
            $this->in_db_transaction = true;
        }
    }

    public function commitTransaction(): void {
        if ($this->ENABLE_ROW_LOCKING && $this->in_db_transaction) {
            $this->query("COMMIT;");
            $this->in_db_transaction = false;
        }
    }
    public function rollbackTransaction(): void {
        if ($this->ENABLE_ROW_LOCKING && $this->in_db_transaction) {
            $this->query("ROLLBACK;");
            $this->in_db_transaction = false;
        }
    }
}