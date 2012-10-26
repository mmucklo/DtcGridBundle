<?php
namespace Dtc\GridBundle\Generator;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

class GridConfigGenerator {
	public function __construct() {

	}

	public function generate(BundleInterface $bundle, $entity, ClassMetadataInfo $metadata) {
		$parts       = explode('\\', $entity);
		$entityClass = array_pop($parts);

		$className = $entityClass.'GridSource';
		$dirPath         = $bundle->getPath().'/Form';
		$classPath = $dirPath.'/'.str_replace('\\', '/', $entity).'Type.php';

		ve($className);
	}
}