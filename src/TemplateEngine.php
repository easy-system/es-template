<?php
/**
 * This file is part of the "Easy System" package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Damon Smith <damon.easy.system@gmail.com>
 */
namespace Es\Template;

use Es\Mvc\ViewModelInterface;
use Es\Services\Provider;
use Es\View\TemplateEngineInterface;

/**
 * The engine of templates rendering.
 */
class TemplateEngine implements TemplateEngineInterface
{
    /**
     * Sets the renderer.
     *
     * @param Renderer $renderer The renderer
     */
    public function setRenderer(Renderer $renderer)
    {
        Provider::getServices()->set('DefaultTemplateRenderer', $renderer);
    }

    /**
     * Gets the renderer.
     *
     * @return Renderer The renderer
     */
    public function getRenderer()
    {
        return Provider::getServices()->get('DefaultTemplateRenderer');
    }

    /**
     * Renders the view model.
     *
     * @param \Es\Mvc\ViewModelInterface $model The view model
     *
     * @return string The result of rendering
     */
    public function render(ViewModelInterface $model)
    {
        return $this->getRenderer()->render($model);
    }
}
