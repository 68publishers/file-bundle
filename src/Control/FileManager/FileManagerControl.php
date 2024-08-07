<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\Control\FileManager;

use Nette\Utils\Json;
use Nette\Http\FileUpload;
use Nette\Application\UI\Control;
use Doctrine\Common\Collections\Collection;
use SixtyEightPublishers\FileBundle\Helper\MaxFileSize;
use SixtyEightPublishers\FileBundle\Entity\FileInterface;
use SixtyEightPublishers\FileBundle\Event\FileUploadEvent;
use SixtyEightPublishers\FileBundle\Action\ActionInterface;
use SixtyEightPublishers\FileBundle\Event\ActionErrorEvent;
use SixtyEightPublishers\FileBundle\Event\FileUploadedEvent;
use SixtyEightPublishers\FileBundle\Event\ActionSuccessEvent;
use SixtyEightPublishers\FileBundle\Exception\UploadException;
use SixtyEightPublishers\FileBundle\Event\ResourceCreatedEvent;
use SixtyEightPublishers\FileBundle\Event\UploadCompletedEvent;
use SixtyEightPublishers\TranslationBridge\TranslatorAwareTrait;
use SixtyEightPublishers\FileBundle\Exception\ExceptionInterface;
use SixtyEightPublishers\FileBundle\Storage\DataStorageInterface;
use SixtyEightPublishers\FileBundle\Event\DropZoneControlSetupEvent;
use SixtyEightPublishers\FileBundle\Exception\InvalidStateException;
use SixtyEightPublishers\TranslationBridge\TranslatorAwareInterface;
use SixtyEightPublishers\FileBundle\Control\DropZone\DropZoneControl;
use SixtyEightPublishers\FileBundle\Exception\InvalidArgumentException;
use SixtyEightPublishers\EventDispatcherExtra\EventDispatcherAwareTrait;
use SixtyEightPublishers\FileBundle\Exception\FileManipulationException;
use SixtyEightPublishers\DoctrinePersistence\TransactionFactoryInterface;
use SixtyEightPublishers\DoctrinePersistence\Context\ErrorContextInterface;
use SixtyEightPublishers\EventDispatcherExtra\EventDispatcherAwareInterface;
use SixtyEightPublishers\FileBundle\ResourceValidator\ResourceValidatorInterface;
use SixtyEightPublishers\FileBundle\Control\DropZone\DropZoneControlFactoryInterface;
use SixtyEightPublishers\FileBundle\Storage\Manipulator\Save\SaveManipulatorInterface;
use SixtyEightPublishers\FileBundle\Storage\Manipulator\Delete\DeleteManipulatorInterface;
use SixtyEightPublishers\SmartNetteComponent\Bridge\Nette\Application\TemplateResolverTrait;
use SixtyEightPublishers\FileBundle\Storage\Manipulator\Sortable\SortableManipulatorInterface;

final class FileManagerControl extends Control implements TranslatorAwareInterface, EventDispatcherAwareInterface
{
	use TranslatorAwareTrait;
	use EventDispatcherAwareTrait;
	use TemplateResolverTrait;

	public const DIRECTION_TOP = 'top';
	public const DIRECTION_BOTTOM = 'bottom';

	public const DIRECTIONS = [
		self::DIRECTION_TOP,
		self::DIRECTION_BOTTOM,
	];

	/** @var \SixtyEightPublishers\FileBundle\Storage\DataStorageInterface  */
	private $storage;

	/** @var \SixtyEightPublishers\FileBundle\Control\DropZone\DropZoneControlFactoryInterface  */
	private $dropZoneControlFactory;

	/** @var \SixtyEightPublishers\DoctrinePersistence\TransactionFactoryInterface  */
	private $transactionFactory;

	/** @var int  */
	private $maxFileSize;

	/** @var int  */
	private $maxAllowedFiles = 100;

	/** @var \SixtyEightPublishers\FileBundle\Action\ActionInterface[]  */
	private $actions = [];

	/** @var array  */
	private $deleteExistingFileIfMaxAllowedReached = [ FALSE, self::DIRECTION_TOP ];

	/** @var \Doctrine\Common\Collections\Collection|\SixtyEightPublishers\FileBundle\Entity\FileInterface[]|NULL  */
	private $files;

	/**
	 * @param \SixtyEightPublishers\FileBundle\Storage\DataStorageInterface                     $dataStorage
	 * @param \SixtyEightPublishers\FileBundle\Control\DropZone\DropZoneControlFactoryInterface $dropZoneControlFactory
	 * @param \SixtyEightPublishers\DoctrinePersistence\TransactionFactoryInterface             $transactionFactory
	 */
	public function __construct(DataStorageInterface $dataStorage, DropZoneControlFactoryInterface $dropZoneControlFactory, TransactionFactoryInterface $transactionFactory)
	{
		$this->storage = $dataStorage;
		$this->dropZoneControlFactory = $dropZoneControlFactory;
		$this->transactionFactory = $transactionFactory;
		$this->maxFileSize = MaxFileSize::getDefault();
	}

