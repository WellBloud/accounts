<?php

declare(strict_types=1);

use PHP_CodeSniffer\Standards\Generic\Sniffs\ControlStructures\DisallowYodaConditionsSniff;
use PHP_CodeSniffer\Standards\Generic\Sniffs\PHP\LowerCaseTypeSniff;
use PHP_CodeSniffer\Standards\PSR2\Sniffs\ControlStructures\SwitchDeclarationSniff;
use PHP_CodeSniffer\Standards\Squiz\Sniffs\WhiteSpace\SuperfluousWhitespaceSniff;
use PhpCsFixer\Fixer\ArrayNotation\ArraySyntaxFixer;
use PhpCsFixer\Fixer\Casing\NativeFunctionTypeDeclarationCasingFixer;
use PhpCsFixer\Fixer\CastNotation\ModernizeTypesCastingFixer;
use PhpCsFixer\Fixer\ClassNotation\ClassAttributesSeparationFixer;
use PhpCsFixer\Fixer\ClassNotation\SingleTraitInsertPerStatementFixer;
use PhpCsFixer\Fixer\Comment\NoEmptyCommentFixer;
use PhpCsFixer\Fixer\ControlStructure\NoUnneededControlParenthesesFixer;
use PhpCsFixer\Fixer\ControlStructure\TrailingCommaInMultilineFixer;
use PhpCsFixer\Fixer\FunctionNotation\FunctionTypehintSpaceFixer;
use PhpCsFixer\Fixer\Import\NoUnusedImportsFixer;
use PhpCsFixer\Fixer\NamespaceNotation\BlankLineAfterNamespaceFixer;
use PhpCsFixer\Fixer\NamespaceNotation\SingleBlankLineBeforeNamespaceFixer;
use PhpCsFixer\Fixer\Operator\TernaryToNullCoalescingFixer;
use PhpCsFixer\Fixer\Phpdoc\GeneralPhpdocAnnotationRemoveFixer;
use PhpCsFixer\Fixer\Phpdoc\NoEmptyPhpdocFixer;
use PhpCsFixer\Fixer\Phpdoc\NoSuperfluousPhpdocTagsFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocAlignFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocInlineTagNormalizerFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocLineSpanFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocOrderFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocReturnSelfReferenceFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocTrimFixer;
use PhpCsFixer\Fixer\PhpTag\BlankLineAfterOpeningTagFixer;
use PhpCsFixer\Fixer\StringNotation\ExplicitStringVariableFixer;
use PhpCsFixer\Fixer\StringNotation\SingleQuoteFixer;
use PhpCsFixer\Fixer\Whitespace\BlankLineBeforeStatementFixer;
use PhpCsFixer\Fixer\Whitespace\NoExtraBlankLinesFixer;
use PhpCsFixer\Fixer\Whitespace\NoWhitespaceInBlankLineFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Option;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return static function (ECSConfig $ecsConfig): void {
    $parameters = $ecsConfig->parameters();
    $parameters->set(Option::PARALLEL, true);
    $parameters->set(Option::CACHE_DIRECTORY, __DIR__ . '/.ecs_cache');

    $ecsConfig->sets([SetList::PSR_12]);

    $ecsConfig->paths(
        [
            __DIR__ . '/app',
            __DIR__ . '/bootstrap',
            __DIR__ . '/config',
            __DIR__ . '/database',
            __DIR__ . '/public',
            __DIR__ . '/routes',
            __DIR__ . '/tests',
        ]
    );

    // Ensures all switch statements are defined correctly
    $ecsConfig->rule(SwitchDeclarationSniff::class);

    // Checks that all PHP types are lowercase
    $ecsConfig->rule(LowerCaseTypeSniff::class);

    // Converts array() to []
    $ecsConfig->ruleWithConfiguration(ArraySyntaxFixer::class, [
        'syntax' => 'short'
    ]);

    // PHP multi-line arrays should have a trailing comma
    $ecsConfig->rule(TrailingCommaInMultilineFixer::class);

    // Native type hints for functions should use the correct case.
    $ecsConfig->rule(NativeFunctionTypeDeclarationCasingFixer::class);

    // Replaces `intval`, `floatval`, `doubleval`, `strval` and `boolval` function calls with according type casting operator
    $ecsConfig->rule(ModernizeTypesCastingFixer::class);

    // There should not be any empty comments
    $ecsConfig->rule(NoEmptyCommentFixer::class);
    $ecsConfig->rule(NoEmptyPhpdocFixer::class);

    // Removes unneeded parentheses around control statements
    $ecsConfig->rule(NoUnneededControlParenthesesFixer::class);

    // Class, trait and interface elements must be separated with one blank line
    $ecsConfig->ruleWithConfiguration(ClassAttributesSeparationFixer::class, [
        'elements' => [
            'method' => 'one',
            'property' => 'one'
        ]
    ]);

    // Ensure single space between function's argument and its typehint
    $ecsConfig->rule(FunctionTypehintSpaceFixer::class);

    // There MUST be one blank line after the namespace declaration
    $ecsConfig->rule(BlankLineAfterNamespaceFixer::class);

    // There should be exactly one blank line before a namespace declaration
    $ecsConfig->rule(SingleBlankLineBeforeNamespaceFixer::class);

    // Use `null` coalescing operator `??` where possible
    $ecsConfig->rule(TernaryToNullCoalescingFixer::class);

    // Configured annotations should be omitted from PHPDoc
    $ecsConfig->ruleWithConfiguration(GeneralPhpdocAnnotationRemoveFixer::class, [
        'annotations' => ['author', 'package']
    ]);

    // Fix PHPDoc inline tags, make `@inheritdoc` always inline
    $ecsConfig->rule(PhpdocInlineTagNormalizerFixer::class);

    // Changes doc blocks from single to multi line. Works for class constants, properties and methods only.
    $ecsConfig->ruleWithConfiguration(PhpdocLineSpanFixer::class, [
        'const' => 'multi',
        'property' => 'multi',
        'method' => 'multi'
    ]);

    // Annotations in PHPDoc should be ordered so that `@param` annotations come first, then `@throws` annotations, then `@return` annotations
    $ecsConfig->rule(PhpdocOrderFixer::class);

    // The type of `@return` annotations of methods returning a reference to itself must the configured one
    $ecsConfig->rule(PhpdocReturnSelfReferenceFixer::class);

    // PHPDoc should start and end with content, excluding the very first and last line of the docblocks
    $ecsConfig->rule(PhpdocTrimFixer::class);

    // Aligns all the Phpdocs properly
    $ecsConfig->ruleWithConfiguration(PhpdocAlignFixer::class, [
        'align' => 'left'
    ]);

    // Ensure there is no code on the same line as the PHP open tag and it is followed by a blank line
    $ecsConfig->rule(BlankLineAfterOpeningTagFixer::class);

    // Converts implicit variables into explicit ones in double-quoted strings or heredoc syntax
    $ecsConfig->rule(ExplicitStringVariableFixer::class);

    // Convert double quotes to single quotes for simple strings
    $ecsConfig->rule(SingleQuoteFixer::class);

    // Removes extra blank lines and/or blank lines following configuration
    $ecsConfig->rule(NoExtraBlankLinesFixer::class);

    // Remove trailing whitespace at the end of blank lines
    $ecsConfig->rule(NoWhitespaceInBlankLineFixer::class);

    // Removes superfluous whitespaces
    $ecsConfig->rule(SuperfluousWhitespaceSniff::class);

    // Fixes yoda style to natural way
    $ecsConfig->rule(DisallowYodaConditionsSniff::class);

    // removes useless php annotations
    $ecsConfig->ruleWithConfiguration(NoSuperfluousPhpdocTagsFixer::class, [
        'allow_mixed' => true
    ]);

    // Each trait `use` must be done as single statement
    $ecsConfig->rule(SingleTraitInsertPerStatementFixer::class);

    // Remove unused imports
    $ecsConfig->rule(NoUnusedImportsFixer::class);

    // add blank line before statements
    $ecsConfig->rule(BlankLineBeforeStatementFixer::class);
};
