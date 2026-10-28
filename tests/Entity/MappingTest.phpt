<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\Tests\Entity;

use Tester\Assert;
use ReflectionMethod;
use Doctrine\ORM\Events;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\DBAL\DriverManager;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Types\GuidType;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Gedmo\SoftDeleteable\SoftDeleteableListener;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Mapping\UnderscoreNamingStrategy;
use Doctrine\ORM\Tools\ResolveTargetEntityListener;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use SixtyEightPublishers\FileBundle\Entity\AbstractFile;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;
use SixtyEightPublishers\FileBundle\DI\FileBundleExtension;
use SixtyEightPublishers\FileBundle\Entity\Basic\File as BasicFile;
use SixtyEightPublishers\DoctrineBridge\Bridge\Nette\DI\EntityMapping;
use SixtyEightPublishers\FileStorage\Bridge\Doctrine\DbalType\FileInfoType;
use SixtyEightPublishers\FileBundle\Entity\SoftDeletable\File as SoftDeletableFile;

require __DIR__ . '/../bootstrap.php';

if (!Type::hasType('uuid')) {
	Type::addType('uuid', GuidType::class);
}

if (!Type::hasType('file_info')) {
	Type::addType('file_info', FileInfoType::class);
}

/**
 * The package entities (attributes, registered like doctrine-bridge does) + the application entities from tests/Fixtures/<$appMapping>.
 */
function createEntityManager(string $entity, string $appMapping, object ...$eventSubscribers): EntityManager
{
	$extension = new FileBundleExtension();
	$extension->setConfig((object) ['entity' => $entity, 'file_managers' => []]);

	$chain = new MappingDriverChain();
	$attributeDriver = new AttributeDriver([], TRUE);

	foreach ($extension->getEntityMappings() as $mapping) {
		Assert::same(EntityMapping::DRIVER_ATTRIBUTE, $mapping->driver);
		$attributeDriver->addPaths([$mapping->path]);
		$chain->addDriver($attributeDriver, $mapping->namespace);
	}

	$chain->addDriver(createAppDriver($appMapping, $attributeDriver), 'SixtyEightPublishers\FileBundle\Tests\Fixtures\\' . $appMapping);

	$config = new Configuration();
	$config->setMetadataDriverImpl($chain);
	$config->setProxyDir(TEMP_PATH . '/proxies');
	$config->setProxyNamespace('Proxies');
	$config->setNamingStrategy(new UnderscoreNamingStrategy(CASE_LOWER, TRUE)); # like nettrine/orm

	if (PHP_VERSION_ID >= 80400 && method_exists($config, 'enableNativeLazyObjects')) {
		$config->enableNativeLazyObjects(TRUE);
	}

	$resolveTargetEntityListener = new ResolveTargetEntityListener();

	foreach ($extension->getTargetEntities() as $targetEntity) {
		$resolveTargetEntityListener->addResolveTargetEntity($targetEntity->originalEntity, $targetEntity->newEntity, []);
	}

	$eventManager = new EventManager();
	$eventManager->addEventListener([Events::loadClassMetadata, Events::onClassMetadataNotFound], $resolveTargetEntityListener);

	foreach ($eventSubscribers as $eventSubscriber) {
		$eventManager->addEventSubscriber($eventSubscriber);
	}

	$connection = DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => TRUE], NULL, $eventManager);

	return (new ReflectionMethod(EntityManager::class, '__construct'))->isPublic()
		? new EntityManager($connection, $config, $eventManager)
		: EntityManager::create($connection, $config, $eventManager); # protected constructor in older doctrine/orm 2 versions
}

function createAppDriver(string $appMapping, AttributeDriver $attributeDriver): MappingDriver
{
	if ('Attribute' === $appMapping) {
		$attributeDriver->addPaths([__DIR__ . '/../Fixtures/Attribute']);

		return $attributeDriver;
	}

	$driverClass = 'Doctrine\ORM\Mapping\Driver\AnnotationDriver'; # doctrine/orm 2 only

	return new $driverClass(new AnnotationReader(), [__DIR__ . '/../Fixtures/Annotation']);
}

/**
 * Creates the schema (checks the SQL is valid) and returns its tables.
 *
 * @return array<string, \Doctrine\DBAL\Schema\Table>
 */
