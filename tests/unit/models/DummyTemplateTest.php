<?php

namespace unit\models;

use app\models\DummyTemplate;
use Codeception\Test\Unit;
use LogicException;
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

    public function testSimpleParam()
    {
        $template = DummyTemplate::fromResult(
            'Hello, my name is Juni.',
            "Hello, my name is {$this->doubleOpenedTag}name$this->doubleClosedTag."
        );
        expect($template->getParams())->equals(['name' => 'Juni']);
    }

    public function testInvalidTemplate()
    {
        $this->expectExceptionObject(new LogicException('Invalid template.'));
        DummyTemplate::fromResult(
            'Hello, my name is Juni.',
            "Hello, my name is {$this->doubleOpenedTag}name$this->onceClosedTag."
        );
    }

    public function testResultTemplateMismatch()
    {
        $this->expectExceptionObject(new LogicException('Result not matches original template.'));
        DummyTemplate::fromResult(
            'Hello, my lastname is Juni.',
            "Hello, my name is {$this->doubleOpenedTag}name$this->doubleClosedTag."
        );
    }

    public function testEmptyParam()
    {
        $template = DummyTemplate::fromResult(
            'Hello, my name is .',
            "Hello, my name is {$this->doubleOpenedTag}name$this->doubleClosedTag."
        );
        expect($template->getParams())->equals(['name' => '']);
    }

    public function testHtmlParam()
    {
        $template = DummyTemplate::fromResult(
            'Hello, my name is <robot>.',
            "Hello, my name is {$this->onceOpenedTag}name$this->onceClosedTag."
        );
        expect($template->getParams())->equals(['name' => '<robot>']);
    }

    public function testHtmlEncodedParam()
    {
        $template = DummyTemplate::fromResult(
            'Hello, my name is &lt;robot&gt;.',
            "Hello, my name is {$this->doubleOpenedTag}name$this->doubleClosedTag."
        );
        expect($template->getParams())->equals(['name' => '<robot>']);
    }
}