<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\Control\DropZone;

use Nette\Utils\Json;
use Nette\Http\IRequest;
use Nette\Utils\Strings;
use Nette\Http\IResponse;
use Nette\Http\FileUpload;
use Nette\Utils\IHtmlString;
use SixtyEightPublishers\SmartNetteComponent\UI\Control;
use SixtyEightPublishers\FileBundle\Event\FileUploadEvent;
use SixtyEightPublishers\FileBundle\Event\UploadErrorEvent;
use SixtyEightPublishers\FileBundle\Exception\UploadException;
use SixtyEightPublishers\FileBundle\Event\UploadCompletedEvent;
use SixtyEightPublishers\TranslationBridge\TranslatorAwareTrait;
use SixtyEightPublishers\FileBundle\Exception\ExceptionInterface;
use SixtyEightPublishers\TranslationBridge\TranslatorAwareInterface;
use SixtyEightPublishers\FileBundle\Exception\InvalidArgumentException;
use SixtyEightPublishers\EventDispatcherExtra\EventDispatcherAwareTrait;
use SixtyEightPublishers\EventDispatcherExtra\EventDispatcherAwareInterface;

final class DropZoneControl extends Control implements TranslatorAwareInterface, EventDispatcherAwareInterface
{
	use TranslatorAwareTrait;
	use EventDispatcherAwareTrait;

	/** @var \Nette\Http\IRequest  */
	private $request;

	/** @var \Nette\Http\IResponse  */
	private $response;

	/** @var array  */
	private $settings = [];

	/** @var string[]  */
	private $acceptedMimeTypes = [];

	/** @var string[]  */
	private $acceptedExtensions = [];

	/** @var NULL|string */
	private $dropZoneId;

	/** @var \Nette\Utils\IHtmlString[] */
	private $contentHtml = [];

	/** @var array */
	private $extensions = [];

	/**
	 * @param \Nette\Http\IRequest  $request
	 * @param \Nette\Http\IResponse $response
	 */
	public function __construct(IRequest $request, IResponse $response)
	{
		$this->request = $request;
		$this->response = $response;
	}

	/**
	 * @internal
	 *
	 * @return void
	 */
	public function handleUpload(): void
	{
		try {
			$file = $this->request->getFile('file');

			if (!$file instanceof FileUpload || !$file->isOk()) {
				throw UploadException::invalidFileUpload();
			}

			if (!$this->isUploadAcceptable($file)) {
				throw UploadException::unsupportedType($file->getContentType());
			}

			$this->eventDispatcher->dispatch(new FileUploadEvent($file), FileUploadEvent::NAME);
		} catch (ExceptionInterface $e) {
			$this->eventDispatcher->dispatch(new UploadErrorEvent($e), UploadErrorEvent::NAME);
			$this->response->setCode(IResponse::S406_NOT_ACCEPTABLE);
		}
	}

	/**
	 * @internal
	 *
	 * @param int|NULL $filesCount
	 *
	 * @return void
	 */
	public function handleCompleted(?int $filesCount = NULL): void
	{
		$this->eventDispatcher->dispatch(new UploadCompletedEvent($filesCount), UploadCompletedEvent::NAME);
	}

	/**
	 * @internal
	 *
	 * @return void
	 * @throws \Nette\Application\UI\InvalidLinkException
	 * @throws \Nette\Utils\JsonException
	 */
	public function render(): void
	{
		$this->template->setTranslator($this->getPrefixedTranslator());

		$this->template->settings = Json::encode(array_merge([
			'url' => $this->link('upload!'),
		], $this->settings));

		$this->template->extensions = Json::encode(array_merge($this->getDefaultExtensions(), $this->extensions));
		$this->template->contentHtml = $this->contentHtml;
		$this->template->dropzoneId = $this->dropZoneId ?? ($this->getUniqueId() . '--dropzone');

		$this->doRender();
	}

