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
use DmitryMamontov\Server\Http;
use DmitryMamontov\Server\Server;

/**
 * Provider - Receives information about the provider.
 *
 * @author    Dmitry Mamontov <d.slonyara@gmail.com>
 * @copyright 2015 Dmitry Mamontov <d.slonyara@gmail.com>
 * @license   http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @version   Release: 1.0.3
 * @link      https://github.com/dmamontov/benchmark-tools/
 * @since     Class available since Release 1.0.3
 */
class Provider
{
    const REGX = '/^(25[0-5]|2[0-4][0-9]|[0-1][0-9]{2}|[0-9]{2}|[0-9])(\.(25[0-5]|2[0-4][0-9]|[0-1][0-9]{2}|[0-9]{2}|[0-9])){3}$/';
    private $data = array();

    /**
     * The class constructor initializes variables.
     * @param string $ip
     * @final
     */
    final public function __construct($ip = '')
    {
        if (empty($ip) && Http::RealIP()) {
            $ip = Http::RealIP();
        }

        if (
            empty($ip) === false ||
            Server::PerlRegex() != false ||
            @preg_match(self::REGX, trim($ip)) != false ||
            Server::Json() != false
        ) {
            $geo = @json_decode(@file_get_contents("http://api.2ip.com.ua/geo.json?ip=$ip"), true);
            if (empty($geo) == false) {
                $this->data = array_merge($this->data, $geo);
            }

            $provider = @json_decode(@file_get_contents("http://api.2ip.com.ua/provider.json?ip=$ip"), true);
            if (empty($provider) == false) {
                $this->data = array_merge($this->data, $provider);
            }
        }
    }

    /**
     * Gets the name of the country.
     * @return string
     * @final
     */
    final public function Country()
    {
        return isset($this->data['country']) ? $this->data['country'] : false;
    }

    /**
     * Gets the name of the region.
     * @return string
     * @final
     */
    final public function Region()
    {
        return isset($this->data['region']) ? $this->data['region'] : false;
    }

    /**
     * Gets the name of the city.
     * @return string
     * @final
     */
    final public function City()
    {
        return isset($this->data['city']) ? $this->data['city'] : false;
    }

    /**
     * Gets zip code.
     * @return string
     * @final
     */
    final public function ZipCode()
    {
        return isset($this->data['zip_code']) ? $this->data['zip_code'] : false;
    }

    /**
     * Gets latitude.
     * @return string
     * @final
     */
    final public function Latitude()
    {
        return isset($this->data['latitude']) ? $this->data['latitude'] : false;
    }

    /**
     * Gets longitude.
     * @return string
     * @final
     */
    final public function Longitude()
    {
        return isset($this->data['longitude']) ? $this->data['longitude'] : false;
    }

    /**
     * Gets Time Zone.
     * @return string
     * @final
     */
    final public function TimeZone()
    {
        return isset($this->data['time_zone']) ? $this->data['time_zone'] : false;
    }

    /**
     * Gets the name of the provider.
     * @return string
     * @final
     */
    final public function Name()
    {
        return isset($this->data['name_ripe']) ? $this->data['name_ripe'] : false;
    }

    /**
     * Gets the provider website.
     * @return string
     * @final
     */
    final public function Site()
    {
        return isset($this->data['site']) ? "<a href=\"{$this->data['site']}\" target=\"_blank\">{$this->data['site']}</a>" : false;
    }

    /**
     * Will receive a link to Google Maps.
     * @return string
     * @final
     */
    final public function Map()
    {
        if ($this->Latitude() == false || $this->Longitude() == false) {
            return false;
        }

        return "<a href=\"https://www.google.ru/maps/@{$this->Latitude()},{$this->Longitude()},10z\" target=\"_blank\">On the map.</a>";
    }

    /**
     * Gets the autonomous system number provider.
     * @return string
     * @final
     */
    final public function AutonomousSystemNumber()
    {
        return isset($this->data['as']) ? $this->data['as'] : false;
    }

    /**
     * Gets the network provider.
     * @return string
     * @final
     */
    final public function Network()
    {
        return isset($this->data['route']) ? $this->data['route'] : false;
    }

    /**
     * Gets mask network provider.
     * @return string
     * @final
     */
    final public function NetworkMask()
    {
        return isset($this->data['mask']) ? $this->data['mask'] : false;
    }

    /**
     * Gets the range of ip.
     * @return string
     * @final
     */
    final public function RangeIP()
    {
        if (isset($this->data['ip_range_start']) == false || isset($this->data['ip_range_end']) == false) {
            return false;
        }

        return long2ip($this->data['ip_range_start']) . ' - ' . long2ip($this->data['ip_range_end']);
    }
}