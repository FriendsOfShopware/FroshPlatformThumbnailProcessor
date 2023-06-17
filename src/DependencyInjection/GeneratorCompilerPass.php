<?php declare(strict_types=1);

namespace Frosh\ThumbnailProcessor\DependencyInjection;

use Composer\Autoload\ClassLoader;
use PhpParser\Comment\Doc;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\NodeFinder;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use Shopware\Core\Content\Media\File\FileSaver as OriginalFileSaver;
use Shopware\Core\Content\Media\Thumbnail\ThumbnailService as OriginalThumbnailService;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class GeneratorCompilerPass implements CompilerPassInterface
{
    private string $class;

    public function __construct(
        string $class
    ) {
        $this->class = $class;
    }

    public function process(ContainerBuilder $container): void
    {
        $this->resetClassLoader();

        $fileContents = $this->getFileContent();

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

        if ($namespace->name === null) {
            $this->removeReflectionClass();

            return;
        }

        $originalNamespace = $namespace->name->toString();

        if (empty($originalNamespace)) {
            $this->removeReflectionClass();

            return;
        }

        $namespace->name = new Name(__NAMESPACE__);

        $this->addUsesOfNamespace($namespace->stmts, $originalNamespace);

        /** @var Class_ $class */
        $class = $nodeFinder->findFirstInstanceOf($ast, Class_::class);
        $class->extends = new Name('\\' . $this->class);
        $class->setDocComment(new Doc(
            '/**' . \PHP_EOL . 'THIS CLASS HAS BEEN GENERATED AUTOMATICALLY' . \PHP_EOL . '*/'
        ));

        if ($this->class === OriginalThumbnailService::class) {
            $this->handleThumbnailService($nodeFinder, $ast);
        } elseif ($this->class === OriginalFileSaver::class) {
            $this->handleFileSaver($nodeFinder, $ast);
        } else {
            throw new \RuntimeException(\sprintf('Class %s is not implemented', $this->class));
        }

        $printer = new Standard();

        file_put_contents($this->getTargetPath(), $printer->prettyPrintFile($ast));

        $container->getDefinition($this->class)
            ->setClass(__NAMESPACE__ . '\\' . $this->getClassName());
    }

    /**
     * @param array<Stmt> $ast
     */
    private function handleThumbnailService(NodeFinder $nodeFinder, array $ast): void
    {
        $createThumbnailsForSizesNode = $this->getClassMethod($nodeFinder, 'createThumbnailsForSizes', $ast);

        // we don't need to generate the files, so we just return the array
        $createThumbnailsForSizesNode->stmts = (new ParserFactory())->create(ParserFactory::PREFER_PHP7)
            ->parse('<?php if ($thumbnailSizes === null) {
                                return [];
                            }

                            if ($thumbnailSizes->count() === 0) {
                                return [];
                            }

                            $savedThumbnails = [];

                            foreach ($thumbnailSizes as $size) {
                                $savedThumbnails[] = [
                                    \'mediaId\' => $media->getId(),
                                    \'width\' => $size->getWidth(),
                                    \'height\' => $size->getHeight(),
                                ];
                            }

                            return $savedThumbnails;');

        // the strict option is useless with this plugin, so this should always be false
        $updateThumbnailsNode = $this->getClassMethod($nodeFinder, 'updateThumbnails', $ast);

        $stmts = $updateThumbnailsNode->getStmts();
        if (empty($stmts)) {
            throw new \RuntimeException(\sprintf('Method %s in class %s is empty', 'updateThumbnails', $this->class));
        }

        array_unshift($stmts, new Expression(new Assign(
            new Variable('strict'),
            new ConstFetch(new Name('false'))
        )));

        $updateThumbnailsNode->stmts = $stmts;
    }

    /**
     * @param array<Stmt> $ast
     */
    private function handleFileSaver(NodeFinder $nodeFinder, array $ast): void
    {
        $renameThumbnailNode = $this->getClassMethod($nodeFinder, 'renameThumbnail', $ast);
        $renameThumbnailNode->stmts = (new ParserFactory())->create(ParserFactory::PREFER_PHP7)
            ->parse('<?php return [];');
    }

    /**
     * @param array<Stmt> $ast
     */
    private function getClassMethod(NodeFinder $nodeFinder, string $name, array $ast): ClassMethod
    {
        /** @var ?ClassMethod $node */
        $node = $nodeFinder->findFirst($ast, function ($node) use ($name) {
            return $node instanceof ClassMethod && $node->name->toString() === $name;
        });

        if (empty($node)) {
            throw new \RuntimeException(\sprintf('Method %s in class %s is missing', $name, $this->class));
        }

        return $node;
    }

    /**
     * @param array<Stmt>|null $stmts
     */
    private function addUsesOfNamespace(?array &$stmts, string $namespace): void
    {
        if (!\is_array($stmts)) {
            return;
        }

        $uses = [];

        $filePath = $this->getFileName();
        $files = glob(\dirname($filePath) . '/*.php');

        if (empty($files)) {
            return;
        }

        foreach ($files as $file) {
            $class = $namespace . '\\' . \basename($file, '.php');

            if ($class === $this->class) {
                continue;
            }

            $uses[] = new Stmt\UseUse(new Name($class));
        }

        if (empty($uses)) {
            return;
        }

        array_unshift($stmts, new Stmt\Use_($uses));
    }

    private function getFileContent(): ?string
    {
        return file_get_contents($this->getFileName()) ?: null;
    }

    private function getFileName(): string
    {
        $reflectionClass = new \ReflectionClass($this->class);

        $fileName = $reflectionClass->getFileName();

        if (empty($fileName)) {
            throw new \RuntimeException(\sprintf('Cannot get fileName of class %s', $this->class));
        }

        return $fileName;
    }

    private function getTargetPath(): string
    {
        return __DIR__ . '/' . $this->getClassName() . '.php';
    }

    private function removeReflectionClass(): void
    {
        if (!is_file($this->getTargetPath())) {
            return;
        }

        unlink($this->getTargetPath());
    }

    private function getClassName(): string
    {
        $lastOccur = strrchr($this->class, '\\');

        if (empty($lastOccur)) {
            throw new \RuntimeException(\sprintf('Cannot determine className from %s', $this->class));
        }

        return substr($lastOccur, 1);
    }

    private function resetClassLoader(): void
    {
        $file = __DIR__ . '/../../vendor/autoload.php';
        if (!is_file($file)) {
            return;
        }

        $classLoader = require_once $file;

        if ($classLoader instanceof ClassLoader) {
            $classLoader->unregister();
            $classLoader->register(false);
        }
    }
}
