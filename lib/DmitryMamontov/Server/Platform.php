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
use DmitryMamontov\Server\Server;

/**
 * Platform - Class platform for the identification of the site.
 *
 * @author    Dmitry Mamontov <d.slonyara@gmail.com>
 * @copyright 2015 Dmitry Mamontov <d.slonyara@gmail.com>
 * @license   http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @version   Release: 1.0.0
 * @link      https://github.com/dmamontov/benchmark-tools/
 * @since     Class available since Release 1.0.0
 * @todo      Requires improvements.
 */
class Platform
{
    private $version, $db, $name;
    private $platforms = array(
        'Wordpress', 'Drupal', 'Joomla', 'HostCMS', 'AmiroCMS', 'Bitrix', 'InstantCMS',
        'PHPFusion', 'WebAsyst', 'OSCommerce', 'NetCat', 'DanneoCMS', 'EleanorCMS',
        'PHPNuke', 'MODX', 'TypoЗ', 'Magento'
    );

    /**
     * Search platform.
     * @final
     */
    final public function __construct(){
        array_push($this->platforms, 'Others');
        foreach ($this->platforms as $platform) {
            if (method_exists($this, $platform)) {
                @eval("\$this->$platform();");
            }

            if (is_null($this->name) === false) {
                break;
            }
        }
    }

    /**
     * Returns the name.
     * @return string
     * @final
     */
    final public function Name()
    {
        return is_null($this->name) ? false : $this->name;
    }

    /**
     * Returns the version.
     * @return string
     * @final
     */
    final public function Version()
    {
        return is_null($this->version) ? false : $this->version;
    }

    /**
     * Returns settings to connect to the DB.
     * @return array
     * @final
     */
    final public function DB()
    {
        return is_null($this->db) ? false : $this->db;
    }

    /**
     * Search data others platform.
     * @return boolean
     * @final
     */
    final private function Others()
    {
        global $docroot;

        $root = $docroot && empty($docroot) === false ? $docroot : $_SERVER['DOCUMENT_ROOT'];
        if (count(glob("$root/*.php")) > 0) {
            $this->name = 'Others PHP';
        } elseif (count(glob("$root/*.rb")) > 0) {
            $this->name = 'Others Ruby';
        } elseif (count(glob("$root/*.py")) > 0) {
            $this->name = 'Others Python';
        } elseif (count(glob("$root/*.cs")) > 0 || count(glob("$root/*.dll")) > 0) {
            $this->name = 'Others .NET';
        } elseif (count(glob("$root/*.java")) > 0 || count(glob("$root/*.jar")) > 0) {
            $this->name = 'Others Java';
        } elseif (count(glob("$root/*.js")) > 0) {
            $this->name = 'Others JS';
        }

        return true;
    }

    /**
     * Search data platform Wordpress.
     * @return boolean
     * @final
     */
    final private function Wordpress()
    {
        global $docroot;

        $root = $docroot && empty($docroot) === false ? $docroot : $_SERVER['DOCUMENT_ROOT'];
        if (file_exists("$root/wp-includes") && is_dir("$root/wp-includes")) {
            $this->name = 'Wordpress';

            if (file_exists("$root/wp-includes/version.php")) {
                @include("$root/wp-includes/version.php");
                if (isset($wp_version)) {
                    $this->version = $wp_version;
                }
            }

            if (file_exists("$root/wp-config.php") && Server::PerlRegex()) {
                $this->db['driver'] = 'mysql';
                $file = file_get_contents("$root/wp-config.php");
                if (preg_match("/.*define\(\'DB\_HOST\'\,\s\'(.*)\'\)\;.*/", $file, $regx)) {
                    $this->db['host'] = $regx[1];
                }
                if (preg_match("/.*define\(\'DB\_USER\'\,\s\'(.*)\'\)\;.*/", $file, $regx)) {
                    $this->db['user'] = $regx[1];
                }
                if (preg_match("/.*define\(\'DB\_PASSWORD\'\,\s\'(.*)\'\)\;.*/", $file, $regx)) {
                    $this->db['password'] = $regx[1];
                }
                if (preg_match("/.*define\(\'DB\_NAME\'\,\s\'(.*)\'\)\;.*/", $file, $regx)) {
                    $this->db['dbname'] = $regx[1];
                }
            }

            return true;
        }

        return false;
    }

