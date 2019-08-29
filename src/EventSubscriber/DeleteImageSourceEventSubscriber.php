<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\EventSubscriber;

use Nette;
use Doctrine;
use SixtyEightPublishers;

final class DeleteImageSourceEventSubscriber implements Doctrine\Common\EventSubscriber
{
	use Nette\SmartObject;

	/** @var \SixtyEightPublishers\ImageStorage\IImageStorageProvider  */
	private $imageStorageProvider;

	/** @var \SplQueue  */
	private $queue;

	/**
	 * @param \SixtyEightPublishers\ImageStorage\IImageStorageProvider $imageStorageProvider
	 */
	public function __construct(SixtyEightPublishers\ImageStorage\IImageStorageProvider $imageStorageProvider)
	{
		$this->imageStorageProvider = $imageStorageProvider;
		$this->queue = new \SplQueue();
	}

	/**
	 * {@inheritdoc}
	 */
	public function onFlush(Doctrine\ORM\Event\OnFlushEventArgs $args): void
	{
		$em = $args->getEntityManager();
		$uow = $em->getUnitOfWork();

		foreach ($uow->getScheduledEntityDeletions() as $entity) {
			if ($entity instanceof SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage && !$entity instanceof SixtyEightPublishers\ImageBundle\DoctrineEntity\ISoftDeletableImage) {
				$this->queue->enqueue($entity);
			}
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function postFlush(): void
	{
		while (!$this->queue->isEmpty()) {
			/** @var \SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage $image */
			$image = $this->queue->dequeue();
			$source = $image->getSource();

			$this->imageStorageProvider->get($source->getStorageName())->delete($source);
		}
	}

	/***************** interface \Doctrine\Common\EventSubscriber *****************/

	/**
	 * {@inheritdoc}
	 */
	public function getSubscribedEvents(): array
	{
		return [
			Doctrine\ORM\Events::onFlush,
			Doctrine\ORM\Events::postFlush,
		];
	}
}