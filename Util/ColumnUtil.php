<?php

namespace Dtc\GridBundle\Util;

use Dtc\GridBundle\Annotation\Action;
use Symfony\Component\Yaml\Yaml;

class ColumnUtil
{
    /**
     * @return string
     *
     * @throws \Exception
     */
    public static function createCacheFilename($cacheDir, $fqn)
    {
        $directory = $cacheDir.'/DtcGridBundle';
        $umask = decoct(umask());
        $umask = str_pad($umask, 4, '0', STR_PAD_LEFT);

        // Is there a better way to do this?
        $permissions = '0777';
        $permissions[1] = intval($permissions[1]) - intval($umask[1]);
        $permissions[2] = intval($permissions[2]) - intval($umask[2]);
        $permissions[3] = intval($permissions[3]) - intval($umask[3]);

        $name = str_replace('\\', DIRECTORY_SEPARATOR, $fqn);
        $name = ltrim($name, DIRECTORY_SEPARATOR);
        $filename = $directory.DIRECTORY_SEPARATOR.$name.'.php';

        if (($dir = dirname($filename)) && !is_dir($dir) && !mkdir($dir, octdec($permissions), true)) {
            throw new \Exception("Can't create: ".$dir);
        }
        if (!is_writable($dir)) {
            throw new \Exception("Can't write to: $dir");
        }

        return $filename;
    }

    /**
     * Materialize a cache-shaped column info array (column specs with
     * 'class'/'arguments') into instantiated GridColumn objects. This is the
     * runtime twin of the code populateCacheFile() emits, kept here so the
     * cache format is defined in one place.
     *
     * @return array ['columns' => array<GridColumn>, 'sort' => array]
     */
    public static function instantiateColumnInfo(array $columnInfo)
    {
        $columns = [];
        foreach ($columnInfo['columns'] as $field => $info) {
            $class = $info['class'];
            $columns[$field] = new $class(...$info['arguments']);
        }

        return ['columns' => $columns, 'sort' => isset($columnInfo['sort']) ? $columnInfo['sort'] : []];
    }

    /**
     * @param string $filename
     * @param string $source   'runtime' (read on request from annotations/attributes)
     *                         or 'compile' (written from dtc_grid YAML at container build).
     *                         The reader only resurrects a timestamp-stale cache when its
     *                         source is 'compile', since YAML grids have no request-time
     *                         regeneration path.
     */
    public static function populateCacheFile($filename, array $classInfo, $source = 'runtime')
    {
        // Cache the column *specs* (class + constructor arguments), not
        // instantiated objects: a plain var_export'd array is materialized on
        // read by instantiateColumnInfo(), so the format lives in one place and
        // there is no hand-written code generation to keep parse-safe.
        $spec = [
            'columns' => isset($classInfo['columns']) ? $classInfo['columns'] : [],
            'sort' => isset($classInfo['sort']) ? $classInfo['sort'] : [],
            'source' => $source,
        ];

        self::atomicWrite($filename, "<?php\n\nreturn ".var_export($spec, true).";\n");
    }

    /**
     * Write atomically so a concurrent request can never include() a
     * half-written cache file.
     *
     * @param string $filename
     * @param string $contents
     */
    private static function atomicWrite($filename, $contents)
    {
        $tmp = tempnam(dirname($filename), 'dtcgrid_');
        if (false === $tmp) {
            file_put_contents($filename, $contents);

            return;
        }
        file_put_contents($tmp, $contents);
        @chmod($tmp, 0666 & ~umask());
        if (!@rename($tmp, $filename)) {
            @unlink($tmp);
            file_put_contents($filename, $contents);
        }
    }

    /**
     * @param string $cacheDir
     * @param string $filename
     *
     * @throws \Exception
     */
    public static function cacheClassesFromFile($cacheDir, $filename)
    {
        $classes = self::extractClassesFromFile($filename);
        foreach ($classes as $class => $columnInfo) {
            $filename = ColumnUtil::createCacheFilename($cacheDir, $class);
            self::populateCacheFile($filename, $columnInfo, 'compile');
        }
    }

    /**
     * @param string $filename
     *
     * @return array
     *
     * @throws \Exception
     */
    public static function extractClassesFromFile($filename)
    {
        // @TODO probably break this into multiple functions
        if (!is_readable($filename)) {
            throw new \Exception("Can't read {$filename}");
        }
        $stat = stat($filename);
        $result = method_exists('Symfony\Component\Yaml\Yaml', 'parseFile') ? Yaml::parseFile($filename) : Yaml::parse(file_get_contents($filename));
        if (!$result && $stat['size'] > 0) {
            throw new \Exception("Can't parse data from {$filename}");
        }

        $classes = [];
        foreach ($result as $class => $info) {
            if (!isset($info['columns'])) {
                // @TODO some kind of warning here - or try to read using reflection?
                continue;
            }
            $class = ltrim($class, '\\');
            if (!class_exists($class)) {
                throw new \Exception("$class - class does not exist");
            }
            $classes[$class]['columns'] = [];
            foreach ($info['columns'] as $name => $columnDef) {
                $label = isset($columnDef['label']) ? $columnDef['label'] : CamelCase::fromCamelCase($name);
                $column = ['class' => '\Dtc\GridBundle\Grid\Column\GridColumn', 'arguments' => [$name, $label]];
                $column['arguments'][] = isset($columnDef['formatter']) ? $columnDef['formatter'] : null;
                if (isset($columnDef['sortable'])) {
                    $column['arguments'][] = ['sortable' => $columnDef['sortable'] ? true : false];
                } else {
                    $column['arguments'][] = [];
                }
                $column['arguments'][] = isset($columnDef['searchable']) ? ($columnDef['searchable'] ? true : false) : false;
                $column['arguments'][] = null;
                $classes[$class]['columns'][$name] = $column;
            }

            if (isset($info['actions'])) {
                $field = '\$-action';
                $actionArgs = [$field];
                $actionDefs = [];
                /* @var Action $action */
                foreach ($info['actions'] as $action) {
                    if (!isset($action['label'])) {
                        throw new \Exception("$class - action definition missing 'label' ".print_r($action, true));
                    }
                    $actionDef = ['label' => $action['label']];
                    if (isset($action['route'])) {
                        $actionDef['route'] = $action['route'];
                    }
                    if (isset($action['onclick'])) {
                        $actionDef['onclick'] = $action['onclick'];
                    }
                    if (isset($action['button_class'])) {
                        $actionDef['button_class'] = $action['button_class'];
                    }
                    $type = '';
                    if (isset($action['type'])) {
                        $type = $action['type'];
                    }
                    switch ($type) {
                        case 'show':
                            $actionDef['action'] = 'show';
                            break;
                        case 'delete':
                            $actionDef['action'] = 'delete';
                            break;
                        default:
                            $actionDef['action'] = 'custom';
                    }
                    $actionDefs[] = $actionDef;
                }
                $actionArgs[] = $actionDefs;
                $classes[$class]['columns'][$field] = ['class' => '\Dtc\GridBundle\Grid\Column\ActionGridColumn', 'arguments' => $actionArgs];
            }
            if (isset($info['sort'])) {
                foreach ($info['sort'] as $key => $value) {
                    if (!isset($info['columns'][$key])) {
                        throw new \Exception("$class - can't find sort column $key in list of columns.");
                    }
                    switch ($value) {
                        case 'ASC':
                            break;
                        case 'DESC':
                            break;
                        default:
                            throw new \Exception("$class - sort type should be ASC or DESC instead of $value.");
                    }
                }
                $classes[$class]['sort'] = $info['sort'];
            }
        }

        return $classes;
    }
}
