<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\Storage;

use SixtyEightPublishers\FileBundle\Exception\InvalidArgumentException;

final class DataStorageFactory implements DataStorageFactoryInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function create(string $storageClassName, ...$args): DataStorageInterface
	{
		if (!is_subclass_of($storageClassName, DataStorageInterface::class, TRUE)) {
			throw new InvalidArgumentException(sprintf(
				'Class %s is not implementor of interface %s',
				$storageClassName,
				DataStorageInterface::class
			));
		}

		return new $storageClassName(...$args);
	}
}
