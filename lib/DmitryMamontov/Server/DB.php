<?php
/**
 * BenchmarkTools
 *
 * Copyright (c) 2015, Dmitry Mamontov <d.slonyara@gmail.com>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Dmitry Mamontov nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package   benchmark-tools
 * @author    Dmitry Mamontov <d.slonyara@gmail.com>
 * @copyright 2015 Dmitry Mamontov <d.slonyara@gmail.com>
 * @license   http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @since     File available since Release 1.0.1
 */
namespace DmitryMamontov\Server;
use DmitryMamontov\Server\Server;
use DmitryMamontov\Tools\Tools;

/**
 * DB - Class to check the database.
 *
 * @author    Dmitry Mamontov <d.slonyara@gmail.com>
 * @copyright 2015 Dmitry Mamontov <d.slonyara@gmail.com>
 * @license   http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @version   Release: 1.0.1
 * @link      https://github.com/dmamontov/benchmark-tools/
 * @since     Class available since Release 1.0.1
 * @todo      Requires improvements.
 */
class DB
{
    private $db = null;
    private $dbname;

    /**
     * The class constructor initializes variables.
     * @param string $user
     * @param string $pass
     * @param string $dbname
     * @param string $host
     * @param string $driver
     * @final
     */
    final public function __construct($user, $pass, $dbname = null, $host = 'localhost', $driver = 'mysql')
    {
        if (Server::PDO()) {
            $this->dbname = $dbname;
            try {
                $this->db = new \PDO("$driver:host=$host" . (is_null($dbname) ? '' :";dbname=$dbname"), $user, $pass);
            } catch (PDOException $e) {}
        }
    }

    /**
     * Gets the version.
     * @return string
     * @final
     */
    final public function Version()
    {
        if (is_null($this->db) || empty($this->db)) {
            return false;
        }

        $version = $this->db->query('SELECT version();');

        return reset($version->fetch(\PDO::FETCH_NUM));
    }

    /**
     * Checking the time difference.
     * @return boolean
     * @final
     */
    final public function TimeDiff()
    {
        if (is_null($this->db) || empty($this->db)) {
            return false;
        }

        $s = time();

        $time = $this->db->query('SELECT NOW() AS `time`');
        if (abs($s - strtotime(reset($time->fetch(\PDO::FETCH_NUM)))) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Gets SqlMode.
     * @return string
     * @final
     */
    final public function SqlMode()
    {
        if (is_null($this->db) || empty($this->db)) {
            return false;
        }

        $mode = $this->db->query('SHOW VARIABLES LIKE \'sql_mode\'');
        $mode = end($mode->fetch(\PDO::FETCH_NUM));

        return empty($mode) ? false : $mode;
    }

    /**
     * Gets Characters.
     * @return array
     * @final
     */
    final public function Characters()
    {
        if (is_null($this->db) || empty($this->db)) {
            return false;
        }

        $result = false;

        $characters = $this->db->query("SHOW VARIABLES LIKE 'character\_set\_%'");
        while($character = $characters->fetch(\PDO::FETCH_NUM)) {
            $result[$character[0]] = $character[1];
        }

        return $result;
    }

    /**
     * Check support InnoDB.
     * @return boolean
     * @final
     */
    final public function Innodb()
    {
        if (is_null($this->db) || empty($this->db) || is_null($this->dbname)) {
            return false;
        }

        $result = false;
        $innodb = $this->db->prepare('CREATE TABLE test_table (test varchar(100), test2 varchar(50), test3 varchar(30), test4 text) ENGINE=INNODB');
        if ($innodb->execute()) {
            $innodb = $this->db->query('SHOW CREATE TABLE test_table;');
            if (stripos(end($innodb->fetch()), 'ENGINE=InnoDB')) {
                $result = true;
            }
            $innodb = $this->db->prepare('DROP TABLE IF EXISTS test_table;');
            $innodb->execute();
         }

         return $result;
    }

    /**
     * Check support MyIsam.
     * @return boolean
     * @final
     */
    final public function MyIsam()
    {
        if (is_null($this->db) || empty($this->db) || is_null($this->dbname)) {
            return false;
        }

        $result = false;
        $myisam = $this->db->prepare('CREATE TABLE test_table (test varchar(100), test2 varchar(50), test3 varchar(30), test4 text) ENGINE=MYISAM');
        if ($myisam->execute()) {
            $myisam = $this->db->query('SHOW CREATE TABLE test_table;');
            if (stripos(end($myisam->fetch()), 'ENGINE=MYISAM')) {
                $result = true;
            }
            $myisam = $this->db->prepare('DROP TABLE IF EXISTS test_table;');
            $myisam->execute();
        }

        return $result;
    }

    /**
     * Speed test insert 1000 records.
     * @return array
     * @final
     */
    final public function SpeedInsert()
    {
        if ($this->MyIsam()) {
            $type = 'MYISAM';
        } elseif ($this->Innodb()) {
            $type = 'InnoDB';
        } else {
            return false;
        }

        $result = false;
        $table = $this->db->prepare("CREATE TABLE test_table (test varchar(100), test2 varchar(50), test3 varchar(30), test4 text) ENGINE=$type");
        if ($table->execute()) {
            $time = Tools::getTime();

            for ($i=0; $i < 1000; $i++) {
                $insert = $this->db->prepare("INSERT INTO test_table VALUES ('test1','test2','test3','test4')");
                if ($insert->execute() == false) {
                    return false;
                }
            }

            $result = array(
                'value' => round(1000 / (Tools::getTime() - $time)),
                'postfix' => 'q/sec.'
            );

            $table = $this->db->prepare('DROP TABLE IF EXISTS test_table;');
            $table->execute();
        }

        return $result;
    }

    /**
     * Speed test select 1000 records.
     * @return array
     * @final
     */
    final public function SpeedSelect()
    {
        if ($this->MyIsam()) {
            $type = 'MYISAM';
        } elseif ($this->Innodb()) {
            $type = 'InnoDB';
        } else {
            return false;
        }

        $result = false;
        $table = $this->db->prepare("CREATE TABLE test_table (test varchar(100), test2 varchar(50), test3 varchar(30), test4 text) ENGINE=$type");
        if ($table->execute()) {
            $time = Tools::getTime();

            for ($i=0; $i < 1000; $i++) {
                $insert = $this->db->prepare("INSERT INTO test_table VALUES ('test1','test2','test3','test4')");
                if ($insert->execute() == false) {
                    return false;
                }
            }

            $time = Tools::getTime();

            $select = $this->db->prepare("SELECT * FROM test_table");
            if ($select->execute() == false) {
                return false;
            }

            $result = array(
                'value' => round(1000 / (Tools::getTime() - $time)),
                'postfix' => 'q/sec.'
            );

            $table = $this->db->prepare('DROP TABLE IF EXISTS test_table;');
            $table->execute();
        }

        return $result;
    }

    /**
     * Gets the number of records in the tables.
     * @return array
     * @final
     */
    final public function CountRow($skip = false)
    {
        if (is_null($this->db) || empty($this->db)) {
            return false;
        }

        $result = false;

        $tables = $this->db->query('SHOW TABLES');
        while ($table = $tables->fetch(\PDO::FETCH_NUM)) {
            $table = end($table);
            $count = $this->db->query("SELECT COUNT(0) FROM $table");
            $c = reset($count->fetch(\PDO::FETCH_NUM));
            if ($c > 0 || $skip === false) {
                $result[$table] = array(
                    'value' => $c,
                    'postfix' => 'rec.'
                );
            }
        }

        return $result;
    }
}
