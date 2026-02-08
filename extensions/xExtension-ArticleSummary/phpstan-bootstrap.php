<?php
/**
 * PHPStan bootstrap file
 * PHPStan 启动文件
 */

// Define constants for FreshRSS if not already defined
if (!defined('FRESHRSS_PATH')) {
    define('FRESHRSS_PATH', __DIR__ . '/../../..');
}

// Define XML constants if not already defined
if (!defined('XML_ELEMENT_NODE')) {
    define('XML_ELEMENT_NODE', 1);
}

if (!defined('XML_TEXT_NODE')) {
    define('XML_TEXT_NODE', 3);
}

// Mock FreshRSS classes for static analysis
if (!class_exists('FreshRSS_Context')) {
    class FreshRSS_Context {
        public static $user_conf;
    }
}

if (!class_exists('FreshRSS_Entry')) {
    class FreshRSS_Entry {
        public function id(): string {
            return '';
        }
        public function title(): string {
            return '';
        }
        public function author(): string {
            return '';
        }
        public function content(): string {
            return '';
        }
        public function _content(string $content): void {
        }
    }
}

if (!class_exists('FreshRSS_Factory')) {
    class FreshRSS_Factory {
        public static function createEntryDao() {
            return null;
        }
    }
}

if (!class_exists('Minz_Extension')) {
    class Minz_Extension {
        protected array $csp_policies = [];
        
        public function init(): void {
        }
        
        public function registerHook(string $hook, callable $callback): void {
        }
        
        public function registerController(string $name): void {
        }
        
        public function registerTranslates(string $path): void {
        }
        
        public function getFileUrl(string $file, string $type): string {
            return '';
        }
        
        public function handleConfigureAction(): void {
        }
    }
}

if (!class_exists('Minz_ActionController')) {
    class Minz_ActionController {
        public $view;
        
        public function __construct() {
            $this->view = new stdClass();
        }
    }
}

if (!class_exists('Minz_Request')) {
    class Minz_Request {
        public static function param(string $name, $default = null) {
            return $default;
        }
        
        public static function isPost(): bool {
            return false;
        }
    }
}

if (!class_exists('Minz_Url')) {
    class Minz_Url {
        public static function display(array $params): string {
            return '';
        }
    }
}

if (!class_exists('Minz_View')) {
    class Minz_View {
        public static function appendStyle(string $url): void {
        }
        
        public static function appendScript(string $url): void {
        }
    }
}

if (!class_exists('Minz_HookType')) {
    class Minz_HookType {
        public const EntryBeforeDisplay = 'entry_before_display';
    }
}

if (!class_exists('DOMNode')) {
    class DOMNode {
        public int $nodeType;
        public string $nodeName;
        public string $nodeValue;
        public iterable $childNodes;
        public ?DOMNode $firstChild;
        
        public function getAttribute(string $name): string {
            return '';
        }
        
        public function hasChildNodes(): bool {
            return false;
        }
        
        public function getFirstChild(): ?DOMNode {
            return null;
        }
    }
}

if (!class_exists('DOMDocument')) {
    class DOMDocument {
        public function loadHTML(string $html): bool {
            return true;
        }
    }
}

if (!class_exists('DOMXPath')) {
    class DOMXPath {
        public function __construct() {
        }
        
        public function query(string $expression): DOMNodeList {
            return new DOMNodeList();
        }
    }
}

if (!class_exists('DOMNodeList')) {
    class DOMNodeList implements IteratorAggregate {
        public function getIterator(): Traversable {
            return new ArrayIterator([]);
        }
    }
}

if (!function_exists('_t')) {
    function _t(string $key, ...$args): string {
        return $key;
    }
}
