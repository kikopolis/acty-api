<?php

declare(strict_types = 1);

use App\Controller\ApiController;
use Symfony\Component\HttpFoundation\Request;
use App\DB\DB;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

$request = Request::createFromGlobals();
// Since only two endpoints exist, a switch statement is fine as a router
switch ($request->getMethod()) {
	case Request::METHOD_GET:
		return (new ApiController(DB::getEm()))->retrieve($request);
	case Request::METHOD_POST:
		return (new ApiController(DB::getEm()))->store($request);
	default:
		echo "404";
}