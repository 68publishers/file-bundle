<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\Action;

use SixtyEightPublishers\FileBundle\Entity\FileInterface;
use SixtyEightPublishers\FileBundle\Storage\DataStorageInterface;
use SixtyEightPublishers\FileBundle\Exception\InvalidStateException;

final class DownloadAction implements ActionInterface
{
	/** @var string|NULL */
	protected $label;

	/**
	 * @param string $label
	 */
	public function __construct(?string $label = NULL)
	{
		$this->label = $label;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getName(): string
	{
		return 'download';
	}

	/**
	 * {@inheritDoc}
	 */
	public function getLabel(): string
	{
		return $this->label ?? $this->getName();
	}

	/**
	 * {@inheritDoc}
	 */
	public function isImplemented(DataStorageInterface $storage): bool
	{
		return TRUE;
	}

	/**
	 * {@inheritDoc}
	 */
	public function isApplicableOnFile(FileInterface $file, DataStorageInterface $dataStorage): bool
	{
		return TRUE;
	}

	/**
	 * {@inheritDoc}
	 */
	public function run(DataStorageInterface $dataStorage, FileInterface $file): void
	{
		throw new InvalidStateException(sprintf(
			'The action of type %s can\'t be called server side.',
			self::class
		));
	}
}
