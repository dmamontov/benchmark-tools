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
 * @since     File available since Release 1.0.0
 */
namespace DmitryMamontov\Server;
use DmitryMamontov\Tools\Tools;
use DmitryMamontov\Server\Server;

/**
 * FileSystem - Class test file system
 *
 * @author    Dmitry Mamontov <d.slonyara@gmail.com>
 * @copyright 2015 Dmitry Mamontov <d.slonyara@gmail.com>
 * @license   http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @version   Release: 1.0.0
 * @link      https://github.com/dmamontov/benchmark-tools/
 * @since     Class available since Release 1.0.0
 */
class FileSystem
{
    /**
     * Checking disk space.
     * @return array
     * @static
     * @final
     */
    final public static function DiskSpace()
    {
        return array(
            'value' => intval(@disk_free_space($_SERVER['DOCUMENT_ROOT'])/1024/1024),
            'postfix' => 'Mb'
        );
    }

    /**
     * Checking access rights to the folder.
     * @param string $folder - The path to the folder.
     * @return array
     * @static
     * @final
     */
    final public static function PermissionsFolder($folder = null)
    {
        if (Server::Posix() == false) {
            return false;
        }

        if (is_null($folder) === true) {
            $folder = $_SERVER['DOCUMENT_ROOT'];
        }

        return array(
            'value' => substr(sprintf('%o', @fileperms($folder)), -4),
            'user'  => reset(posix_getpwuid(fileowner($folder))),
            'group' => reset(posix_getgrgid(filegroup($folder)))
        );
    }

    /**
     * Checking folder creation.
     * @return boolean
     * @static
     * @final
     */
    final public static function FolderCreation()
    {
        if (file_exists("{$_SERVER['DOCUMENT_ROOT']}/test_dir")) {
            @rmdir("{$_SERVER['DOCUMENT_ROOT']}/test_dir");
        }

        @mkdir("{$_SERVER['DOCUMENT_ROOT']}/test_dir");
        $result = file_exists("{$_SERVER['DOCUMENT_ROOT']}/test_dir") ? true : false;
        @rmdir("{$_SERVER['DOCUMENT_ROOT']}/test_dir");

        return $result;
    }

    /**
     * Checking delete the folder.
     * @return boolean
     * @static
     * @final
     */
    final public static function FolderDeletion()
    {
        if (self::FolderCreation() === false) {
            return false;
        }

        return file_exists("{$_SERVER['DOCUMENT_ROOT']}/test_dir") ? false : true;
    }

    /**
     * Checking access rights to the new folder.
     * @return array
     * @static
     * @final
     */
    final public static function PermissionsFolderCreation()
    {
        if (self::FolderDeletion() !== true) {
            return false;
        }

        if (file_exists("{$_SERVER['DOCUMENT_ROOT']}/test_dir")) {
            @rmdir("{$_SERVER['DOCUMENT_ROOT']}/test_dir");
        }

        @mkdir("{$_SERVER['DOCUMENT_ROOT']}/test_dir");
        $result = self::PermissionsFolder("{$_SERVER['DOCUMENT_ROOT']}/test_dir");
        @rmdir("{$_SERVER['DOCUMENT_ROOT']}/test_dir");

        return $result;
    }

    /**
     * Checking file creation.
     * @return boolean
     * @static
     * @final
     */
    final public static function FileCreation()
    {
        if (file_exists("{$_SERVER['DOCUMENT_ROOT']}/test_file.dat")) {
            @unlink("{$_SERVER['DOCUMENT_ROOT']}/test_file.dat");
        }

        $file = @fopen("{$_SERVER['DOCUMENT_ROOT']}/test_file.dat", 'wb');
        @fclose($file);
        $result = file_exists("{$_SERVER['DOCUMENT_ROOT']}/test_file.dat") ? true : false;
        @unlink("{$_SERVER['DOCUMENT_ROOT']}/test_file.dat");

        return $result;
    }

    /**
     * Checking delete the file.
     * @return boolean
     * @static
     * @final
     */
    final public static function FileDeletion()
    {
        if (self::FileCreation() !== true) {
            return false;
        }

        return file_exists(__DIR__ . '/test_file.dat') ? false : true;
    }

