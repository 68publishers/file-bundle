<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\Storage;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use SixtyEightPublishers\FileBundle\Entity\FileInterface;
use SixtyEightPublishers\FileBundle\Storage\ExternalAssociation\Reference;
use SixtyEightPublishers\FileBundle\Storage\Manipulator\ManipulatorInterface;
use SixtyEightPublishers\FileBundle\Storage\Manipulator\ExternalAssociationStorageAwareInterface;
use SixtyEightPublishers\FileBundle\Storage\ExternalAssociation\ExternalAssociationStorageInterface;

final class ExternalAssociationDataStorage implements DataStorageInterface
{
	use DataStorageTrait {
		addManipulator as private _addManipulator;
	}

	/** @var \Doctrine\ORM\EntityManagerInterface  */
	private $em;

	/** @var \SixtyEightPublishers\FileBundle\Storage\ExternalAssociation\ExternalAssociationStorageInterface  */
	private $externalAssociationStorage;

	/** @var string  */
	private $entityClassName;

	/**
	 * @param \Doctrine\ORM\EntityManagerInterface                                                             $em
	 * @param \SixtyEightPublishers\FileBundle\Storage\ExternalAssociation\ExternalAssociationStorageInterface $externalAssociationStorage
	 * @param string|NULL                                                                                      $namespace
	 * @param string                                                                                           $entityClassName
	 */
	public function __construct(EntityManagerInterface $em, ExternalAssociationStorageInterface $externalAssociationStorage, ?string $namespace = NULL, string $entityClassName = FileInterface::class)
	{
		$this->em = $em;
		$this->externalAssociationStorage = $externalAssociationStorage;
		$this->entityClassName = $entityClassName;

		if (NULL !== $namespace) {
			$externalAssociationStorage->setNamespace($namespace);
		}
	}

	/**
	 * @return \SixtyEightPublishers\FileBundle\Storage\ExternalAssociation\ExternalAssociationStorageInterface
	 */
	public function getExternalAssociationStorage(): ExternalAssociationStorageInterface
	{
		return $this->externalAssociationStorage;
	}

	/**
	 * {@inheritdoc}
	 */
	public function addManipulator(ManipulatorInterface $manipulator): void
	{
		if ($manipulator instanceof ExternalAssociationStorageAwareInterface) {
			$manipulator->setExternalAssociationStorage($this->externalAssociationStorage);
		}

		$this->_addManipulator($manipulator);
	}

	/**
	 * {@inheritdoc}
	 *
	 * @throws \Doctrine\ORM\Mapping\MappingException
	 */
	public function getFiles(): Collection
	{
		$qb = $this->em->createQueryBuilder();
		$cm = $this->em->getClassMetadata($this->entityClassName);

		$ids = $this->externalAssociationStorage->getReferences()->map(static function (Reference $reference) {
			return $reference->getId();
		});

		if (0 >= count($ids)) {
			return new ArrayCollection();
		}

		$qb->select('i')
			->from($this->entityClassName, 'i')
			->where($qb->expr()->in('i.' . $cm->getSingleIdentifierFieldName(), $ids));

		$files = (new DoctrineDataStorage($qb))->getFiles()->toArray();

		usort($files, static function (FileInterface $first, FileInterface $second) use ($ids) {
			return array_search((string) $first->getId(), $ids, TRUE) - array_search((string) $second->getId(), $ids, TRUE);
		});

		return new ArrayCollection($files);
	}
}