    /**
     * Search data platform Drupal.
     * @return boolean
     * @final
     */
    final private function Drupal()
    {
        global $docroot;

        $root = $docroot && empty($docroot) === false ? $docroot : $_SERVER['DOCUMENT_ROOT'];
        if (file_exists("$root/sites/default/settings.php")) {
            $this->name = 'Drupal';

            if (file_exists("$root/modules/system/system.info")) {
                $tmps = file("$root/modules/system/system.info", FILE_SKIP_EMPTY_LINES);
                foreach ($tmps as $tmp) {
                    if (stripos($tmp, 'version') !== false) {
                        $version = eval("return \${$tmp};");
                        if (is_numeric($version)) {
                            $this->version = $version;
                            break;
                        }
                    }
                }
            }

            @include("$root/sites/default/settings.php");
            if (is_null($databases) === false && is_array($databases)) {
                $databases = reset(reset($databases));
                if (isset($databases['driver'])) {
                    $this->db['driver'] = $databases['driver'];
                }
                if (isset($databases['host'])) {
                    $this->db['host'] = $databases['host'];
                }
                if (isset($databases['username'])) {
                    $this->db['user'] = $databases['username'];
                }
                if (isset($databases['password'])) {
                    $this->db['password'] = $databases['password'];
                }
                if (isset($databases['database'])) {
                    $this->db['dbname'] = $databases['database'];
                }
            }

            return true;
        }

        return false;
    }

    /**
     * Search data platform Joomla.
     * @return boolean
     * @final
     */
    final private function Joomla()
    {
        global $docroot;

        $root = $docroot && empty($docroot) === false ? $docroot : $_SERVER['DOCUMENT_ROOT'];
        if (file_exists("$root/configuration.php") && file_exists("$root/administrator")) {
            $this->name = 'Joomla';

            if (file_exists("$root/language/en-GB/en-GB.xml") && Server::SimpleXML()) {
                $version = simplexml_load_file("$root/language/en-GB/en-GB.xml");
                $this->version = (string) $version->version;
            }

            @include("$root/configuration.php");
            if (class_exists('JConfig')) {
                $config = new JConfig;
                if (isset($config->dbtype)) {
                    $this->db['driver'] = $config->dbtype;
                }
                if (isset($config->host)) {
                    $this->db['host'] = $config->host;
                }
                if (isset($config->user)) {
                    $this->db['user'] = $config->user;
                }
                if (isset($config->password)) {
                    $this->db['password'] = $config->password;
                }
                if (isset($config->db)) {
                    $this->db['dbname'] = $config->db;
                }
            }

            return true;
        }

        return false;
    }

    /**
     * Search data platform HostCMS.
     * @return boolean
     * @final
     */
    final private function HostCMS()
    {
        global $docroot;

        $root = $docroot && empty($docroot) === false ? $docroot : $_SERVER['DOCUMENT_ROOT'];
        if (file_exists("$root/hostcmsfiles") && is_dir("$root/hostcmsfiles")) {
            $this->name = 'HostCMS';

            if (file_exists("$root/main_classes.php") && Server::PerlRegex()) {
                $file = file_get_contents("$root/main_classes.php");
                if (preg_match("/.*\@version\s(.*)\n/", $file, $regx)) {
                    $this->version = trim($regx[1]);
                }
            }

            if (file_exists("$root/hostcmsfiles/config_db.php")) {
                @include("$root/hostcmsfiles/config_db.php");
                $this->db['driver'] = 'mysql';
                if (defined('DB_HOST')) {
                    $this->db['host'] = DB_HOST;
                }
                if (defined('DB_USER_NAME')) {
                    $this->db['user'] = DB_USER_NAME;
                }
                if (defined('DB_PASSWORD')) {
                    $this->db['password'] = DB_PASSWORD;
                }
                if (defined('DB_NAME')) {
                    $this->db['dbname'] = DB_NAME;
                }
            }

            return true;
        }

        return false;
    }

