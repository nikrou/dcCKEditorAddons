<?php
/*
 *  -- BEGIN LICENSE BLOCK ----------------------------------
 *
 *  This file is part of dcCKEditorAddons, a plugin for DotClear2.
 *
 *  Licensed under the GPL version 2.0 license.
 *  See LICENSE file or
 *  http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 *
 *  -- END LICENSE BLOCK ------------------------------------
 */

// the final goal is to respect all PSR-2 rules.

$header = <<<'EOF'
 -- BEGIN LICENSE BLOCK ----------------------------------

 This file is part of dcCKEditorAddons, a plugin for DotClear2.

 Licensed under the GPL version 2.0 license.
 See LICENSE file or
 http://www.gnu.org/licenses/old-licenses/gpl-2.0.html

 -- END LICENSE BLOCK ------------------------------------
EOF;

$finder = PhpCsFixer\Finder::create()->in(__DIR__);

$config = new PhpCsFixer\Config();

return $config
    ->setRules([
        '@PSR1' => true,

        // arrays
        'array_indentation' => true,
        'array_syntax' => ['syntax' => 'short'],
        'no_multiline_whitespace_around_double_arrow' => true,
        'no_whitespace_before_comma_in_array' => true,
        'trim_array_spaces' => true,
        'whitespace_after_comma_in_array' => true,

        // class
        'class_attributes_separation' => ['elements' => ['method' => 'one']],
        'class_definition' => ['single_line' => true],
        'method_argument_space' => true,

        // comments
        'align_multiline_comment' => ['comment_type' => 'all_multiline'],
        'header_comment' => ['comment_type' => 'comment', 'header' => $header, 'location' => 'after_open', 'separate' => 'bottom'],
        'no_trailing_whitespace' => true,
        'single_line_comment_style' => true,

        // spaces
        'binary_operator_spaces' => ['operators' => ['=>' => 'single_space', '=' => 'single_space']],
        'concat_space' => ['spacing' => 'one'],
        'no_spaces_inside_parenthesis' => true,

        // global
        'blank_line_after_opening_tag' => true,
        'blank_line_after_namespace' => true,
        'braces' => [
            'position_after_control_structures' => 'same',
            'position_after_functions_and_oop_constructs' => 'next',
        ],
        'constant_case' => true,
        'combine_consecutive_issets' => true,
        'combine_consecutive_unsets' => true,
        // 'encoding' => true,
        'elseif' => true,
        // 'full_opening_tag' => true,
        'heredoc_to_nowdoc' => true,
        'lowercase_cast' => true,
        'lowercase_keywords' => true,
        'no_closing_tag' => true,
        'no_leading_import_slash' => true,
        'single_blank_line_at_eof' => true,

        'no_unused_imports' => true,
    ])
    ->setFinder($finder);
