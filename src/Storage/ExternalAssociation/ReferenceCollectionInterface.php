<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\Storage\ExternalAssociation;

use Countable;
use IteratorAggregate;

interface ReferenceCollectionInterface extends Countable, IteratorAggregate
{
	/**
	 * @param \SixtyEightPublishers\FileBundle\Storage\ExternalAssociation\Reference $reference
	 *
	 * @return void
	 */
	public function add(Reference $reference): void;

	/**
	 * @param \SixtyEightPublishers\FileBundle\Storage\ExternalAssociation\Reference $reference
	 */
	public function prepend(Reference $reference): void;

	/**
	 * @param \SixtyEightPublishers\FileBundle\Storage\ExternalAssociation\Reference $reference
	 * @param \SixtyEightPublishers\FileBundle\Storage\ExternalAssociation\Reference $targetReference
	 *
	 * @return void
	 */
	public function moveBefore(Reference $reference, Reference $targetReference): void;

	/**
	 * @param \SixtyEightPublishers\FileBundle\Storage\ExternalAssociation\Reference $reference
	 * @param \SixtyEightPublishers\FileBundle\Storage\ExternalAssociation\Reference $targetReference
	 *
	 * @return void
	 */
	public function moveAfter(Reference $reference, Reference $targetReference): void;

	/**
	 * @param string $id
	 *
	 * @return \SixtyEightPublishers\FileBundle\Storage\ExternalAssociation\Reference|NULL
	 */
	public function find(string $id): ?Reference;

	/**
	 * @param \SixtyEightPublishers\FileBundle\Storage\ExternalAssociation\Reference $reference
	 *
	 * ￿@return void
	 */
	public function remove(Reference $reference): void;

	/**
	 * @param callable $callback
	 *
	 * @return array
	 */
	public function map(callable $callback): array;

	/**
	 * @return array
	 */
	public function toArray(): array;
}
