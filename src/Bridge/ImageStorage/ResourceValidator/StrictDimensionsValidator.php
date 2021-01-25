<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\Bridge\ImageStorage\ResourceValidator;

use Intervention\Image\Image;
use SixtyEightPublishers\FileStorage\Resource\ResourceInterface;
use SixtyEightPublishers\FileBundle\Exception\TranslatableException;
use SixtyEightPublishers\FileBundle\Exception\FileManipulationException;
use SixtyEightPublishers\FileBundle\ResourceValidator\ResourceValidatorInterface;

final class StrictDimensionsValidator implements ResourceValidatorInterface
{
	/** @var string[]  */
	private $allowedDimensions;

	/**
	 * Use these formats:
	 *      - "600x300" => width 600 and height 300
	 *      - "600x" => width 600, arbitrary height
	 *      - "x600" => arbitrary width, height 600
	 *
	 * @param array $allowedDimensions
	 */
	public function __construct(array $allowedDimensions)
	{
		$this->allowedDimensions = $allowedDimensions;
	}

	/**
	 * {@inheritDoc}
	 */
	public function validate(ResourceInterface $resource): void
	{
		$source = $resource->getSource();

		if (!$source instanceof Image) {
			return;
		}

		$width = $source->width();
		$height = $source->height();

		$dimensions = [
			$width . 'x',
			'x' . $height,
			$width . 'x' . $height,
		];

		if (0 < count(array_intersect($dimensions, $this->allowedDimensions))) {
			return;
		}

		$args = [
			'width' => $width,
			'height' => $height,
			'allowed' => implode(', ', $this->allowedDimensions),
		];

		throw new TranslatableException(
			'strict_dimensions_validator',
			$args,
			0,
			FileManipulationException::error('validation - strict dimensions')
		);
	}
}
