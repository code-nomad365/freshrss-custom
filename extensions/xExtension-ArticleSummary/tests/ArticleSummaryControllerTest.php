<?php
/**
 * Unit tests for ArticleSummaryController
 * ArticleSummaryController 单元测试
 */

namespace Tests;

use PHPUnit\Framework\TestCase;

// 加载 bootstrap 文件中的模拟类
require_once __DIR__ . '/../phpstan-bootstrap.php';

// 加载主要的类文件
require_once __DIR__ . '/../Controllers/ArticleSummaryController.php';

/**
 * Test class for ArticleSummaryController
 * ArticleSummaryController 测试类
 */
class ArticleSummaryControllerTest extends TestCase
{
    /**
     * Test that the controller class exists
     * 测试控制器类是否存在
     */
    public function testControllerClassExists(): void
    {
        $this->assertTrue(class_exists('FreshExtension_ArticleSummary_Controller'));
    }

    /**
     * Test that the controller extends Minz_ActionController
     * 测试控制器是否继承自 Minz_ActionController
     */
    public function testControllerExtendsMinzActionController(): void
    {
        $reflection = new \ReflectionClass('FreshExtension_ArticleSummary_Controller');
        $this->assertTrue($reflection->isSubclassOf('Minz_ActionController'));
    }

    /**
     * Test that the controller is final
     * 测试控制器是否为 final 类
     */
    public function testControllerIsFinal(): void
    {
        $reflection = new \ReflectionClass('FreshExtension_ArticleSummary_Controller');
        $this->assertTrue($reflection->isFinal());
    }

    /**
     * Test that the summarizeAction method exists
     * 测试 summarizeAction 方法是否存在
     */
    public function testSummarizeActionMethodExists(): void
    {
        $this->assertTrue(method_exists('FreshExtension_ArticleSummary_Controller', 'summarizeAction'));
    }

    /**
     * Test that the isEmpty method exists
     * 测试 isEmpty 方法是否存在
     */
    public function testIsEmptyMethodExists(): void
    {
        $this->assertTrue(method_exists('FreshExtension_ArticleSummary_Controller', 'isEmpty'));
    }

    /**
     * Test that the htmlToMarkdown method exists
     * 测试 htmlToMarkdown 方法是否存在
     */
    public function testHtmlToMarkdownMethodExists(): void
    {
        $this->assertTrue(method_exists('FreshExtension_ArticleSummary_Controller', 'htmlToMarkdown'));
    }

    /**
     * Test that the processNode method exists
     * 测试 processNode 方法是否存在
     */
    public function testProcessNodeMethodExists(): void
    {
        $this->assertTrue(method_exists('FreshExtension_ArticleSummary_Controller', 'processNode'));
    }
}
