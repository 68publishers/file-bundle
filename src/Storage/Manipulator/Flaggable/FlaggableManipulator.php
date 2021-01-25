<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\Storage\Manipulator\Flaggable;

use SixtyEightPublishers\FileBundle\Entity\FileInterface;
use SixtyEightPublishers\FileBundle\Exception\InvalidStateException;
use SixtyEightPublishers\FileBundle\Storage\Options\OptionsInterface;
use SixtyEightPublishers\FileBundle\Storage\Manipulator\AbstractManipulator;
use SixtyEightPublishers\FileBundle\Storage\Manipulator\ExternalAssociationStorageAwareTrait;
use SixtyEightPublishers\FileBundle\Storage\Manipulator\ExternalAssociationStorageAwareInterface;

class FlaggableManipulator extends AbstractManipulator implements FlaggableManipulatorInterface, ExternalAssociationStorageAwareInterface
{
	use ExternalAssociationStorageAwareTrait;

	/** @var \SixtyEightPublishers\FileBundle\Storage\Manipulator\Flaggable\FlagHandlerInterface[]  */
	private $handlers = [];

	/**
	 * @param array $handlers
	 */
	public function __construct(array $handlers = [])
	{
		foreach ($handlers as $flag => $handler) {
			$this->addHandler((string) $flag, $handler);
		}
	}

	/**
	 * @param string                                                                              $flag
	 * @param \SixtyEightPublishers\FileBundle\Storage\Manipulator\Flaggable\FlagHandlerInterface $handler
	 *
	 * @return void
	 */
	public function addHandler(string $flag, FlagHandlerInterface $handler): void
	{
		$this->handlers[$flag] = $handler;
	}

	/**
	 * {@inheritDoc}
	 */
	public function isFlagSupported(string $flag): bool
	{
		return isset($this->handlers[$flag]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function isFlagApplicableOnFile(string $flag, FileInterface $file): bool
	{
		if (!$this->isFlagSupported($flag)) {
			return FALSE;
		}

		return $this->handlers[$flag]->canHandle($file);
	}

	/**
	 * {@inheritDoc}
	 */
	public function __invoke(OptionsInterface $options, FileInterface $file, string $flag, bool $unique = FALSE): void
	{
		if (!$this->isFlagApplicableOnFile($flag, $file)) {
			throw new InvalidStateException(sprintf(
				'Missing handler for flag "%s".',
				$flag
			));
		}

		$this->handlers[$flag]($options, $file, $unique);

		$associationStorage = $this->getExternalAssociationStorage();

		if (NULL === $associationStorage) {
			return;
		}

		$references = $associationStorage->getReferences();
		$selectedReference = $references->find((string) $file->getId());

		if (NULL === $selectedReference) {
			return;
		}

		if (TRUE === $unique) {
			/** @var \SixtyEightPublishers\FileBundle\Storage\ExternalAssociation\Reference $reference */
			foreach ($references as $reference) {
				$reference->removeMetadata($flag);
			}
		}

		$selectedReference->addMetadata($flag, TRUE);
		$associationStorage->flush();
	}
}
