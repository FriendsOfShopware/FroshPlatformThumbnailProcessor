<?php declare(strict_types=1);

namespace Frosh\ThumbnailProcessor\DependencyInjection;

use PhpParser\Node;
use PhpParser\Node\Stmt\Expression;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;

class PhpParserReplaceMethodVisitor extends NodeVisitorAbstract
{
    public function leaveNode(Node $node): void
    {
        // we don't need to generate the files, so we just return the array
        if ($node instanceof Node\Stmt\ClassMethod && $node->name->toString() === 'createThumbnailsForSizes') {
            $node->stmts = (new ParserFactory())->create(ParserFactory::PREFER_PHP7)
                ->parse('<?php if ($thumbnailSizes->count() === 0) {
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

            return;
        }

        // the strict option is useless with this plugin, so this should always be false
        if ($node instanceof Node\Stmt\ClassMethod && $node->name->toString() === 'updateThumbnails') {
            $stmts = $node->getStmts();
            array_unshift($stmts, new Expression(new Node\Expr\Assign(
                new Node\Expr\Variable('strict'),
                new Node\Expr\ConstFetch(new Node\Name('false'))
            )));
            $node->stmts = $stmts;
        }
    }
}
