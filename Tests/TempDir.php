<?php

namespace Dtc\GridBundle\Tests;

/**
 * Shared recursive temp-directory cleanup used by the test cases and the
 * screenshot test-app setup script, so the delete logic lives in one place.
 */
class TempDir
{
    /**
     * @param string $dir
     */
    public static function remove($dir)
    {
        if (!is_dir($dir)) {
            return;
        }
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($iterator as $item) {
            $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
        }
        rmdir($dir);
    }
}
