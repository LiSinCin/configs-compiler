<?php

namespace Sli\ConfigsCompiler;

use Symfony\Component\Yaml\Yaml;

/**
 * @internal
 *
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */
class ConfigsHandler
{
    /**
     * TODO we should consult ConfigValueDeclarations in order to understand how to properly merge
     * certain values
     *
     * @internal
     *
     * @param string[] $paths
     *
     * @return array
     */
    public function merge(array $paths)
    {
        $result = array(
            'props' => array(),
        );

        foreach ($paths as $pathname) {
            if (!file_exists($pathname)) {
                throw new \RuntimeException(sprintf(
                    'Configuration file "%s" doesn\'t exist.', $pathname
                ));
            }

            $ext = $this->extractExtension($pathname);

            $contents = file_get_contents($pathname);

            if ('json' == $ext) {
                $contents = json_decode($contents, true);
            } else if ('yml' == $ext) {
                $contents = Yaml::parse($contents);
            }

            $result = array_merge_recursive($result, $contents);
        }

        return $result;
    }

    /**
     * @param array $props
     * @param string $pathname
     */
    public function write(array $props, $pathname)
    {
        $ext = $this->extractExtension($pathname);

        $contents = array('props' => $props);

        if ('json' == $ext) {
            file_put_contents($pathname, json_encode($contents, \JSON_PRETTY_PRINT));
        } else if ('yml' == $ext) {
            file_put_contents($pathname, Yaml::dump($contents));
        }
    }

    private function extractExtension($pathname)
    {
        $extPos = strrpos($pathname, '.');
        if (false === $extPos) {
            throw new \RuntimeException("Unable to guess extension for configuration file $pathname");
        }

        $ext = strtolower(substr($pathname, strrpos($pathname, '.')+1));
        if (!in_array($ext, ['json', 'yml'])) {
            throw new \RuntimeException(sprintf(
                'Unable to handle %s file, only JSON and YML configuration files are supported as of now.',
                $pathname
            ));
        }

        return $ext;
    }
}