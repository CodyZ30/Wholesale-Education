<?php
// admin/translation.php — Translation system for admin panel

class Translation {
    private static $translations = [];
    private static $current_language = 'en';
    private static $available_languages = [
        'en' => 'English',
        'es' => 'Spanish', 
        'fr' => 'French',
        'de' => 'German',
        'it' => 'Italian',
        'pt' => 'Portuguese',
        'zh' => 'Chinese',
        'ja' => 'Japanese',
        'ko' => 'Korean',
        'ar' => 'Arabic',
        'ru' => 'Russian'
    ];

    /**
     * Initialize the translation system
     */
    public static function init() {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Get current language from session or default to English
        self::$current_language = $_SESSION['admin_language'] ?? 'en';
        
        // Load translations for current language
        self::loadTranslations(self::$current_language);
    }

    /**
     * Load translations for a specific language
     */
    private static function loadTranslations($language) {
        $translation_file = __DIR__ . "/translations/{$language}.json";
        
        if (file_exists($translation_file)) {
            $json_content = file_get_contents($translation_file);
            $translations = json_decode($json_content, true);
            
            if (is_array($translations)) {
                self::$translations = $translations;
            }
        }
        
        // Fallback to English if current language fails to load
        if (empty(self::$translations) && $language !== 'en') {
            self::loadTranslations('en');
        }
    }

    /**
     * Translate a key to the current language
     */
    public static function t($key, $default = null) {
        if (empty(self::$translations)) {
            self::init();
        }
        
        return self::$translations[$key] ?? $default ?? $key;
    }

    /**
     * Set the current language
     */
    public static function setLanguage($language) {
        if (array_key_exists($language, self::$available_languages)) {
            self::$current_language = $language;
            $_SESSION['admin_language'] = $language;
            self::loadTranslations($language);
            return true;
        }
        return false;
    }

    /**
     * Get current language
     */
    public static function getCurrentLanguage() {
        return self::$current_language;
    }

    /**
     * Get available languages
     */
    public static function getAvailableLanguages() {
        return self::$available_languages;
    }

    /**
     * Get language name by code
     */
    public static function getLanguageName($code) {
        return self::$available_languages[$code] ?? $code;
    }

    /**
     * Check if a translation key exists
     */
    public static function hasTranslation($key) {
        return isset(self::$translations[$key]);
    }

    /**
     * Get all translations for current language
     */
    public static function getAllTranslations() {
        return self::$translations;
    }
}

// Initialize translation system
Translation::init();

// Helper function for easy access
function __($key, $default = null) {
    return Translation::t($key, $default);
}

// Helper function to get current language
function getCurrentLanguage() {
    return Translation::getCurrentLanguage();
}

// Helper function to get available languages
function getAvailableLanguages() {
    return Translation::getAvailableLanguages();
}
?>
