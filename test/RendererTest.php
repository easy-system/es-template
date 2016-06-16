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

use Error;
use Es\Services\Services;
use Es\Services\ServicesTrait;
use Es\Template\Renderer;
use Es\View\Resolver;
use Es\View\ViewModel;
use Es\ViewHelpers\ViewHelpers;
use Exception;

class RendererTest extends \PHPUnit_Framework_TestCase
{
    use ServicesTrait;

    protected $files;

    public function setUp()
    {
        require_once 'FakeHelper.php';

        $this->files = __DIR__ . DIRECTORY_SEPARATOR
                     . 'files' . DIRECTORY_SEPARATOR;

        if (! class_exists('Error', false)) {
            require_once 'Error.php';
        }
    }

    public function testGetResolver()
    {
        $resolver = new Resolver();
        $services = new Services();
        $services->set('ViewResolver', $resolver);

        $this->setServices($services);
        $renderer = new Renderer();
        $this->assertSame($resolver, $renderer->getResolver());
    }

    public function testSetResolver()
    {
        $services = new Services();
        $this->setServices($services);

        $resolver = new Resolver();
        $renderer = new Renderer();
        $renderer->setResolver($resolver);
        $this->assertSame($resolver, $services->get('ViewResolver'));
    }

    public function testGetHelpers()
    {
        $helpers  = new ViewHelpers();
        $services = new Services();
        $services->set('ViewHelpers', $helpers);

        $this->setServices($services);
        $renderer = new Renderer();
        $this->assertSame($helpers, $renderer->getHelpers());
    }

    public function testSetHelpers()
    {
        $services = new Services();
        $this->setServices($services);

        $helpers  = new ViewHelpers();
        $renderer = new Renderer();
        $renderer->setHelpers($helpers);
        $this->assertSame($helpers, $services->get('ViewHelpers'));
    }

    public function testSetVariables()
    {
        $variables = [
            'foo' => 'bar',
            'bat' => 'baz',
        ];
        $renderer = new Renderer();
        $renderer->setVariables($variables);
        $this->assertSame($variables, $renderer->getVariables());
    }

    public function testGetVariables()
    {
        $renderer = new Renderer();
        $this->assertInternalType('array', $renderer->getVariables());
    }

    public function invalidSourceDataProvider()
    {
        $sources = [
            true,
            false,
            100,
            [],
            new \stdClass(),
        ];
        $return = [];
        foreach ($sources as $source) {
            $return[] = [$source];
        }

        return $return;
    }

    /**
     * @dataProvider invalidSourceDataProvider
     */
    public function testRenderRaiseExceptionIfInvalidSourceProvided($source)
    {
        $renderer = new Renderer();
        $this->setExpectedException('InvalidArgumentException');
        $renderer->render($source);
    }

    public function testRenderRendersTemplate()
    {
        $file     = $this->files . 'foo.phtml';
        $template = 'foo/foo';

        $renderer = new Renderer();
        $resolver = $this->getMock(Resolver::CLASS);
        $renderer->setResolver($resolver);

        $resolver
            ->expects($this->once())
            ->method('resolve')
            ->with($this->identicalTo($template))
            ->will($this->returnValue($file));

        $this->assertSame('foo', $renderer->render($template));
    }

    public function testRenderRendersViewModel()
    {
        $file     = $this->files . 'foo.phtml';
        $template = 'foo/foo';
        $module   = 'Foo';

        $model = new ViewModel();
        $model->setTemplate($template);
        $model->setModule($module);

        $renderer = new Renderer();
        $resolver = $this->getMock(Resolver::CLASS);
        $renderer->setResolver($resolver);

        $resolver
            ->expects($this->once())
            ->method('resolve')
            ->with(
                $this->identicalTo($template),
                $this->identicalTo($module)
            )
            ->will($this->returnValue($file));

        $this->assertSame('foo', $renderer->render($model));
    }

    public function testRenderSetsLocalVariables()
    {
        $file      = $this->files . 'local_variables.phtml';
        $template  = 'foo/foo';
        $variables = ['foo' => 'foo', 'bar' => 'bar'];

        $renderer = new Renderer();
        $resolver = $this->getMock(Resolver::CLASS);
        $renderer->setResolver($resolver);

        $resolver
            ->expects($this->once())
            ->method('resolve')
            ->with($this->identicalTo($template))
            ->will($this->returnValue($file));

        $this->assertSame('foobar', $renderer->render($template, $variables));
    }

