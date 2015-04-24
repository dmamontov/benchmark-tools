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
    require_once 'vendor/autoload.php';

    use DmitryMamontov\BenchmarkTools;
    use DmitryMamontov\Server\Server;
    use DmitryMamontov\Server\HighLoad;
    use DmitryMamontov\Server\Http;
    use DmitryMamontov\Server\FileSystem;
    use DmitryMamontov\Server\Platform;
    use DmitryMamontov\Server\DB;
    use DmitryMamontov\Server\Provider;

    $platform = new Platform;
    $b = new BenchmarkTools('Testing');

    $b->addHeader('Information about the platform.');
    $b->add('Name', $platform->Name(), 'Bitrix', '=', 'Found the platform.');
    $b->add('Version', $platform->Version());
    $platformdb = $platform->DB();
    $b->add('Platform DataBase', $platformdb);

    $provider = new Provider();
    $b->addHeader('Provider.');
    $b->add('Country', $provider->Country(), null, null, 'Gets the name of the country.');
    $b->add('Region', $provider->Region(), null, null, 'Gets the name of the region.');
    $b->add('City', $provider->City(), null, null, 'Gets the name of the city.');
    $b->add('Zip Code', $provider->ZipCode(), null, null, 'Gets zip code.');
    $b->add('Latitude', $provider->Latitude(), null, null, 'Gets latitude.');
    $b->add('Longitude', $provider->Longitude(), null, null, 'Gets longitude.');
    $b->add('Time Zone', $provider->TimeZone(), null, null, 'Gets Time Zone.');
    $b->add('Name', $provider->Name(), null, null, 'Gets the name of the provider.');
    $b->add('Range IP', $provider->RangeIP(), null, null, 'Gets the range of ip.');
    $b->add('Site', $provider->Site(), null, null, 'Gets the provider website.');
    $b->add('Autonomous System Number', $provider->AutonomousSystemNumber(), null, null, 'Gets the autonomous system number provider.');
    $b->add('Network', $provider->Network(), null, null, 'Gets the network provider.');
    $b->add('Network Mask', $provider->NetworkMask(), null, null, 'Gets mask network provider.');
    $b->add('Map', $provider->Map(), null, null, 'Will receive a link to Google Maps.');

    $b->addHeader('Main.');
    $b->add('PHP Interface', Server::PHPInterface(), null, null, 'Finds interface php.');
    $b->add('PHP Version', Server::PHPVersion(), null, null, 'Finds version of php.');
    $b->add('PHP Accelerator', Server::PHPAccelerator(), null, null, 'Finds accelerator php.');
    $b->add('Safe Mode', Server::SafeMode(), null, null, 'Checking Safe Mode.');
    $b->add('Short Open Tag', Server::ShortOpenTag(), null, null, 'Checking Short Open Tag.');
    $b->add('Shared Memory', Server::SharedMemory(), null, null, 'Checking Shared Memory.');
    $b->add('Posix', Server::Posix(), null, null, 'Checking posix.');
    $b->add('Pcntl', Server::Pcntl(), null, null, 'Checking pcntl.');
    $b->add('Email Sanding', Server::EmailSending(), null, null, 'Checking Messages.');
    $b->add('Mcrypt', Server::Mcrypt(), null, null, 'Checking mcrypt.');
    $b->add('Sockets', Server::Sockets(), null, null, 'Checking sockets.');
    $b->add('PHP Regex', Server::PHPRegex(), null, null, 'Checking php regex.');
    $b->add('Perl Regex', Server::PerlRegex(), null, null, 'Checking perl regex.');
    $b->add('Zlib', Server::Zlib(), null, null, 'Checking zlib.');
    $b->add('GDlib', Server::GDlib(), null, null, 'Checking gdlib.');
    $b->add('Free Type', Server::FreeType(), null, null, 'Checking free type.');
    $b->add('Mbstring', Server::Mbstring(), null, null, 'Checking mbstring.');
    $b->add('Json', Server::Json(), null, null, 'Checking Json.');
    $b->add('PDO', Server::PDO(), null, null, 'Checking PDO');
    $b->add('SimpleXML', Server::SimpleXML(), null, null, 'Checking SimpleXML');
    $b->add('DOMDocument', Server::DOMDocument(), null, null, 'Checking DOMDocument');
    $b->add('Curl', Server::Curl(), null, null, 'Checking Curl');
    $b->add('Memory Limit', Server::MemoryLimit(), null, null, 'Checking Memory Limit');
    $b->add('Max Execution Time', Server::MaxExecutionTime(), null, null, 'Checking Max Execution Time');
    $b->add('Umask', Server::Umask(), null, null, 'Finds and returns the umask.');
    $b->add('Post Max Size', Server::PostMaxSize(), null, null, 'Finds and returns the post max size.');
    $b->add('Register Globals', Server::RegisterGlobals(), null, null, 'Checking Register Globals.');
    $b->add('Display Errors', Server::DisplayErrors(), null, null, 'Checking Display Errors.');
    $b->add('PHP File Uploads', Server::PHPFileUploads(), null, null, 'Checking PHPFileUploads.');
    $b->add('Server Time', Server::ServerTime(), null, null, 'Returns the current server time.');

    $b->addHeader('High Load.');
    $b->add('Actual Memory Limit', HighLoad::ActualMemoryLimit(), 128, '>', 'Checks the actual memory limit.');
    $b->add('Actual Execution Time', HighLoad::ActualExecutionTime(5000), null, null, 'Checks real-time execution of the script.');
    $b->add('Sending Big Emails', HighLoad::SendingBigEmails(), null, null, 'Checking the sending big emails.');
    $b->add('Uploads Big File', HighLoad::UploadsBigFile(), null, null, 'Checking upload big files to the server.');

    $b->addHeader('Http server.');
    $b->add('Server', Http::Server(), null, null, 'Finds the current http server.');
    $b->add('Protocol', Http::Protocol(), null, null, 'Gets the protocol HTTP.');
    $b->add('Real IP', Http::RealIP(), null, null, 'Gets real ip address of the server.');
    $b->add('Authorization', Http::Authorization(), null, null, 'Checks authorization via http.');
    $b->add('Sessions', Http::Sessions(), null, null, 'Checks work sessions via http.');
    $b->add('SSL', Http::SSL('www.google.com'), null, null, 'Checks operation ssl via http.');
    $b->add('Local Redirect', Http::LocalRedirect(), null, null, 'Checks work local redirect via http.');

    $b->addHeader('File system.');
    $b->add('Disk Space', FileSystem::DiskSpace(), '255', '>', 'Checking disk space.');
    $b->add('Permissions Folder', FileSystem::PermissionsFolder(), '0777', '>', 'Checking access rights to the folder.');
    $b->add('Folder Creation', FileSystem::FolderCreation(), true, '=', 'Checking folder creation.');
    $b->add('Folder Deletion', FileSystem::FolderDeletion(), true, '=', 'Checking delete the folder.');
    $b->add('Permissions Folder Creation', FileSystem::PermissionsFolderCreation(), null, null, 'Checking access rights to the new folder.');
    $b->add('File Creation', FileSystem::FileCreation(), null, null, 'Checking file creation.');
    $b->add('File Deletion', FileSystem::FileDeletion(), true, '=', 'Checking delete the file.');
    $b->add('Permissions File Creation', FileSystem::PermissionsFileCreation(), null, null, 'Checking access rights to the new file.');
    $b->add('File Execution', FileSystem::FileExecution(), true, '=', 'Checking the execution file.');
    $b->add('Processing Htaccess', FileSystem::ProcessingHtaccess(), true, '=', 'Checking processing htaccess.');
    $b->add('Time To Create 1000 File', FileSystem::TimeToCreateFile(1000), 1, '<', 'Checking the time required to create the file.');
    $b->add('File Uploads', FileSystem::FileUploads(), null, null, 'Checking upload files to the server.');

    if (
        isset($platformdb['driver']) &&
        isset($platformdb['host']) &&
        isset($platformdb['user']) &&
        isset($platformdb['password']) &&
        isset($platformdb['dbname'])
    ) {
        $db = new DB($platformdb['user'], $platformdb['password'], $platformdb['dbname'], $platformdb['host'], $platformdb['driver']);

        $b->addHeader('DataBase.');
        $b->add('Version', $db->Version());
        $b->add('Sql Mode', $db->SqlMode());
        $b->add('Time Diff', $db->TimeDiff(), null, null, 'Checking the time difference.');
        $b->add('Innodb support', $db->Innodb(), null, null, 'Check support InnoDB.');
        $b->add('MyIsam support', $db->MyIsam(), null, null, 'Check support MyIsam.');
        $b->add('Speed Insert', $db->SpeedInsert(), null, null, 'Speed test insert 1000 records.');
        $b->add('Speed Select', $db->SpeedSelect(), null, null, 'Speed test select 1000 records.');
        $b->add('Characters', $db->Characters());
        $b->add('Count Records', $db->CountRow(true));
    }

    $b->draw();
?>