    /**
     * Checking access rights to the new file.
     * @return array
     * @static
     * @final
     */
    final public static function PermissionsFileCreation()
    {
        if (self::FileDeletion() !== true) {
            return false;
        }

        if (file_exists("{$_SERVER['DOCUMENT_ROOT']}/test_file.dat")) {
            @unlink("{$_SERVER['DOCUMENT_ROOT']}/test_file.dat");
        }

        $file = @fopen("{$_SERVER['DOCUMENT_ROOT']}/test_file.dat", 'wb');
        @fclose($file);
        $result = self::PermissionsFolder("{$_SERVER['DOCUMENT_ROOT']}/test_file.dat");
        @unlink("{$_SERVER['DOCUMENT_ROOT']}/test_file.dat");

        return $result;
    }

    /**
     * Checking the execution file.
     * @return boolean
     * @static
     * @final
     */
    final public static function FileExecution()
    {
        if (self::FileDeletion() !== true) {
            return false;
        }

        if (file_exists("{$_SERVER['DOCUMENT_ROOT']}/test_exec.php")) {
            @unlink("{$_SERVER['DOCUMENT_ROOT']}/test_exec.php");
        }

        $file = @fopen("{$_SERVER['DOCUMENT_ROOT']}/test_exec.php", "wb");
        @fputs($file, "<?php\necho true;\n?>");
        @fclose($file);

        if (Server::PHPInterface() == 'cli') {
            @exec("php {$_SERVER['DOCUMENT_ROOT']}/test_exec.php", $result);
            $result = @reset($result) == '1' ? true : false;
        } else {
            $body = 'GET ' . dirname($_SERVER['PHP_SELF']) . "/test_exec.php HTTP/1.1\r\n" .
                    'Host: ' . Tools::getHost() . "\r\n\r\n";
            $result = file_exists("{$_SERVER['DOCUMENT_ROOT']}/test_exec.php") ? Tools::CreateRequest($body) : false;
        }

        if (file_exists("{$_SERVER['DOCUMENT_ROOT']}/test_exec.php")) {
            @unlink("{$_SERVER['DOCUMENT_ROOT']}/test_exec.php");
        }

        return $result;
    }

    /**
     * Checking processing htaccess.
     * @return boolean
     * @static
     * @final
     */
    final public static function ProcessingHtaccess()
    {
        if (
            Server::PHPInterface() == 'cli' ||
            self::FolderDeletion() === false ||
            self::FileDeletion() === false ||
            isset($_SERVER['PHP_SELF']) === false
        ) {
            return false;
        }

        if (file_exists("{$_SERVER['DOCUMENT_ROOT']}/test_htaccess")) {
            @unlink("{$_SERVER['DOCUMENT_ROOT']}/test_htaccess/.htaccess");
            @unlink("{$_SERVER['DOCUMENT_ROOT']}/test_htaccess/404.php");
            @rmdir("{$_SERVER['DOCUMENT_ROOT']}/test_htaccess");
        }

        @mkdir("{$_SERVER['DOCUMENT_ROOT']}/test_htaccess");

        $file = @fopen("{$_SERVER['DOCUMENT_ROOT']}/test_htaccess/.htaccess", 'wb');
        @fputs(
            $file,
            'ErrorDocument 404 ' . dirname($_SERVER['PHP_SELF']) . "/test_htaccess/404.php\n" .
            "<IfModule mod_rewrite.c>\n    RewriteEngine Off\n</IfModule>"
        );
        @fclose($file);

        $file = @fopen("{$_SERVER['DOCUMENT_ROOT']}/test_htaccess/404.php", 'wb');
        @fputs($file, "<?php\nheader(\"HTTP/1.1 200 OK\");\necho true;\n?>");
        @fclose($file);

        $body = 'GET ' . dirname($_SERVER['PHP_SELF']) . "/test_htaccess/test.php HTTP/1.1\r\n" .
                'Host: ' . Tools::getHost() . "\r\n\r\n";

        $result = file_exists("{$_SERVER['DOCUMENT_ROOT']}/test_htaccess") ? Tools::CreateRequest($body) : false;

        if (file_exists("{$_SERVER['DOCUMENT_ROOT']}/test_htaccess")) {
            @unlink("{$_SERVER['DOCUMENT_ROOT']}/test_htaccess/.htaccess");
            @unlink("{$_SERVER['DOCUMENT_ROOT']}/test_htaccess/404.php");
            @rmdir("{$_SERVER['DOCUMENT_ROOT']}/test_htaccess");
        }

        return $result;
    }

