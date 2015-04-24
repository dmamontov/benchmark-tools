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
 * @since     File available since Release 1.0.2
 */
namespace DmitryMamontov\Tools;
use DmitryMamontov\Server\Server;

/**
 * Tools - Helper class.
 *
 * @author    Dmitry Mamontov <d.slonyara@gmail.com>
 * @copyright 2015 Dmitry Mamontov <d.slonyara@gmail.com>
 * @license   http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @version   Release: 1.0.2
 * @link      https://github.com/dmamontov/benchmark-tools/
 * @since     Class available since Release 1.0.2
 */
class Tools
{
    /**
     * Creates an http request.
     * @param string $body
     * @return boolean
     * @static
     * @final
     */
    final public static function CreateRequest($body = '')
    {
        if (Server::Sockets() == false || isset($_SERVER['PHP_SELF']) === false || empty($body)) {
            return false;
        }

        $res = @fsockopen(self::getHost(), $_SERVER['SERVER_PORT'] ? $_SERVER['SERVER_PORT'] : 80, $errno, $errstr, 3);
        if ($res) {
            fputs($res, $body);
            $result = end(explode("\n", fread($res, 4096)));
            fclose($res);
            return $result == '1' ? true : false;
        } else {
            return false;
        }
    }

    /**
     * Gets the host server.
     * @return string
     * @static
     * @final
     */
    final public static function getHost()
    {
        $port = $_SERVER['SERVER_PORT'] ? $_SERVER['SERVER_PORT'] : 80;

        return ($port == 443 ? 'ssl://' : '') . ($_SERVER['SERVER_NAME'] ? $_SERVER['SERVER_NAME'] : 'localhost');
    }

    /**
     * Get the exact current time.
     * @return float
     * @static
     * @final
     */
    final public static function getTime()
    {
        $time = explode(" ", microtime());
        return (float) $time[0] + (float) $time[1];
    }
}