    /**
     * Search data platform AmiroCMS.
     * @return boolean
     * @final
     */
    final private function AmiroCMS()
    {
        global $docroot;

        $root = $docroot && empty($docroot) === false ? $docroot : $_SERVER['DOCUMENT_ROOT'];
        if (file_exists("$root/_local/_admin") && is_dir("$root/_local/_admin")) {
            $this->name = 'Amiro.CMS';

            if (($file = reset(glob("$root/_local/plugins_distr/ami_ajax_responder/config.*.php"))) != array() && Server::PerlRegex()) {
                if (preg_match("/^.*config\.backup(.*)$/", basename($file, '.php'), $regx)) {
                    $this->version = trim($regx[1]);
                }
            }

            if (file_exists("$root/_local/config.ini.php")) {
                $config = parse_ini_file("$root/_local/config.ini.php");
                $this->db['driver'] = 'mysql';
                if (isset($config['DB_Host'])) {
                    $this->db['host'] = $config['DB_Host'];
                }
                if (isset($config['DB_User'])) {
                    $this->db['user'] = $config['DB_User'];
                }
                if (isset($config['DB_Password'])) {
                    $this->db['password'] = $config['DB_Password'];
                }
                if (isset($config['DB_Database'])) {
                    $this->db['dbname'] = $config['DB_Database'];
                }
            }

            return true;
        }

        return false;
    }

    /**
     * Search data platform Bitrix.
     * @return boolean
     * @final
     */
    final private function Bitrix()
    {
        global $docroot;

        $path = ($docroot && empty($docroot) === false ? $docroot : $_SERVER['DOCUMENT_ROOT']) . '/bitrix';
        if (file_exists($path) && is_dir($path)) {
            $this->name = 'Bitrix';
            if (file_exists("$path/modules/main/classes/general/version.php")) {
                @include("$path/modules/main/classes/general/version.php");
                if (defined('SM_VERSION')) {
                    $this->version = SM_VERSION;
                }
            }
    
            if (file_exists("$path/php_interface/dbconn.php")) {
                @include("$path/php_interface/dbconn.php");
                if (isset($DBType)) {
                    $this->db['driver'] = $DBType;
                }
                if (isset($DBHost)) {
                    $this->db['host'] = $DBHost;
                }
                if (isset($DBLogin)) {
                    $this->db['user'] = $DBLogin;
                }
                if (isset($DBPassword)) {
                    $this->db['password'] = $DBPassword;
                }
                if (isset($DBName)) {
                    $this->db['dbname'] = $DBName;
                }
            }

            return true;
        }

        return false;
    }

    /**
     * Search data platform InstantCMS.
     * @return boolean
     * @final
     */
    final private function InstantCMS()
    {
        global $docroot;

        $root = $docroot && empty($docroot) === false ? $docroot : $_SERVER['DOCUMENT_ROOT'];
        if (file_exists("$root/admin/login.php")) {
            $file = file_get_contents("$root/admin/login.php");
            if (stripos($file, 'InstantCMS') !== false) {
                $this->name = 'InstantCMS';

                if (Server::PerlRegex() && preg_match("/.*InstantCMS\sv(.*)\s.*/", $file, $regx)) {
                    $this->version = reset(explode(' ', $regx[1]));
                }

                if (file_exists("$root/includes/config.inc.php")) {
                    define('VALID_CMS', true);
                    @include("$root/includes/config.inc.php");
                    $this->db['driver'] = 'mysql';
                    if (isset($_CFG['db_host'])) {
                        $this->db['host'] = $_CFG['db_host'];
                    }
                    if (isset($_CFG['db_user'])) {
                        $this->db['user'] = $_CFG['db_user'];
                    }
                    if (isset($_CFG['db_pass'])) {
                        $this->db['password'] = $_CFG['db_pass'];
                    }
                    if (isset($_CFG['db_base'])) {
                        $this->db['dbname'] = $_CFG['db_base'];
                    }
                }

                return true;
            }
        }

        return false;
    }

