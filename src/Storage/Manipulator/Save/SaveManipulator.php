<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\Storage\Manipulator\Save;

use Nette\Http\FileUpload;
use Doctrine\ORM\EntityManagerInterface;
use SixtyEightPublishers\FileStorage\FileInfoInterface;
use SixtyEightPublishers\FileBundle\Entity\FileInterface;
use SixtyEightPublishers\FileBundle\Event\ResourceCreatedEvent;
use SixtyEightPublishers\FileBundle\ResourceMetadata\MetadataName;
use SixtyEightPublishers\FileBundle\Storage\Options\OptionsInterface;
use SixtyEightPublishers\DoctrinePersistence\TransactionFactoryInterface;
use SixtyEightPublishers\FileBundle\Storage\ExternalAssociation\Reference;
use SixtyEightPublishers\FileBundle\Storage\Manipulator\AbstractManipulator;
use SixtyEightPublishers\FileBundle\EntityFactory\FileEntityFactoryInterface;
use SixtyEightPublishers\FileBundle\FileStorageResolver\FileStorageResolverInterface;
use SixtyEightPublishers\FileBundle\ResourceMetadata\ResourceMetadataFactoryInterface;
use SixtyEightPublishers\FileBundle\Storage\Manipulator\ExternalAssociationStorageAwareTrait;
use SixtyEightPublishers\FileBundle\Storage\Manipulator\ExternalAssociationStorageAwareInterface;

class SaveManipulator extends AbstractManipulator implements SaveManipulatorInterface, ExternalAssociationStorageAwareInterface
{
	use ExternalAssociationStorageAwareTrait;

	/** @var \SixtyEightPublishers\FileBundle\FileStorageResolver\FileStorageResolverInterface  */
	private $fileStorageResolver;

	/** @var \SixtyEightPublishers\FileBundle\EntityFactory\FileEntityFactoryInterface  */
	private $fileEntityFactory;

	/** @var \SixtyEightPublishers\FileBundle\ResourceMetadata\ResourceMetadataFactoryInterface  */
	private $resourceMetadataFactory;

	/** @var \SixtyEightPublishers\DoctrinePersistence\TransactionFactoryInterface  */
	private $transactionFactory;

	/**
	 * @param \SixtyEightPublishers\FileBundle\FileStorageResolver\FileStorageResolverInterface  $fileStorageResolver
	 * @param \SixtyEightPublishers\FileBundle\EntityFactory\FileEntityFactoryInterface          $fileEntityFactory
	 * @param \SixtyEightPublishers\FileBundle\ResourceMetadata\ResourceMetadataFactoryInterface $resourceMetadataFactory
	 * @param \SixtyEightPublishers\DoctrinePersistence\TransactionFactoryInterface              $transactionFactory
	 */
	public function __construct(FileStorageResolverInterface $fileStorageResolver, FileEntityFactoryInterface $fileEntityFactory, ResourceMetadataFactoryInterface $resourceMetadataFactory, TransactionFactoryInterface $transactionFactory)
	{
		$this->fileStorageResolver = $fileStorageResolver;
		$this->fileEntityFactory = $fileEntityFactory;
		$this->resourceMetadataFactory = $resourceMetadataFactory;
		$this->transactionFactory = $transactionFactory;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @throws \Exception
	 */
	public function __invoke(OptionsInterface $options, FileUpload $fileUpload): FileInterface
	{
		$helper = new Options($options);
		$storage = $this->fileStorageResolver->resolve($fileUpload);

		$resource = $storage->createResourceFromFile(
			$storage->createPathInfo(sprintf('%s/%s', $helper->getNamespace(), $helper->getSourceName($fileUpload))),
			$fileUpload->getTemporaryFile()
		);

		$this->getEventDispatcher()->dispatch(new ResourceCreatedEvent($resource), ResourceCreatedEvent::NAME);

		$resourceMetadata = array_merge($this->resourceMetadataFactory->create($resource), $helper->getCustomMetadata());

		if (!isset($resourceMetadata[MetadataName::NAME])) {
			$resourceMetadata[MetadataName::NAME] = $fileUpload->getUntrustedName();
		}

		$storage->save($resource);

		$transaction = $this->transactionFactory->create(function (EntityManagerInterface $em, FileInfoInterface $fileInfo) use ($resourceMetadata): FileInterface {
			$file = $this->fileEntityFactory->create($fileInfo);

			$file->setMetadata($resourceMetadata);
			$em->persist($file);

			return $file;
		}, [
			'fileInfo' => $storage->createFileInfo($resource->getPathInfo()),
			'options' => $options,
		]);

		# External associations
		$associationStorage = $this->getExternalAssociationStorage();

		if (NULL !== $associationStorage) {
			$transaction->finally(static function (FileInterface $file) use ($associationStorage) {
				$associationStorage->getReferences()->add(new Reference((string) $file->getId()));
				$associationStorage->flush();
			});
		}

		$this->dispatchExtendTransactionEvent($transaction, $options);

		return $transaction->run();
	}
}
