<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\Action;

use SixtyEightPublishers\FileBundle\Entity\FileInterface;
use SixtyEightPublishers\FileBundle\Storage\DataStorageInterface;
use SixtyEightPublishers\FileBundle\Storage\Manipulator\Delete\DeleteManipulatorInterface;

final class DeleteAction extends AbstractAction
{
	/**
	 * {@inheritdoc}
	 */
	public function getName(): string
	{
		return 'delete';
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getManipulatorClass(): string
	{
		return DeleteManipulatorInterface::class;
	}

	/**
	 * {@inheritdoc}
	 */
	public function run(DataStorageInterface $dataStorage, FileInterface $file): void
	{
		$dataStorage->manipulate($this->getManipulatorClass(), $file);
	}
}
