<?php
/**
 * This file is part of the "Easy System" package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Damon Smith <damon.easy.system@gmail.com>
 */
namespace Es\Template\Test;

use Es\Services\Services;
use Es\Services\ServicesTrait;
use Es\Template\Renderer;
use Es\Template\TemplateEngine;
use Es\View\ViewModel;

class TemplateEngineTest extends \PHPUnit_Framework_TestCase
{
    use ServicesTrait;

    public function testGetRenderer()
    {
        $services = new Services();
        $renderer = new Renderer();
        $services->set('DefaultTemplateRenderer', $renderer);

        $this->setServices($services);
        $engine = new TemplateEngine();
        $this->assertSame($renderer, $engine->getRenderer());
    }

    public function testSetRenderer()
    {
        $services = new Services();
        $this->setServices($services);

        $renderer = new Renderer();
        $engine   = new TemplateEngine();
        $engine->setRenderer($renderer);
        $this->assertSame($renderer, $services->get('DefaultTemplateRenderer'));
    }

    public function testRender()
    {
        $result   = 'Lorem ipsum dolor sit amet';
        $model    = new ViewModel();
        $renderer = $this->getMock(Renderer::CLASS);
        $engine   = new TemplateEngine();
        $engine->setRenderer($renderer);

        $renderer
            ->expects($this->once())
            ->method('render')
            ->with($this->identicalTo($model))
            ->will($this->returnValue($result));

        $return = $engine->render($model);
        $this->assertSame($return, $result);
    }
}
