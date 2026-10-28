<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\Entity;

use DateTime;
use DateTimeZone;
use Ramsey\Uuid\Uuid;
use DateTimeInterface;
use Nette\Utils\Random;
use Ramsey\Uuid\UuidInterface;
use Doctrine\ORM\Mapping as ORM;
use SixtyEightPublishers\FileStorage\FileInfoInterface;

#[ORM\MappedSuperclass]
#[ORM\HasLifecycleCallbacks]
abstract class AbstractFile implements FileInterface
{
	/** @var \Ramsey\Uuid\UuidInterface */
	#[ORM\Id]
	#[ORM\Column(type: 'uuid', unique: TRUE)]
	protected $id;

	/** @var \DateTime */
	#[ORM\Column(type: 'datetime')]
	protected $created;

	/** @var \DateTime */
	#[ORM\Column(type: 'datetime')]
	protected $updated;

	/** @var \SixtyEightPublishers\FileStorage\FileInfoInterface */
	#[ORM\Column(type: 'file_info')]
	protected $source;

	/** @var array */
	#[ORM\Column(type: 'json', options: ['jsonb' => TRUE])]
	protected $metadata = [];

	/**
	 * @param \SixtyEightPublishers\FileStorage\FileInfoInterface $fileInfo
	 * @param \Ramsey\Uuid\UuidInterface|null                     $uuid
	 *
	 * @throws \Exception
	 */
	public function __construct(FileInfoInterface $fileInfo, ?UuidInterface $uuid = NULL)
	{
		$this->id = $uuid ?? Uuid::uuid4();
		$this->created = new DateTime('now', new DateTimeZone('UTC'));
		$this->updated = new DateTime('now', new DateTimeZone('UTC'));

		$this->setSource($fileInfo->withVersion($this->createSourceVersion($this->created)));
	}

	/**
	 * @internal
	 *
	 * @return void
	 * @throws \Exception
	 */
	#[ORM\PreUpdate]
	public function onPreUpdate(): void
	{
		$this->update();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getId(): UuidInterface
	{
		return $this->id;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getSource(): FileInfoInterface
	{
		return $this->source;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getCreated(): DateTimeInterface
	{
		return $this->created;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getMetadata(?string $key = NULL, $default = NULL)
	{
		if (NULL === $key) {
			return $this->metadata;
		}

		return $this->metadata[$key] ?? $default;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setMetadata(array $metadata): void
	{
		$this->metadata = $metadata;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @throws \Exception
	 */
	public function update(): void
	{
		$this->updated = new DateTime('now', new DateTimeZone('UTC'));
		$this->setSource($this->getSource()->withVersion($this->createSourceVersion($this->updated)));
	}

	/**
	 * @param \SixtyEightPublishers\FileStorage\FileInfoInterface $fileInfo
	 *
	 * @return void
	 */
	public function setSource(FileInfoInterface $fileInfo): void
	{
		$this->source = $fileInfo;
	}

	/**
	 * @return \DateTime
	 */
	public function getUpdated(): DateTime
	{
		return $this->updated;
	}

	/**
	 * @param \DateTime $dateTime
	 *
	 * @return string
	 */
	private function createSourceVersion(DateTime $dateTime): string
	{
		$version = $dateTime->format('HyidsM') . Random::generate(4, 'a-zA-Z0-9');

		return rtrim(strtr(base64_encode($version), '+/', '-_'), '=');
	}
}
