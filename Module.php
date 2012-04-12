<?php
/**
 * Copyright (c) 2011-2012 Andreas Heigl<andreas@heigl.org>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @category  ModuleManager
 * @package   ModuleManager
 * @author    Andreas Heigl <andreas@heigl.org>
 * @copyright 2011-2012 Andreas Heigl
 * @license   http://www.opesource.org/licenses/mit-license.php MIT-License
 * @version   0.0
 * @link      http://github.com/heiglandreas/ModuleManager
 * @since     10.04.2012
 */
namespace ModuleManager;


use Zend\Module\Manager,
    Zend\EventManager\StaticEventManager,
    Zend\Module\Consumer\AutoloaderProvider;

/**
 * The Module-Provider
 *
 * @category  ModuleManager
 * @package   ModuleManager
 * @author    Andreas Heigl <andreas@heigl.org>
 * @copyright 2011-2012 Andreas Heigl
 * @license   http://www.opesource.org/licenses/mit-license.php MIT-License
 * @version   0.0
 * @link      http://github.com/heiglandreas/ModuleManager
 * @since     10.04.2012
 */
class Module implements AutoloaderProvider
{

    /**
     * Initialize the Module
     *
     * @param Manager $moduleManager The Module-Manager instance
     *
     * @return void
     */
    public function init(Manager $moduleManager)
    {
        $events = StaticEventManager::getInstance();
        //$events->attach('bootstrap', 'bootstrap', array($this, 'checkInstallation'), 99);
        $events->attach('bootstrap', 'bootstrap', array($this, 'initializeView'), 100);
    }

    /**
     * Get the configuration for the autoloader
     *
     * @return array
     */
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
        __DIR__ . '/autoload_classmap.php',
        ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
        __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
        ),
        ),
        );
    }

    /**
     * Get the configuration of that module
     *
     * @return array
     */
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    /**
     * Check whether the installation of the module is OK
     *
     * @param \Zend\Mvc\MvcEvent $e The event that was triggered
     *
     * @return void
     */
    public function checkInstallation($e)
    {
        //
    }

    /**
     * Initialize the view
     *
     * @param \Zend\Mvc\MvcEvent $e The event that was triggered
     *
     * @return void
     */
    public function initializeView($e)
    {
        $app          = $e->getParam('application');
        $basePath     = $app->getRequest()->getBasePath();
        $locator      = $app->getLocator();
        $renderer     = $locator->get('Zend\View\Renderer\PhpRenderer');
        $renderer->plugin('url')->setRouter($app->getRouter());
        $renderer->doctype()->setDoctype('HTML5');
        $renderer->plugin('basePath')->setBasePath($basePath);
    }
}
