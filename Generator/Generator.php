<?php

namespace Dtc\GridBundle\Generator;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class Generator
{
    protected function render($skeletonDir, $template, $parameters)
    {
        if (class_exists('Twig\Environment')) {
            $environment = new Environment(new FilesystemLoader($skeletonDir), [
                'debug' => true,
                'cache' => false,
                'strict_variables' => true,
                'autoescape' => false,
            ]);

            return $environment->render($template, $parameters);
        }
        throw new \Exception("Class not found: Twig\Environment");
    }

    protected function renderFile($skeletonDir, $template, $target, $parameters)
    {
        $output = $this->render($skeletonDir, $template, $parameters);

        return $this->saveFile($target, $output);
    }

    protected function saveFile($target, $output)
    {
        if (!is_dir(dirname($target))) {
            mkdir(dirname($target), 0777, true);
        }

        return file_put_contents($target, $output);
    }
}
