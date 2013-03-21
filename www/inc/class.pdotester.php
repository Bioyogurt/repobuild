<?php
class pdotester extends PDO {
    public function __construct($dsn, $username = null, $password = null, $driver_options = array()) {
        parent::__construct($dsn, $username, $password, $driver_options);
        $this->setAttribute(PDO::ATTR_STATEMENT_CLASS, array('PDOStatementTester', array($this)));
    }
}
class PDOStatementTester extends PDOStatement {
    protected $connection;

    protected function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    public function execute() {
        global $dbg;
        $s = timer();
        parent::execute();
        $t = timer() - $s;
        $dbg['queries'][] = '['.$t.'] '.htmlspecialchars($this->queryString);
        if(!isset($dbg['time']['queries']))
            $dbg['time']['queries'] = 0;
        $dbg['time']['queries'] += $t;
    }
}