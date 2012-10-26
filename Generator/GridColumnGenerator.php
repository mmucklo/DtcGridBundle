<?php
namespace Dtc\GridBundle\Generator;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\Yaml\Yaml;

use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\Bundle\DoctrineBundle\Mapping\MetadataFactory;

class GridColumnGenerator
	extends Generator
{
	public function __construct($skeletonDir, ContainerInterface $container) {
		$this->skeletonDir = $skeletonDir;
		$this->container = $container;
	}

    protected function fromCamelCase($str)
    {
        $func = function ($str)
        {
            return ' ' . $str[0];
        };

        $value = preg_replace_callback('/([A-Z])/', $func, $str);
        $value = ucfirst($value);

        return $value;
    }


	protected function generateColumns(BundleInterface $bundle, $entity, ClassMetadataInfo $metadata) {
		$parts       = explode('\\', $entity);
		$entityClass = array_pop($parts);
		$entityNamespace = implode('\\', $parts);

		$gridColumnsNamespace = $bundle->getNamespace() . '\\Grid\\Columns';
		$gridColumnsNamespace .= ($entityNamespace) ? '\\' . $entityNamespace : '';

		$gridColumnClass = $entityClass.'GridColumn';
		$dirPath         = $bundle->getPath().'/Grid/Columns';
		$gridColumnPath = $dirPath.'/'.str_replace('\\', '/', $entity).'GridColumn.php';
		$templatePath = $bundle->getPath() . '/Resources/views/' . str_replace('\\', '/', $entity) . '/_grid.html.twig';

		$fields = array();
		foreach ($this->getFieldsFromMetadata($metadata) as $field) {
			$fields[$field] = $this->fromCamelCase($field);
		}

		$params = array(
				'fields'           => $fields,
				'namespace'        => $gridColumnsNamespace,
				'class'            => $gridColumnClass,
				'template_name'    => "{$bundle->getName()}:{$entity}:_grid.html.twig"
		);

		$this->renderFile($this->skeletonDir, 'grid_template.html.twig', $templatePath, $params);
		$this->renderFile($this->skeletonDir, 'GridColumns.php.twig', $gridColumnPath, $params);

		return array($gridColumnClass, $gridColumnsNamespace, $gridColumnPath, $templatePath);
	}

	protected function generateSource(BundleInterface $bundle, $entity, ClassMetadataInfo $metadata) {
		$parts       = explode('\\', $entity);
		$entityClass = array_pop($parts);
		$entityNamespace = implode('\\', $parts);

		$gridSourceNamespace = $bundle->getNamespace() . '\\Grid\\Source';
		$gridSourceNamespace .= ($entityNamespace) ? '\\' . $entityNamespace : '';

		$gridSourceClass = $entityClass.'GridSource';
		$dirPath         = $bundle->getPath().'/Grid/Source';
		$gridSourcePath = $dirPath.'/'. str_replace('\\', '/', $entity) . 'GridSource.php';

		$params = array(
				'namespace'        => $gridSourceNamespace,
				'entity_class'     => $metadata->getReflectionClass()->getName(),
				'class'            => $gridSourceClass
		);

		$this->renderFile($this->skeletonDir, 'GridSource.php.twig', $gridSourcePath, $params);
		return array($gridSourceClass, $gridSourceNamespace, $gridSourcePath);
	}

	public function generate(BundleInterface $bundle, $entity, ClassMetadataInfo $metadata) {
		$parts       = explode('\\', $entity);
		$entityClass = array_pop($parts);

		list($gridColumnClass, $gridColumnsNamespace, $gridColumnPath, $templatePath) =
			$this->generateColumns($bundle, $entity, $metadata);

		list($gridSourceClass, $gridSourceNamespace, $gridSourcePath) =
			$this->generateSource($bundle, $entity, $metadata);

		$config = array();
		$serviceName = "grid.source." . strtolower($entityClass);
		$documentManager = '@doctrine.orm.default_entity_manager';
		$config['services'][$serviceName] = array(
				'class' => $gridSourceNamespace . '\\' . $gridSourceClass,
				'arguments' => array($documentManager),
				'tags' => array(array('name' => 'dtc_grid.source')),
				'calls' => array(
						array('setColumns', array (
								'@' . $serviceName . '.columns'
							)
						)
					)
			);

		$config['services'][$serviceName . '.columns'] = array(
				'class' => $gridColumnsNamespace . '\\' . $gridColumnClass,
				'arguments' => array('@twig', '@templating.globals')
			);

		$configFile = $bundle->getPath().'/Resources/config/grid.yml';
		if (file_exists($configFile)) {
			$config = array_merge(Yaml::parse($configFile), $config);
		}

		$this->saveFile($configFile, Yaml::dump($config, 3));
		$files = array(
			'grid_source' => $gridSourcePath,
			'grid_columns' => $gridColumnPath,
			'grid_template' => $templatePath
		);

		$params = array(
			'gridsource_id' => $serviceName,
			'files' => $files
		);

		$output = $this->render($this->skeletonDir, 'controller.php.twig', $params);
		echo $output; exit();
		ve($files);
	}

	private function getFieldsFromMetadata(ClassMetadataInfo $metadata)
	{
		$fields = (array) $metadata->fieldNames;

		// Remove the primary key field if it's not managed manually
		if (!$metadata->isIdentifierNatural()) {
			$fields = array_diff($fields, $metadata->identifier);
		}

		foreach ($metadata->associationMappings as $fieldName => $relation) {
			if ($relation['type'] !== ClassMetadataInfo::ONE_TO_MANY) {
				$fields[] = $fieldName;
			}
		}

		return $fields;
	}
}
