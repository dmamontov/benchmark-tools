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

namespace DmitryMamontov;

use DmitryMamontov\Server\Server;
use DmitryMamontov\Server\FileSystem;

/**
 * BenchmarkTools - The main class
 *
 * @author    Dmitry Mamontov <d.slonyara@gmail.com>
 * @copyright 2015 Dmitry Mamontov <d.slonyara@gmail.com>
 * @license   http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @version   Release: 1.0.3
 * @link      https://github.com/dmamontov/benchmark-tools/
 * @since     Class available since Release 1.0.3
 */
class BenchmarkTools
{
    private $document;

    /**
     * Commissioning and initial setup.
     * @param string $name
     * @final
     */
    final public function __construct($name = '')
    {
        global $count, $js, $docroot;

        $count = 0;
        $js =  '';

        if (function_exists('ini_set') && extension_loaded('xdebug')) {
            ini_set('xdebug.show_exception_trace', false);
            ini_set('xdebug.scream', false);
        }

        if (function_exists('date_default_timezone_set') && function_exists('date_default_timezone_get')) {
            date_default_timezone_set(@date_default_timezone_get());
        }

        $backtrace = reset(debug_backtrace());
        $docroot = $_SERVER['DOCUMENT_ROOT'];
        $_SERVER['DOCUMENT_ROOT'] = dirname($backtrace['file']);

        if (empty($name) == false) {
            $name = " for \"$name\"";
        }

        if (file_exists("{$_SERVER['DOCUMENT_ROOT']}/test_clear.php")) {
            @unlink("{$_SERVER['DOCUMENT_ROOT']}/test_clear.php");
        }

        $file = @fopen("{$_SERVER['DOCUMENT_ROOT']}/test_clear.php", 'wb');
        @fputs(
            $file,
            "<?php\n" .
            "@unlink('{$_SERVER['DOCUMENT_ROOT']}/test_auth.php');\n" .
            "@unlink('{$_SERVER['DOCUMENT_ROOT']}/test_session.php');\n" .
            "@unlink('{$_SERVER['DOCUMENT_ROOT']}/test_file.dat');\n" .
            "@unlink('{$_SERVER['DOCUMENT_ROOT']}/test.dat');\n" .
            "@unlink('{$_SERVER['DOCUMENT_ROOT']}/test_exec.php');\n" .
            "@unlink('{$_SERVER['DOCUMENT_ROOT']}/test_upload.php');\n" .
            "@unlink('{$_SERVER['DOCUMENT_ROOT']}/test_actual_time.php');\n" .
            "@unlink('{$_SERVER['DOCUMENT_ROOT']}/test_actual_time_wait.php');\n" .
            "@unlink('{$_SERVER['DOCUMENT_ROOT']}/test_actual_memory.php');\n" .
            "@unlink('{$_SERVER['DOCUMENT_ROOT']}/test_redirect.php');\n" .
            "@unlink('{$_SERVER['DOCUMENT_ROOT']}/test_big_email.php');\n" .
            "@unlink('{$_SERVER['DOCUMENT_ROOT']}/test_big.dat');\n" .
            "@unlink('{$_SERVER['DOCUMENT_ROOT']}/test_big_upload.php');\n" .
            "@unlink('{$_SERVER['DOCUMENT_ROOT']}/test_big_uploader.php');\n" .
            "@exec('rm -rf {$_SERVER['DOCUMENT_ROOT']}/test_htaccess');\n" .
            "@exec('rm -rf {$_SERVER['DOCUMENT_ROOT']}/test_time');\n".
            "@exec('rm -rf {$_SERVER['DOCUMENT_ROOT']}/test_dir');\n".
            "@exec('rm -rf {$_SERVER['DOCUMENT_ROOT']}/test_operations');\n".
            "@unlink(__FILE__);\n" .
            '?>'
        );
        @fclose($file);

        $js = <<<HTML
$(".panel-heading").on('click', function(e) {
    if ($(this).siblings('.panel-collapse').hasClass('in')) {
        $(this).addClass('bottom');
        $(this).siblings('.panel-collapse').removeClass('in');
    } else {
        $(this).siblings('.panel-collapse').addClass('in');
        $(this).removeClass('bottom');
    }
});
HTML;

        $this->document = <<<HTML
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Benchmark Tools</title>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
        <style>
            header {
                font-size: 21px;
                text-align: center;
                position: relative;
                padding: 30px 60px;
                color: #cdbfe3;
                text-shadow: 0 1px 0 rgba(0,0,0,.1);
                background-color: #6f5499;
                margin-bottom: 20px;
            }
            header h1 {
                font-size: 36px;
                line-height: 1;
                margin-top: 0;
                color: #fff;
            }
            header p {
                margin-bottom: 0;
                font-weight: 300;
                line-height: 1.4;
            }
            header .copyright {
                color: rgba(255,255,255,.5);
                font-size: 15px;
                position: absolute;
                right: 13px;
                top: 10px;
            }
            .panel-group {
                margin-bottom: 0px;
            }
            .col-md-12 > .panel-group:last-child {
                margin-bottom: 40px;
            }
            .panel-group .panel-heading {
                text-align: center;
                cursor: pointer;
            }
            .panel-group .panel-heading, .panel-group .panel {
                 border-radius: 0px;
            }
            .table tr td {
                 font-weight: bold;
            }
            .bottom {
                 margin-bottom: 2px;
            }
        </style>
    </head>
    <body>
        <header>
            <div class="container">
                <a class="copyright" href="http://www.slobel.ru/">by Mamontov Dmitry</a>
                <h1>Benchmark Tools$name</h1>
                <p>Testing server within reason.</p>
            </div>
        </header>
        <div class="container">
            <div class="row">
                <div class="col-md-12">
HTML;
    }