	/**
	 * @param \Nette\Utils\IHtmlString $htmlString
	 *
	 * @return \SixtyEightPublishers\FileBundle\Control\DropZone\DropZoneControl
	 */
	public function addContentHtml(IHtmlString $htmlString): self
	{
		if ($htmlString instanceof Content\TranslatableHtmlStringInterface) {
			$htmlString->setTranslator($this->getPrefixedTranslator());
		}

		if ($htmlString instanceof Content\ProgressBar) {
			$this->addExtension('progressbar');
		}

		$this->contentHtml[] = $htmlString;

		return $this;
	}

	/**
	 * @param array $settings
	 *
	 * @return \SixtyEightPublishers\FileBundle\Control\DropZone\DropZoneControl
	 */
	public function setSettings(array $settings): self
	{
		foreach ($settings as $key => $setting) {
			$this->addSetting($key, $setting);
		}

		return $this;
	}

	/**
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return \SixtyEightPublishers\FileBundle\Control\DropZone\DropZoneControl
	 */
	public function addSetting(string $key, $value): self
	{
		if ($key === 'acceptedFiles') {
			$this->setAccepted(is_array($value) ? $value : explode(',', (string) $value));
		} else {
			$this->settings[$key] = $value;
		}

		return $this;
	}

	/**
	 * @param array $accepted
	 *
	 * @return \SixtyEightPublishers\FileBundle\Control\DropZone\DropZoneControl
	 */
	public function setAccepted(array $accepted): self
	{
		$this->acceptedExtensions = $this->acceptedMimeTypes = [];

		foreach ($accepted as $accept) {
			if (preg_match('~^[-\w\+\*]+/[-\w\+\*]+$~', $accept)) {
				$this->acceptedMimeTypes[] = $accept;
			} elseif (preg_match('~^\.[\w\.]+$~', $accept)) {
				$this->acceptedExtensions[] = $accept;
			} else {
				throw new InvalidArgumentException(sprintf(
					'Accepted file type "%s" is not valid mime-type or file extension.',
					$accept
				));
			}
		}

		$this->settings['acceptedFiles'] = implode(',', $accepted);

		return $this;
	}

	/**
	 * @param string $dropZoneId
	 *
	 * @return \SixtyEightPublishers\FileBundle\Control\DropZone\DropZoneControl
	 */
	public function setDropZoneId(string $dropZoneId): self
	{
		$this->dropZoneId = $dropZoneId;

		return $this;
	}

	/**
	 * @param string $name
	 * @param array  $options
	 *
	 * @return \SixtyEightPublishers\FileBundle\Control\DropZone\DropZoneControl
	 */
	public function addExtension(string $name, array $options = []): self
	{
		$this->extensions[$name] = $options;

		return $this;
	}

	/**
	 * @param \Nette\Http\FileUpload $upload
	 *
	 * @return bool
	 */
	private function isUploadAcceptable(FileUpload $upload): bool
	{
		if (empty($this->acceptedMimeTypes) && empty($this->acceptedExtensions)) {
			return TRUE;
		}

		if (is_string($mime = $upload->getContentType())) {
			[ $mimeStart, $mimeEnd ] = explode('/', $mime);
			$acceptedForms = [ $mime, '*/' . $mimeStart, $mimeEnd . '/*' ];

			foreach ($this->acceptedMimeTypes as $acceptedMimeType) {
				if (in_array($acceptedMimeType, $acceptedForms, TRUE)) {
					return TRUE;
				}
			}
		}

		$name = $upload->getUntrustedName();

		foreach ($this->acceptedExtensions as $acceptedExtension) {
			if (Strings::endsWith($name, $acceptedExtension)) {
				return TRUE;
			}
		}

		return FALSE;
	}

	/**
	 * @return array
	 * @throws \Nette\Application\UI\InvalidLinkException
	 */
	private function getDefaultExtensions(): array
	{
		return [
			'completed_signal' => [
				'count_parameter' => $this->getUniqueId() . '-filesCount',
				'url' => $this->link('completed!'),
			],
		];
	}
}
