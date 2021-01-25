<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\Action;

use SixtyEightPublishers\FileBundle\Entity\FileInterface;
use SixtyEightPublishers\FileBundle\Storage\DataStorageInterface;
use SixtyEightPublishers\FileBundle\Storage\Manipulator\Flaggable\FlaggableManipulatorInterface;

final class SetFlagAction implements ActionInterface
{
	/** @var string  */
	private $name;

	/** @var string  */
	private $flag;

	/** @var bool  */
	private $unique;

	/** @var string */
	private $label;

	/**
	 * @param string      $name
	 * @param string      $flag
	 * @param bool        $unique
	 * @param string|NULL $label
	 */
	public function __construct(string $name, string $flag, bool $unique = FALSE, ?string $label = NULL)
	{
		$this->name = $name;
		$this->flag = $flag;
		$this->unique = $unique;
		$this->label = $label ?? $name;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getLabel(): string
	{
		return $this->label;
	}

	/**
	 * {@inheritdoc}
	 */
	public function isImplemented(DataStorageInterface $dataStorage): bool
	{
		if (!$dataStorage->hasManipulator(FlaggableManipulatorInterface::class)) {
			return FALSE;
		}

		/** @var \SixtyEightPublishers\FileBundle\Storage\Manipulator\Flaggable\FlaggableManipulatorInterface $manipulator */
		$manipulator = $dataStorage->getManipulator(FlaggableManipulatorInterface::class);

		return $manipulator->isFlagSupported($this->flag);
	}

	/**
	 * {@inheritdoc}
	 */
	public function isApplicableOnFile(FileInterface $file, DataStorageInterface $dataStorage): bool
	{
		/** @var \SixtyEightPublishers\FileBundle\Storage\Manipulator\Flaggable\FlaggableManipulatorInterface $manipulator */
		$manipulator = $dataStorage->getManipulator(FlaggableManipulatorInterface::class);

		return $manipulator->isFlagApplicableOnFile($this->flag, $file);
	}

	/**
	 * {@inheritdoc}
	 */
	public function run(DataStorageInterface $dataStorage, FileInterface $file): void
	{
		$dataStorage->manipulate(FlaggableManipulatorInterface::class, $file, $this->flag, $this->unique);
	}
}
