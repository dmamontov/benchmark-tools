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

/**
 * Server - One of the major classes of receiving data from the server.
 *
 * @author    Dmitry Mamontov <d.slonyara@gmail.com>
 * @copyright 2015 Dmitry Mamontov <d.slonyara@gmail.com>
 * @license   http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @version   Release: 1.0.0
 * @link      https://github.com/dmamontov/benchmark-tools/
 * @since     Class available since Release 1.0.0
 */
class Server
{
    /**
     * Finds interface php.
     * @return string
     * @static
     * @final
     */
    final public static function PHPInterface()
    {
        return php_sapi_name();
    }

    /**
     * Finds version of php.
     * @return string
     * @static
     * @final
     */
    final public static function PHPVersion()
    {
        return phpversion();
    }

    /**
     * Finds accelerator php.
     * @return string
     * @static
     * @final
     */
    final public static function PHPAccelerator()
    {
        if (function_exists('eaccelerator_info')) {
            return 'EAccelerator';
        } elseif (function_exists('accelerator_reset')) {
            return 'Zend Accelerator';
        } elseif (function_exists('apc_fetch')) {
            return 'APC';
        } elseif (function_exists('xcache_get')) {
            return 'XCache';
        } elseif (function_exists('opcache_reset') && ini_get('opcache.enable')) {
            return 'OPcache';
        } else {
            return false;
        }
    }

    /**
     * Checking Safe Mode.
     * @return boolean
     * @static
     * @final
     */
    final public static function SafeMode()
    {
        return (bool) ini_get("safe_mode");
    }

    /**
     * Checking Short Open Tag.
     * @return boolean
     * @static
     * @final
     */
    final public static function ShortOpenTag()
    {
        return (bool) ini_get("short_open_tag");
    }

    /**
     * Checking Shared Memory.
     * @return boolean
     * @static
     * @final
     */
    final public static function SharedMemory()
    {
        return (bool) function_exists('shm_attach');
    }

    /**
     * Checking posix.
     * @return boolean
     * @static
     * @final
     */
    final public static function Posix()
    {
        return (bool) function_exists('posix_getpwuid') && function_exists('posix_getgrgid');
    }

    /**
     * Checking pcntl.
     * @return boolean
     * @static
     * @final
     */
    final public static function Pcntl()
    {
        return (bool) function_exists('pcntl_fork');
    }

    /**
     * Checking Messages.
     * @return boolean
     * @static
     * @final
     */
    final public static function EmailSanding()
    {
        return (bool) mail('test@test.com', 'Server Test', 'Delete it.');
    }

    /**
     * Checking mcrypt.
     * @return boolean
     * @static
     * @final
     */
    final public static function Mcrypt()
    {
        return (bool) function_exists('mcrypt_encrypt');
    }

    /**
     * Checking sockets.
     * @return boolean
     * @static
     * @final
     */
    final public static function Sockets()
    {
        return (bool) function_exists('fsockopen');
    }

    /**
     * Checking php regex.
     * @return boolean
     * @static
     * @final
     */
    final public static function PHPRegex()
    {
        return (bool) function_exists('eregi');
    }

    /**
     * Checking perl regex.
     * @return boolean
     * @static
     * @final
     */
    final public static function PerlRegex()
    {
        return (bool) function_exists('preg_match');
    }

    /**
     * Checking zlib.
     * @return boolean
     * @static
     * @final
     */
    final public static function Zlib()
    {
        return (bool) (extension_loaded('zlib') && function_exists('gzcompress'));
    }

    /**
     * Checking gdlib.
     * @return boolean
     * @static
     * @final
     */
    final public static function GDlib()
    {
        return (bool) function_exists('imagecreate');
    }

    /**
     * Checking free type.
     * @return boolean
     * @static
     * @final
     */
    final public static function FreeType()
    {
        return (bool) function_exists('imagettftext');
    }

    /**
     * Checking mbstring.
     * @return boolean
     * @static
     * @final
     */
    final public static function Mbstring()
    {
        return (bool) function_exists('mb_substr');
    }

    /**
     * Checking PDO.
     * @return boolean
     * @static
     * @final
     */
    final public static function PDO()
    {
        return (bool) class_exists('PDO');
    }

    /**
     * Checking SimpleXML.
     * @return boolean
     * @static
     * @final
     */
    final public static function SimpleXML()
    {
        return (bool) class_exists('SimpleXMLElement');
    }

    /**
     * Checking DOMDocument.
     * @return boolean
     * @static
     * @final
     */
    final public static function DOMDocument()
    {
        return (bool) class_exists('DOMDocument');
    }

    /**
     * Checking Curl.
     * @return boolean
     * @static
     * @final
     */
    final public static function Curl()
    {
        return (bool) function_exists('curl_init');
    }

    /**
     * Checking Memory Limit.
     * @return array
     * @static
     * @final
     */
    final public static function MemoryLimit()
    {
        $value = (string) ini_get('memory_limit') ? ini_get('memory_limit') : get_cfg_var("memory_limit");
        $postfix = '';

        if (stripos($value, 'M')) {
            $postfix = 'Mb';
        } elseif (stripos($value, 'K')) {
            $postfix = 'Kb';
        }

        return array(
            'value' => (int) $value,
            'postfix' => $postfix
        );
    }
    
    /**
     * Checking Max Execution Time.
     * @return array
     * @static
     * @final
     */
    final public static function MaxExecutionTime()
    {
        return array(
            'value' => (int) ini_get('max_execution_time'),
            'postfix' => 's'
        );
    }

    /**
     * Finds and returns the umask.
     * @return string
     * @static
     * @final
     */
    final public static function Umask()
    {
        return sprintf("%03o",umask());
    }

    /**
     * Finds and returns the post max size.
     * @return array
     * @static
     * @final
     */
    final public static function PostMaxSize()
    {
        $value = (string) ini_get("post_max_size");
        $postfix = '';

        if (stripos($value, 'M')) {
            $postfix = 'Mb';
        } elseif (stripos($value, 'K')) {
            $postfix = 'Kb';
        }

        return array(
            'value' => (int) $value,
            'postfix' => $postfix
        );
    }

    /**
     * Checking Register Globals.
     * @return boolean
     * @static
     * @final
     */
    final public static function RegisterGlobals()
    {
        return (bool) intval(ini_get('register_globals'));
    }

    /**
     * Checking Register Display Errors.
     * @return boolean
     * @static
     * @final
     */
    final public static function DisplayErrors()
    {
        return (bool) intval(ini_get('display_errors'));
    }

    /**
     * Checking PHP File Uploads.
     * @return boolean
     * @static
     * @final
     */
    final public static function PHPFileUploads()
    {
        return (bool) intval(ini_get('file_uploads'));
    }

    /**
     * Returns the current server time.
     * @return string
     * @static
     * @final
     */
    final public static function ServerTime()
    {
        return date("d-m-Y H:i");
    }
}