    /**
     * Search data platform PHPFusion.
     * @return boolean
     * @final
     */
    final private function PHPFusion()
    {
        global $docroot;

        $root = $docroot && empty($docroot) === false ? $docroot : $_SERVER['DOCUMENT_ROOT'];
        if (file_exists("$root/administration/administrators.php")) {
            $file = file_get_contents("$root/administration/administrators.php");
            if (stripos($file, 'PHP-Fusion') !== false) {
                $this->name = 'PHP-Fusion';

                if (file_exists("$root/administration/upgrade.php") && Server::PerlRegex()) {
                    $version = file_get_contents("$root/administration/upgrade.php");
                    if (preg_match("/.*settings\_value\=\'(.*)\'\s.*/", $version, $regx)) {
                        $this->version = $regx[1];
                    }
                }
    
                if (file_exists("$root/config.php")) {
                    @include("$root/config.php");
                    $this->db['driver'] = 'mysql';
                    if (isset($db_host)) {
                        $this->db['host'] = $db_host;
                    }
                    if (isset($db_user)) {
                        $this->db['user'] = $db_user;
                    }
                    if (isset($db_pass)) {
                        $this->db['password'] = $db_pass;
                    }
                    if (isset($db_name)) {
                        $this->db['dbname'] = $db_name;
                    }
                }
            }

            return true;
        }

        return false;
    }

    /**
     * Search data platform WebAsyst.
     * @return boolean
     * @final
     */
    final private function WebAsyst()
    {
        global $docroot;

        $root = $docroot && empty($docroot) === false ? $docroot : $_SERVER['DOCUMENT_ROOT'];
        if (file_exists("$root/wa-apps") && is_dir("$root/wa-apps")) {
            $this->name = 'WebAsyst (new)';
            if (file_exists("$root/wa-apps/installer/lib/config/app.php") && file_exists("$path/installer/lib/config/build.php")) {
                $app = @include("$root/wa-apps/installer/lib/config/app.php");
                $build = @include("$root/wa-apps/installer/lib/config/build.php");
                $this->version = "{$app['version']}.{$build}";
            }
    
            if (file_exists("$root/wa-config/db.php")) {
                $db = reset(include("$root/wa-config/db.php"));
                if (isset($db['type'])) {
                    $this->db['driver'] = $db['type'];
                }
                if (isset($db['host'])) {
                    $this->db['host'] = $db['host'];
                }
                if (isset($db['user'])) {
                    $this->db['user'] = $db['user'];
                }
                if (isset($db['password'])) {
                    $this->db['password'] = $db['password'];
                }
                if (isset($db['database'])) {
                    $this->db['dbname'] = $db['database'];
                }
            }

            return true;
        } elseif (file_exists("$root/kernel/wbs.xml")) {
            $this->name = 'WebAsyst (old)';
            if (Server::SimpleXML()) {
                $settings = simplexml_load_file("$root/kernel/wbs.xml");
                if (isset($settings['VERSION'])) {
                    $this->version = (string) $settings['VERSION'];
                }

                if (($file = reset(glob("$root/dblist/*.xml"))) != array()) {
                    $db = simplexml_load_file($file);
                    $this->db['driver'] = 'mysql';
                    if (isset($db->DBSETTINGS['SQLSERVER'])) {
                        $this->db['host'] = (string) $db->DBSETTINGS['SQLSERVER'];
                    }
                    if (isset($db->DBSETTINGS['DB_USER'])) {
                        $this->db['user'] = (string) $db->DBSETTINGS['DB_USER'];
                    }
                    if (isset($db->DBSETTINGS['DB_PASSWORD'])) {
                        $this->db['password'] = (string) $db->DBSETTINGS['DB_PASSWORD'];
                    }
                    if (isset($db->DBSETTINGS['DB_NAME'])) {
                        $this->db['dbname'] = (string) $db->DBSETTINGS['DB_NAME'];
                    }
                }
            }

            return true;
        }

        return false;
    }

