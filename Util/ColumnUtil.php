<?php

namespace Dtc\GridBundle\Util;

use Dtc\GridBundle\Annotation\Action;
use Symfony\Component\Yaml\Yaml;
use Exception;

class ColumnUtil {
    /**
     * @param $cacheDir
     * @param $fqn
     *
     * @return string
     *
     * @throws Exception
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
            throw new Exception("Can't create: ".$dir);
        }
        if (!is_writable($dir)) {
            throw new Exception("Can't write to: $dir");
        }

        return $filename;
    }

    /**
     * @param string $filename
     * @param array $classInfo
     */
    public static function populateCacheFile($filename, array $classInfo) {
        $columns = isset($classInfo['columns']) ? $classInfo['columns'] : [];
        $sort = isset($classInfo['sort']) ? $classInfo['sort'] : [];

        if ($columns) {
            $output = "<?php\nreturn array('columns' => array(\n";
            foreach ($columns as $field => $info) {
                $class = $info['class'];
                $output .= "'$field' => new $class(";
                $first = true;
                foreach ($info['arguments'] as $argument) {
                    if ($first) {
                        $first = false;
                    } else {
                        $output .= ',';
                    }
                    $output .= var_export($argument, true);
                }
                $output .= '),';
            }
            $output .= "), 'sort' => array(";
            foreach ($sort as $key => $value) {
                $output .= "'$key'".' => ';
                if (null === $value) {
                    $output .= 'null,';
                } else {
                    $output .= "'$value',";
                }
            }
            $output .= "));\n";
        } else {
            $output = "<?php\nreturn false;\n";
        }
        file_put_contents($filename, $output);
    }

    /**
     * @param string $filename
     * @return array
     * @throws Exception
     */
    public static function extractClassesFromFile($filename) {
        // @TODO probably break this into multiple functions
        if (!is_readable($filename)) {
            throw new Exception("Can't read {$filename}");
        }
        $stat = stat($filename);
        $result = Yaml::parseFile($filename);
        if (!$result && $stat['size'] > 0) {
            throw new Exception("Can't parse data from {$filename}");
        }

        $classes = [];
        foreach ($result as $class => $info) {
            if (!isset($info['columns'])) {
                // @TODO some kind of warning here - or try to read using reflection?
                continue;
            }
            $class = ltrim($class, "\\");
            if (!class_exists($class)) {
                throw new Exception("$class - class does not exist");
            }
            $classes[$class]['columns'] = [];
            foreach ($info['columns'] as $name => $columnDef) {
                $label = isset($columnDef['label']) ? $columnDef['label'] : CamelCase::fromCamelCase($name);
                $column = ['class' => '\Dtc\GridBundle\Grid\Column\GridColumn', 'arguments' => [$name, $label]];
                $column['arguments'][] = isset($columnDef['formatter']) ? $columnDef['formatter'] : null;
                if ($columnDef['sortable']) {
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
                        throw new Exception("$class - action definition missing 'label' ". print_r($action, true));
                    }
                    if (!isset($action['route'])) {
                        throw new Exception("$class - action definition missing 'route' ". print_r($action, true));
                    }
                    if (!isset($action['type'])) {
                        throw new Exception("$class - action definition missing 'type' ". print_r($action, true));
                    }
                    $actionDef = ['label' => $action['label'], 'route' => $action['route']];
                    switch($action['type']) {
                        case 'show':
                            $actionDef['action'] = 'show';
                            break;
                        case 'delete':
                            $actionDef['action'] = 'delete';
                            break;
                        default:
                            throw new Exception("$class - action definition unknown 'type' {$action['type']} ". print_r($action, true));
                    }
                    $actionDefs[] = $actionDef;
                }
                $actionArgs[] = $actionDefs;
                $classes[$class]['columns'][$field] = ['class' => '\Dtc\GridBundle\Grid\Column\ActionGridColumn', 'arguments' => $actionArgs, ];
            }
            if (isset($info['sort'])) {
                foreach($info['sort'] as $key => $value) {
                    if (!isset($info['columns'][$key])) {
                        throw new Exception("$class - can't find sort column $key in list of columns.");
                    }
                    switch($value) {
                        case 'ASC':
                            break;
                        case 'DESC':
                            break;
                        default:
                            throw new Exception("$class - sort type should be ASC or DESC instead of $value.");
                    }
                }
                $classes[$class]['sort'] = $info['sort'];
            }
        }
        return $classes;
    }

}