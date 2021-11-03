<?php

namespace unit\models;

use app\models\DummyTemplate\DummyTemplate;
use app\models\DummyTemplate\InvalidTemplateException;
use app\models\DummyTemplate\ResultTemplateMismatchException;
use Codeception\Test\Unit;
use UnitTester;

class DummyTemplateTest extends Unit
{
    protected UnitTester $tester;

    private string $onceOpenedTag;
    private string $doubleOpenedTag;
    private string $onceClosedTag;
    private string $doubleClosedTag;

    protected function _before()
    {
        $this->onceOpenedTag = DummyTemplate::OPEN_TAG;
        $this->doubleOpenedTag = DummyTemplate::OPEN_TAG . DummyTemplate::OPEN_TAG;
        $this->onceClosedTag = DummyTemplate::CLOSE_TAG;
        $this->doubleClosedTag = DummyTemplate::CLOSE_TAG . DummyTemplate::CLOSE_TAG;
    }

    protected function _after()
    {
    }

    /**
     * @throws InvalidTemplateException
     * @throws ResultTemplateMismatchException
     */
    public function testSimpleParam()
    {
        $template = DummyTemplate::fromResult(
            'Hello, my name is Juni.',
            "Hello, my name is {$this->doubleOpenedTag}name$this->doubleClosedTag."
        );
        expect($template->getParams())->equals(['name' => 'Juni']);
    }

    /**
     * @throws InvalidTemplateException
     * @throws ResultTemplateMismatchException
     */
    public function testInvalidTemplate()
    {
        $this->expectExceptionObject(new InvalidTemplateException('Invalid template.'));
        DummyTemplate::fromResult(
            'Hello, my name is Juni.',
            "Hello, my name is {$this->doubleOpenedTag}name$this->onceClosedTag."
        );
    }

    /**
     * @throws InvalidTemplateException
     * @throws ResultTemplateMismatchException
     */
    public function testResultTemplateMismatch()
    {
        $this->expectExceptionObject(new ResultTemplateMismatchException('Result not matches original template.'));
        DummyTemplate::fromResult(
            'Hello, my lastname is Juni.',
            "Hello, my name is {$this->doubleOpenedTag}name$this->doubleClosedTag."
        );
    }

    /**
     * @throws InvalidTemplateException
     * @throws ResultTemplateMismatchException
     */
    public function testEmptyParam()
    {
        $template = DummyTemplate::fromResult(
            'Hello, my name is .',
            "Hello, my name is {$this->doubleOpenedTag}name$this->doubleClosedTag."
        );
        expect($template->getParams())->equals(['name' => '']);
    }

    /**
     * @throws InvalidTemplateException
     * @throws ResultTemplateMismatchException
     */
    public function testHtmlParam()
    {
        $template = DummyTemplate::fromResult(
            'Hello, my name is <robot>.',
            "Hello, my name is {$this->onceOpenedTag}name$this->onceClosedTag."
        );
        expect($template->getParams())->equals(['name' => '<robot>']);
    }

    /**
     * @throws InvalidTemplateException
     * @throws ResultTemplateMismatchException
     */
    public function testHtmlEncodedParam()
    {
        $template = DummyTemplate::fromResult(
            'Hello, my name is &lt;robot&gt;.',
            "Hello, my name is {$this->doubleOpenedTag}name$this->doubleClosedTag."
        );
        expect($template->getParams())->equals(['name' => '<robot>']);
    }
}