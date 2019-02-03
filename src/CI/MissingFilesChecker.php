<?php declare(strict_types=1);

namespace SymfonyDocsBuilder\CI;

use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use SymfonyDocsBuilder\BuildContext;

class MissingFilesChecker
{
    private $finder;
    private $filesystem;
    private $buildContext;

    public function __construct(BuildContext $buildContext)
    {
        $this->finder       = new Finder();
        $this->filesystem   = new Filesystem();
        $this->buildContext = $buildContext;
    }

    public function checkMissingFiles(SymfonyStyle $io)
    {
        $this->finder->in($this->buildContext->getSourceDir())
            ->exclude(['_build', '.github', '.platform', '_images'])
            ->notName('*.rst.inc')
            ->files()
            ->name('*.rst');

        foreach ($this->finder as $file) {
            $htmlFile = str_replace(
                [$this->buildContext->getSourceDir(), '.rst'],
                [$this->buildContext->getHtmlOutputDir(), '.html'],
                $file->getRealPath()
            );

            $firstLine = fgets(fopen($file->getRealPath(), 'r'));
            if (!$this->filesystem->exists($htmlFile) && ':orphan:' !== trim($firstLine)) {
                $io->warning(sprintf('Missing file "%s"', $htmlFile));
            }
        }
    }
}
