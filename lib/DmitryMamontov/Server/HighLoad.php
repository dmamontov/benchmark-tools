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
 * @todo      This script is VERY load on the system.
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
 * @version   Release: 1.0.3
 * @link      https://github.com/dmamontov/benchmark-tools/
 * @since     Class available since Release 1.0.3
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
               "    .fail(function() {\n" .
               "        $('#value-$cnt').html('No');\n" .
               "        $('#value-$cnt').parent('tr').addClass('danger');\n" .
               "        clearInterval(tid);\n" .
               "    })\n" .
               "}\n";

        return array(
            'value' => 0,
            'postfix' => 's'
        );
    }

    /**
     * Number of operations of the CPU.
     * @return array
     * @static
     * @final
     */
    final public static function NumberCpuOperations()
    {
        $operations = array();
        for ($i = 0; $i < 4; $i++) {

            $time = Tools::getTime();
            for ($j = 0; $j < 1000000; $j++) {}
            $firstResult = Tools::getTime() - $time;

            $time = Tools::getTime();
            for ($j = 0; $j < 1000000; $j++) {
                $a++;
                $a--;
                $a++;
                $a--;
            }
            $secondResult = Tools::getTime() - $time;

            if ($secondResult > $firstResult) {
                $operations[] = 1 / ($secondResult - $firstResult);
            }
        }

        return array(
            'value'   => number_format(count($operations) > 0 ? array_sum($operations) / (float) count($operations) : 0, 1),
            'postfix' => 'MFLOPS'
        );
    }

    /**
     * Number of file operations.
     * @return array
     * @static
     * @final
     */
    final public static function NumberFileOperations()
    {
        if (FileSystem::FolderDeletion() === false || FileSystem::FileDeletion() === false) {
            return false;
        }

        if (file_exists("{$_SERVER['DOCUMENT_ROOT']}/test_operations")) {
            for ($j = 0; $j < 100; $j++) {
                @unlink("{$_SERVER['DOCUMENT_ROOT']}/test_operations/test_file_$j.php");
            }
            @rmdir("{$_SERVER['DOCUMENT_ROOT']}/test_operations");
        }

        $operations = array();

        @mkdir("{$_SERVER['DOCUMENT_ROOT']}/test_operations");

        $fileName = "{$_SERVER['DOCUMENT_ROOT']}/test_operations/test_file_#j#.php";
        $body = "<?\$a='" . str_repeat("q", 1024) . "';?><?/*" . str_repeat("w", 1024) . "*/?><?\$b='" . str_repeat("e", 1024) . "';?>";

        for ($i = 0; $i < 4; $i++) {

            $time = Tools::getTime();
            for ($j = 0; $j < 100; $j++) {
                $file = str_replace("#j#", $j, $fileName);
            }
            $firstResult = Tools::getTime() - $time;

            $time = Tools::getTime();
            for ($j = 0; $j < 100; $j++) {
                $file = str_replace("#j#", $j, $fileName);
                $fileRes = @fopen($file, "wb");
                @fwrite($fileRes, $body);
                @fclose($fileRes);
                @include($file);
                @unlink($file);
            }
            $secondResult = Tools::getTime() - $time;

            if ($secondResult > $firstResult) {
                $operations[] = 100 / ($secondResult - $firstResult);
            }
        }

        if (file_exists("{$_SERVER['DOCUMENT_ROOT']}/test_operations")) {
            for ($j = 0; $j < 100; $j++) {
                @unlink("{$_SERVER['DOCUMENT_ROOT']}/test_operations/test_file_$j.php");
            }
            @rmdir("{$_SERVER['DOCUMENT_ROOT']}/test_operations");
        }

        return array(
            'value'   => number_format(count($operations) > 0 ? array_sum($operations) / (float) count($operations) : 0, 1, '.', ''),
            'postfix' => 'FOPS'
        );
    }

    /**
     * Checking the sending big emails.
     * @param integer $size
     * @param string $email
     * @return array
     * @static
     * @final
     */
    final public static function SendingBigEmails($size = 65, $email = 'test@test.com')
    {
        global $count, $js;

        if (
            Server::PHPInterface() == 'cli' ||
            Server::EmailSending() !== true ||
            FileSystem::FileDeletion() === false
        ) {
            return false;
        }

        if (file_exists("{$_SERVER['DOCUMENT_ROOT']}/test_big_email.php")) {
            @unlink("{$_SERVER['DOCUMENT_ROOT']}/test_big_email.php");
        }

        $file = @fopen("{$_SERVER['DOCUMENT_ROOT']}/test_big_email.php", 'wb');
        @fputs(
            $file,
            "<?php\n" .
            "ini_set('display_errors', false);\n" .
            "@error_reporting(-1);\n\n" .
            "\$body = str_repeat(str_repeat('*', 1023) . \"\\n\", $size);\n" .
            "\$time = explode(' ', microtime());\n" .
            "\$time = (float) \$time[0] + (float) \$time[1];\n" .
            "\$mail = @mail('$email', \"Server Test\\r\\n\\tmultiline subject\", \$body, \"BCC: $email\\r\\n\");\n" .
            "\$finishtime = explode(' ', microtime());\n" .
            "\$finishtime = (float) \$finishtime[0] + (float) \$finishtime[1];\n" .
            "\$time = round(\$finishtime - \$time, 2);\n" .
            "if (\$mail) {\n" .
            "    echo \"Yes \$time s\";\n" .
            "} else {\n" .
            "    echo \"No\";\n" .
            "}\n" .
            "unlink(__FILE__)\n" .
            '?>'
        );
        @fclose($file);

        $cnt = $count + 1;
        $js .= "$('#value-$cnt').parent('tr').removeClass().addClass('active');\n" .
               "$('#value-$cnt').siblings('.loader').html('<img src=\"https://www.crazydogtshirts.com/skin/frontend/mtcolias/default/images/loader.gif\"/>');\n" .
               "$.get( \"" . dirname($_SERVER['PHP_SELF']) . "/test_big_email.php\", function(data) {\n" .
               "    $('#value-$cnt').siblings('.loader').children().remove();\n" .
               "    $('#value-$cnt').parent('tr').removeClass();\n" .
               "    if (data == 'No' || data == '') {\n" .
               "        $('#value-$cnt').html(data);\n".
               "        $('#value-$cnt').parent('tr').addClass('danger');\n\n" .
               "    } else {\n" .
               "        $('#value-$cnt').html(data);\n".
               "        $('#value-$cnt').parent('tr').addClass('success');\n\n" .
               "    }\n" .
               "})\n" .
               ".fail(function() {\n" .
               "    $('#value-$cnt').siblings('.loader').children().remove();\n" .
               "    $('#value-$cnt').html('No');\n" .
               "    $('#value-$cnt').parent('tr').addClass('danger');\n" .
               "})\n";

        return 'Wait';
    }

    /**
     * Checking upload big files to the server.
     * @param integer $size
     * @return boolean
     * @static
     * @final
     */
    final public static function UploadsBigFile($size = 1024)
    {
        global $count, $js;

        if (
            Server::PHPInterface() == 'cli' ||
            Server::PHPFileUploads() === false ||
            FileSystem::FileDeletion() === false
        ) {
            return false;
        }

        if (file_exists("{$_SERVER['DOCUMENT_ROOT']}/test_big.dat")) {
            @unlink("{$_SERVER['DOCUMENT_ROOT']}/test_big.dat");
        }
        if (file_exists("{$_SERVER['DOCUMENT_ROOT']}/test_big_upload.php")) {
            @unlink("{$_SERVER['DOCUMENT_ROOT']}/test_big_upload.php");
        }
        if (file_exists("{$_SERVER['DOCUMENT_ROOT']}/test_big_uploader.php")) {
            @unlink("{$_SERVER['DOCUMENT_ROOT']}/test_big_uploader.php");
        }

        $file = @fopen("{$_SERVER['DOCUMENT_ROOT']}/test_big_upload.php", 'wb');
        @fputs(
            $file,
            "<?php\n" .
            "if (isset(\$_FILES['filename']) && is_uploaded_file(\$_FILES['filename']['tmp_name'])) {\n" .
            "    @move_uploaded_file(\$_FILES['filename']['tmp_name'], \$_REQUEST['root'] . '/test_big.dat');\n" .
            "    echo file_exists(\$_REQUEST['root'] . '/test_big.dat');\n" .
            "}\n" .
            "@unlink(__FILE__);\n" .
            '?>'
        );
        @fclose($file);

        $file = @fopen("{$_SERVER['DOCUMENT_ROOT']}/test_big_uploader.php", 'wb');
        @fputs(
            $file,
            "<?php\n" .
            "ini_set('display_errors', false);\n" .
            "@error_reporting(-1);\n\n" .
            "\$text = str_repeat(str_repeat('*', 1023) . \"\\n\", $size);\n" .
            "\$boundary = sha1(1);\n" .
            "\$file = \"--\$boundary\\r\\n\" .\n" .
            "         \"Content-Disposition: form-data; name=\\\"filename\\\"; filename=\\\"test_big.dat\\\"\\r\\n\" .\n" .
            "         \"Content-Type: text/plain; charset=us-ascii\\r\\n\" .\n" .
            "         \"Content-Length: \" . (1024 * $size) . \"\\r\\n\" .\n" .
            "         \"Content-Type: application/octet-stream\\r\\n\\r\\n\" .\n" .
            "         \"\$text\\r\\n\" .\n" .
            "         \"--\$boundary--\";\n\n" .
            "\$body = \"POST " . dirname($_SERVER['PHP_SELF']) . "/test_big_upload.php?root={$_SERVER['DOCUMENT_ROOT']} HTTP/1.1\\r\\n\" .\n" .
            "         \"Host: " . Tools::getHost() . "\\r\\n\" .\n" .
            "         \"Content-Type: multipart/form-data; boundary=\$boundary\\r\\n\" .\n" .
            "         'Content-Length: ' . strlen(\$file) . \"\\r\\n\" .\n" .
            "         \"Connection: Close\\r\\n\\r\\n\" .\n" .
            "         \$file;\n\n" .
            "if (file_exists(\"{$_SERVER['DOCUMENT_ROOT']}/test_big_upload.php\")) { \n" .
            "    \$res = @fsockopen('" . Tools::getHost() . "', " . ($_SERVER['SERVER_PORT'] ? $_SERVER['SERVER_PORT'] : 80) . ", \$errno, \$errstr, 3);\n" .
            "     if (\$res) {\n" .
            "          \$time = explode(' ', microtime());\n" .
            "          \$time = (float) \$time[0] + (float) \$time[1];\n" .
            "          fputs(\$res, \$body);\n" .
            "          \$result = end(explode(\"\\n\", fread(\$res, 4096)));\n" .
            "          fclose(\$res);\n" .
            "          \$finishtime = explode(' ', microtime());\n" .
            "          \$finishtime = (float) \$finishtime[0] + (float) \$finishtime[1];\n" .
            "          \$time = round(\$finishtime - \$time, 2);\n" .
            "          echo \$result == '1' ? \"Yes \$time s\" : 'No';\n" .
            "     } else {\n" .
            "         echo 'No';\n" .
            "     }\n" .
            "} else {\n" .
            "    echo 'No';\n" .
            "}\n" .
            "@unlink('{$_SERVER['DOCUMENT_ROOT']}/test_big_upload.php');\n" .
            "@unlink('{$_SERVER['DOCUMENT_ROOT']}/test_big.dat');\n" .
            "@unlink(__FILE__);\n" .
            '?>'
        );
        @fclose($file);

        $cnt = $count + 1;
        $js .= "$('#value-$cnt').parent('tr').removeClass().addClass('active');\n" .
               "$('#value-$cnt').siblings('.loader').html('<img src=\"https://www.crazydogtshirts.com/skin/frontend/mtcolias/default/images/loader.gif\"/>');\n" .
               "$.get( \"" . dirname($_SERVER['PHP_SELF']) . "/test_big_uploader.php\", function(data) {\n" .
               "    $('#value-$cnt').siblings('.loader').children().remove();\n" .
               "    $('#value-$cnt').parent('tr').removeClass();\n" .
               "    if (data == 'No' || data == '') {\n" .
               "        $('#value-$cnt').html(data);\n".
               "        $('#value-$cnt').parent('tr').addClass('danger');\n\n" .
               "    } else {\n" .
               "        $('#value-$cnt').html(data);\n".
               "        $('#value-$cnt').parent('tr').addClass('success');\n\n" .
               "    }\n" .
               "})\n" .
               ".fail(function() {\n" .
               "    $('#value-$cnt').siblings('.loader').children().remove();\n" .
               "    $('#value-$cnt').html('No');\n" .
               "    $('#value-$cnt').parent('tr').addClass('danger');\n" .
               "})\n";

        return 'Wait';
    }
}