    /**
     * Search data platform OSCommerce.
     * @return boolean
     * @final
     */
    final private function OSCommerce()
    {
        global $docroot;

        $root = $docroot && empty($docroot) === false ? $docroot : $_SERVER['DOCUMENT_ROOT'];
        if (file_exists("$root/admin/includes") && is_dir("$root/admin/includes") && file_exists("$root/admin/includes/configure.php")) {
            $this->name = 'OSCommerce';
            if (file_exists("$root/admin/includes/application_top.php") && Server::PerlRegex()) {
                if (
                    preg_match(
                        "/.*define\(\'PROJECT\_VERSION\'\,\s\'.*v(.*)\'\)\;.*/",
                        file_get_contents("$root/admin/includes/application_top.php"),
                        $regx
                    )
                ) {
                    $this->version = $regx[1];
                }
            }
    
            $this->db['driver'] = 'mysql';
            @include("$root/admin/includes/configure.php");
            if (defined('DB_SERVER')) {
                $this->db['host'] = DB_SERVER;
            }
            if (defined('DB_SERVER_USERNAME')) {
                $this->db['user'] = DB_SERVER_USERNAME;
            }
            if (defined('DB_SERVER_PASSWORD')) {
                $this->db['password'] = DB_SERVER_PASSWORD;
            }
            if (defined('DB_DATABASE')) {
                $this->db['dbname'] = DB_DATABASE;
            }

            return true;
        } else {

            return false;
        }
    }

    /**
     * Search data platform NetCat.
     * @return boolean
     * @final
     */
    final private function NetCat()
    {
        global $docroot;

        $root = $docroot && empty($docroot) === false ? $docroot : $_SERVER['DOCUMENT_ROOT'];
        if (file_exists("$root/netcat") && is_dir("$root/netcat")) {
            $this->name = 'NetCat';

            if (file_exists("$root/vars.inc.php")) {
                $this->db['driver'] = 'mysql';
                $settings = file("$root/vars.inc.php", FILE_SKIP_EMPTY_LINES);
                foreach ($settings as $setting) {
                    if (stripos($setting, '$MYSQL') !== false) {
                        if (stripos($setting, 'HOST') !== false) {
                            $this->db['host'] = eval('return ' . $setting);
                            continue;
                        } elseif (stripos($setting, 'USER') !== false) {
                            $this->db['user'] = eval('return ' . $setting);
                            continue;
                        } elseif (stripos($setting, 'PASSWORD') !== false) {
                            $this->db['password'] = eval('return ' . $setting);
                            continue;
                        } elseif (stripos($setting, 'DB_NAME') !== false) {
                            $this->db['dbname'] = eval('return ' . $setting);
                            continue;
                        }
                    }
                }
            }

            if (
                isset($this->db['host']) &&
                isset($this->db['user']) &&
                isset($this->db['password']) &&
                isset($this->db['dbname']) &&
                Server::PDO()
            ) {
                try {
                    $db = new \PDO("{$this->db['driver']}:host={$this->db['host']};dbname={$this->db['dbname']}", $this->db['user'], $this->db['password']);
                    $v = $db->prepare("SELECT `Value` as `version` FROM `Settings` WHERE `Key` = 'VersionNumber'");
                    $v->execute();
                    if ($tmp = $v->fetch(\PDO::FETCH_ASSOC)) {
                        $this->version = $tmp['version'];
                    }
                } catch (PDOException $e) {}
            }

            return true;
        }

        return false;
    }

    /**
     * Search data platform DanneoCMS.
     * @return boolean
     * @final
     */
    final private function DanneoCMS()
    {
        global $docroot;

        $root = $docroot && empty($docroot) === false ? $docroot : $_SERVER['DOCUMENT_ROOT'];
        if (file_exists("$root/apanel") && is_dir("$root/apanel")) {
            $this->name = 'Danneo CMS';

            if (file_exists("$root/base/danneo.setting.php")) {
                define('DNREAD', true);
                @include("$root/base/danneo.setting.php");
                $this->db['driver'] = 'mysql';
                if (isset($hostname)) {
                    $this->db['host'] = $hostname;
                }
                if (isset($nameuser)) {
                    $this->db['user'] = $nameuser;
                }
                if (isset($password)) {
                    $this->db['password'] = $password;
                }
                if (isset($namebase)) {
                    $this->db['dbname'] = $namebase;
                }
            }

            if (
                isset($this->db['host']) &&
                isset($this->db['user']) &&
                isset($this->db['password']) &&
                isset($this->db['dbname']) &&
                isset($basepref) &&
                Server::PDO()
            ) {
                try {
                    $db = new \PDO("{$this->db['driver']}:host={$this->db['host']};dbname={$this->db['dbname']}", $this->db['user'], $this->db['password']);
                    $v = $db->prepare("SELECT `setval` as `version` FROM `{$basepref}_settings` WHERE `setopt` = 'system' AND `setname` = 'version'");
                    $v->execute();
                    if ($tmp = $v->fetch(\PDO::FETCH_ASSOC)) {
                        $this->version = $tmp['version'];
                    }
                } catch (PDOException $e) {}
            }

            return true;
        }

        return false;
    }

