<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\Control\FileManager;

use SixtyEightPublishers\FileBundle\Storage\DataStorageInterface;

interface FileManagerControlFactoryInterface
{
	/**
	 * @param \SixtyEightPublishers\FileBundle\Storage\DataStorageInterface $dataStorage
	 *
	 * @return \SixtyEightPublishers\FileBundle\Control\FileManager\FileManagerControl
	 */
	public function create(DataStorageInterface $dataStorage): FileManagerControl;
}
