#!/usr/bin/env bash

export PHP_CS_FIXER=~/.composer/vendor/bin/php-cs-fixer

if ! [ -x "$(command -v ${PHP_CS_FIXER})" ]; then
    echo 'Command `php-cs-fixer` not found. Installing...'
    composer global require friendsofphp/php-cs-fixer
fi

${PHP_CS_FIXER} fix  --fixers="\
    array_element_no_space_before_comma,\
    array_element_white_space_after_comma,\
    braces,\
    concat_with_spaces,\
    duplicate_semicolon,\
    elseif,\
    encoding,\
    eof_ending,\
    extra_empty_lines,\
    function_call_space,\
    function_declaration,\
    indentation,\
    join_function,\
    line_after_namespace,\
    linefeed,\
    list_commas,\
    lowercase_constants,\
    lowercase_keywords,\
    method_argument_space,\
    multiline_array_trailing_comma,\
    multiple_use,\
    new_with_braces,\
    no_blank_lines_after_class_opening,\
    no_empty_lines_after_phpdocs,\
    object_operator,\
    operators_spaces,\
    ordered_use,\
    parenthesis,\
    php4_constructor,\
    php_closing_tag,\
    php_unit_construct,\
    phpdoc_indent,\
    phpdoc_no_access,\
    phpdoc_no_package,\
    phpdoc_order,\
    phpdoc_scalar,\
    phpdoc_separation,\
    phpdoc_to_comment,\
    phpdoc_trim,\
    phpdoc_type_to_var,\
    phpdoc_types,\
    phpdoc_var_without_name,\
    print_to_echo,\
    remove_leading_slash_use,\
    remove_lines_between_uses,\
    return,\
    short_array_syntax,\
    short_bool_cast,\
    short_tag,\
    single_blank_line_before_namespace,\
    single_line_after_imports,\
    single_quote,\
    spaces_before_semicolon,\
    standardize_not_equal,\
    ternary_spaces,\
    trailing_spaces,\
    trim_array_spaces,\
    unalign_double_arrow,\
    unalign_equals,\
    unneeded_control_parentheses,\
    unused_use,\
    visibility,\
    whitespacy_lines"\
    $@ .
