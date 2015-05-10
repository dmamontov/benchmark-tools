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

use DmitryMamontov\Tools\Tools;
use DmitryMamontov\Server\Server;
use DmitryMamontov\Server\FileSystem;

/**
 * Http - Class verifies that the http server
 *
 * @author    Dmitry Mamontov <d.slonyara@gmail.com>
 * @copyright 2015 Dmitry Mamontov <d.slonyara@gmail.com>
 * @license   http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @version   Release: 1.0.3
 * @link      https://github.com/dmamontov/benchmark-tools/
 * @since     Class available since Release 1.0.3
 */
class Http
{
    /**
     * Finds the current http server.
     * @return string
     * @static
     * @final
     */
    final public static function Server()
    {
        if (Server::PHPInterface() == 'cli' || Server::PerlRegex() === false) {
            return false;
        }

        $server = is_null($_SERVER["SERVER_SOFTWARE"]) || @strlen($_SERVER["SERVER_SOFTWARE"]) < 1
                    ? $_SERVER["SERVER_SIGNATURE"]
                    : $_SERVER["SERVER_SOFTWARE"];

        if (@preg_match("#^([a-zA-Z-]+).*?([\d]+\.[\d]+(\.[\d]+)?)#i", trim($server), $arServer)) {
            return "{$arServer[1]} {$arServer[2]}";
        } else {
            return false;
        }
    }

    /**
     * Gets real ip address of the server.
     * @return string
     * @static
     * @final
     */
    final public static function RealIP()
    {
        if (
            Server::PHPInterface() == 'cli' ||
            function_exists('gethostbyname') == false ||
            isset($_SERVER['HTTP_HOST']) == false
        ) {
            return false;
        }

        return gethostbyname($_SERVER['HTTP_HOST']);
    }

    /**
     * Gets the protocol HTTP.
     * @return string
     * @static
     * @final
     */
    final public static function Protocol()
    {
        if (Server::PHPInterface() == 'cli' || isset($_SERVER['SERVER_PROTOCOL']) == false) {
            return false;
        }

        $protocol = $_SERVER['SERVER_PROTOCOL'];

        if (stripos($protocol, '/') !== false) {
            $protocol = @str_replace('/', ' ', $protocol);
        }

        return $protocol;
    }

    /**
     * Checks authorization via http.
     * @return boolean
     * @static
     * @final
     */
    final public static function Authorization()
    {
        if (
            Server::PHPInterface() == 'cli' ||
            Server::Sockets() === false ||
            FileSystem::FileDeletion() === false
        ) {
            return false;
        }

        if (file_exists("{$_SERVER['DOCUMENT_ROOT']}/test_auth.php")) {
            @unlink("{$_SERVER['DOCUMENT_ROOT']}/test_auth.php");
        }

        $file = @fopen("{$_SERVER['DOCUMENT_ROOT']}/test_auth.php", 'wb');
        @fputs(
            $file,
            "<?php\n" .
            "\$remoteUser =  base64_decode(@substr(\$_SERVER['REMOTE_USER'] ? \$_SERVER['REMOTE_USER'] : \$_SERVER['REDIRECT_REMOTE_USER']));\n" .
            "if (\$remoteUser) {\n" .
            "    list(\$_SERVER['PHP_AUTH_USER'], \$_SERVER['PHP_AUTH_PW']) = explode(':', \$remoteUser);\n" .
            "}\n" .
            "echo \$_SERVER['PHP_AUTH_USER'] == 'test' && \$_SERVER['PHP_AUTH_PW'] == 'test' ? true : false;\n" .
            '?>'
        );
        @fclose($file);

        $body = 'GET ' . dirname($_SERVER['PHP_SELF']) . "/test_auth.php HTTP/1.1\r\n" .
                'Host: ' . Tools::getHost() . "\r\n" .
                'Authorization: Basic ' . base64_encode("test:test") . "\r\n\r\n";

        $result = file_exists("{$_SERVER['DOCUMENT_ROOT']}/test_auth.php") ? Tools::CreateRequest($body) : false;

        if (file_exists("{$_SERVER['DOCUMENT_ROOT']}/test_auth.php")) {
            @unlink("{$_SERVER['DOCUMENT_ROOT']}/test_auth.php");
        }

        return $result;
    }

