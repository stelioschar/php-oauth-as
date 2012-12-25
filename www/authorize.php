<?php

/**
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as
 *  published by the Free Software Foundation, either version 3 of the
 *  License, or (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

require_once '../lib/SplClassLoader.php';
$c1 = new SplClassLoader("RestService", "../extlib/php-rest-service/lib");
$c1->register();
$c2 =  new SplClassLoader("OAuth", "../lib");
$c2->register();

use \RestService\Utils\Config as Config;
use \RestService\Utils\Logger as Logger;
use \RestService\Http\HttpRequest as HttpRequest;
use \RestService\Http\IncomingHttpRequest as IncomingHttpRequest;
use \OAuth\Authorize as Authorize;
use \RestService\Http\HttpResponse as HttpResponse;

$logger = NULL;
$request = NULL;
$response = NULL;

try {
    $config = new Config(dirname(__DIR__) . DIRECTORY_SEPARATOR . "config" . DIRECTORY_SEPARATOR . "oauth.ini");
    $logger = new Logger($config->getSectionValue('Log', 'logLevel'), $config->getValue('serviceName'), $config->getSectionValue('Log', 'logFile'), $config->getSectionValue('Log', 'logMail', FALSE));

    $a = new Authorize($config, $logger);
    $request = HttpRequest::fromIncomingHttpRequest(new IncomingHttpRequest());
    $response = $a->handleRequest($request);

} catch (Exception $e) {
    // internal server error, inform resource owner through browser
    $response = new HttpResponse();
    $response->setStatusCode(500);
    ob_start();
    require dirname(__DIR__) . DIRECTORY_SEPARATOR . "templates" . DIRECTORY_SEPARATOR . "errorPage.php";
    $response->setContent(ob_get_clean());
    if (NULL !== $logger) {
        $logger->logFatal($e->getMessage() . PHP_EOL . $request . PHP_EOL . $response);
    }
}

if (NULL !== $logger) {
    $logger->logDebug($request);
}
if (NULL !== $logger) {
    $logger->logDebug($response);
}
if (NULL !== $response) {
    $response->sendResponse();
}
