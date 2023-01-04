<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\Bridge\ImageStorage\Storage\Manipulator\Rotation;

use Doctrine\ORM\EntityManagerInterface;
use SixtyEightPublishers\FileBundle\Entity\FileInterface;
use SixtyEightPublishers\ImageStorage\Resource\ResourceInterface;
use SixtyEightPublishers\FileStorage\FileStorageProviderInterface;
use SixtyEightPublishers\FileBundle\Exception\InvalidStateException;
use SixtyEightPublishers\FileBundle\Storage\Options\OptionsInterface;
use SixtyEightPublishers\DoctrinePersistence\TransactionFactoryInterface;
use SixtyEightPublishers\FileBundle\Storage\Manipulator\AbstractManipulator;

class RotationManipulator extends AbstractManipulator implements RotationManipulatorInterface
{
	/** @var \SixtyEightPublishers\FileStorage\FileStorageProviderInterface  */
	private $fileStorageProvider;

	/** @var \SixtyEightPublishers\DoctrinePersistence\TransactionFactoryInterface  */
	private $transactionFactory;

	/**
	 * @param \SixtyEightPublishers\FileStorage\FileStorageProviderInterface        $fileStorageProvider
	 * @param \SixtyEightPublishers\DoctrinePersistence\TransactionFactoryInterface $transactionFactory
	 */
	public function __construct(FileStorageProviderInterface $fileStorageProvider, TransactionFactoryInterface $transactionFactory)
	{
		$this->fileStorageProvider = $fileStorageProvider;
		$this->transactionFactory = $transactionFactory;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @throws \SixtyEightPublishers\FileStorage\Exception\FilesystemException
	 */
	public function __invoke(OptionsInterface $options, FileInterface $file, int $degrees): void
	{
		$source = $file->getSource();
		$fileStorage = $this->fileStorageProvider->get($source->getStorageName());
		$resource = $fileStorage->createResource($source);

		if (!$resource instanceof ResourceInterface) {
			throw new InvalidStateException(sprintf(
				'Resource must be implementor of %s.',
				ResourceInterface::class
			));
		}

		$resource = $resource->modifyImage([
			'o' => (string) $degrees,
		]);

		$fileStorage->save($resource);

		$transaction = $this->transactionFactory->create(static function (EntityManagerInterface $em, FileInterface $file) {
			$file->update();
			$em->persist($file);
		}, [
			'file' => $file,
			'options' => $options,
		]);

		$this->dispatchExtendTransactionEvent($transaction, $options);

		$transaction->run();
	}
}
