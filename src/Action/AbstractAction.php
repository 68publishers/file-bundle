<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\Action;

use SixtyEightPublishers\FileBundle\Entity\FileInterface;
use SixtyEightPublishers\FileBundle\Storage\DataStorageInterface;

abstract class AbstractAction implements ActionInterface
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
	 * {@inheritdoc}
	 */
	public function getLabel(): string
	{
		return $this->label ?? $this->getName();
	}

	/**
	 * {@inheritdoc}
	 */
	public function isImplemented(DataStorageInterface $dataStorage): bool
	{
		$manipulatorClass = $this->getManipulatorClass();

		return $dataStorage->hasManipulator($manipulatorClass);
	}

	/**
	 * {@inheritdoc}
	 */
	public function isApplicableOnFile(FileInterface $file, DataStorageInterface $dataStorage): bool
	{
		return TRUE;
	}

	/**
	 * @return string
	 */
	abstract protected function getManipulatorClass(): string;
}
