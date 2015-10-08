<?php
namespace WebSharks\ZenCache\Pro;
/*
* Convert line-delimited patterns to a regex.
*
* @since 15xxxx Enhancing exclusion pattern support.
*
* @param string $patterns Line-delimited list of patterns.
*
* @return string A `/(?:list|of|regex)/i` patterns.
*/
$self->lineDelimitedPatternsToRegex = function ($patterns) use ($self) {
    $regex    = ''; // Initialize.
    $patterns = (string)$patterns;

    if (($patterns = preg_split('/['."\r\n".']+/', $patterns, null, PREG_SPLIT_NO_EMPTY))) {
        $regex = '/(?:'.implode(
            '|',
            array_map(
                function ($string) {
                    return preg_replace(
                        array(
                            '/\\\\\^/',
                            '/\\\\\*\\\\\*/',
                            '/\\\\\*/',
                            '/\\\\\$/',
                        ),
                        array(
                            '^', // Beginning of line.
                            '.*?', // Zero or more chars.
                            '[^\/]*?', // Zero or more chars != /.
                            '$', // End of line.
                        ),
                        preg_quote($string, '/')
                    );
                },
                $patterns
            )
        ).')/i';
    }
    return $regex;
};
