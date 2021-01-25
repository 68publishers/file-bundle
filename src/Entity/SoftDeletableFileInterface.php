<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\Entity;

use DateTimeInterface;

interface SoftDeletableFileInterface extends FileInterface
{
	/**
	 * @param \DateTimeInterface|NULL $deletedAt
	 */
	public function setDeletedAt(?DateTimeInterface $deletedAt): void;

	/**
	 * @return \DateTime|NULL
	 */
	public function getDeletedAt(): ?DateTimeInterface;

	/**
	 * @return bool
	 */
	public function isDeleted(): bool;
}
