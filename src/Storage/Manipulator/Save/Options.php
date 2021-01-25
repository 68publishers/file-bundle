<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\Storage\Manipulator\Save;

use Ramsey\Uuid\Uuid;
use Nette\Utils\Strings;
use Nette\Http\FileUpload;
use SixtyEightPublishers\FileBundle\Storage\Options\OptionsInterface;

final class Options
{
	public const NAMESPACE = 'save_manipulator.namespace';
	public const SOURCE_NAME_CALLBACK = 'save_manipulator.source_name_callback';
	public const CUSTOM_RESOURCE_METADATA = 'save_manipulator.custom_resource_metadata';

	/** @var \SixtyEightPublishers\FileBundle\Storage\Options\OptionsInterface  */
	private $options;

	/**
	 * @param \SixtyEightPublishers\FileBundle\Storage\Options\OptionsInterface $options
	 */
	public function __construct(OptionsInterface $options)
	{
		$this->options = $options;
	}

	/**
	 * @return string
	 */
	public function getNamespace(): string
	{
		return $this->options->has(self::NAMESPACE, 'string') ? $this->options->get(self::NAMESPACE) : '';
	}

	/**
	 * @param \Nette\Http\FileUpload $fileUpload
	 *
	 * @return string
	 * @throws \Exception
	 */
	public function getSourceName(FileUpload $fileUpload): string
	{
		if ($this->options->has(self::SOURCE_NAME_CALLBACK, 'callable')) {
			$cb = $this->options->get(self::SOURCE_NAME_CALLBACK);

			return (string) $cb($fileUpload);
		}

		$extension = Strings::lower(pathinfo($fileUpload->getUntrustedName(), PATHINFO_EXTENSION));

		return Uuid::uuid4()->toString() . (empty($extension) ? '' : '.' . $extension);
	}

	/**
	 * @return array
	 */
	public function getCustomMetadata(): array
	{
		return $this->options->has(self::CUSTOM_RESOURCE_METADATA, 'array') ? $this->options->get(self::CUSTOM_RESOURCE_METADATA) : [];
	}
}
