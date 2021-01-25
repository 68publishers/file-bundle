<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\Storage;

interface DataStorageFactoryInterface
{
	/**
	 * @param string $storageClassName
	 * @param mixed  ...$args
	 *
	 * @return \SixtyEightPublishers\FileBundle\Storage\DataStorageInterface
	 */
	public function create(string $storageClassName, ...$args): DataStorageInterface;
}
