<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\Action;

use SixtyEightPublishers\FileBundle\Entity\FileInterface;
use SixtyEightPublishers\FileBundle\Storage\DataStorageInterface;

interface ActionInterface
{
	/**
	 * @return string
	 */
	public function getName(): string;

	/**
	 * @return string
	 */
	public function getLabel(): string;

	/**
	 * @param \SixtyEightPublishers\FileBundle\Storage\DataStorageInterface $storage
	 *
	 * @return bool
	 */
	public function isImplemented(DataStorageInterface $storage): bool;

	/**
	 * @param \SixtyEightPublishers\FileBundle\Entity\FileInterface         $file
	 * @param \SixtyEightPublishers\FileBundle\Storage\DataStorageInterface $dataStorage
	 *
	 * @return bool
	 */
	public function isApplicableOnFile(FileInterface $file, DataStorageInterface $dataStorage): bool;

	/**
	 * @param \SixtyEightPublishers\FileBundle\Storage\DataStorageInterface $dataStorage
	 * @param \SixtyEightPublishers\FileBundle\Entity\FileInterface         $file
	 *
	 * @return void
	 * @throws \SixtyEightPublishers\FileBundle\Exception\FileManipulationException
	 */
	public function run(DataStorageInterface $dataStorage, FileInterface $file): void;
}
