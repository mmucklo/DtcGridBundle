<?php

namespace Dtc\GridBundle\Generator;

use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Dtc\GridBundle\Util\CamelCaseTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\Yaml\Yaml;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * @deprecated
 * Class GridSourceGenerator
 */
class GridSourceGenerator extends Generator
{
    use CamelCaseTrait;

    private $saveCache;
    private $skeletonDir;

    public function __construct($skeletonDir, ContainerInterface $container)
    {
        $this->skeletonDir = $skeletonDir;
        $this->container = $container;
    }

    protected function generateColumns(BundleInterface $bundle, $entity, $metadata)
    {
        $parts = explode('\\', $entity);
        $entityClass = array_pop($parts);
        $entityNamespace = implode('\\', $parts);

        $gridColumnsNamespace = $bundle->getNamespace().'\\Grid\\Columns';
        $gridColumnsNamespace .= ($entityNamespace) ? '\\'.$entityNamespace : '';

        $gridColumnClass = $entityClass.'GridColumn';
        $dirPath = $bundle->getPath().'/Grid/Columns';
        $gridColumnPath = $dirPath.'/'.str_replace('\\', '/', $entity).'GridColumn.php';
        $templatePath = $bundle->getPath().'/Resources/views/'.str_replace('\\', '/', $entity).'/_grid.html.twig';

        $fields = array();
        foreach ($this->getFieldsFromMetadata($metadata) as $field) {
            $fields[$field] = $this->fromCamelCase($field);
        }

        $params = array(
                'fields' => $fields,
                'namespace' => $gridColumnsNamespace,
                'class' => $gridColumnClass,
                'template_name' => "{$bundle->getName()}:{$entity}:_grid.html.twig",
        );

        $this->saveCache[$templatePath] = $this->render($this->skeletonDir, 'grid_template.html.twig', $params);
        $this->saveCache[$gridColumnPath] = $this->render($this->skeletonDir, 'GridColumns.php.twig', $params);

        return array($gridColumnClass, $gridColumnsNamespace, $gridColumnPath, $templatePath);
    }

    public function generate(BundleInterface $bundle, $entityDocument, $metadata, $columns = false)
    {
        if ($metadata instanceof ClassMetadataInfo) {
            $manager = '@doctrine.orm.default_entity_manager';
            $class = 'Dtc\GridBundle\Grid\Source\EntityGridSource';
        } elseif ($metadata instanceof \Doctrine\ODM\MongoDB\Mapping\ClassMetadataInfo) {
            $manager = '@doctrine_mongodb.odm.default_document_manager';
            $class = 'Dtc\GridBundle\Grid\Source\DocumentGridSource';
        } else {
            throw new \Exception(__METHOD__.' - Unknown class for metadata: '.get_class($metadata));
        }
        $entityDocumentClassPath = $metadata->getReflectionClass()->getName();

        $parts = explode('\\', $entityDocument);
        $entityDocumentClass = array_pop($parts);
        $files = [];

        if ($columns) {
            list($gridColumnClass, $gridColumnsNamespace, $gridColumnPath, $templatePath) =
                $this->generateColumns($bundle, $entityDocument, $metadata);

            $files = array(
                'grid_columns' => $gridColumnPath,
                'grid_template' => $templatePath,
            );
        }

        // Check to see if the files exists
        if ($this->saveCache) {
            foreach ($this->saveCache as $file => $output) {
                $this->saveFile($file, $output);
            }
        }

        $config = array();
        $serviceName = 'grid.source.'.strtolower($entityDocumentClass);
        $config[$serviceName] = array(
                'class' => $class,
                'arguments' => array($manager, $entityDocumentClassPath),
                'tags' => array(array('name' => 'dtc_grid.source')),
                'calls' => array(
                    array('autoDiscoverColumns'),
        ), );

        if ($columns && isset($gridColumnsNamespace) && isset($gridColumnClass)) {
            $config[$serviceName]['calls'] = array(
                array('setColumns', array(
                    '@'.$serviceName.'.columns',
                ),
                ),
            );

            $config[$serviceName.'.columns'] = array(
                'class' => $gridColumnsNamespace.'\\'.$gridColumnClass,
                'arguments' => array('@twig'),
            );
        }

        $configFile = $bundle->getPath().'/Resources/config/grid.yml';
        $services = array();
        if (file_exists($configFile)) {
            $services = Yaml::parse($contents = file_get_contents($configFile));
            if (isset($services['services'])) {
                $services['services'] = array_merge($services['services'], $config);
            } else {
                $services['services'] = $config;
            }
        } else {
            $services['services'] = $config;
        }

        $this->saveFile($configFile, Yaml::dump($services, 3));

        $params = array(
            'gridsource_id' => $serviceName,
            'files' => $files,
        );

        $output = $this->render($this->skeletonDir, 'controller.php.twig', $params);
        echo $output;
        exit();
    }

    private function getFieldsFromMetadata($metadata)
    {
        if ($metadata instanceof ClassMetadataInfo) {
            $fields = $metadata->getFieldNames();

            // Remove the primary key field if it's not managed manually
            if (!$metadata->isIdentifierNatural()) {
                $fields = array_diff($fields, $metadata->getIdentifier());
            }

            foreach ($metadata->associationMappings as $fieldName => $relation) {
                if (ClassMetadataInfo::ONE_TO_MANY !== $relation['type']) {
                    $fields[] = $fieldName;
                }
            }

            return $fields;
        } elseif ($metadata instanceof ClassMetadata) {
            $fields = $metadata->getFieldNames();
            $retFields = [];
            $identifier = $metadata->getIdentifier();
            $identifier = isset($identifier[0]) ? $identifier[0] : null;
            foreach ($fields as $field) {
                if ($identifier === $field) {
                    $mapping = $metadata->getFieldMapping($field);
                    if (isset($mapping['strategy']) && 'auto' == $mapping['strategy']) {
                        continue;
                    }
                }
                $retFields[] = $field;
            }

            return $retFields;
        }
    }
}
