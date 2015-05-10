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
 * JavaScript - He finds java scripts involved on the main page.
 *
 * @author    Dmitry Mamontov <d.slonyara@gmail.com>
 * @copyright 2015 Dmitry Mamontov <d.slonyara@gmail.com>
 * @license   http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @version   Release: 1.0.3
 * @link      https://github.com/dmamontov/benchmark-tools/
 * @since     Class available since Release 1.0.3
 * @todo      This script is VERY load on the system.
 */
class JavaScript
{
    /**
     * Finds Jquery.
     * @return array|boolean
     * @final
     */
    final public static function Jquery()
    {
        return self::search(
            "/<script.*src=\"(.*\/jquery.*)\">/",
            "/*! jQuery",
            "/jQuery\sv(.*)\s/"
        );
    }

    /**
     * Finds Jquery plugin Form.
     * @return array|boolean
     * @final
     */
    final public static function JqueryForm()
    {
        return self::search(
            "/<script.*src=\"(.*\/jquery\.form.*)\">/",
            "jQuery Form Plugin;",
            "/jQuery\sForm\sPlugin;\sv(.*)/"
        );
    }

    /**
     * Finds Jquery plugin Cycle2.
     * @return array|boolean
     * @final
     */
    final public static function JqueryCycle2()
    {
        return self::search(
            "/<script.*src=\"(.*\/jquery\.cycle2.*)\">/",
            "jQuery Cycle2;",
            "/jQuery\sCycle2;\sversion:\s(.*)\sbuild\:.*/"
        );
    }

    /**
     * Finds Jquery plugin Scrollspy.
     * @return array|boolean
     * @final
     */
    final public static function JqueryScrollspy()
    {
        return self::search(
            "/<script.*src=\"(.*\/jquery-scrollspy.*)\">/",
            "jQuery Scrollspy"
        );
    }

    /**
     * Finds Bootstrap.
     * @return array|boolean
     * @final
     */
    final public static function Bootstrap()
    {
        return self::search(
            "/<script.*src=\"(.*\/bootstrap.*)\">/",
            "/* Bootstrap",
            "/Bootstrap\sv(.*)\s/"
        );
    }

    /**
     * Finds Less.
     * @return array|boolean
     * @final
     */
    final public static function Less()
    {
        return self::search(
            "/<script.*src=\"(.*\/less.*)\">/",
            "// LESS - Leaner CSS",
            "/Leaner\sCSS\sv(.*)/"
        );
    }

    /**
     * Finds Angularjs.
     * @return array|boolean
     * @final
     */
    final public static function Angularjs()
    {
        return self::search(
            "/<script.*src=\"(.*\/angular.*)\">/",
            "AngularJS",
            "/AngularJS\sv(.*)/"
        );
    }

    /**
     * Finds Backbone.
     * @return array|boolean
     * @final
     */
    final public static function Backbone()
    {
        return self::search(
            "/<script.*src=\"(.*\/backbone.*)\">/",
            "Backbone",
            "/[Backbone|e]\.VERSION.*=.*['|\"](.*)['|\"]/"
        );
    }

    /**
     * Finds Knockout.
     * @return array|boolean
     * @final
     */
    final public static function Knockout()
    {
        return self::search(
            "/<script.*src=\"(.*\/knockout.*)\">/",
            "knockout",
            "/Knockout\sJavaScript\slibrary\sv(.*)/"
        );
    }

    /**
     * Finds React.
     * @return array|boolean
     * @final
     */
    final public static function React()
    {
        return self::search(
            "/<script.*src=\"(.*\/react.*)\">/",
            "React",
            "/React\sv(.*)/"
        );
    }

    /**
     * Finds Requirejs.
     * @return array|boolean
     * @final
     */
    final public static function Requirejs()
    {
        return self::search(
            "/<script.*src=\"(.*\/r\.js.*)\">/",
            "@license r.js",
            "/r.js\s(.*)/"
        );
    }

    /**
     * Finds Ember.
     * @return array|boolean
     * @final
     */
    final public static function Ember()
    {
        return self::search(
            "/<script.*src=\"(.*\/ember.*)\">/",
            "Ember - JavaScript Application Framework",
            "/@version\s+(.*)/"
        );
    }

    /**
     * Searches for java scripts on the site
     * @param string $regxHtml
     * @param string $regxFind
     * @param string $regxVersion
     * @return array|boolean
     * @final
     */
    final private static function search($regxHtml, $regxFind, $regxVersion = null)
    {
        global $docroot;

        if (Server::PHPInterface() == 'cli' || Server::PerlRegex() == false) {
            return false;
        }

        $root = $docroot && empty($docroot) === false ? $docroot : $_SERVER['DOCUMENT_ROOT'];
        $content = false;

        $port = $_SERVER['SERVER_PORT'] ? $_SERVER['SERVER_PORT'] : 80;
        $html = file_get_contents(($port == 443 ? 'https://' : 'http://') . Tools::getHost());
        if (preg_match($regxHtml, $html, $regx)) {
            if (stripos($regx[1], 'http') === false) {
                if (stripos($regx[1], '//') === false) {
                    $regx[1] = ($port == 443 ? 'https://' : 'http://') . Tools::getHost() . "/";
                } else {
                    $regx[1] = "http:{$regx[1]}";
                }
            }
            $content = file_get_contents($regx[1]);
        }

        if ($content == false && function_exists('exec')) {
            exec("find $root -name \"*.js\" -exec grep \"$regxFind\" {} \; | head -n 1", $out);
            $content = reset($out);
        }

        if (is_null($regxVersion) == false && preg_match($regxVersion, substr($content, 0, 2000), $regx)) {
            return array(
                'value' => true,
                'version' => empty($regx[1]) == false ? reset(explode(' ', $regx[1])) : ''
            );
        } elseif ($content !== false) {
            return true;
        }

        return false;
    }
}
