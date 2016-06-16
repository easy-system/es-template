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

use Error;
use Es\Container\AbstractContainer;
use Es\Container\Property\PropertyInterface;
use Es\Container\Property\PropertyTrait;
use Es\Mvc\ViewHelpersInterface;
use Es\Mvc\ViewModelInterface;
use Es\Services\Provider;
use Es\View\Resolver;
use Exception;
use InvalidArgumentException;

/**
 * The renderer of templates.
 */
class Renderer extends AbstractContainer implements PropertyInterface
{
    use PropertyTrait;

    /**
     * Sets the resolver.
     *
     * @param \Es\View\Resolver $resolver The resolver
     */
    public function setResolver(Resolver $resolver)
    {
        Provider::getServices()->set('ViewResolver', $resolver);
    }

    /**
     * Gets the resolver.
     *
     * @return \Es\View\Resolver The resolver
     */
    public function getResolver()
    {
        return Provider::getServices()->get('ViewResolver');
    }

    /**
     * Sets the view helpers.
     *
     * @param \Es\Mvc\ViewHelpersInterface $helpers The view helpers
     */
    public function setHelpers(ViewHelpersInterface $helpers)
    {
        Provider::getServices()->set('ViewHelpers', $helpers);
    }

    /**
     * Gets the view helpers.
     *
     * @return \Es\Mvc\ViewHelpersInterface The view helpers
     */
    public function getHelpers()
    {
        return Provider::getServices()->get('ViewHelpers');
    }

    /**
     * Sets the variables.
     *
     * @param array $variables The variables
     *
     * @return array The old variables
     */
    public function setVariables(array $variables)
    {
        $container       = $this->container;
        $this->container = $variables;

        return $container;
    }

    /**
     * Gets the variables.
     *
     * @return array The variables
     */
    public function getVariables()
    {
        return $this->container;
    }

    /**
     * Renders the view model or template.
     *
     * @param string|Es\Mvc\ViewModelInterface $nameOrModel The name of template
     *                                                      or instance of
     *                                                      view model
     * @param array                            $variables   Optional; the variables
     *                                                      uses to render
     *
     * @throws \InvalidArgumentException If invalid type of render source
     *                                   specified
     *
     * @return string The result of rendering
     */
    public function render($nameOrModel, array $variables = [])
    {
        $__module = null;

        if ($nameOrModel instanceof ViewModelInterface) {
            $model       = $nameOrModel;
            $nameOrModel = $model->getTemplate();
            $__module    = $model->getModule();
            $variables   = array_merge($model->getVariables(), $variables);
            unset($model);
        } elseif (! is_string($nameOrModel)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid render source provided; must be a string or instance '
                . 'of "%s", "%s" received.',
                ViewModelInterface::CLASS,
                is_object($nameOrModel) ? get_class($nameOrModel)
                                        : gettype($nameOrModel)
            ));
        }

        extract($variables);
        $__old = $this->setVariables($variables);
        unset($variables);

        try {
            ob_start();
            include $this->getResolver()->resolve($nameOrModel, $__module);
            $__return = ob_get_clean();
        } catch (Error $e) {
            ob_end_clean();
            throw $e;
        } catch (Exception $e) {
            ob_end_clean();
            throw $e;
        }
        $this->setVariables($__old);

        return $__return;
    }

    /**
     * Gets a view helper with specified options.
     *
     * @param string $name    The name of view helper
     * @param array  $options Optional; the options
     *
     * @return object Returns the specified view helper
     */
    public function helper($name, array $options = [])
    {
        return $this->getHelpers()->getHelper($name, $options);
    }

    /**
     * The overloading returns/calls view helper.
     *
     * @param string $name   The name of view helper
     * @param array  $params Optional; the parameters uses to call
     *
     * @return mixed The result of calling the view helper, if the view helper
     *               is callable, the view helper otherwise
     */
    public function __call($name, array $params = [])
    {
        $helper = $this->getHelpers()->get($name);
        if (is_callable($helper)) {
            return call_user_func_array($helper, $params);
        }

        return $helper;
    }
}
