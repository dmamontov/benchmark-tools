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
use DmitryMamontov\BenchmarkTools;
use DmitryMamontov\Tools\Tools;
use DmitryMamontov\Server\Server;
use DmitryMamontov\Server\FileSystem;

/**
 * HighLoad - Class de checking heavily calculations.
 *
 * @author    Dmitry Mamontov <d.slonyara@gmail.com>
 * @copyright 2015 Dmitry Mamontov <d.slonyara@gmail.com>
 * @license   http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @version   Release: 1.0.1
 * @link      https://github.com/dmamontov/benchmark-tools/
 * @since     Class available since Release 1.0.1
 */
class HighLoad
{
    /**
     * Checks the actual memory limit.
     * @return array
     * @static
     * @final
     */
    final public static function ActualMemoryLimit()
    {
        if (FileSystem::FileDeletion() === false) {
            return false;
        }

        if (file_exists("{$_SERVER['DOCUMENT_ROOT']}/test_actual_memory.php")) {
            @unlink("{$_SERVER['DOCUMENT_ROOT']}/test_actual_memory.php");
        }

        $file = @fopen("{$_SERVER['DOCUMENT_ROOT']}/test_actual_memory.php", 'wb');
        @fputs(
            $file,
            "<?php\n" .
            "if (function_exists('pcntl_fork') === false || function_exists('shm_attach') === false) {\n" .
            "    exit();\n" .
            "}\n\n" .
            "\$shmId = shm_attach(ftok(__FILE__, 'A'));\n" .
            "\$pid = @pcntl_fork();\n\n" .
            "if (!\$pid) {\n" .
            "    ini_set('display_errors', false);\n" .
            "    error_reporting(-1);\n\n" .
            "    \$memory = (int) '" . reset(Server::MemoryLimit()) . "';\n\n" .
            "    for (\$i = 1; \$i <= \$memory * 2; \$i++) {\n" .
            "        \$a[] = @str_repeat(chr(\$i), 1024 * 1024);\n" .
            "        shm_put_var(\$shmId, 1, \$i);\n" .
            "    }\n" .
            "    exit();\n" .
            "}\n\n" .
            "\$actual = 0;\n" .
            "while (true) {\n" . 
            "    if (\$actual === @shm_get_var(\$shmId, 1)) {\n" .
            "        shm_remove(\$shmId);\n" .
            "        echo \$actual;\n" .
            "        break;\n" .
            "    } elseif (@shm_has_var(\$shmId, 1) && \$actual < @shm_get_var(\$shmId, 1)) {\n" .
            "        \$actual = @shm_get_var(\$shmId, 1);\n" .
            "    }\n" .
            "    time_nanosleep(0, 250000000);\n" .
            "}\n" .
            '?>'
        );
        @fclose($file);

        @exec("php {$_SERVER['DOCUMENT_ROOT']}/test_actual_memory.php", $out);

        if (file_exists("{$_SERVER['DOCUMENT_ROOT']}/test_actual_memory.php")) {
            @unlink("{$_SERVER['DOCUMENT_ROOT']}/test_actual_memory.php");
        }

        if (isset($out[0]) && $out[0] > 0) {
            return array(
                'value' => reset($out),
                'postfix' => 'Mb'
            );
        } else {
            return false;
        }
    }

