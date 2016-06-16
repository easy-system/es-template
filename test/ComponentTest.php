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

use Es\Template\Component;

class ComponentTest extends \PHPUnit_Framework_TestCase
{
    protected $requiredServices = [
        'DefaultTemplateEngine',
    ];

    protected $requiredExtensions = [
        'phtml',
    ];

    public function testGetVersion()
    {
        $component = new Component();
        $version   = $component->getVersion();
        $this->assertInternalType('string', $version);
        $this->assertRegExp('#\d+.\d+.\d+#', $version);
    }

    public function testGetServicesConfig()
    {
        $component = new Component();
        $config    = $component->getServicesConfig();
        $this->assertInternalType('array', $config);
        foreach ($this->requiredServices as $item) {
            $this->assertArrayHasKey($item, $config);
        }
    }

    public function testGetSystemConfig()
    {
        $component    = new Component();
        $systemConfig = $component->getSystemConfig();
        $this->assertInternalType('array', $systemConfig);
        $this->assertArrayHasKey('view', $systemConfig);

        $viewConfig = $systemConfig['view'];
        $this->assertArrayHasKey('strategy', $viewConfig);

        $strategyConfig = $viewConfig['strategy'];
        foreach ($this->requiredExtensions as $item) {
            $this->assertArrayHasKey($item, $strategyConfig);
        }
    }
}
