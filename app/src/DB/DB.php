<?php

declare(strict_types = 1);

namespace App\DB;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Setup;
use Exception;
use function dirname;

/**
 * Class DB
 * @package App\DB
 * @author  Kristo Leas <kristo.leas@gmail.com>
 */
final class DB {
	private static EntityManagerInterface $em;
	
	/**
	 * Create and configure the Doctrine EntityManager
	 */
	public static function boot() {
		try {
			$config = Setup::createAnnotationMetadataConfiguration([dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Entity'], true, useSimpleAnnotationReader: false);
			$conn   = ['driver' => 'pdo_mysql', 'user' => 'root', 'password' => 'password', 'dbname' => 'acty-api', 'host' => 'mariadb-acty-container'];
			DB::$em = EntityManager::create($conn, $config);
		} catch (Exception $e) {
			dd($e);
		}
	}
	
	public static function getEm(): EntityManagerInterface {
		if (! isset(DB::$em)) {
			DB::boot();
		}
		return DB::$em;
	}
}