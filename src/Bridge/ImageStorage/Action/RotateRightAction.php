<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\Bridge\ImageStorage\Action;

use SixtyEightPublishers\FileBundle\Entity\FileInterface;
use SixtyEightPublishers\FileBundle\Action\AbstractAction;
use SixtyEightPublishers\FileBundle\Storage\DataStorageInterface;
use SixtyEightPublishers\ImageStorage\FileInfoInterface as ImageFileInfoInterface;
use SixtyEightPublishers\FileBundle\Bridge\ImageStorage\Storage\Manipulator\Rotation\RotationManipulatorInterface;

final class RotateRightAction extends AbstractAction
{
	/**
	 * {@inheritdoc}
	 */
	public function getName(): string
	{
		return 'rotate_right';
	}

	/**
	 * {@inheritdoc}
	 */
	public function run(DataStorageInterface $dataStorage, FileInterface $file): void
	{
		$dataStorage->manipulate($this->getManipulatorClass(), $file, -90);
	}

	/**
	 * {@inheritDoc}
	 */
	public function isApplicableOnFile(FileInterface $file, DataStorageInterface $dataStorage): bool
	{
		return $file->getSource() instanceof ImageFileInfoInterface;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getManipulatorClass(): string
	{
		return RotationManipulatorInterface::class;
	}
}
