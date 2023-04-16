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
        $thumbnailService = new \ReflectionClass(ThumbnailService::class);

        $phpParser = (new ParserFactory())->create(ParserFactory::ONLY_PHP7);
        $ast = $phpParser->parse(file_get_contents($thumbnailService->getFileName()));

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

        file_put_contents(__DIR__ . '/ThumbnailService.php', $printer->prettyPrintFile($ast));

        $container->getDefinition(ThumbnailService::class)
            ->setClass(__NAMESPACE__ . '\\ThumbnailService');
    }
}
