<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\Storage\Manipulator\Delete;

use Doctrine\ORM\EntityManagerInterface;
use SixtyEightPublishers\FileBundle\Entity\FileInterface;
use SixtyEightPublishers\FileBundle\Storage\Options\OptionsInterface;
use SixtyEightPublishers\DoctrinePersistence\TransactionFactoryInterface;
use SixtyEightPublishers\FileBundle\Storage\Manipulator\AbstractManipulator;
use SixtyEightPublishers\FileBundle\Storage\Manipulator\ExternalAssociationStorageAwareTrait;
use SixtyEightPublishers\FileBundle\Storage\Manipulator\ExternalAssociationStorageAwareInterface;

class DeleteManipulator extends AbstractManipulator implements DeleteManipulatorInterface, ExternalAssociationStorageAwareInterface
{
	use ExternalAssociationStorageAwareTrait;

	/** @var \SixtyEightPublishers\DoctrinePersistence\TransactionFactoryInterface  */
	private $transactionFactory;

	/**
	 * @param \SixtyEightPublishers\DoctrinePersistence\TransactionFactoryInterface $transactionFactory
	 */
	public function __construct(TransactionFactoryInterface $transactionFactory)
	{
		$this->transactionFactory = $transactionFactory;
	}

	/**
	 * {@inheritdoc}
	 */
	public function __invoke(OptionsInterface $options, FileInterface $file): void
	{
		$transaction = $this->transactionFactory->create(static function (EntityManagerInterface $em, FileInterface $file) {
			$em->remove($file);

			return $file->getSource();
		}, [
			'file' => $file,
			'options' => $options,
		]);

		# External associations
		$associationStorage = $this->getExternalAssociationStorage();

		if (NULL !== $associationStorage) {
			$transaction->finally(static function () use ($associationStorage, $file) {
				$references = $associationStorage->getReferences();
				$reference = $references->find((string) $file->getId());

				if (NULL === $reference) {
					return;
				}

				$references->remove($reference);
				$associationStorage->flush();
			});
		}

		$this->dispatchExtendTransactionEvent($transaction, $options);

		$transaction->run();
	}
}
