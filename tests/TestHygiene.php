<?php

use Nullai\Hygiene\FilterBasicTags;
use PHPUnit\Framework\TestCase;
use \Nullai\Hygiene\SanitizeHtml;

class TestHygiene extends TestCase
{
    public function testViewEngineSanitizeAttributes()
    {
        $content = SanitizeHtml::escAttr('<&">');
        $this->assertEquals('&lt;&amp;&quot;&gt;', $content);
    }

    public function testViewEngineSanitizeHtml()
    {
        $content = SanitizeHtml::escHtml('<&">');
        $this->assertEquals('&lt;&amp;"&gt;', $content);
    }

    public function testViewEngineSanitizeJson()
    {
        $content = SanitizeHtml::escJson(['site' => '<My <a> " & Site> & " >>']);
        $this->assertEquals('{"site":"\u003CMy \u003Ca\u003E \u0022 \u0026 Site\u003E \u0026 \u0022 \u003E\u003E"}', $content);
    }

    public function testViewEngineAllowTags()
    {
        $content = SanitizeHtml::filterTags(
            "<script>alert('test');</script><a href=\"<script></script>\">Link</a>",
            ['a' => []]
        );
        $this->assertEquals('<a>Link</a>', $content);
    }

    public function testViewEngineAllowTagsWithAttributes()
    {
        $content = SanitizeHtml::filterTags(
            "<script>alert('test');</script><A HREF=\"'#'\" styLe='<script>alert(\"true\");</script>'>Link</A>",
            'a:href|style,br,p,ol,ul,figure:src'
        );
        $this->assertEquals('<a href="\'#\'" style="<script>alert(&quot;true&quot;);</script>">Link</a>', $content);

        $content = SanitizeHtml::filterTags(
            "<script>alert('test');</script><A HREF='#' styLe='content: \"main\"'>Link</A><br>",
            'a:href|style,br'
        );
        $this->assertEquals('<a href="#" style="content: &quot;main&quot;">Link</a><br>', $content);
    }

    public function testViewEngineAllowTagsWithTextareaAttributes()
    {
        $content = SanitizeHtml::filterTags(
            '<textarea value="some value"><script>alert(\'test\');</script></textarea>',
            'textarea'
        );
        $this->assertEquals('<textarea>&lt;script&gt;alert(\'test\');&lt;/script&gt;</textarea>', $content);

        $content = SanitizeHtml::filterTags(
            '<textarea value="<script>alert(\'test\');</script>"><script>alert(\'test\');</script></textarea>',
            'textarea:value'
        );
        $this->assertEquals('<textarea value="<script>alert(\'test\');</script>">&lt;script&gt;alert(\'test\');&lt;/script&gt;</textarea>', $content);
    }

    public function testViewEngineFilterTagsClass()
    {
        $content = SanitizeHtml::filterTags(
            '<textarea><script>alert(\'test\');</script></textarea>',
            new FilterBasicTags()->add('textarea', [])
        );
        $this->assertEquals('<textarea>&lt;script&gt;alert(\'test\');&lt;/script&gt;</textarea>', $content);

        $content = SanitizeHtml::filterTags(
            '<textarea><script>alert(\'test\');</script></textarea>',
            new FilterBasicTags()
        );
        $this->assertEquals('', $content);

        $content = SanitizeHtml::filterTags(
            '<a class="hover:mt-0"></a>',
            new FilterBasicTags()
        );
        $this->assertEquals('<a class="hover:mt-0"></a>', $content);

        $content = SanitizeHtml::filterTags(
            html: '<a></a>',
            tags: new FilterBasicTags(),
            allow: false
        );
        $this->assertEquals('', $content);
    }

    public function testViewEngineFilterTagsClassBlacklist()
    {
        $content = SanitizeHtml::filterTags(
            html: '<a></a><iframe></iframe><script></script>',
            tags: 'iframe,script',
            allow: false
        );
        $this->assertEquals('<a></a>', $content);
    }
}
