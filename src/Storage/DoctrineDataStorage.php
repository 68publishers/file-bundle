<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\Storage;

use Doctrine\ORM\QueryBuilder;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Common\Collections\ArrayCollection;
use SixtyEightPublishers\FileBundle\Entity\FileInterface;
use SixtyEightPublishers\FileBundle\Exception\InvalidStateException;

final class DoctrineDataStorage implements DataStorageInterface
{
	use DataStorageTrait;

	/** @var \Doctrine\ORM\QueryBuilder  */
	protected $qb;

	/**
	 * @param \Doctrine\ORM\QueryBuilder $qb
	 */
	public function __construct(QueryBuilder $qb)
	{
		$this->qb = $qb;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getFiles(): Collection
	{
		$collection = new ArrayCollection();

		foreach (new Paginator($this->qb->getQuery(), FALSE) as $file) {
			if (!$file instanceof FileInterface) {
				throw new InvalidStateException(sprintf(
					'Invalid entities returned from passed Query. Entities must be instances of interface %s, %s given.',
					FileInterface::class,
					get_class($file)
				));
			}

			$collection->add($file);
		}

		return $collection;
	}
}