    /**
     * Search data platform EleanorCMS.
     * @return boolean
     * @final
     */
    final private function EleanorCMS()
    {
        global $docroot;

        $root = $docroot && empty($docroot) === false ? $docroot : $_SERVER['DOCUMENT_ROOT'];
        if (file_exists("$root/admin.php")) {
            $file = file_get_contents("$root/admin.php");
            if (stripos($file, 'Eleanor CMS') !== false) {
                $this->name = 'Eleanor CMS';

                if (file_exists("$root/config_general.php")) {
                    $conf = @include("$root/config_general.php");
                    if (defined('ELEANOR_VERSION')) {
                        $this->version = ELEANOR_VERSION;
                    }

                    $this->db['driver'] = 'mysql';
                    if (isset($conf['db_host'])) {
                        $this->db['host'] = $conf['db_host'];
                    }
                    if (isset($conf['db_user'])) {
                        $this->db['user'] = $conf['db_user'];
                    }
                    if (isset($conf['db_pass'])) {
                        $this->db['password'] = $conf['db_pass'];
                    }
                    if (isset($conf['db'])) {
                        $this->db['dbname'] = $conf['db'];
                    }
                }

                return true;
            }
        }

        return false;
    }

    /**
     * Search data platform PHPNuke.
     * @return boolean
     * @final
     */
    final private function PHPNuke()
    {
        global $docroot;

        $root = $docroot && empty($docroot) === false ? $docroot : $_SERVER['DOCUMENT_ROOT'];
        if (file_exists("$root/admin.php")) {
            $file = file_get_contents("$root/admin.php");
            if (stripos($file, 'PHP-NUKE') !== false) {
                $this->name = 'PHP-Nuke';

                if (file_exists("$root/config.php")) {
                    @include("config.php");
                    if (isset($dbtype)) {
                        $this->db['driver'] = strtolower($dbtype);
                    }
                    if (isset($dbhost)) {
                        $this->db['host'] = $dbhost;
                    }
                    if (isset($dbuname)) {
                        $this->db['user'] = $dbuname;
                    }
                    if (isset($dbpass)) {
                        $this->db['password'] = $dbpass;
                    }
                    if (isset($dbname)) {
                        $this->db['dbname'] = $dbname;
                    }
                }

                if (
                    isset($this->db['driver']) &&
                    isset($this->db['host']) &&
                    isset($this->db['user']) &&
                    isset($this->db['password']) &&
                    isset($this->db['dbname']) &&
                    isset($prefix) &&
                    Server::PDO()
                ) {
                    try {
                        $db = new \PDO("{$this->db['driver']}:host={$this->db['host']};dbname={$this->db['dbname']}", $this->db['user'], $this->db['password']);
                        $v = $db->prepare("SELECT `Version_Num` as `version` FROM `{$prefix}_config` LIMIT 1");
                        $v->execute();
                        if ($tmp = $v->fetch(\PDO::FETCH_ASSOC)) {
                            $this->version = reset(explode(' ', $tmp['version']));
                        }
                    } catch (PDOException $e) {}
                }

                return true;
            }
        }

        return false;
    }