function createSchema(EntityManager $em, string ...$classNames): array
{
	$schemaTool = new SchemaTool($em);
	$metadata = array_map([$em, 'getClassMetadata'], $classNames);
	$schemaTool->createSchema($metadata);
	$tables = [];

	foreach ($schemaTool->getSchemaFromMetadata($metadata)->getTables() as $table) {
		$tables[$table->getName()] = $table;
	}

	return $tables;
}

/**
 * @return array<string, \Doctrine\DBAL\Schema\Table>
 */
function assertFile(EntityManager $em, string $fileClassName, string ...$otherClassNames): array
{
	$metadata = $em->getClassMetadata($fileClassName);

	Assert::same(['id'], $metadata->getIdentifierFieldNames());
	Assert::same(['onPreUpdate'], $metadata->getLifecycleCallbacks(Events::preUpdate));
	Assert::same('json', $metadata->getTypeOfField('metadata'));
	Assert::same(['jsonb' => TRUE], $metadata->getFieldMapping('metadata')['options']);

	$tables = createSchema($em, $fileClassName, ...$otherClassNames);
	$columnNames = array_map(static fn ($column): string => $column->getName(), array_values($tables['file']->getColumns()));

	Assert::same(['id', 'created', 'updated', 'source', 'metadata'], array_slice($columnNames, 0, 5));
	Assert::true($tables['file']->hasIndex('IDX_FILE_CREATED'));

	return $tables;
}

/**
 * @param array<string, \Doctrine\DBAL\Schema\Table> $tables
 */
function assertFileVariant(EntityManager $em, array $tables, string $fileVariantClassName, string $fileClassName): void
{
	Assert::same($fileClassName, $em->getClassMetadata($fileVariantClassName)->getAssociationTargetClass('file'));

	$foreignKey = array_values($tables['file_variant']->getForeignKeys())[0];
	Assert::same('file', $foreignKey->getForeignTableName());
	Assert::same('CASCADE', $foreignKey->getOption('onDelete'));
}

$appMappings = ['Attribute'];

if (class_exists(AnnotationReader::class) && class_exists('Doctrine\ORM\Mapping\Driver\AnnotationDriver')) {
	$appMappings[] = 'Annotation'; # doctrine/orm 2
}

foreach ($appMappings as $appMapping) {
	$fixtures = 'SixtyEightPublishers\FileBundle\Tests\Fixtures\\' . $appMapping;

	test("The basic entity, an application entity relates to FileInterface ($appMapping)", static function () use ($appMapping, $fixtures): void {
		$em = createEntityManager(BasicFile::class, $appMapping);
		$tables = assertFile($em, BasicFile::class, $fixtures . '\FileVariant');

		assertFileVariant($em, $tables, $fixtures . '\FileVariant', BasicFile::class);
	});

	test("A custom application entity extends AbstractFile ($appMapping)", static function () use ($appMapping, $fixtures): void {
		$em = createEntityManager($fixtures . '\File', $appMapping);
		$tables = assertFile($em, $fixtures . '\File', $fixtures . '\FileVariant');

		Assert::true($tables['file']->hasIndex('IDX_GIN_FILE_METADATA'));
		assertFileVariant($em, $tables, $fixtures . '\FileVariant', $fixtures . '\File');

		# the bundled entities are not mapped
		$classNames = $em->getConfiguration()->getMetadataDriverImpl()->getAllClassNames();
		sort($classNames);
		Assert::same([AbstractFile::class, $fixtures . '\File', $fixtures . '\FileVariant'], $classNames);
	});
}

test('The soft-deletable entity', static function (): void {
	$softDeleteableListener = new SoftDeleteableListener();
	$em = createEntityManager(SoftDeletableFile::class, 'Attribute', $softDeleteableListener);
	$tables = assertFile($em, SoftDeletableFile::class);

	Assert::true($em->getClassMetadata(SoftDeletableFile::class)->getFieldMapping('deletedAt')['nullable']);
	Assert::true($tables['file']->hasColumn('deleted_at'));

	$configuration = $softDeleteableListener->getConfiguration($em, SoftDeletableFile::class);
	Assert::true($configuration['softDeleteable']);
	Assert::same('deletedAt', $configuration['fieldName']);
	Assert::false($configuration['timeAware']);
});
