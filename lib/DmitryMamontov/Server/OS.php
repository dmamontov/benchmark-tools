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

/**
 * OS - Obtaining data of the operating system
 *
 * @author    Dmitry Mamontov <d.slonyara@gmail.com>
 * @copyright 2015 Dmitry Mamontov <d.slonyara@gmail.com>
 * @license   http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @version   Release: 1.0.3
 * @link      https://github.com/dmamontov/benchmark-tools/
 * @since     Class available since Release 1.0.3
 */
class OS
{
    /**
     * Get the name of the operating system.
     * @return string|boolean
     * @final
     */
    final public static function Name()
    {
        if (function_exists('exec') == false) {
            return false;
        }

        exec('cat /etc/issue', $os);

        return reset(explode(' ', trim(stripcslashes(reset($os)), "\s\r\t\n\l ")));
    }

    /**
     * Version of the operating system.
     * @return string|boolean
     * @final
     */
    final public static function Version()
    {
        if (function_exists('exec') == false) {
            return false;
        }

        exec('cat /etc/issue', $os);
        $os = explode(' ', trim(stripcslashes(reset($os)), "\s\r\t\n\l "));
        unset($os[0]);

        return implode(' ', $os);
    }

    /**
     * Gets the operating system architecture.
     * @return string|boolean
     * @final
     */
    final public static function Architecture()
    {
        if (function_exists('exec') == false) {
            return false;
        }

        exec('uname -a', $arch);

        return stripos(reset($arch), 'x86_64') ? 'x64' : 'x32';
    }

    /**
     * Gets the time of the operating system.
     * @return string|boolean
     * @final
     */
    final public static function UpTime()
    {
        if (function_exists('exec') == false || Server::PerlRegex() == false) {
            return false;
        }

        $result = false;
        exec('uptime', $up);
        if (preg_match("/.*up\s(\d+)\sdays,\s(\d+)\:(\d+),.*/", reset($up), $regx)) {
            $result = "{$regx[1]}d {$regx[2]}h {$regx[3]}m";
        }

        return $result;
    }

    /**
     * Checking the time difference.
     * @return boolean
     * @final
     */
    final public static function TimeDiff()
    {
        if (function_exists('exec') == false) {
            return false;
        }

        $s = time();
        exec('date +%s', $time);

        return abs($s - reset($time)) == 0 ? true : false;
    }

    /**
     * Date of installation of the operating system.
     * @return string|boolean
     * @final
     */
    final public static function InstallDate()
    {
        if (function_exists('exec') == false) {
            return false;
        }

        exec('ls -clt / | tail -n 1 | awk \'{ print $7, $6, $8 }\'', $date);
        $date = date('Y-m-d', strtotime(reset($date)));

        return $date != '1970-01-01' ? $date : false;
    }

    /**
     * The kernel version of the operating system.
     * @return string|boolean
     * @final
     */
    final public static function KernelVersion()
    {
        if (function_exists('exec') == false) {
            return false;
        }

        exec('uname -r', $kernel);

        return reset($kernel);
    }

    /**
     * It gets the name of the CPU.
     * @return string|boolean
     * @final
     */
    final public static function CPUName()
    {
        if (function_exists('exec') == false || Server::PerlRegex() == false) {
            return false;
        }

        $result = false;
        exec('cat /proc/cpuinfo | grep -m 1 "model name"', $cpu);
        if (preg_match("/.*\:\s(.*)\sCPU\s(.*)\sv\d\s@.*/", reset($cpu), $regx)) {
            $result = str_replace('(R)', '', $regx[1]) . " {$regx[2]}";
        }

        return $result;
    }

    /**
     * Receives the clock frequency of the CPU.
     * @return array|boolean
     * @final
     */
    final public static function CPUClock()
    {
        if (function_exists('exec') == false || Server::PerlRegex() == false) {
            return false;
        }

        $result = false;
        exec('cat /proc/cpuinfo | grep -m 1 "model name"', $cpu);
        if (preg_match("/.*@\s(.*)/", reset($cpu), $regx)) {
            $result = array(
                'value'   => preg_replace('/[^0-9\.]/', '', $regx[1]),
                'postfix' => preg_replace('/[^a-zA-Z]/', '', $regx[1])
            );
        }

        return $result;
    }

    /**
     * The amount of RAM.
     * @return array|boolean
     * @final
     */
    final public static function RAM()
    {
        if (function_exists('exec') == false) {
            return false;
        }

        exec('free -m | grep Mem | awk \'{ print $2 }\'', $memory);

        return Tools::FormatSize(reset($memory) * 1048576);
    }
}