    /**
     * Checks real-time execution of the script.
     * @param integer $time
     * @return array
     * @static
     * @final
     */
    final public static function ActualExecutionTime($time = 0)
    {
        global $count, $js;

        if (
            Server::PHPInterface() == 'cli' ||
            FileSystem::FileDeletion() === false ||
            Server::SharedMemory() === false
        ) {
            return false;
        }

        if ($time == 0) {
            $time = Server::MaxExecutionTime();
        }

        $time = (int) $time;
        $tok = ftok(__FILE__, 'A');
        $shmId = shm_attach($tok);
        @shm_remove($shmId);

        if (file_exists("{$_SERVER['DOCUMENT_ROOT']}/test_actual_time.php")) {
            @unlink("{$_SERVER['DOCUMENT_ROOT']}/test_actual_time.php");
        }
        if (file_exists("{$_SERVER['DOCUMENT_ROOT']}/test_actual_time_wait.php")) {
            @unlink("{$_SERVER['DOCUMENT_ROOT']}/test_actual_time_wait.php");
        }

        $file = @fopen("{$_SERVER['DOCUMENT_ROOT']}/test_actual_time.php", 'wb');
        @fputs(
            $file,
            "<?php\n" .
            "if (function_exists('shm_attach') === false) {\n" .
            "    exit();\n" .
            "}\n\n" .
            "\$shmId = shm_attach($tok);\n" .
            "@ini_set('display_errors', false);\n" .
            "@error_reporting(-1);\n\n" .
            "@set_time_limit($time);\n" .
            "@ini_set('max_execution_time', $time);\n\n" .
            "for (\$i = 1; \$i <= $time; \$i++) {\n" .
            "    sleep(1);\n" .
            "    shm_put_var(\$shmId, 1, \$i);\n" .
            "}\n" .
            '?>'
        );
        @fclose($file);

        $file = @fopen("{$_SERVER['DOCUMENT_ROOT']}/test_actual_time_wait.php", 'wb');
        @fputs(
            $file,
            "<?php\n" .
            "if (function_exists('shm_attach') === false) {\n" .
            "    exit();\n" .
            "}\n\n" .
            "\$shmId = shm_attach($tok);\n" .
            "@ini_set('display_errors', false);\n" .
            "@error_reporting(-1);\n\n" .
            "@set_time_limit($time);\n" .
            "@ini_set('max_execution_time', $time);\n\n" .
            "if (@shm_has_var(\$shmId, 1) && \$_GET['seconds'] < @shm_get_var(\$shmId, 1)) {\n" .
            "    echo @shm_get_var(\$shmId, 1);\n" .
            "} else {\n" .
            "    @shm_remove(\$shmId);\n" .
            "    @unlink('{$_SERVER['DOCUMENT_ROOT']}/test_actual_time.php');\n" .
            "    @unlink('{$_SERVER['DOCUMENT_ROOT']}/test_actual_time_wait.php');\n" .
            "}\n" .
            '?>'
        );
        @fclose($file);

        $cnt = $count + 1;
        $js .= "$('#value-$cnt').parent('tr').removeClass().addClass('active');\n" .
               "$('#value-$cnt').siblings('.loader').html('<img src=\"https://www.crazydogtshirts.com/skin/frontend/mtcolias/default/images/loader.gif\"/>');\n" .
               "$.get( \"" . dirname($_SERVER['PHP_SELF']) . "/test_actual_time.php\");\n" .
               "var tid = setInterval(testactualtime, 2500);\n" .
               "var seconds = 0;\n" .
               "function testactualtime() {\n" .
               "    $.get( \"" . dirname($_SERVER['PHP_SELF']) . "/test_actual_time_wait.php?seconds=\" + seconds, function(data) {\n" .
               "        if (data == '') {\n" .
               "            data = 0;\n" .
               "        }\n" .
               "        if (parseInt(seconds) >= parseInt(data)) {\n" .
               "            $('#value-$cnt').siblings('.loader').children().remove();\n" .
               "            $('#value-$cnt').parent('tr').removeClass();\n" .
               "            if (parseInt(seconds) == parseInt($time)) {\n" .
               "                $('#value-$cnt').parent('tr').addClass('success');\n" .
               "            } else {\n" .
               "                $('#value-$cnt').parent('tr').addClass('danger');\n" .
               "            }\n" .
               "            clearInterval(tid);\n" .
               "        } else {\n" .
               "            seconds = data;\n" .
               "            $('#value-$cnt').html(seconds + ' s');\n".
               "        }\n" .
               "    })\n" .
               "}\n";

        return array(
            'value' => 0,
            'postfix' => 's'
        );
    }

    /**
     * Checking the sending big emails.
     * @param string $email
     * @return array
     * @static
     * @final
     */
    final public static function SendingBigEmails($email = 'test@test.com')
    {
        global $count, $js;

        if (Server::EmailSending() !== true) {
            return false;
        }

        $body = str_repeat(file_get_contents(__FILE__), 10);
        $time = Tools::getTime();
        $mail = @mail($email, "Server Test\r\n\tmultiline subject", $body, "BCC: $email\r\n");
        $time = round(Tools::getTime() - $time, 2);
        $result = $mail ? true : false;

        return array(
            'value' => $result,
            'time'  => "$time s"
        );
    }

    /**
     * Checking upload big files to the server.
     * @return boolean
     * @static
     * @final
     */
    final public static function UploadsBigFile()
    {
        return FileSystem::FileUploads(true);
    }
}