	/**
	 * @param string $actionName
	 * @param string $id
	 *
	 * @return void
	 */
	public function handleDoAction(string $actionName, string $id): void
	{
		try {
			$file = $this->findFile($id);
			$action = $this->getValidAction($actionName, $file);

			$action->run($this->getStorage(), $file);
		} catch (ExceptionInterface $e) {
			$this->getEventDispatcher()->dispatch(new ActionErrorEvent($actionName, $e), ActionErrorEvent::NAME);

			return;
		}

		$this->redrawUpload();
		$this->redrawFiles();
		$this->getEventDispatcher()->dispatch(new ActionSuccessEvent($actionName, $file), ActionSuccessEvent::NAME);
	}

	/**
	 * @param string|NULL $sortedId
	 * @param string|NULL $previousId
	 * @param string|NULL $nextId
	 *
	 * @return void
	 * @throws \SixtyEightPublishers\FileBundle\Exception\InvalidStateException
	 */
	public function handleSort(?string $sortedId, ?string $previousId, ?string $nextId): void
	{
		if (NULL === $sortedId) {
			throw new InvalidStateException(sprintf(
				'Parameter $sortedId passed into %s must be type of string.',
				__METHOD__
			));
		}

		try {
			$this->storage->manipulate(
				SortableManipulatorInterface::class,
				$this->findFile($sortedId),
				NULL !== $previousId ? $this->findFile($previousId) : NULL,
				NULL !== $nextId ? $this->findFile($nextId) : NULL
			);
		} catch (ExceptionInterface $e) {
			$this->getEventDispatcher()->dispatch(new ActionErrorEvent('sort', $e), ActionErrorEvent::NAME);
			$this->redrawFiles();

			return;
		}
	}

	/**
	 * @param \SixtyEightPublishers\FileBundle\Action\ActionInterface $action
	 *
	 * @return \SixtyEightPublishers\FileBundle\Control\FileManager\FileManagerControl
	 * @throws \SixtyEightPublishers\FileBundle\Exception\InvalidArgumentException
	 */
	public function addAction(ActionInterface $action): self
	{
		if (!$action->isImplemented($this->storage)) {
			throw new InvalidArgumentException(sprintf(
				'Action %s (%s) can\'t be used with current DataStorage.',
				$action->getName(),
				get_class($action)
			));
		}

		$this->actions[] = $action;

		return $this;
	}

	/**
	 * @param \SixtyEightPublishers\FileBundle\ResourceValidator\ResourceValidatorInterface $resourceValidator
	 *
	 * @return \SixtyEightPublishers\FileBundle\Control\FileManager\FileManagerControl
	 */
	public function addResourceValidator(ResourceValidatorInterface $resourceValidator): self
	{
		/** @var \SixtyEightPublishers\FileBundle\Storage\Manipulator\Save\SaveManipulatorInterface $manipulator */
		$manipulator = $this->storage->getManipulator(SaveManipulatorInterface::class);

		$manipulator->getEventDispatcher()->addListener(ResourceCreatedEvent::NAME, static function (ResourceCreatedEvent $event) use ($resourceValidator) {
			$resourceValidator->validate($event->getResource());
		});

		return $this;
	}

