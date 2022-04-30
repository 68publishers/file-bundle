<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\EventSubscriber;

use Nette\Application\IPresenter;
use Nette\Application\Application;
use Nette\Application\UI\Presenter;
use SixtyEightPublishers\NotificationBundle\Notifier;
use SixtyEightPublishers\FileBundle\Event\ActionErrorEvent;
use SixtyEightPublishers\FileBundle\Event\UploadErrorEvent;
use SixtyEightPublishers\FileBundle\Event\ActionSuccessEvent;
use SixtyEightPublishers\NotificationBundle\INotifierFactory;
use SixtyEightPublishers\FileBundle\Event\UploadCompletedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use SixtyEightPublishers\FileBundle\Exception\ExceptionInterface;
use SixtyEightPublishers\FileBundle\Exception\InvalidStateException;
use SixtyEightPublishers\FileBundle\Exception\TranslatableException;
use SixtyEightPublishers\NotificationBundle\Notification\Notification;
use SixtyEightPublishers\FileBundle\Control\FileManager\FileManagerControl;
use SixtyEightPublishers\NotificationBundle\Notification\NotificationBuilder;

final class NotificationEventSubscriber implements EventSubscriberInterface
{
	/** @var \SixtyEightPublishers\NotificationBundle\INotifierFactory  */
	private $notifierFactory;

	/** @var \Nette\Application\IPresenter|\Nette\Application\UI\Presenter|NULL */
	private $presenter;

	/** @var string  */
	private $notificationEndpoint = Notification::ENDPOINT_TOASTR;

	/** @var string|NULL */
	private $prefix;

	/** @var \SixtyEightPublishers\NotificationBundle\Notifier|NULL */
	private $notifier;

	/** @var array  */
	private $disabledActions = [
		'success' => [],
		'error' => [],
	];

	/**
	 * @param \SixtyEightPublishers\NotificationBundle\INotifierFactory $notifierFactory
	 * @param \Nette\Application\Application                            $application
	 */
	public function __construct(INotifierFactory $notifierFactory, Application $application)
	{
		$this->notifierFactory = $notifierFactory;
		$this->presenter = $application->getPresenter();

		$application->onPresenter[] = function ($_, IPresenter $presenter) {
			$this->presenter = $presenter;
		};
	}

	/**
	 * {@inheritdoc}
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			UploadCompletedEvent::NAME => 'onUploadCompleted',
			UploadErrorEvent::NAME => 'onUploadError',
			ActionSuccessEvent::NAME => 'onActionSuccess',
			ActionErrorEvent::NAME => 'onActionError',
		];
	}

	/**
	 * @param \SixtyEightPublishers\FileBundle\Event\UploadCompletedEvent $event
	 *
	 * @return void
	 *@internal
	 *
	 */
	public function onUploadCompleted(UploadCompletedEvent $event): void
	{
		if (!$this->isUiPresenter()) {
			return;
		}

		$this->getNotifier()
			->success('upload_completed', $event->getFilesCount())
			->schedule($this->notificationEndpoint);

		$this->redrawMessages();
	}

	/**
	 * @param \SixtyEightPublishers\FileBundle\Event\UploadErrorEvent $event
	 *
	 * @return void
	 *@internal
	 *
	 */
	public function onUploadError(UploadErrorEvent $event): void
	{
		if (!$this->isUiPresenter()) {
			return;
		}

		$this->createErrorNotificationBuilder('upload_error', $event->getException())
			->schedule($this->notificationEndpoint);

		$this->redrawMessages();
	}

	/**
	 * @param \SixtyEightPublishers\FileBundle\Event\ActionSuccessEvent $event
	 *
	 * @return void
	 *@internal
	 *
	 */
	public function onActionSuccess(ActionSuccessEvent $event): void
	{
		if (!$this->isUiPresenter()) {
			return;
		}

		if (in_array($event->getActionName(), $this->disabledActions['success'], TRUE)) {
			return;
		}

		$this->getNotifier()
			->success('action_success.' . $event->getActionName())
			->schedule($this->notificationEndpoint);

		$this->redrawMessages();
	}

