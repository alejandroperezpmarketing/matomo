<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Translation;

use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Log\LoggerInterface;
use Piwik\Piwik;
use Piwik\Translation\Loader\LoaderInterface;

/**
 * Translates messages.
 *
 * @api
 */
class Translator
{
    /**
     * Contains the translated messages, indexed by the language name.
     *
     * @var array
     */
    private $translations = [];

    /**
     * @var string
     */
    private $currentLanguage;

    /**
     * @var string
     */
    private $fallback = 'en';

    /**
     * Directories containing the translations to load.
     *
     * @var string[]
     */
    private $directories = [];

    /**
     * @var LoaderInterface
     */
    private $loader;

    private const LIST_TYPE_AND = 'And';
    private const LIST_TYPE_OR = 'Or';

    public function __construct(LoaderInterface $loader, ?array $directories = null)
    {
        $this->loader          = $loader;
        $this->currentLanguage = $this->getDefaultLanguage();

        if ($directories === null) {
            // TODO should be moved out of this class
            $directories = [PIWIK_INCLUDE_PATH . '/lang'];
        }
        $this->directories = $directories;
    }

    /**
     * Clean a string that may contain HTML special chars, single/double quotes, HTML entities, leading/trailing whitespace
     *
     * @param string $s
     * @return string
     */
    public static function clean($s)
    {
        return html_entity_decode(trim($s), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Returns an internationalized string using a translation ID. If a translation
     * cannot be found for the ID, the ID is returned.
     *
     * @param string           $translationId Translation ID, eg, `General_Date`.
     * @param array|string|int $args          `sprintf` arguments to be applied to the internationalized
     *                                        string.
     * @param string|null      $language      Optionally force the language.
     * @return string The translated string or `$translationId`.
     * @api
     */
    public function translate($translationId, $args = [], $language = null)
    {
        $args          = is_array($args) ? $args : [$args];
        $translationId = $translationId ?? '';

        if (strpos($translationId, "_") !== false) {
            [$plugin, $key] = explode("_", $translationId, 2);
            $language = is_string($language) ? $language : $this->currentLanguage;

            $translationId = $this->getTranslation($translationId, $language, $plugin, $key);
        }

        if (count($args) == 0) {
            return str_replace('%%', '%', $translationId);
        }
        return vsprintf($translationId, $args);
    }

    /**
     * Converts the given list of items into a listing (e.g. One, Two, and Three)
     *
     * @param array       $items
     * @param string|null $language
     * @return string
     */
    public function createAndListing(array $items, ?string $language = null): string
    {
        return $this->createListing(self::LIST_TYPE_AND, $items, $language);
    }

    /**
     * Converts the given list of items into a or listing (e.g. One, Two, or Three)
     *
     * @param array       $items
     * @param string|null $language
     * @return string
     */
    public function createOrListing(array $items, ?string $language = null): string
    {
        return $this->createListing(self::LIST_TYPE_OR, $items, $language);
    }

    /**
     * @param string      $listType type of the list (LIST_TYPE_AND or LIST_TYPE_OR)
     * @param array       $items
     * @param string|null $language
     * @return string
     */
    private function createListing(string $listType, array $items, ?string $language = null): string
    {
        switch (count($items)) {
            case 0:
                return '';
            case 1:
                return end($items);
            case 2:
                $pattern = $this->translate('Intl_ListPattern' . $listType . '2', [], $language);
                return str_replace(['{0}', '{1}'], [$items[0], $items[1]], $pattern);
            default:
                $patternStart  = $this->translate('Intl_ListPattern' . $listType . 'Start', [], $language);
                $patternMiddle = $this->translate('Intl_ListPattern' . $listType . 'Middle', [], $language);
                $patternEnd    = $this->translate('Intl_ListPattern' . $listType . 'End', [], $language);

                $result = $patternStart;

                while (count($items) > 2) {
                    $pattern = count($items) > 3 ? $patternMiddle : $patternEnd;
                    $result = str_replace(['{0}', '{1}'], [array_shift($items), $pattern], $result);
                }

                return str_replace(['{0}', '{1}'], [$items[0], $items[1]], $result);
        }
    }

    /**
     * @return string
     */
    public function getCurrentLanguage()
    {
        return $this->currentLanguage;
    }

    /**
     * @param string $language
     */
    public function setCurrentLanguage($language)
    {
        if (!$language) {
            $language = $this->getDefaultLanguage();
        }

        $this->currentLanguage = $language;
    }

    /**
     * @return string The default configured language.
     */
    public function getDefaultLanguage()
    {
        $generalSection = Config::getInstance()->General;

        // the config may not be available (for example, during environment setup), so we default to 'en'
        // if the config cannot be found.
        return @$generalSection['default_language'] ?: 'en';
    }

    /**
     * Generate javascript translations array
     */
    public function getJavascriptTranslations()
    {
        $clientSideTranslations = array();
        foreach ($this->getClientSideTranslationKeys() as $id) {
            if (strpos($id, '_') === false) {
                StaticContainer::get(LoggerInterface::class)->warning(
                    'Unexpected translation key found in client side translations: {translation_key}',
                    ['translation_key' => $id]
                );
                continue;
            }
            [$plugin, $key] = explode('_', $id, 2);
            $clientSideTranslations[$id] = $this->decodeEntitiesSafeForHTML($this->getTranslation($id, $this->currentLanguage, $plugin, $key));
        }

        $js = 'var translations = ' . json_encode($clientSideTranslations) . ';';
        $js .= "\n" . 'if (typeof(piwik_translations) == \'undefined\') { var piwik_translations = new Object; }' .
            'for(var i in translations) { piwik_translations[i] = translations[i];} ';
        return $js;
    }

    /**
     * Decodes all entities in the given string except of &gt; and &lt;
     *
     * @param string $text
     * @return string
     */
    private function decodeEntitiesSafeForHTML(string $text): string
    {
        // replace encoded html tag entities, as they need to remain encoded
        $text = str_replace(
            ['&gt;', '&lt;'],
            ['###gt###', '###lt###'],
            $text
        );

        // decode all remaining entities
        $text = html_entity_decode($text);

        // recover encoded html tag entities
        return str_replace(
            ['###gt###', '###lt###'],
            ['&gt;', '&lt;'],
            $text
        );
    }

    /**
     * Returns the list of client side translations by key. These translations will be outputted
     * to the translation JavaScript.
     */
    private function getClientSideTranslationKeys()
    {
        $result = array();

        /**
         * Triggered before generating the JavaScript code that allows i18n strings to be used
         * in the browser.
         *
         * Plugins should subscribe to this event to specify which translations
         * should be available to JavaScript.
         *
         * Event handlers should add whole translation keys, ie, keys that include the plugin name.
         *
         * **Example**
         *
         *     public function getClientSideTranslationKeys(&$result)
         *     {
         *         $result[] = "MyPlugin_MyTranslation";
         *     }
         *
         * @param array &$result The whole list of client side translation keys.
         */
        Piwik::postEvent('Translate.getClientSideTranslationKeys', array(&$result));

        $result = array_unique($result);

        return $result;
    }

    /**
     * Add a directory containing translations.
     *
     * @param string $directory
     */
    public function addDirectory($directory)
    {
        if (isset($this->directories[$directory])) {
            return;
        }
        // index by name to avoid duplicates
        $this->directories[$directory] = $directory;

        // clear currently loaded translations to force reloading them
        $this->translations = array();
    }

    /**
     * Should be used by tests only, and this method should eventually be removed.
     */
    public function reset()
    {
        $this->currentLanguage = $this->getDefaultLanguage();
        $this->directories = array(PIWIK_INCLUDE_PATH . '/lang');
        $this->translations = array();
    }

    /**
     * @param string $translation
     * @return null|string
     */
    public function findTranslationKeyForTranslation($translation)
    {
        foreach ($this->getAllTranslations() as $key => $translations) {
            $possibleKey = array_search($translation, $translations);
            if (!empty($possibleKey)) {
                return $key . '_' . $possibleKey;
            }
        }

        return null;
    }

    /**
     * Returns all the translation messages loaded.
     *
     * @return array
     */
    public function getAllTranslations()
    {
        $this->loadTranslations($this->currentLanguage);

        if (!isset($this->translations[$this->currentLanguage])) {
            return array();
        }

        return $this->translations[$this->currentLanguage];
    }

    private function getTranslation($id, $lang, $plugin, $key)
    {
        $this->loadTranslations($lang);

        if (
            isset($this->translations[$lang][$plugin])
            && isset($this->translations[$lang][$plugin][$key])
        ) {
            return $this->translations[$lang][$plugin][$key];
        }

        /**
         * Fallback for keys moved to new Intl plugin to avoid untranslated string in non core plugins
         * @todo remove this in Piwik 3.0
         */
        if ($plugin != 'Intl') {
            if (
                isset($this->translations[$lang]['Intl'])
                && isset($this->translations[$lang]['Intl'][$key])
            ) {
                return $this->translations[$lang]['Intl'][$key];
            }
        }

        // fallback
        if ($lang !== $this->fallback) {
            return $this->getTranslation($id, $this->fallback, $plugin, $key);
        }

        return $id;
    }

    private function loadTranslations($language)
    {
        if (empty($language) || isset($this->translations[$language])) {
            return;
        }

        $this->translations[$language] = $this->loader->load($language, $this->directories);
    }
}
