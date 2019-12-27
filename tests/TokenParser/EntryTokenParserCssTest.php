<?php

namespace tests\Fullpipe\TwigWebpackExtension\TokenParser;

use Fullpipe\TwigWebpackExtension\TokenParser\EntryTokenParser;
use Fullpipe\TwigWebpackExtension\TokenParser\EntryTokenParserCss;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Loader\LoaderInterface;
use Twig\Node\TextNode;
use Twig\Parser;
use Twig\Source;

class EntryTokenParserCssTest extends TestCase
{
    public function testItIsAParser()
    {
        $this->assertInstanceOf(EntryTokenParser::class, new EntryTokenParserCss(__DIR__.'/../Resource/manifest.json', '/build/'));
    }

    public function testGenerate()
    {
        $env = $this->getEnv(__DIR__.'/../Resource/manifest.json', '/build/');
        $parser = new Parser($env);
        $source = new Source("{% webpack_entry_css 'main' %}", '');
        $stream = $env->tokenize($source);

        $expected = new TextNode('<link type="text/css" href="/build/main.css" rel="stylesheet">', 1);
        $expected->setSourceContext($source);

        $this->assertEquals(
            $expected,
            $parser->parse($stream)->getNode('body')->getNode('0')
        );
    }

    public function testItThrowsExceptionIfNoManifest()
    {
        $this->expectException(LoaderError::class);

        $env = $this->getEnv(__DIR__.'/../Resource/not_exists.json', '/build/');
        $parser = new Parser($env);
        $source = new Source("{% webpack_entry_css 'main' %}", '');
        $stream = $env->tokenize($source);
        $parser->parse($stream);
    }

    public function testItThrowsExceptionIfEntryNotExists()
    {
        $this->expectException(LoaderError::class);

        $env = $this->getEnv(__DIR__.'/../Resource/manifest.json', '/build/');
        $parser = new Parser($env);
        $source = new Source("{% webpack_entry_css 'not_exists' %}", '');
        $stream = $env->tokenize($source);
        $parser->parse($stream);
    }

    private function getEnv(string $manifest, string $publicPath): Environment
    {
        $env = new Environment(
            $this->getMockBuilder(LoaderInterface::class)->getMock(),
            ['cache' => false, 'autoescape' => false, 'optimizations' => 0]
        );
        $env->addTokenParser(new EntryTokenParserCss($manifest, $publicPath));

        return $env;
    }
}
