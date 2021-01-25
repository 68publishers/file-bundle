<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\EventSubscriber;

use SplQueue;
use Doctrine\ORM\Events;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use SixtyEightPublishers\FileBundle\Entity\FileInterface;
use SixtyEightPublishers\FileStorage\FileStorageProviderInterface;
use SixtyEightPublishers\DoctrinePersistence\Helper\TransactionHelper;
use SixtyEightPublishers\FileBundle\Entity\SoftDeletableFileInterface;

final class DeleteFileSourceEventSubscriber implements EventSubscriber
{
	/** @var \SixtyEightPublishers\FileStorage\FileStorageProviderInterface  */
	private $fileStorageProvider;

	/** @var \SplQueue  */
	private $queue;

	/**
	 * @param \SixtyEightPublishers\FileStorage\FileStorageProviderInterface $fileStorageProvider
	 */
	public function __construct(FileStorageProviderInterface $fileStorageProvider)
	{
		$this->fileStorageProvider = $fileStorageProvider;
		$this->queue = new SplQueue();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getSubscribedEvents(): array
	{
		return [
			Events::onFlush,
			Events::postFlush,
		];
	}

	/**
	 * @internal
	 *
	 * @param \Doctrine\ORM\Event\OnFlushEventArgs $args
	 *
	 * @return void
	 */
	public function onFlush(OnFlushEventArgs $args): void
	{
		$em = $args->getEntityManager();
		$uow = $em->getUnitOfWork();

		foreach ($uow->getScheduledEntityDeletions() as $entity) {
			if ($entity instanceof FileInterface && !$entity instanceof SoftDeletableFileInterface) {
				$this->queue->enqueue($entity);
			}
		}
	}

	/**
	 * @internal
	 *
	 * @param \Doctrine\ORM\Event\PostFlushEventArgs $args
	 *
	 * @return void
	 * @throws \SixtyEightPublishers\FileStorage\Exception\FilesystemException
	 */
	public function postFlush(PostFlushEventArgs $args): void
	{
		if (!TransactionHelper::isEverythingCommitted($args->getEntityManager())) {
			return;
		}

		while (!$this->queue->isEmpty()) {
			/** @var \SixtyEightPublishers\FileBundle\Entity\FileInterface $file */
			$file = $this->queue->dequeue();
			$source = $file->getSource();

			$this->fileStorageProvider->get($source->getStorageName())->delete($source);
		}
	}
}
