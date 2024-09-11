<?php

declare(strict_types=1);

namespace ChristophWurst\Nextcloud\Rector\Rector;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

use function count;

class OcpUtilAddScriptRector extends AbstractRector
{
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [FuncCall::class];
    }

    public function refactor(Node $node): ?Node
    {
        if (!($node instanceof FuncCall)) {
            return null;
        }
        $funcCallName = $this->getName($node->name);
        if ($funcCallName === null) {
            return null;
        }

        if ($funcCallName !== 'script') {
            return null;
        }
        if (count($node->args) < 2) {
            // Can't fix a wrong usage
            return null;
        }
        if ($node->args[1] instanceof Arg && $node->args[1]->value instanceof String_) {
            return new StaticCall(
                new FullyQualified('OCP\Util'),
                'addScript',
                [
                    ...$node->args,
                    new Arg(new String_('core')),
                ],
            );
        }
        if ($node->args[1] instanceof Arg && $node->args[1]->value instanceof Node\Expr\Array_) {
            // TODO: find a way to replace one node with an array of nodes
            return null;
        }

        return null;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Replaces the function to include front end scripts',
            [
                new CodeSample(
                    'script("mail", "mail");',
                    '\OCP\Util::addScript("mail", "mail");',
                ),
            ],
        );
    }
}