    /**
     * Checks work sessions via http.
     * @return boolean
     * @static
     * @final
     */
    final public static function Sessions()
    {
        if (
            Server::PHPInterface() == 'cli' ||
            Server::Sockets() === false ||
            FileSystem::FileDeletion() === false
        ) {
            return false;
        }

        if (file_exists("{$_SERVER['DOCUMENT_ROOT']}/test_session.php")) {
            @unlink("{$_SERVER['DOCUMENT_ROOT']}/test_session.php");
        }

        @session_start();
        $_SESSION['test_session'] = true;
        @session_write_close();

        $file = @fopen("{$_SERVER['DOCUMENT_ROOT']}/test_session.php", 'wb');
        @fputs(
            $file,
            "<?php\n" .
            "@session_start();\n" .
            "if (\$_SESSION['test_session'] === true) {\n" .
            "    unset(\$_SESSION['test_session']);\n" .
            "    echo true;\n" .
            "} else {\n" .
            "    echo false;" .
            "}\n" .
            '?>'
        );
        @fclose($file);

        $body = 'GET ' . dirname($_SERVER['PHP_SELF']) . "/test_session.php HTTP/1.1\r\n" .
                'Host: ' . Tools::getHost() . "\r\n" .
                'Cookie: ' . session_name() . '=' . session_id() . "\r\n\r\n";

        $result = file_exists("{$_SERVER['DOCUMENT_ROOT']}/test_session.php") ? Tools::CreateRequest($body) : false;

        if (file_exists("{$_SERVER['DOCUMENT_ROOT']}/test_session.php")) {
            @unlink("{$_SERVER['DOCUMENT_ROOT']}/test_session.php");
        }

        return $result;
    }
    
    /**
     * Checks work local redirect via http.
     * @param integer $status
     * @return boolean
     * @static
     * @final
     */
    final public static function LocalRedirect($status = 301)
    {
        $statuses = array(300, 301, 302, 303, 304, 305, 307);

        if (
            Server::PHPInterface() == 'cli' ||
            FileSystem::FileDeletion() === false ||
            in_array($status, $statuses) == false
        ) {
            return false;
        }

        if (file_exists("{$_SERVER['DOCUMENT_ROOT']}/test_redirect.php")) {
            @unlink("{$_SERVER['DOCUMENT_ROOT']}/test_redirect.php");
        }

        $file = @fopen("{$_SERVER['DOCUMENT_ROOT']}/test_redirect.php", 'wb');
        @fputs(
            $file,
            "<?php\n" .
            "if (\$_GET['local'] != 'Y') {\n" .
            "    header('Location: ' . dirname(\$_SERVER['PHP_SELF']) . '/test_redirect.php?local=Y', true, $status);\n" .
            "}\n" .
            '?>'
        );
        @fclose($file);

        $port = $_SERVER['SERVER_PORT'] ? $_SERVER['SERVER_PORT'] : 80;
        $host = ($port == 443 ? 'https://' : 'http://') . ($_SERVER['SERVER_NAME'] ? $_SERVER['SERVER_NAME'] : 'localhost');

        $result = file_exists("{$_SERVER['DOCUMENT_ROOT']}/test_redirect.php") ?
                      get_headers($host . dirname($_SERVER['PHP_SELF']) . '/test_redirect.php') :
                      false;

        if (is_array($result)) {
            $result = stripos(reset($result), $status) ? true : false;
        }

        if (file_exists("{$_SERVER['DOCUMENT_ROOT']}/test_redirect.php")) {
            @unlink("{$_SERVER['DOCUMENT_ROOT']}/test_redirect.php");
        }

        return $result;
    }

    /**
     * Checks operation ssl via http.
     * @param string $domain
     * @return boolean
     * @static
     * @final
     */
    final public static function SSL($domain = '')
    {
        if (Server::Sockets() === false) {
            return false;
        }

        if (empty($domain)) {
            $domain = $_SERVER['SERVER_NAME'] ? $_SERVER['SERVER_NAME'] : 'localhost';
        }

        $res = @fsockopen("ssl://$domain", 443, $errno, $errstr, 10);
        if ($res) {
            @fclose($res);
            $result = true;
        } else {
            $result = false;
        }

        return $result;
    }

    /**
     * Checks ssl Version.
     * @return string|boolean
     * @static
     * @final
     */
    final public static function SSLLibVersion()
    {
        if (Server::Curl() === false) {
            return false;
        }

        $ver = curl_version();

        return isset($ver['ssl_version']) ? $ver['ssl_version'] : false;
    }
}