	/**
	 * @return \SixtyEightPublishers\FileBundle\Storage\DataStorageInterface
	 */
	public function getStorage(): DataStorageInterface
	{
		return $this->storage;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @throws \Nette\Utils\JsonException
	 * @throws \Nette\Application\UI\InvalidLinkException
	 */
	public function render(): void
	{
		$maxFiles = $this->getMaxFiles();

		$this->template->setTranslator($this->getPrefixedTranslator());

		$this->template->originalTemplateFile = __DIR__ . '/templates/fileManagerControl.latte';
		$this->template->files = $this->getFiles();
		$this->template->maxAllowedFiles = $this->maxAllowedFiles;
		$this->template->uniqueId = $this->getUniqueId();
		$this->template->isSaveable = $this->storage->hasManipulator(SaveManipulatorInterface::class);
		$this->template->getActions = function (FileInterface $file) {
			return array_filter($this->actions, function (ActionInterface $action) use ($file) {
				return $action->isApplicableOnFile($file, $this->getStorage());
			});
		};
		$this->template->allowUpload = $allowUpload = (NULL === $maxFiles || $maxFiles > 0);
		$this->template->denyUpload = !$allowUpload;
		$this->template->sortable = $sortable = $this->storage->hasManipulator(SortableManipulatorInterface::class);

		if (TRUE === $sortable) {
			$this->template->sortableRequest = Json::encode([
				'endpoint' => $this->link('sort!'),
				'parameters' => [
					'sorted_id' => $this->getUniqueId() . '-sortedId',
					'previous_id' => $this->getUniqueId() . '-previousId',
					'next_id' => $this->getUniqueId() . '-nextId',
				],
			]);
		}

		$this->doRender();
	}

	/**
	 * @param int $maxAllowedFiles
	 *
	 * @return \SixtyEightPublishers\FileBundle\Control\FileManager\FileManagerControl
	 */
	public function setMaxAllowedFiles(int $maxAllowedFiles): self
	{
		$this->maxAllowedFiles = $maxAllowedFiles;

		return $this;
	}

	/**
	 * @param bool   $delete
	 * @param string $direction
	 *
	 * @return \SixtyEightPublishers\FileBundle\Control\FileManager\FileManagerControl
	 * @throws \SixtyEightPublishers\FileBundle\Exception\InvalidArgumentException
	 */
	public function setDeleteExistingFileIfMaxAllowedReached(bool $delete, string $direction = self::DIRECTION_TOP): self
	{
		if (TRUE === $delete) {
			# check manipulator only
			$this->getStorage()->getManipulator(DeleteManipulatorInterface::class);
		}

		if (!in_array($direction, self::DIRECTIONS, TRUE)) {
			throw new InvalidArgumentException(sprintf(
				'Direction %s is not supported.',
				$direction
			));
		}

		$this->deleteExistingFileIfMaxAllowedReached = [
			$delete,
			$direction,
		];

		return $this;
	}

	/**
	 * @param int|string $maxFileSize
	 *
	 * @return \SixtyEightPublishers\FileBundle\Control\FileManager\FileManagerControl
	 */
	public function setMaxFileSize($maxFileSize): self
	{
		$this->maxFileSize = MaxFileSize::parseBytes($maxFileSize);

		return $this;
	}

	/**
	 * @return \SixtyEightPublishers\FileBundle\Control\DropZone\DropZoneControl
	 * @throws \SixtyEightPublishers\FileBundle\Exception\InvalidStateException
	 */
	protected function createComponentDropZone(): DropZoneControl
	{
		if (!$this->storage->hasManipulator(SaveManipulatorInterface::class)) {
			throw new InvalidStateException(sprintf(
				'DataStorage must contains manipulator with type %s if you want to use DropZone.',
				SaveManipulatorInterface::class
			));
		}

		$dropZone = $this->dropZoneControlFactory->create();
		$translator = $this->getPrefixedTranslator();
		$dispatcher = $dropZone->getEventDispatcher();

		# defaults
		$dropZone->setSettings([
			'previewTemplate' => '', # we have custom list of files
			'previewsContainer' => FALSE,
			'createImageThumbnails' => FALSE,
			'maxFilesize' => $this->maxFileSize / 1024 / 1024,
			'dictFileTooBig' => $translator->translate('message.dict_file_too_big'),
			'dictInvalidFileType' => $translator->translate('message.dict_invalid_file_type'),
			'dictResponseError' => $translator->translate('message.dict_response_error'),
			'dictMaxFilesExceeded' => $translator->translate('message.dict_max_files_exceeded'),
		]);

		if (NULL !== ($maxFiles = $this->getMaxFiles())) {
			$dropZone->addSetting('maxFiles', $maxFiles);
		}

		$dispatcher->addListener(UploadCompletedEvent::NAME, function () {
			$this->redrawUpload();
		});

		$dispatcher->addListener(FileUploadEvent::NAME, function (FileUploadEvent $event) {
			$file = $this->uploadFile($event->getFileUpload());

			$this->redrawFiles();
			$this->getEventDispatcher()->dispatch(new FileUploadedEvent($file), FileUploadedEvent::NAME);
		});

		$this->getEventDispatcher()->dispatch(new DropZoneControlSetupEvent($dropZone), DropZoneControlSetupEvent::NAME);

		return $dropZone;
	}

	/**
	 * @param \Nette\Http\FileUpload $fileUpload
	 *
	 * @return \SixtyEightPublishers\FileBundle\Entity\FileInterface
	 * @throws \SixtyEightPublishers\FileBundle\Exception\FileManipulationException
	 * @throws \SixtyEightPublishers\FileBundle\Exception\UploadException
	 * @throws \Throwable
	 */
	private function uploadFile(FileUpload $fileUpload): FileInterface
	{
		if ($this->getFiles()->count() >= $this->maxAllowedFiles) {
			[$delete, $direction] = $this->deleteExistingFileIfMaxAllowedReached;

			if (FALSE === $delete) {
				throw UploadException::maximumFilesReached($this->maxAllowedFiles);
			}

			return $this->doFileUploadWithDeletion($fileUpload, $direction);
		}

		return $this->doFileUpload($fileUpload);
	}

	/**
	 * @param \Nette\Http\FileUpload $fileUpload
	 *
	 * @return \SixtyEightPublishers\FileBundle\Entity\FileInterface
	 * @throws \SixtyEightPublishers\FileBundle\Exception\FileManipulationException
	 * @throws \SixtyEightPublishers\FileBundle\Exception\ExceptionInterface
	 */
	private function doFileUpload(FileUpload $fileUpload): FileInterface
	{
		/** @var \SixtyEightPublishers\FileBundle\Storage\Manipulator\Save\SaveManipulatorInterface $manipulator */
		$manipulator = $this->storage->getManipulator(SaveManipulatorInterface::class);

		return $manipulator->manipulate($this->storage->getOptions(), $fileUpload);
	}

	/**
	 * @param \Nette\Http\FileUpload $fileUpload
	 * @param string                 $direction
	 *
	 * @return \SixtyEightPublishers\FileBundle\Entity\FileInterface
	 * @throws \SixtyEightPublishers\FileBundle\Exception\FileManipulationException
	 * @throws \Throwable
	 */
	private function doFileUploadWithDeletion(FileUpload $fileUpload, string $direction): FileInterface
	{
		$files = $this->getFiles();
		$fileForDelete = self::DIRECTION_TOP === $direction ? $files->first() : $files->last();

		if (!$fileForDelete instanceof FileInterface) {
			return $this->doFileUpload($fileUpload);
		}

		$transaction = $this->transactionFactory->create(function (FileUpload $fileUpload, FileInterface $fileForDelete) {
			$this->getStorage()->manipulate(DeleteManipulatorInterface::class, $fileForDelete);

			return $this->doFileUpload($fileUpload);
		});

		$transaction->error(static function (ErrorContextInterface $context) {
			if ($context->getError() instanceof ExceptionInterface) {
				$context->stopPropagation();

				return;
			}

			FileManipulationException::error('upload new and delete existing', 0, $context->getError());
		});

		return $transaction
			->withArguments([
				'fileUpload' => $fileUpload,
				'fileForDelete' => $fileForDelete,
			])
			->run();
	}

	/**
	 * @return void
	 */
	private function redrawUpload(): void
	{
		$this->redrawControl('upload');
	}

	/**
	 * @return void
	 */
	private function redrawFiles(): void
	{
		$this->files = NULL;

		$this->redrawControl('files');
	}

	/**
	 * @return \Doctrine\Common\Collections\Collection|\SixtyEightPublishers\FileBundle\Entity\FileInterface[]
	 */
	private function getFiles(): Collection
	{
		return $this->files ?: $this->files = $this->storage->getFiles();
	}

	/**
	 * @return int|NULL
	 */
	private function getMaxFiles(): ?int
	{
		if (NULL === $this->maxAllowedFiles) {
			return NULL;
		}

		if (TRUE === $this->deleteExistingFileIfMaxAllowedReached[0]) {
			return $this->maxAllowedFiles;
		}

		$max = $this->maxAllowedFiles - $this->getFiles()->count();

		return 0 < $max ? $max : 0;
	}

	/**
	 * @param string        $name
	 * @param FileInterface $file
	 *
	 * @return \SixtyEightPublishers\FileBundle\Action\ActionInterface
	 * @throws \SixtyEightPublishers\FileBundle\Exception\InvalidStateException
	 */
	private function getValidAction(string $name, FileInterface $file): ActionInterface
	{
		$found = NULL;
		foreach ($this->actions as $action) {
			if ($action->getName() === $name) {
				$found = $action;

				break;
			}
		}

		if (NULL === $found) {
			throw new InvalidStateException(sprintf(
				'Missing action with name %s.',
				$name
			));
		}

		if (!$found->isApplicableOnFile($file, $this->getStorage())) {
			throw new InvalidStateException(sprintf(
				'Action %s can\'t be used with current DataStorage.',
				$name
			));
		}

		return $found;
	}

	/**
	 * @param string $id
	 *
	 * @return \SixtyEightPublishers\FileBundle\Entity\FileInterface
	 * @throws \SixtyEightPublishers\FileBundle\Exception\InvalidStateException
	 */
	private function findFile(string $id): FileInterface
	{
		$file = $this->getFiles()->filter(static function (FileInterface $file) use ($id) {
			return (string) $file->getId() === $id;
		})->first();

		if (!$file instanceof FileInterface) {
			throw new InvalidStateException(sprintf(
				'File with ID %s not found in DataStorage.',
				$id
			));
		}

		return $file;
	}
}
