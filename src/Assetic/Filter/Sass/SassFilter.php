<?php

/*
 * This file is part of the Assetic package, an OpenSky project.
 *
 * (c) 2010-2014 OpenSky Project Inc
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Assetic\Filter\Sass;

use Assetic\Process;
use Assetic\Asset\AssetInterface;
use Assetic\Exception\FilterException;
use Assetic\Util\FilesystemUtils;

/**
 * Loads SASS files.
 *
 * @link http://sass-lang.com/
 * @author Kris Wallsmith <kris.wallsmith@gmail.com>
 */
class SassFilter extends BaseSassFilter
{
    const STYLE_NESTED     = 'nested';
    const STYLE_EXPANDED   = 'expanded';
    const STYLE_COMPACT    = 'compact';
    const STYLE_COMPRESSED = 'compressed';

    private $sassPath;
    private $rubyPath;
    private $unixNewlines;
    private $scss;
    private $style;
    private $precision;
    private $quiet;
    private $debugInfo;
    private $lineNumbers;
    private $sourceMap;
    private $cacheLocation;
    private $noCache;
    private $compass;

    public function __construct($sassPath = '/usr/bin/sass', $rubyPath = null)
    {
        $this->sassPath = $sassPath;
        $this->rubyPath = $rubyPath;
        $this->cacheLocation = FilesystemUtils::getTemporaryDirectory();
    }

    public function setUnixNewlines($unixNewlines)
    {
        $this->unixNewlines = $unixNewlines;
    }

    public function setScss($scss)
    {
        $this->scss = $scss;
    }

    public function setStyle($style)
    {
        $this->style = $style;
    }

    public function setPrecision($precision)
    {
        $this->precision = $precision;
    }

    public function setQuiet($quiet)
    {
        $this->quiet = $quiet;
    }

    public function setDebugInfo($debugInfo)
    {
        $this->debugInfo = $debugInfo;
    }

    public function setLineNumbers($lineNumbers)
    {
        $this->lineNumbers = $lineNumbers;
    }

    public function setSourceMap($sourceMap)
    {
        $this->sourceMap = $sourceMap;
    }

    public function setCacheLocation($cacheLocation)
    {
        $this->cacheLocation = $cacheLocation;
    }

    public function setNoCache($noCache)
    {
        $this->noCache = $noCache;
    }

    public function setCompass($compass)
    {
        $this->compass = $compass;
    }

    public function filterLoad(AssetInterface $asset)
    {
        $sassProcessArgs = array($this->sassPath);
        if (null !== $this->rubyPath) {
            $sassProcessArgs = array_merge(explode(' ', $this->rubyPath), $sassProcessArgs);
        }

        $commandline = $sassProcessArgs;

        if ($dir = $asset->getSourceDirectory()) {
            array_push($commandline, '--load-path', $dir);
        }

        if ($this->unixNewlines) {
            array_push($commandline, '--unix-newlines');
        }

        if (true === $this->scss || (null === $this->scss && 'scss' == pathinfo($asset->getSourcePath(), PATHINFO_EXTENSION))) {
            array_push($commandline, '--scss');
        }

        if ($this->style) {
            array_push($commandline, '--style', $this->style);
        }

        if ($this->precision) {
            array_push($commandline, '--precision', $this->precision);
        }

        if ($this->quiet) {
            array_push($commandline, '--quiet');
        }

        if ($this->debugInfo) {
            array_push($commandline, '--debug-info');
        }

        if ($this->lineNumbers) {
            array_push($commandline, '--line-numbers');
        }

        if ($this->sourceMap) {
            array_push($commandline, '--sourcemap');
        }

        foreach ($this->loadPaths as $loadPath) {
            array_push($commandline, '--load-path', $loadPath);
        }

        if ($this->cacheLocation) {
            array_push($commandline, '--cache-location', $this->cacheLocation);
        }

        if ($this->noCache) {
            array_push($commandline, '--no-cache');
        }

        if ($this->compass) {
            array_push($commandline, '--compass');
        }

        // input
        array_push($commandline, $input = FilesystemUtils::createTemporaryFile('sass'));
        file_put_contents($input, $asset->getContent());

        $proc = Process::fromShellCommandline(implode(' ', $commandline));
        $code = $proc->run();
        unlink($input);

        if (0 !== $code) {
            throw FilterException::fromProcess($proc)->setInput($asset->getContent());
        }

        $asset->setContent($proc->getOutput());
    }

    public function filterDump(AssetInterface $asset)
    {
    }
}
