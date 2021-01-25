<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\Entity;

use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * (!) ADD THIS ANNOTATION INTO YOUR ENTITY:
 *
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
trait SoftDeletableFileTrait
{
	/**
	 * @var \DateTime|NULL
	 *
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	protected $deletedAt;

	/**
	 * @param \DateTimeInterface|NULL $deletedAt
	 *
	 * @return void
	 */
	public function setDeletedAt(?DateTimeInterface $deletedAt): void
	{
		$this->deletedAt = $deletedAt;
	}

	/**
	 * @return \DateTimeInterface|NULL
	 */
	public function getDeletedAt(): ?DateTimeInterface
	{
		return $this->deletedAt;
	}

	/**
	 * @return bool
	 */
	public function isDeleted(): bool
	{
		return NULL !== $this->deletedAt;
	}
}