    /**
     * Search data platform Mod-X.
     * @return boolean
     * @final
     */
    final private function MODX()
    {
        global $docroot;

        $root = $docroot && empty($docroot) === false ? $docroot : $_SERVER['DOCUMENT_ROOT'];
        if (file_exists("$root/manager") && file_exists("$root/assets")) {
            $this->name = 'Mod-X';

            if (file_exists("$root/manager/includes/version.inc.php")) {
                @include("$root/manager/includes/version.inc.php");
                if (isset($modx_version)) {
                    $this->version = $modx_version;
                }

                if (isset($modx_branch)) {
                    $this->version .= " $modx_branch";
                }
            }

            if (file_exists("$root/manager/includes/config.inc.php")) {
                $settings = file("$root/manager/includes/config.inc.php", FILE_SKIP_EMPTY_LINES);
                foreach ($settings as $row => $setting) {
                    if ($row > 15) {
                        break;
                    }
                    if (stripos($setting, 'database_type') !== false) {
                        $this->db['driver'] = eval('return ' . $setting);
                        continue;
                    } elseif (stripos($setting, 'database_server') !== false) {
                        $this->db['host'] = eval('return ' . $setting);
                        continue;
                    } elseif (stripos($setting, 'database_user') !== false) {
                        $this->db['user'] = eval('return ' . $setting);
                        continue;
                    } elseif (stripos($setting, 'database_password') !== false) {
                        $this->db['password'] = eval('return ' . $setting);
                        continue;
                    } elseif (stripos($setting, 'dbase') !== false) {
                        $this->db['dbname'] = trim(eval('return ' . $setting), "`");
                        continue;
                    }
                }
            }

            return true;
        }

        return false;
    }

    /**
     * Search data platform TypoЗ.
     * @return boolean
     * @final
     */
    final private function TypoЗ()
    {
        global $docroot;

        $root = $docroot && empty($docroot) === false ? $docroot : $_SERVER['DOCUMENT_ROOT'];
        if (file_exists("$root/typo3") && is_dir("$root/typo3")) {
            $this->name = 'TypoЗ';

            if (file_exists("$root/typo3conf/localconf.php")) {
                $settings = file("$root/typo3conf/localconf.php", FILE_SKIP_EMPTY_LINES);
                $this->db['driver'] = 'mysql';
                foreach ($settings as $row => $setting) {
                    if (stripos($setting, 'compat_version') !== false) {
                        $this->version = eval('return ' . $setting);
                        continue;
                    } elseif (stripos($setting, 'typo_db_host') !== false) {
                        $this->db['host'] = eval('return ' . $setting);
                        continue;
                    } elseif (stripos($setting, 'typo_db_username') !== false) {
                        $this->db['user'] = eval('return ' . $setting);
                        continue;
                    } elseif (stripos($setting, 'typo_db_password') !== false) {
                        $this->db['password'] = eval('return ' . $setting);
                        continue;
                    } elseif (stripos($setting, 'typo_db ') !== false) {
                        $this->db['dbname'] = eval('return ' . $setting);
                        continue;
                    }
                }
            }

            return true;
        }

        return false;
    }

    /**
     * Search data platform Magento.
     * @return boolean
     * @final
     */
    final private function Magento()
    {
        global $docroot;

        $root = $docroot && empty($docroot) === false ? $docroot : $_SERVER['DOCUMENT_ROOT'];
        if (file_exists("$root/app/Mage.php")) {
            $file = file_get_contents("$root/app/Mage.php");
            if (stripos($file, 'Magento') !== false) {
                $this->name = 'Magento';

                $file = str_replace("\n", "", $file);
                if (Server::PerlRegex() && preg_match("/.*getVersionInfo\(\)\s+\{(.*\s+\)\;)\s+\}.*/", $file, $regx)) {
                    $v = @eval($regx[1]);
                    if ($v) {
                        $this->version = trim(
                            "{$v['major']}.{$v['minor']}.{$v['revision']}" .
                            ($v['patch'] != '' ? ".{$v['patch']}" : "") .
                            "-{$v['stability']}{$v['number']}" , '.-'
                            );
                    }
                }

                if (file_exists("$root/app/etc/local.xml") && Server::SimpleXML()) {
                    $config = file_get_contents("$root/app/etc/local.xml");
                    $config = str_replace("<![CDATA[", "", $config);
                    $config = str_replace("]]>", "", $config);
                    $config = simplexml_load_string($config);

                    if (isset($config->global->resources->default_setup->connection)) {
                        $config = $config->global->resources->default_setup->connection;
                        $this->db['driver'] = 'mysql';
                    }

                    if (isset($config->host)) {
                        $this->db['host'] = (string) $config->host;
                    }
                    if (isset($config->username)) {
                        $this->db['user'] = (string) $config->username;
                    }
                    if (isset($config->password)) {
                        $this->db['password'] = (string) $config->password;
                    }
                    if (isset($config->dbname)) {
                        $this->db['dbname'] = (string) $config->dbname;
                    }
                }

                return true;
            }
        }

        return false;
    }
}
