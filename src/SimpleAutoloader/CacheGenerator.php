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
                'value' => '/^.+\.php$/i'

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
     * Found classes
     *
     * @var bool
     */
    private $foundClasses = false;

    /**
     * Read context (file or array via property file)
     *
     * @var string
     */
    private $readContext = 'file';

    /**
     * File contains php files for "array" get context.
     *
     * @var array
     */
    private $files = [];

    /**
     * Write context (file or array via property file)
     * for classes cache
     *
     * @var string
     */
    private $writeContext = 'file';

    /**
     * classes cache.
     *
     * @var array
     */
    private $classesCache = [];

    /**
     * Version
     */
    const VERSION = '0.0.1';

    /**
     * Author
     */
    const AUTHOR = 'Hervé Seignole (herve.seignole@gmail.com)';

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
     * Get classes cache in write context mode (array).
     *
     * @return array
     */
    public function getClassesCache(): array
    {
        return $this->classesCache;
    }

    /**
     * hasFoundClasses
     *
     * @return bool
     */
    public function hasFoundClasses(): bool
    {
        return $this->foundClasses;
    }

    /**
     * Set files array.
     *
     * @param  array $files Files (key = directory + filename, value = content).
     * @return CacheGenerator Provide a fluid interface.
     */
    public function setFiles(array $files): CacheGenerator
    {
        $this->files = $files;
        return $this;
    }

    /**
     * Set read context.
     *
     * @param  string $context Context (file or array).
     * @return CacheGenerator Provide a fluid interface.
     */
    public function setReadContext(string $context): CacheGenerator
    {
        $this->readContext = $context;
        return $this;
    }

    /**
     * Set write context.
     *
     * @param  string $context Context (file or array).
     * @return CacheGenerator Provide a fluid interface.
     */
    public function setWriteContext(string $context): CacheGenerator
    {
        $this->writeContext = $context;
        return $this;
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

        if (!isset($args[1]) && $this->readContext == 'file') {
            $errors[] = [
                'errorNumber' => 0,
                'errorString' => 'A directory is needed!',
                'errorFile' => __FILE__,
                'errorLine' => __LINE__
            ];
            return $errors;
        }

        /*
         * To shift the first args, the program!
         * Not a bug.
         */
        array_shift($args);

        if ($this->readContext == 'file') {
            /* Shift and set the directory */
            $directory = array_shift($args);

            if (!is_dir($directory)) {
                $errors[] = [
                    'errorNumber' => 0,
                    'errorString' => 'Directory "' . $directory . '" does not exists!',
                    'errorFile' => __FILE__,
                    'errorLine' => __LINE__
                ];
            }

            $this->args['mandatory']['directory']['value'] = $directory;
        }

        if (($length = count($args)) % 2 != 0) {
            $errors[] = [
                'errorNumber' => 0,
                'errorString' => 'Option needs a value!',
                'errorFile' => __FILE__,
                'errorLine' => __LINE__
            ];
        } else {
            /* Parse others args (options) */
            $validOptions = array_keys($this->args['options']);

            for ($position = 0; $position < $length; $position += 2) {
                if (!in_array($args[$position], $validOptions)) {
                    $errors[] = [
                        'errorNumber' => 0,
                        'errorString' => 'Option "' . $args[$position] . '" is not valid!',
                        'errorFile' => __FILE__,
                        'errorLine' => __LINE__
                    ];
                } elseif (strlen($args[$position + 1]) == 0) {
                    $errors[] = [
                        'errorNumber' => 0,
                        'errorString' => 'Option value of "' . $args[$position] . '" is empty!',
                        'errorFile' => __FILE__,
                        'errorLine' => __LINE__
                    ];
                } else {
                    $this->args['options'][$args[$position]]['value'] = $args[$position + 1];
                }
            }
        }

        return $errors;
    }

    /**
     * Get arguments.
     *
     * @retun array
     */
    public function getArgs(): array
    {
        return $this->args;
    }

    /**
     * Scan directory to find PHP files
     *
     * @param  string $directory Directory to be scan.
     * @param  string $regexp    Regex to filter file.
     * @return array
     * @throws \UnexpectedValueException If the path can't be found or isn't a directory.
     */
    private function scanDirectoryToFindFiles(
        string $directory,
        string $regexp
    ): array {
        /*
         * If we are in array context, we use
         * files array.
         */
        if ($this->readContext === 'array') {
            return array_keys($this->files);
        }

        /* List of PHP files */
        $files = [];

        try {

            $recursiveDirectoryIterator = new \RecursiveDirectoryIterator(
                $directory
            );
            
            $iterator = new \RecursiveIteratorIterator(
                $recursiveDirectoryIterator
            );
            
            $regex = new \RegexIterator(
                $iterator,
                $regexp,
                \RecursiveRegexIterator::GET_MATCH
            );
        } catch (\UnexpectedValueException $exception) {
            throw $exception;
        }

        foreach ($regex as $file) {
            if (is_array($file)) {
                $files[] = $file[0];
            }
        }

        return $files;
    }

    /**
     * Run cache generation
     *
     * @param  array $args Arguments.
     * @return array Errors list.
     */
    public function run(array $args)
    {
        $errors = [];

        if (count($errors = $this->setArgs($args)) != 0) {
            $this->errors = $errors;
            return $errors;
        }

        try {
            $files = $this->scanDirectoryToFindFiles(
                $this->args['mandatory']['directory']['value'],
                $this->args['options']['--regexfilter']['value']
            );
        } catch (\UnexpectedValueException $exception) {
            $errors[] = [
                'errorNumber' => 0,
                'errorString' => $exception->getMessage(),
                'errorFile' => __FILE__,
                'errorLine' => __LINE__
            ];
            return $errors;
        }

        if (count($files) != 0) {

            $classes = [];
            $filename = '';

            if ($this->writeContext == 'file') {
                $filename = $this->args['options']['--filename']['value'];

                if (file_exists($filename)) {
                    try {
                        $classes = include $filename;

                        if ($classes === 1) {
                            $classes = [];
                        }
                    } catch (\Throwable $exception) {
                        $errors[] = [
                            'errorNumber' => 0,
                            'errorString' => 'Parse error when including "' . $filename . '"',
                            'errorFile' => __FILE__,
                            'errorLine' => __LINE__
                        ];
                        return $errors;
                    }
                }
            } else {
                $classes = $this->classesCache;
            }
        }

        $found = false;

        foreach ($files as $file) {
            $returnParseFile = $this->parseFile($file, $classes, $found);
            if (count($returnParseFile['errors']) != 0) {
                return $returnParseFile['errors'];
            }
            $classes = $returnParseFile['classes'];
            $found = $returnParseFile['found'];
        }

        $this->foundClasses = $found;

        /* If we found new classes, you need to write a new
         * "classes.php.cache"
         */
        if ($found) {

            if (count($errors = $this->putContent($filename, $classes)) != 0) {
                return $errors;
            }
        }

        return $errors;
    }

    /**
     * Get Content from filename or file property.
     *
     * @param  string $filename File name.
     * @return array Returns content of file and errors.
     */
    private function getContent(string $filename): array
    {
        $errors = [];

        if ($this->readContext === 'file') {
            return $this->fileGetContents($filename);
        } else {
            if (isset($this->files[$filename])) {
                return [
                    'content' => $this->files[$filename],
                    'errors' => []
                ];
            }

            return [
                'content' => '',
                'errors' => [
                    [
                        'errorNumber' => 0,
                        'errorString' => 'File "' . $filename . '" doesn\'t exists!',
                        'errorFile' => __FILE__,
                        'errorLine' => __LINE__
                    ]
                ]
            ];
        }
    }

    /**
     * Encapsulate file_get_contents function for test.
     *
     * @param  string $filename File name.
     * @return array
     */
    private function fileGetContents(string $filename): array
    {
        $errors = [];

        set_error_handler(
            function(
                $errorNumber,
                $errorString,
                $errorFile,
                $errorLine,
                array $errcontext) use (&$errors) {
                $errors[] = [
                    'errorNumber' => $errorNumber,
                    'errorString' => $errorString,
                    'errorFile' => $errorFile,
                    'errorLine' => $errorLine
                ];
            }
        );
        $content = file_get_contents($filename);
        restore_error_handler();
        return [
            'content' => (string) $content,
            'errors' => $errors
        ];
    }

    /**
     * put content
     *
     * @param  string $filename File name.
     * @param  string $classes Array of classes.
     * @return array  Errors
     */
    private function putContent(string $filename, array $classes): array
    {
        $errors = [];

        if ($this->writeContext === 'file') {

            $content = '<?php' . "\n" .
                'return ' .
                str_replace(['array (', ')'], ['[', ']'], var_export($classes, true)) .
                ';';
            $errors = $this->filePutContents($filename, $content);

        } else {
            $this->classesCache = $classes;
        }

        return $errors;
    }

    /**
     * Encapsulate file_put_contents function for test.
     *
     * @param  string $filename File name.
     * @param  string $content  Content.
     * @return array
     */
    private function filePutContents(string $filename, string $content): array
    {
        $errors = [];

        set_error_handler(
            function(
                $errorNumber,
                $errorString,
                $errorFile,
                $errorLine,
                array $errcontext) use (&$errors) {
                $errors[] = [
                    'errorNumber' => $errorNumber,
                    'errorString' => $errorString,
                    'errorFile' => $errorFile,
                    'errorLine' => $errorLine
                ];
            }
        );
        file_put_contents($filename, $content);
        restore_error_handler();
        return $errors;
    }

    /**
     * Parse file and search Class, Interface, Trait, Namespace.
     * Populate the classes array.
     *
     * @param  string $file    A PHP file.
     * @param  array  $classes An array of (classes => files).
     * @param  bool   $found   Found a new class.
     * @return array Returns classes modified / errors / found.
     */
    public function parseFile(string $file, array $classes, bool $found): array
    {
        $namespace = '';

        $contentAndErrors = $this->getContent($file);

        if (count($errors = $contentAndErrors['errors']) != 0) {
            return ['errors' => $errors];
        }

        $content = $contentAndErrors['content'];

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
                            if ($this->readContext == 'file') {
                                $classes[$class] = realpath($file);
                            } else {
                                $classes[$class] = $file;
                            }
                            $found = true;
                        } elseif (($this->readContext == 'file' &&
                            $classes[$class] != realpath($file)) ||
                            ($this->readContext == 'array' &&
                            $classes[$class] != $file)) {
                            $errors[] = [
                                'errorNumber' => 0,
                                'errorString' => 'class "' . $class . '" already load [conflict]',
                                'errorFile' => __FILE__,
                                'errorLine' => __LINE__
                            ];
                            return ['errors' => $errors];
                        }
                    }
                }
            }
        }

        return [
            'classes' => $classes,
            'found' => $found,
            'errors' => []
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
                $return .= '  ' . $index . '. ' . $error['errorString'] . PHP_EOL;
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