    /**
     * Checking the time required to create the file.
     * @param integer $files
     * @return array
     * @static
     * @final
     */
    final public static function TimeToCreateFile($files = 1000)
    {
        if (self::FolderDeletion() === false || self::FileDeletion() === false) {
            return false;
        }

        if (file_exists("{$_SERVER['DOCUMENT_ROOT']}/test_time")) {
            for ($i=0; $i < $files; $i++) {
                @unlink("{$_SERVER['DOCUMENT_ROOT']}/test_time/test_file_$i");
            }
            @rmdir("{$_SERVER['DOCUMENT_ROOT']}/test_time");
        }

        @mkdir("{$_SERVER['DOCUMENT_ROOT']}/test_time");

        $time = Tools::getTime();

        for ($i=0; $i < $files; $i++) {
            $file = @fopen("{$_SERVER['DOCUMENT_ROOT']}/test_time/test_file_$i", 'wb');
            @fwrite($file, '<?php #Hello, test! ?>');
            @fclose($file);
            if (file_exists("{$_SERVER['DOCUMENT_ROOT']}/test_time/test_file_$i") == false) {
                continue;
            }
        }

        $result = round(Tools::getTime() - $time, 2);

        if (file_exists("{$_SERVER['DOCUMENT_ROOT']}/test_time")) {
            for ($i=0; $i < $files; $i++) {
                @unlink("{$_SERVER['DOCUMENT_ROOT']}/test_time/test_file_$i");
            }
            @rmdir("{$_SERVER['DOCUMENT_ROOT']}/test_time");
        }

        return array(
            'value' => $result,
            'postfix' => 's'
        );
    }

    /**
     * Checking upload files to the server.
     * @return boolean
     * @static
     * @final
     */
    final public static function FileUploads()
    {
        if (
            Server::PHPInterface() == 'cli' ||
            Server::PHPFileUploads() === false ||
            self::FileDeletion() === false
        ) {
            return false;
        }

        if (file_exists("{$_SERVER['DOCUMENT_ROOT']}/test.dat")) {
            @unlink("{$_SERVER['DOCUMENT_ROOT']}/test.dat");
        }
        if (file_exists("{$_SERVER['DOCUMENT_ROOT']}/test_upload.php")) {
            @unlink("{$_SERVER['DOCUMENT_ROOT']}/test_upload.php");
        }

        $file = @fopen("{$_SERVER['DOCUMENT_ROOT']}/test_upload.php", 'wb');
        @fputs(
            $file,
            "<?php\n" .
            "if (isset(\$_FILES['filename']) && is_uploaded_file(\$_FILES['filename']['tmp_name'])) {\n" .
            "    @move_uploaded_file(\$_FILES['filename']['tmp_name'], \$_REQUEST['root'] . '/test.dat');\n" .
            "    echo file_exists(\$_REQUEST['root'] . '/test.dat');\n" .
            "}\n" .
            '?>'
        );
        @fclose($file);

        $boundary = sha1(1);
        $file = "--$boundary\r\n" .
                "Content-Disposition: form-data; name=\"filename\"; filename=\"test.dat\"\r\n" .
                "Content-Type: text/plain; charset=us-ascii\r\n" .
                "Content-Length: 11\r\n" .
                "Content-Type: application/octet-stream\r\n\r\n" .
                "Test upload\r\n" .
                "--$boundary--";
        $body = 'POST ' . dirname($_SERVER['PHP_SELF']) . "/test_upload.php?root={$_SERVER['DOCUMENT_ROOT']} HTTP/1.1\r\n" .
                'Host: ' . Tools::getHost() . "\r\n" .
                "Content-Type: multipart/form-data; boundary=$boundary\r\n" .
                'Content-Length: ' . strlen($file) . "\r\n" .
                "Connection: Close\r\n\r\n" .
                $file;

        $result = file_exists("{$_SERVER['DOCUMENT_ROOT']}/test_upload.php") ? Tools::CreateRequest($body) : false;

        if (file_exists("{$_SERVER['DOCUMENT_ROOT']}/test.dat")) {
            @unlink("{$_SERVER['DOCUMENT_ROOT']}/test.dat");
        }
        if (file_exists("{$_SERVER['DOCUMENT_ROOT']}/test_upload.php")) {
            @unlink("{$_SERVER['DOCUMENT_ROOT']}/test_upload.php");
        }

        return $result;
    }
}
