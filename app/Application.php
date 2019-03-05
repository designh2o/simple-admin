<?php
/**
 * Created by PhpStorm.
 * User: Andrey Dubinin
 * Date: 03.03.19
 * Time: 15:49
 */

namespace App;



use Doctrine\DBAL\Connection;
use Twig\Environment;

/**
 * Main class application
 * Class Application
 * @package App
 */
class Application
{
	private static $instance;
	private $twig;
	private $doctrine;

	public static function getInstance()
	{
		if (null === self::$instance)
		{
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function __construct()
	{
		$this->initDotEnv();
		$this->initDoctrine();
		$this->initTwig();
	}

	/**
	 * @return Environment
	 */
	public function getTwig()
	{
		return $this->twig;
	}

	/**
	 * @return Connection
	 */
	public function getDoctrine()
	{
		return $this->doctrine;
	}

	protected function initTwig()
	{
		$loader = new \Twig\Loader\FilesystemLoader(__DIR__.'/../resources/views/');
		$this->twig = new \Twig\Environment($loader, [
			//'cache' => __DIR__.'/../public/cache/',
			'debug' => true
		]);
		$this->twig->addExtension(new \Twig\Extension\DebugExtension());
	}

	protected function initDotEnv()
	{
		$dotenv = \Dotenv\Dotenv::create(__DIR__.'/../');
		$dotenv->load();
	}

	protected function initDoctrine()
	{
		$config = new \Doctrine\DBAL\Configuration();
		$connectionParams = array(
			'dbname' => getenv('DB_DATABASE'),
			'user' => getenv('DB_USERNAME'),
			'password' => getenv('DB_PASSWORD'),
			'host' => getenv('DB_HOST'),
			'port' => getenv('DB_PORT'),
			'driver' => 'pdo_mysql',
		);
		$this->doctrine = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);
	}

	private function __clone () {}
	private function __wakeup () {}
}