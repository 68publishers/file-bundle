<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\Storage\Manipulator\Sortable;

use Doctrine\ORM\EntityManagerInterface;
use SixtyEightPublishers\FileBundle\Entity\FileInterface;
use SixtyEightPublishers\FileBundle\Storage\Options\OptionsInterface;
use SixtyEightPublishers\DoctrinePersistence\TransactionFactoryInterface;

abstract class AbstractPersistentSortableManipulator extends AbstractSortableManipulator
{
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
	 * Return sorted file!
	 *
	 * @param \Doctrine\ORM\EntityManagerInterface                              $em
	 * @param \SixtyEightPublishers\FileBundle\Storage\Options\OptionsInterface $options
	 * @param \SixtyEightPublishers\FileBundle\Entity\FileInterface             $sortedFile
	 * @param \SixtyEightPublishers\FileBundle\Entity\FileInterface|NULL        $previousFile
	 * @param \SixtyEightPublishers\FileBundle\Entity\FileInterface|NULL        $nextFile
	 *
	 * @return \SixtyEightPublishers\FileBundle\Entity\FileInterface
	 */
	abstract public function doSortProcess(EntityManagerInterface $em, OptionsInterface $options, FileInterface $sortedFile, ?FileInterface $previousFile, ?FileInterface $nextFile): FileInterface;

	/**
	 * {@inheritDoc}
	 */
	public function doSort(OptionsInterface $options, FileInterface $sortedFile, ?FileInterface $previousFile, ?FileInterface $nextFile): bool
	{
		$transaction = $this->transactionFactory->create([$this, 'doSortProcess'], [
			'options' => $options,
			'sortedFile' => $sortedFile,
			'previousFile' => $previousFile,
			'nextFile' => $nextFile,
		]);

		$this->dispatchExtendTransactionEvent($transaction, $options);

		$transaction->run();

		return TRUE;
	}
}
