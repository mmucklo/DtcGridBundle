<?php

namespace Dtc\GridBundle\Generator;

class Generator
{
    protected function render($skeletonDir, $template, $parameters)
    {
        $twig = new \Twig_Environment(new \Twig_Loader_Filesystem($skeletonDir), array(
                'debug' => true,
                'cache' => false,
                'strict_variables' => true,
                'autoescape' => false,
        ));

        return $twig->render($template, $parameters);
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