    public function testRenderMergesAndSetsVariablesUsingViewModelVariables()
    {
        $file     = $this->files . 'local_variables.phtml';
        $template = 'foo/foo';

        $modelVariables  = ['foo' => 'foo'];
        $directVariables = ['bar' => 'bar'];

        $model = new ViewModel($modelVariables);
        $model->setTemplate($template);

        $renderer = new Renderer();
        $resolver = $this->getMock(Resolver::CLASS);
        $renderer->setResolver($resolver);

        $resolver
            ->expects($this->once())
            ->method('resolve')
            ->with($this->identicalTo($template))
            ->will($this->returnValue($file));

        $this->assertSame('foobar', $renderer->render($model, $directVariables));
    }

    public function testRenderSetsVariablesToContainer()
    {
        $file      = $this->files . 'container_variables.phtml';
        $template  = 'foo/foo';
        $variables = ['foo' => 'foo', 'bar' => 'bar'];

        $renderer = new Renderer();
        $resolver = $this->getMock(Resolver::CLASS);
        $renderer->setResolver($resolver);

        $resolver
            ->expects($this->once())
            ->method('resolve')
            ->with($this->identicalTo($template))
            ->will($this->returnValue($file));

        $this->assertSame('foobar',   $renderer->render($template, $variables));
    }

    public function testRendererRestoreVariablesAfterRendering()
    {
        $initialVariables = ['con' => 'com'];

        $file      = $this->files . 'container_variables.phtml';
        $template  = 'foo/foo';
        $variables = ['foo' => 'foo', 'bar' => 'bar'];

        $renderer = new Renderer();
        $renderer->setVariables($initialVariables);

        $resolver = $this->getMock(Resolver::CLASS);
        $renderer->setResolver($resolver);

        $resolver
            ->expects($this->once())
            ->method('resolve')
            ->with($this->identicalTo($template))
            ->will($this->returnValue($file));

        $this->assertSame('foobar',   $renderer->render($template, $variables));
        $this->assertSame($initialVariables, $renderer->getVariables());
    }

    public function testRenderNormalizeBufferOnError()
    {
        $file     = $this->files . 'template_with_error.phtml';
        $template = 'foo';

        $renderer = new Renderer();
        $resolver = $this->getMock(Resolver::CLASS);
        $renderer->setResolver($resolver);

        $resolver
            ->expects($this->once())
            ->method('resolve')
            ->with($this->identicalTo($template))
            ->will($this->returnValue($file));

        $bufferLevel = ob_get_level();
        try {
            $renderer->render($template);
        } catch (Error $ex) {
            //
        }
        $this->assertSame($bufferLevel, ob_get_level());
    }

    public function testRenderNormalizeBufferOnException()
    {
        $file     = $this->files . 'template_with_exception.phtml';
        $template = 'foo';

        $renderer = new Renderer();
        $resolver = $this->getMock(Resolver::CLASS);
        $renderer->setResolver($resolver);

        $resolver
            ->expects($this->once())
            ->method('resolve')
            ->with($this->identicalTo($template))
            ->will($this->returnValue($file));

        $bufferLevel = ob_get_level();
        try {
            $renderer->render($template);
        } catch (Exception $ex) {
            //
        }
        $this->assertSame($bufferLevel, ob_get_level());
    }

    public function testHelper()
    {
        $helper  = $this->getMock(FakeHelper::CLASS, ['setOptions']);
        $helpers = new ViewHelpers();
        $helpers->set('foo', $helper);

        $options = [
            'foo' => 'bar',
            'bat' => 'baz',
        ];

        $renderer = new Renderer();
        $renderer->setHelpers($helpers);

        $helper
            ->expects($this->once())
            ->method('setOptions')
            ->with($this->identicalTo($options));

        $result = $renderer->helper('foo', $options);
        $this->assertSame($result, $helper);
    }

    public function testCallCallsHelper()
    {
        $helper  = $this->getMock(FakeHelper::CLASS, ['__invoke']);
        $helpers = new ViewHelpers();
        $helpers->set('foo', $helper);

        $params = [
            'foo' => 'bar',
            'bat' => 'baz',
        ];

        $renderer = new Renderer();
        $renderer->setHelpers($helpers);

        $helper
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($params));

        $renderer->foo($params);
    }

    public function testCallReturnsHelperIfHelperIsNotCallable()
    {
        $helper  = new \stdClass();
        $helpers = new ViewHelpers();
        $helpers->set('foo', $helper);

        $renderer = new Renderer();
        $renderer->setHelpers($helpers);

        $return = $renderer->foo();
        $this->assertSame($return, $helper);
    }
}