	/**
	 * @param \SixtyEightPublishers\FileBundle\Event\ActionErrorEvent $event
	 *
	 * @return void
	 *@internal
	 *
	 */
	public function onActionError(ActionErrorEvent $event): void
	{
		if (!$this->isUiPresenter()) {
			return;
		}

		if (in_array($event->getActionName(), $this->disabledActions['error'], TRUE)) {
			return;
		}

		$this->createErrorNotificationBuilder('action_error.' . $event->getActionName(), $event->getException())
			->schedule($this->notificationEndpoint);

		$this->redrawMessages();
	}

	/**
	 * @param string $prefix
	 *
	 * @return \SixtyEightPublishers\FileBundle\EventSubscriber\NotificationEventSubscriber
	 */
	public function setPrefix(string $prefix): self
	{
		$this->prefix = $prefix;
		$this->notifier = NULL;

		return $this;
	}

	/**
	 * @param string $notificationEndpoint
	 *
	 * @return \SixtyEightPublishers\FileBundle\EventSubscriber\NotificationEventSubscriber
	 */
	public function setNotificationEndpoint(string $notificationEndpoint): self
	{
		$this->notificationEndpoint = $notificationEndpoint;

		return $this;
	}

	/**
	 * @param array $success
	 * @param array $error
	 *
	 * @return \SixtyEightPublishers\FileBundle\EventSubscriber\NotificationEventSubscriber
	 */
	public function disableActions(array $success, array $error = []): self
	{
		$this->disabledActions = [
			'success' => $success,
			'error' => $error,
		];

		return $this;
	}

	/**
	 * @return \SixtyEightPublishers\NotificationBundle\Notifier
	 */
	private function getNotifier(): Notifier
	{
		if (NULL !== $this->notifier) {
			return $this->notifier;
		}

		return $this->notifier = $this->notifierFactory->create(
			$this->prefix ?? (str_replace('\\', '_', FileManagerControl::class) . '.message')
		);
	}

	/**
	 * @return void
	 * @throws \SixtyEightPublishers\FileBundle\Exception\InvalidStateException
	 */
	private function redrawMessages(): void
	{
		if (NULL === $this->presenter) {
			throw new InvalidStateException('Current Presenter is not set.');
		}

		if (!$this->isUiPresenter() || !$this->presenter->isAjax()) {
			return;
		}

		# @todo: implement some interface ...
		if (!is_callable([$this->presenter, 'redrawMessages'])) {
			trigger_error(sprintf(
				'Presenter %s does\'t implement method ::redrawMessages(), notifications can\'t be redrawn.',
				get_class($this->presenter)
			));
		}

		/** @noinspection PhpUndefinedMethodInspection */
		$this->presenter->redrawMessages();
	}

	/**
	 * @return bool
	 */
	private function isUiPresenter(): bool
	{
		return $this->presenter instanceof Presenter;
	}

	/**
	 * @param string                                                        $messageBase
	 * @param \SixtyEightPublishers\FileBundle\Exception\ExceptionInterface $e
	 *
	 * @return \SixtyEightPublishers\NotificationBundle\Notification\NotificationBuilder
	 */
	private function createErrorNotificationBuilder(string $messageBase, ExceptionInterface $e): NotificationBuilder
	{
		$notifier = $this->getNotifier();

		if ($e instanceof TranslatableException || $e->getPrevious() instanceof TranslatableException) {
			$translatableException = $e instanceof TranslatableException ? $e : $e->getPrevious();
			assert($translatableException instanceof TranslatableException);

			return $notifier->error($messageBase . '.' . $translatableException->getMessage(), $translatableException->getArgs());
		}

		return $notifier->error($messageBase . '.default', [
			'code' => $e->getCode(),
			'message' => $e->getMessage(),
		]);
	}
}
