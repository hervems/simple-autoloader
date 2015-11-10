<?php
/**
 * This file is part of Simple Autoloader
 *
 * @copyright Copyright (c) 2015 Hervé Seignole (herve.seignole@gmail.com)
 * @license   LGPL, please view the LICENSE file.
 * @author    Hervé Seignole <herve.seignole@gmail.com>
 */

declare(strict_types=1);

namespace SimpleAutoloader;

/**
 * Cache Generator.
 */
class CacheGenerator
{
    /**
     * Arguments
     *
     * @var array
     */
    private $args = [
        'options' => [
            '--filename' => [
                'desc' => '--filename <file>       Use <file> for the cache',
                'value' => 'classes.php.cache'
            ],
            '--regexfilter' => [
                'desc' => '--regexfilter <filter>  Use <filter> expression to find file',
                'value' => '/^.+\.php/i'

            ]
        ],
        'mandatory' => [
            'directory' => [
                'desc' => '<directory>             Directory of classes files',
                'value' => ''
            ]
        ]
    ];

    /**
     * Errors list
     *
     * @var array
     */
    private $errors = [];

    /**
     * Version
     */
    const VERSION = '0.0.1';

    /**
     * Author
     */
    const AUTHOR = 'Hervé Seignole (herve.seignole@gmail.com)';

    /**
     * Constructor
     *
     * @param array $args Arguments.
     * @return void
     */
    public function __construct(array $args)
    {
        if (count($errors = $this->setArgs($args)) === 0) {
            $this->run();
        } else {
            $this->helpMessage = $this->help($errors);
            $this->errors = $errors;
        }
    }

    /**
     * Get help message.
     *
     * @return string
     */
    public function getHelpMessage(): string
    {
        return $this->helpMessage;
    }

    /**
     * Get errors list.
     *
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Set arguments.
     *
     * @param  array $args Arguments.
     * @return array Return errors list.
     */
    public function setArgs(array $args): array
    {
        $errors = [];

        if (!isset($args[1])) {
            $errors[] = 'A directory is needed!';
            return $errors;
        }

        /*
         * To shift the first args, the program!
         * Not a bug.
         */
        array_shift($args);

        /* shift and set the directory */
        $directory = array_shift($args);

        if (!is_dir($directory)) {
            $errors[] = 'Directory "' . $directory . '" does not exists!';
        }

        $this->args['mandatory']['directory']['value'] = $directory;

        if (($length = count($args)) % 2 != 0) {
            $errors[] = 'Option needs a value!';
        } else {
            /* Parse others args (options) */
            $validOptions = array_keys($this->args['options']);

            for ($position = 0; $position < $length; $position += 2) {
                if (!in_array($args[$position], $validOptions)) {
                    $errors[] = 'Option "' . $args[$position] . '" is not valid!';
                } elseif (strlen($args[$position + 1]) == 0) {
                    $errors[] = 'Option value of "' . $args[$position] . '" is empty!';
                } else {
                    $this->args['options'][$args[$position]]['value'] = $args[$position + 1];
                }
            }
        }

        return $errors;
    }

    /**
     * Run cache generation
     *
     * @return void
     * @throws \Exception Parse error if the file is bad.
     */
    public function run()
    {
        $directory = new \RecursiveDirectoryIterator(
            $this->args['mandatory']['directory']['value']
        );

        $iterator = new \RecursiveIteratorIterator($directory);

        $regex = new \RegexIterator(
            $iterator,
            $this->args['options']['--regexfilter']['value'],
            \RecursiveRegexIterator::GET_MATCH
        );

        $classes = [];

        $filename = $this->args['options']['--filename']['value'];

        if (file_exists($filename)) {
            try {
                $classes = include $filename;
            } catch (\Throwable $exception) {
                throw new \Exception(
                    'Parse error when including "' . $filename . '"'
                );
            }
        }

        $found = false;

        foreach ($regex as $file) {
            if (is_array($file)) {
                list($classes, $found) = $this->parseFile($file[0], $classes, $found);
            }
        }

        /* If we found new classes, you need to write a new
         * "classes.php.cache"
         */
        if ($found) {
            $content = '<?php' . "\n" .
                'return ' .
                str_replace(['array (', ')'], ['[', ']'], var_export($classes, true)) .
                ';';

            file_put_contents($filename, $content);
        }
    }

    /**
     * Parse file and search Class, Interface, Trait, Namespace.
     * Populate the classes array.
     *
     * @param string $file    A PHP file.
     * @param array  $classes An array of (classes => files).
     * @param bool   $found   Found a new class.
     *
     * @return array Returns $classes modified.
     * @throws \Exception If we have conflict, two same classes but in two different file.
     */
    public function parseFile(string $file, array $classes, bool $found): array
    {
        $namespace = '';

        $content = file_get_contents($file);

        $tokens = token_get_all($content);
        $length = count($tokens);

        foreach ($tokens as $index => $token) {

            if (is_array($token)) {
                $token[0] = token_name($token[0]);
            }

            /* is Namespace */
            if (is_array($token) &&
                $token[0] == 'T_NAMESPACE') {
                $namespace = '';

                for ($position = $index + 2; $position < $length; ++$position) {
                    if (isset($tokens[$position]) &&
                        is_array($tokens[$position])) {
                        $namespace .= $tokens[$position][1];
                    } elseif (isset($tokens[$position]) &&
                        is_string($tokens[$position]) &&
                        $tokens[$position] === ';') {
                        break;
                    }
                }
            }

            /* is Class or Interface or Trait */
            if (is_array($token) &&
                ($token[0] == 'T_CLASS' ||
                $token[0] == 'T_INTERFACE' ||
                $token[0] == 'T_TRAIT')) {

                /* bypass token whitespace */
                if (isset($tokens[$index + 1]) &&
                    is_array($tokens[$index + 1]) &&
                    $tokens[$index + 1][0] == T_WHITESPACE) {

                    if (isset($tokens[$index + 2]) &&
                        is_array($tokens[$index + 2]) &&
                        $tokens[$index + 2][0] == T_STRING) {

                        $class = $tokens[$index + 2][1];

                        if ($namespace != '') {
                            $class = $namespace . '\\' . $class;
                        }

                        if (!isset($classes[$class])) {
                            $classes[$class] = realpath($file);
                            $found = true;
                        } elseif ($classes[$class] != realpath($file)) {
                            throw new \Exception('class "' . $class . '" already load [conflict]');
                        }
                    }
                }
            }
        }

        return [
            $classes,
            $found
        ];
    }

    /**
     * Help message
     *
     * @param  array $errors Errors list.
     * @return string Return help.
     */
    public function help(array $errors): string
    {
        /* Header */
        $return = 'autoload-cache-generator (' .
            CacheGenerator::VERSION . ') by ' .
            CacheGenerator::AUTHOR . PHP_EOL . PHP_EOL;

        /* Usage */
        $return .= 'Usage: autoload-cache-generator <directory> [options]' .
            PHP_EOL . PHP_EOL;
 
        $return .= '  ' . $this->args['mandatory']['directory']['desc'] . PHP_EOL . PHP_EOL;

        if (count($errors) != 0) {
            $return .= 'Errors:' . PHP_EOL;
            foreach ($errors as $index => $error) {
                $return .= '  ' . $index . '. ' . $error . PHP_EOL;
            }
            $return .= PHP_EOL;
        }

        $return .= 'Options:' . PHP_EOL . PHP_EOL;

        foreach ($this->args['options'] as $option) {
            $return .= '  ' . $option['desc'] .
                ' (default value: ' . $option['value'] . ')' . PHP_EOL;
        }

        return $return;
    }
}