    /**
     * Adding test.
     * @param string $title
     * @param string $value
     * @param string $comparable
     * @param string $compare
     * @param string $note
     * @final
     */
    final public function add($title = '', $value = '', $comparable = null, $compare = null, $note = '')
    {
        global $count;

        if ($value == '') {
            $value = false;
        }

        if (is_array($value) && isset($value['value']) === false) {
            $this->document .= <<<HTML
            <tr><td colspan="4">
HTML;
            $this->addHeader($title, true);
            foreach ($value as $key => $val) {
                $this->add($key, $val);
            }

            $this->document .= <<<HTML
            </table></div></div></div></td></tr>
HTML;
            return true;
        }

        $class = 'danger';
        if (
            is_null($comparable) ||
            is_null($compare) ||
            in_array($compare, array('>', '<', '=', '==', '<=', '>=')) === false
        ) {
            $class = 'active';
            if (isset($value['value']) && is_bool($value['value'])) {
                $class = $value['value'] === true ? 'success' : 'danger';
                $value['value'] = $value['value'] === true ? 'Yes' : 'No';
            }
            if (is_array($value)) {
                $value = implode(' ', $value);
            }
        } else {
            if ($compare == '=') {
                $compare = '==';
            }
            if (is_array($value) === false) {
                $tmp = $value;
                unset($value);
                $value['value'] = $tmp;
            }

            if (isset($value['value'])) {
                $class = @eval("return {$value['value']} $compare $comparable;") ? 'success' : 'danger';
                $tmp = $value['value'];
                unset($value['value']);
                if (count($value) > 0) {
                    $tmp .= ' ' . implode(' ', $value);
                }
                $value = $tmp;
            }
        }

        if (is_bool($value)) {
            $class = $value === true ? 'success' : 'danger';
            $value = $value === true ? 'Yes' : 'No';
        } elseif (is_array($value)) {
            $value = implode(' ', $value);
        }
        $count++;

        $this->document .= <<<HTML
        <tr class="$class">
            <td class="text-right" style="width: 25%">$title:</td>
            <td id="value-{$count}" class="text-left" style="width: 25%">$value</td>
            <td class="text-left" style="width: 49%">$note</td>
            <td class="text-left loader" style="width: 44px"></td>
        </tr>
HTML;
        return true;
    }

    /**
     * Adding header.
     * @param string $header
     * @param boolean $sub
     * @final
     */
    final public function addHeader($header = '', $sub = false)
    {
        global $count, $heading;

        if (empty($header)) {
            return false;
        }

        $heading++;

        if ($heading > 1 && $sub === false) {
            $this->document .= <<<HTML
            </table></div></div></div>
HTML;
        }

        $class = $sub === false ? 'in' : '';
        $classh = $sub === false ? 'panel-primary' : 'panel-default';

        $this->document .= <<<HTML
<div class="panel-group" role="tablist">
    <div class="panel $classh">
        <div class="panel-heading" id="heading$heading">
            <h4 class="panel-title">
            $header
            </h4>
        </div>
        <div id="group$heading" class="panel-collapse collapse $class">
            <table class="table">
HTML;
        return true;
    }

    /**
     * Drawing.
     * @final
     */
    public function draw()
    {
        global $js;

        $phpself = dirname($_SERVER['PHP_SELF']);

        $footer = <<<HTML
        </table></div></div></div>
                </div>
            </div>
        </div>
        <script>
        window.onbeforeunload = function(e) {
            $.get("$phpself/test_clear.php");
            return 'Who will delete all the files for testing.';
        };
        $( document ).ready(function() {
            $js
        });
        </script>
    </body>
</html>
HTML;
        echo $this->document . $footer;

        return true;
    }
}
