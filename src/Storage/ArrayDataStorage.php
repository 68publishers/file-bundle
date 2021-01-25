<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\Storage;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use SixtyEightPublishers\FileBundle\Entity\FileInterface;

final class ArrayDataStorage implements DataStorageInterface
{
	use DataStorageTrait;

	/** @var \SixtyEightPublishers\FileBundle\Entity\FileInterface[]  */
	protected $files = [];

	/**
	 * @param \SixtyEightPublishers\FileBundle\Entity\FileInterface[] $files
	 */
	public function __construct(array $files = [])
	{
		foreach ($files as $file) {
			$this->addFile($file);
		}
	}

	/**
	 * @param \SixtyEightPublishers\FileBundle\Entity\FileInterface $file
	 *
	 * @return void
	 */
	public function addFile(FileInterface $file): void
	{
		$this->files[] = $file;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getFiles(): Collection
	{
		return new ArrayCollection($this->files);
	}
}
