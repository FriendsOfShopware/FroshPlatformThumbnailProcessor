<?php declare(strict_types=1);

namespace Frosh\ThumbnailProcessor\DependencyInjection;

use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\NodeFinder;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use Shopware\Core\Content\Media\Thumbnail\ThumbnailService;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ThumbnailServiceGeneratorPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $fileContents = $this->getThumbnailServiceFileContent();

        if (empty($fileContents)) {
            $this->removeReflectionClass();

            return;
        }

        $phpParser = (new ParserFactory())->create(ParserFactory::ONLY_PHP7);
        $ast = $phpParser->parse($fileContents);

        if ($ast === null) {
            $this->removeReflectionClass();

            return;
        }

        $nodeFinder = new NodeFinder();

        /** @var Namespace_ $namespace */
        $namespace = $nodeFinder->findFirstInstanceOf($ast, Namespace_::class);
        $namespace->name = new Name(__NAMESPACE__);

        /** @var Class_ $class */
        $class = $nodeFinder->findFirstInstanceOf($ast, Class_::class);
        $class->extends = new Name('\\' . ThumbnailService::class);

        $traverser = new NodeTraverser();
        $traverser->addVisitor(new PhpParserReplaceMethodVisitor());
        $ast = $traverser->traverse($ast);

        $printer = new Standard();

        file_put_contents($this->getTargetPath(), $printer->prettyPrintFile($ast));

        $container->getDefinition(ThumbnailService::class)
            ->setClass(__NAMESPACE__ . '\\ThumbnailService');
    }

    private function getThumbnailServiceFileContent(): ?string
    {
        $thumbnailService = new \ReflectionClass(ThumbnailService::class);
        $fileName = $thumbnailService->getFileName();

        if (!\is_string($fileName)) {
            return null;
        }

        return file_get_contents($fileName) ?: null;
    }

    private function getTargetPath(): string
    {
        return __DIR__ . '/ThumbnailService.php';
    }

    private function removeReflectionClass(): void
    {
        if (!is_file($this->getTargetPath())) {
            return;
        }

        unlink($this->getTargetPath());
    }
}
