<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\Control\FileManager;

use Nette\Schema\Expect;
use Nette\Schema\Schema;
use Nette\Schema\Processor;
use Nette\DI\Definitions\Statement;
use SixtyEightPublishers\FileBundle\Action\ActionInterface;
use SixtyEightPublishers\FileBundle\Storage\ArrayDataStorage;
use SixtyEightPublishers\FileBundle\Storage\DataStorageInterface;
use SixtyEightPublishers\FileBundle\Exception\InvalidArgumentException;
use SixtyEightPublishers\FileBundle\Storage\Manipulator\ManipulatorInterface;
use SixtyEightPublishers\FileBundle\ResourceValidator\ResourceValidatorInterface;

final class Configuration
{
	/** @var array  */
	private $config;

	/**
	 * @param array $options
	 */
	public function __construct(array $options = [])
	{
		$this->config = (new Processor())->process(self::getSchema(), $options);
	}

	/**
	 * @param bool $forExtension
	 *
	 * @return \Nette\Schema\Schema
	 */
	public static function getSchema(bool $forExtension = FALSE): Schema
	{
		static $schema = [];

		$key = (int) $forExtension;

		if (isset($schema[$key])) {
			return $schema[$key];
		}

		$items = [
			'storage' => Expect::structure([
				'class_name' => Expect::string(ArrayDataStorage::class)->assert(static function (string $className) {
					return is_subclass_of($className, DataStorageInterface::class, TRUE);
				}),
				'arguments' => Expect::array([]),
				'options' => Expect::array([]),
			])->castTo('array'),
			'template' => Expect::string()->nullable(),
			'max_allowed_files' => Expect::int()->nullable(),
			'max_allowed_files_reached' => Expect::structure([
				'delete' => Expect::bool(FALSE),
				'direction' => Expect::anyOf(...FileManagerControl::DIRECTIONS)->default(FileManagerControl::DIRECTION_TOP),
			])->castTo('array'),
			'max_file_size' => Expect::int()->nullable(),
			'dropzone' => Expect::structure([
				'id' => Expect::string()->nullable(),
				'template' => Expect::string()->nullable(),
				'content_html' => Expect::array([]),
				'settings' => Expect::array([]),
				'extensions' => Expect::array([]),
			])->castTo('array'),
		];

		if ($forExtension) {
			$normalizeStatementList = static function (array $items) {
				return array_map(static function ($item) {
					return $item instanceof Statement ? $item : new Statement($item);
				}, $items);
			};

			$items['manipulators'] = Expect::listOf('string|' . Statement::class)->before($normalizeStatementList);
			$items['actions'] = Expect::listOf('string|' . Statement::class)->before($normalizeStatementList);
			$items['resource_validators'] = Expect::listOf('string|' . Statement::class)->before($normalizeStatementList);
			$items['extends'] = Expect::string()->nullable();
		} else {
			$items['manipulators'] = Expect::listOf(ManipulatorInterface::class);
			$items['actions'] = Expect::listOf(ActionInterface::class);
			$items['resource_validators'] = Expect::listOf(ResourceValidatorInterface::class);
		}

		return $schema[$key] = Expect::structure($items)->castTo('array');
	}

	/**
	 * @param string $key
	 *
	 * @return mixed
	 * @throws \SixtyEightPublishers\FileBundle\Exception\InvalidArgumentException
	 */
	public function get(string $key)
	{
		$keys = explode('.', $key);
		$val = $this->config;

		foreach ($keys as $k) {
			if (!array_key_exists($k, $val)) {
				throw new InvalidArgumentException(sprintf(
					'Configuration doesn\'t contains key %s',
					$key
				));
			}

			$val = $val[$k];
		}

		return $val;
	}
}
