<?php
namespace Core\Framework\Amvc\App;

use Core\Toolbox\Arrays\Flatten;

/**
 * Language.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class Language
{

    /**
     *
     * @var string
     */
    private $code;

    /**
     *
     * @var array
     */
    private $strings = [];

    /**
     *
     * @var Language
     */
    private $fallback_language;

    /**
     * Sets the language code
     *
     * @æparam string $code
     */
    public function setCode(string $code)
    {
        $this->code = $code;
    }

    /**
     * Returns the language code
     *
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * Sets a fallback langoage object which gets queried when there is no matching language string in this object
     *
     * @param Language $fallback_language
     */
    public function setFallbackLanguage(Language $fallback_language)
    {
        $this->fallback_language = $fallback_language;
    }

    /**
     * Loads a language file
     *
     * After laoding the file it's content will be flattened. Then the result will replace all keys already existing in
     * object string repository.
     *
     * @param string $filename
     *
     * @throws AppException
     */
    public function load(string $filename)
    {
        if (!file_exists($filename)) {
            Throw new AppException(sprintf('Languagefile "%s" does not exist.'), $filename);
        }

        $loaded = include $filename;

        if ($loaded) {
            $array = new Flatten($loaded);
            $array->setPreserveFlaggedArraysFlag(true);
            $this->strings = array_replace($this->strings, $array->flatten());
        }
    }

    /**
     * Returns all language strings
     *
     * @return array
     */
    public function getAll(): array
    {
        return $this->strings;
    }

    /**
     * Returns the string of string of a specific language key or an array of strings flagge as preserved
     *
     * You can create linked strings by setting '@@your.linked.string' as result in your language file.
     *
     * Will query a set fallback language object when the requested key is not set in apps language strings.
     * This way it is possible to create a chain of fallback request from one app to another.
     *
     * Important: Do not create circle requests!
     *
     * @param string $key
     *
     * @return string|array
     */
    public function get(string $key)
    { //
        $result = $this->strings[$key] ?? $key;

        // Is there a redirection to another string?
        if (is_string($result) && substr($result, 0, 2) == '@@') {
            $result = $this->get(substr($result, 2));
        }

        if ($result == $key && isset($this->fallback_language)) {
            $result = $this->fallback_language->get($key);
        }

        return $result;
    }
}
