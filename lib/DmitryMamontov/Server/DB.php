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
 * @since     File available since Release 1.0.3
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
 * @version   Release: 1.0.3
 * @link      https://github.com/dmamontov/benchmark-tools/
 * @since     Class available since Release 1.0.3
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
     * @return string|boolean
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
     * Working time database startup.
     * @return string|boolean
     * @final
     */
    final public function UpTime()
    {
        if (is_null($this->db) || empty($this->db)) {
            return false;
        }

        $uptime = $this->db->query('SHOW GLOBAL STATUS LIKE \'Uptime\'');
        $uptime = end($uptime->fetch(\PDO::FETCH_NUM));
        if (empty($uptime) || is_null($uptime)) {
            return false;
        }

        $uptime = array(
            'days'    => (int) ($uptime / 86400),
            'hours'   => (int) (($uptime % 86400) / 3600),
            'minutes' => (int) (($uptime % 3600) / 60),
            'seconds' => (int) ($uptime % 60)
        );

        return "{$uptime['days']}d {$uptime['hours']}h {$uptime['minutes']}m {$uptime['seconds']}s";
    }

    /**
     * Size of global buffers.
     * key_buffer_size + tmp_table_size + innodb_buffer_pool_size + innodb_additional_mem_pool_size + innodb_log_buffer_size + query_cache_size
     * @return array|boolean
     * @final
     */
    final public function GlobalBuffers()
    {
        if (is_null($this->db) || empty($this->db)) {
            return false;
        }

        $variables = $this->db->query(
            "SHOW
                GLOBAL VARIABLES
             WHERE
                Variable_name
             IN
                (
                    'key_buffer_size',
                    'tmp_table_size',
                    'innodb_buffer_pool_size',
                    'innodb_additional_mem_pool_size',
                    'innodb_log_buffer_size',
                    'query_cache_size',
                    'max_heap_table_size'
                 )"
        );

        $result = 0;
        $buffer = array();
        while ($variable = $variables->fetch(\PDO::FETCH_NUM)) {
            $buffer[$variable[0]] = $variable[1];
        }

        if (
            isset($buffer['tmp_table_size']) &&
            isset($buffer['max_heap_table_size']) &&
            $buffer['tmp_table_size'] > $buffer['max_heap_table_size']
        ) {
            $result += $buffer['max_heap_table_size'];
        } elseif (isset($buffer['tmp_table_size'])) {
            $result += $buffer['tmp_table_size'];
        }

        if (isset($buffer['innodb_buffer_pool_size'])) {
            $result += $buffer['innodb_buffer_pool_size'];
        }

        if (isset($buffer['innodb_additional_mem_pool_size'])) {
            $result += $buffer['innodb_additional_mem_pool_size'];
        }

        if (isset($buffer['innodb_log_buffer_size'])) {
            $result += $buffer['innodb_log_buffer_size'];
        }

        if (isset($buffer['query_cache_size'])) {
            $result += $buffer['query_cache_size'];
        }

        return Tools::FormatSize($result);
    }

    /**
     * Single connection buffer size.
     * read_buffer_size + read_rnd_buffer_size + sort_buffer_size + thread_stack + join_buffer_size
     * @return array|boolean
     * @final
     */
    final public function ConnectionBuffers()
    {
        if (is_null($this->db) || empty($this->db)) {
            return false;
        }

        $variables = $this->db->query(
            "SHOW
                GLOBAL VARIABLES
             WHERE
                Variable_name
             IN
                (
                    'read_buffer_size',
                    'read_rnd_buffer_size',
                    'sort_buffer_size',
                    'thread_stack',
                    'join_buffer_size'
                 )"
        );

        $result = 0;
        while ($variable = $variables->fetch(\PDO::FETCH_NUM)) {
            $result += $variable[1];
        }

        return Tools::FormatSize($result);
    }

    /**
     * Max. connections.
     * @return array|boolean
     * @final
     */
    final public function MaxConnections()
    {
        if (is_null($this->db) || empty($this->db)) {
            return false;
        }

        $variables = $this->db->query('SHOW GLOBAL VARIABLES LIKE \'max_connections\'');

        return end($variables->fetch(\PDO::FETCH_NUM));
    }

    /**
     * Maximum memory usage.
     * Global Buffers + Connection Buffers * Connections
     * @return array|boolean
     * @final
     */
    final public function MaxMemoryUsage()
    {
        if (is_null($this->db) || empty($this->db)) {
            return false;
        }

        return Tools::FormatSize(
            Tools::UnFormatSize(self::GlobalBuffers()) +
            (Tools::UnFormatSize(self::ConnectionBuffers()) * self::MaxConnections())
        );
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
     * @return string|boolean
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
     * @return array|boolean
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
     * InnoDB buffer effectiveness.
     * @return array|boolean
     * @final
     */
    final public function InnodbBufferEffectiveness()
    {
        if (is_null($this->db) || empty($this->db)) {
            return false;
        }

        $variables = $this->db->query(
            "SHOW
                GLOBAL STATUS
             WHERE
                Variable_name
             IN
                ('Innodb_buffer_pool_reads', 'Innodb_buffer_pool_read_requests')"
        );

        $buffer = array();
        $result = 0;
        while ($variable = $variables->fetch(\PDO::FETCH_NUM)) {
            $buffer[$variable[0]] = $variable[1];
        }

        if (isset($buffer['Innodb_buffer_pool_reads']) && isset($buffer['Innodb_buffer_pool_read_requests'])) {
            $result = round((1 - $buffer['Innodb_buffer_pool_reads'] / $buffer['Innodb_buffer_pool_read_requests']) * 100, 2);
        }

        return array(
            'value'   => $result,
            'postfix' => '%'
        );
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
     * Size of MyIsam indexes.
     * @return array|boolean
     * @final
     */
    final public function MyIsamIndexes()
    {
        if (is_null($this->db) || empty($this->db)) {
            return false;
        }

        $indexes = $this->db->query(
            'SELECT
                IFNULL(SUM(INDEX_LENGTH),0)
                IND_SIZE
             FROM
                information_schema.TABLES
             WHERE
                TABLE_SCHEMA NOT IN (\'information_schema\')
             AND
                ENGINE = \'MyISAM\''
        );

        return Tools::FormatSize(reset($indexes->fetch(\PDO::FETCH_NUM)));
    }

    /**
     * MyISAM index cache (failures).
     * @return array|boolean
     * @final
     */
    final public function MyIsamIndexesCacheFailures()
    {
        if (is_null($this->db) || empty($this->db)) {
            return false;
        }

        $variables = $this->db->query(
            "SHOW
                GLOBAL STATUS
             WHERE
                Variable_name
             IN
                ('Key_reads', 'Key_read_requests')"
        );

        $buffer = array();
        $result = 0;
        while ($variable = $variables->fetch(\PDO::FETCH_NUM)) {
            $buffer[$variable[0]] = $variable[1];
        }

        if (isset($buffer['Key_read_requests']) && isset($buffer['Key_reads'])) {
            $result = round($buffer['Key_reads'] / $buffer['Key_read_requests'] * 100, 2);
        }

        return array(
            'value'   => $result,
            'postfix' => '%'
        );
    }

    /**
     * Request cache size.
     * @return array|boolean
     * @final
     */
    final public function RequestCacheSize()
    {
        if (is_null($this->db) || empty($this->db)) {
            return false;
        }

        $variables = $this->db->query('SHOW GLOBAL VARIABLES LIKE \'query_cache_size\'');

        return Tools::FormatSize(end($variables->fetch(\PDO::FETCH_NUM)));
    }

    /**
     * Request cache effectiveness.
     * @return array|boolean
     * @final
     */
    final public function RequestCacheEffectiveness()
    {
        if (is_null($this->db) || empty($this->db)) {
            return false;
        }
    
        $variables = $this->db->query(
            "SHOW
                GLOBAL STATUS
             WHERE
                Variable_name
             IN
                ('Qcache_hits', 'Com_select', 'Qcache_not_cached')"
        );
    
        $buffer = array();
        $result = 0;
        while ($variable = $variables->fetch(\PDO::FETCH_NUM)) {
            $buffer[$variable[0]] = $variable[1];
        }

        if (isset($buffer['Qcache_hits']) && isset($buffer['Com_select']) && isset($buffer['Qcache_not_cached'])) {
            $result = round($buffer['Qcache_hits'] / (($buffer['Com_select'] - $buffer['Qcache_not_cached']) + $buffer['Qcache_hits']) * 100, 2);
        }

        return array(
            'value'   => $result,
            'postfix' => '%'
        );
    }

    /**
     * Number of pruned requests.
     * @return string|boolean
     * @final
     */
    final public function RequestCachePrunes()
    {
        if (is_null($this->db) || empty($this->db)) {
            return false;
        }

        $variables = $this->db->query('SHOW GLOBAL STATUS LIKE \'Qcache_lowmem_prunes\'');

        return number_format(end($variables->fetch(\PDO::FETCH_NUM)), 0, '.', ' ');
    }

    /**
     * Total sorts.
     * Sort_scan + Sort_range
     * @return string|boolean
     * @final
     */
    final public function Sorts()
    {
        if (is_null($this->db) || empty($this->db)) {
            return false;
        }

        $variables = $this->db->query(
            "SHOW
                GLOBAL STATUS
             WHERE
                Variable_name
             IN
                ('Sort_scan', 'Sort_range')"
        );

        $result = 0;
        while ($variable = $variables->fetch(\PDO::FETCH_NUM)) {
            $result += end($variable);
        }

        return number_format($result, 0, '.', ' ');
    }

    /**
     * Rate of sort operations requiring creating temporary tables on the disk.
     * @return array|boolean
     * @final
     */
    final public function SortsTemporaryTables()
    {
        if (is_null($this->db) || empty($this->db)) {
            return false;
        }

        $variables = $this->db->query('SHOW GLOBAL STATUS LIKE \'Sort_merge_passes\'');

        return array(
            'value'   => round(end($variables->fetch(\PDO::FETCH_NUM)) / str_replace(' ', '', self::Sorts()) * 100, 2),
            'postfix' => '%'
        );
    }

    /**
     * Number of table join operations not requiring indexes.
     * @return string|boolean
     * @final
     */
    final public function NotRequiringIndexes()
    {
        if (is_null($this->db) || empty($this->db)) {
            return false;
        }

        $variables = $this->db->query(
            "SHOW
                GLOBAL STATUS
             WHERE
                Variable_name
             IN
                ('Select_range_check', 'Select_full_join')"
        );

        $result = 0;
        while ($variable = $variables->fetch(\PDO::FETCH_NUM)) {
            $result += $variable[1];
        }

        return number_format($result, 0, '.', ' ');
    }

    /**
     * Rate of temporary tables requiring temporary disk space.
     * @return array|boolean
     * @final
     */
    final public function TemporaryTables()
    {
        if (is_null($this->db) || empty($this->db)) {
            return false;
        }
    
        $variables = $this->db->query(
            "SHOW
                GLOBAL STATUS
             WHERE
                Variable_name
             IN
                ('Created_tmp_disk_tables', 'Created_tmp_tables')"
        );
    
        $buffer = array();
        $result = 0;
        while ($variable = $variables->fetch(\PDO::FETCH_NUM)) {
            $buffer[$variable[0]] = $variable[1];
        }

        if (isset($buffer['Created_tmp_disk_tables']) && isset($buffer['Created_tmp_tables'])) {
            $result = round(($buffer['Created_tmp_disk_tables'] / ($buffer['Created_tmp_tables'] + $buffer['Created_tmp_disk_tables'])) * 100, 2);
        }

        return array(
            'value'   => $result,
            'postfix' => '%'
        );
    }

    /**
     * Thread cache efficiency.
     * @return array|boolean
     * @final
     */
    final public function ThreadCacheEfficiency()
    {
        if (is_null($this->db) || empty($this->db)) {
            return false;
        }
    
        $variables = $this->db->query(
            "SHOW
                GLOBAL STATUS
             WHERE
                Variable_name
             IN
                ('Threads_created', 'Connections')"
        );

        $buffer = array();
        $result = 0;
        while ($variable = $variables->fetch(\PDO::FETCH_NUM)) {
            $buffer[$variable[0]] = $variable[1];
        }

        if (isset($buffer['Threads_created']) && isset($buffer['Connections'])) {
            $result = round(100 - (($buffer['Threads_created'] / $buffer['Connections']) * 100), 2);
        }

        return array(
            'value'   => $result,
            'postfix' => '%'
        );
    }

    /**
     * Open table cache efficiency.
     * @return array|boolean
     * @final
     */
    final public function OpenTableCacheEfficiency()
    {
        if (is_null($this->db) || empty($this->db)) {
            return false;
        }

        $variables = $this->db->query(
            "SHOW
                GLOBAL STATUS
             WHERE
                Variable_name
             IN
                ('Open_tables', 'Opened_tables')"
        );

        $buffer = array();
        $result = 0;
        while ($variable = $variables->fetch(\PDO::FETCH_NUM)) {
            $buffer[$variable[0]] = $variable[1];
        }

        if (isset($buffer['Open_tables']) && isset($buffer['Opened_tables'])) {
            $result = round($buffer['Open_tables'] / $buffer['Opened_tables'] * 100, 2);
        }

        return array(
            'value'   => $result,
            'postfix' => '%'
        );
    }

    /**
     * Rate of open files.
     * @return array|boolean
     * @final
     */
    final public function OpenFiles()
    {
        if (is_null($this->db) || empty($this->db)) {
            return false;
        }

        $querys = array(
            'SHOW GLOBAL VARIABLES LIKE \'open_files_limit\'',
            'SHOW GLOBAL STATUS LIKE \'Open_files\''
        );
        $buffer = array();

        foreach ($querys as $query) {
            $variables = $this->db->query($query);

            if ($variable = $variables->fetch(\PDO::FETCH_NUM)) {
                $buffer[$variable[0]] = $variable[1];
            }
        }

        $result = 0;

        if (isset($buffer['Open_files']) && isset($buffer['open_files_limit'])) {
            $result = round($buffer['Open_files'] / $buffer['open_files_limit'] * 100, 2);
        }

        return array(
            'value'   => $result,
            'postfix' => '%'
        );
    }

    /**
     * Rate of successfully obtained unenqueued locks.
     * @return array|boolean
     * @final
     */
    final public function Locks()
    {
        if (is_null($this->db) || empty($this->db)) {
            return false;
        }

        $variables = $this->db->query(
            "SHOW
                GLOBAL STATUS
             WHERE
                Variable_name
             IN
                ('Table_locks_waited', 'Table_locks_immediate')"
        );

        $buffer = array();
        $result = 0;
        while ($variable = $variables->fetch(\PDO::FETCH_NUM)) {
            $buffer[$variable[0]] = $variable[1];
        }

        if (isset($buffer['Table_locks_waited']) && isset($buffer['Table_locks_immediate'])) {
            $result = $buffer['Table_locks_waited'] == 0 ? 100 :
                      round($buffer['Table_locks_immediate'] / ($buffer['Table_locks_waited'] + $buffer['Table_locks_immediate']) * 100, 2);
        }

        return array(
            'value'   => $result,
            'postfix' => '%'
        );
    }

    /**
     * Rate of incorrectly closed connections.
     * @return array|boolean
     * @final
     */
    final public function ConnectionAborts()
    {
        if (is_null($this->db) || empty($this->db)) {
            return false;
        }

        $variables = $this->db->query(
            "SHOW
                GLOBAL STATUS
             WHERE
                Variable_name
             IN
                ('Aborted_connects', 'Connections')"
        );

        $buffer = array();
        $result = 0;
        while ($variable = $variables->fetch(\PDO::FETCH_NUM)) {
            $buffer[$variable[0]] = $variable[1];
        }

        if (isset($buffer['Aborted_connects']) && isset($buffer['Connections'])) {
            $result = round(($buffer['Aborted_connects'] / $buffer['Connections']) * 100, 2);
        }

        return array(
            'value'   => $result,
            'postfix' => '%'
        );
    }

    /**
     * Speed test insert 1000 records.
     * @return array|boolean
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
     * @return array|boolean
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
     * @param boolean $skip
     * @return array|boolean
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
