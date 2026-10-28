<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\Tests;

use LogicException;
use Nette\DI\Compiler;
use Nette\DI\Container;
use Nette\Http\Request;
use Nette\Http\Response;
use Nette\Http\UrlScript;
use Nette\DI\ContainerLoader;
use Nette\DI\Definitions\Statement;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Symfony\Component\EventDispatcher\EventDispatcher;
use SixtyEightPublishers\FileBundle\DI\FileBundleExtension;
use SixtyEightPublishers\DoctrinePersistence\TransactionInterface;
use SixtyEightPublishers\DoctrinePersistence\TransactionFactoryInterface;
use SixtyEightPublishers\FileStorage\Bridge\Nette\DI\FileStorageExtension;
use SixtyEightPublishers\EventDispatcherExtra\Bridge\Nette\DI\EventDispatcherExtraExtension;

final class ContainerFactory
{
	/**
	 * @param array ...$fileBundleConfigs every array is passed as a separate config (like a separate config file)
	 *
	 * @return \Nette\DI\Container
	 */
	public static function create(array ...$fileBundleConfigs): Container
	{
		$loader = new ContainerLoader(TEMP_PATH . '/container', TRUE);
		$class = $loader->load(static function (Compiler $compiler) use ($fileBundleConfigs): void {
			$compiler->addExtension('file_storage', new FileStorageExtension());
			$compiler->addExtension('event_dispatcher', new EventDispatcherExtraExtension());
			$compiler->addExtension('file_bundle', new FileBundleExtension());

			$compiler->addConfig([
				'file_storage' => [
					'storages' => [
						'default' => [
							'filesystem' => [
								'adapter' => new Statement(LocalFilesystemAdapter::class, [TEMP_PATH . '/storage']),
							],
						],
					],
				],
				'services' => [
					EventDispatcher::class,
					new Statement(Request::class, [new Statement(UrlScript::class, ['http://localhost/'])]),
					Response::class,
					'transaction_factory' => [
						'type' => TransactionFactoryInterface::class,
						'imported' => TRUE,
					],
				],
			]);

			foreach ($fileBundleConfigs as $fileBundleConfig) {
				$compiler->addConfig(['file_bundle' => $fileBundleConfig]);
			}
		}, md5(serialize($fileBundleConfigs)));

		$container = new $class();
		assert($container instanceof Container);

		$container->addService('transaction_factory', new class implements TransactionFactoryInterface {
			public function create(callable $callback, iterable $arguments = []): TransactionInterface
			{
				throw new LogicException('Not implemented.');
			}
		});

		return $container;
	}
}
