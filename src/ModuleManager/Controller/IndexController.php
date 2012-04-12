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
namespace ModuleManager\Controller;

use Zend\Mvc\Controller\ActionController,
    Zend\View\Model\ViewModel
;

/**
 * The Main Controller of the module manager
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
class IndexController extends ActionController
{
    /**
     * List all modules contained in the module-directories
     *
     * @return void
     */
    public function indexAction()
    {
        $appConP = $this->getApplicationConfigPath();
        $docRoot = $this->getEvent()->getRequest()->server()->get('DOCUMENT_ROOT');

        $appConf = include $appConP;
        $locator = $this->getLocator();
        foreach ( $appConf['module_listener_options']['module_paths'] as $path ) {
            $path = realpath($docRoot . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . $path);
            if ( false === $path ) {
                continue;
            }
            $it = new \DirectoryIterator($path);
            foreach ( $it as $module ) {
                if ( $module->isDot() ) {
                    continue;
                }
                if ( 0 === strpos($module->getFileName(), '.') ) {
                    continue;
                }
                try{
                    $m = $locator->newInstance(
                        'ModuleManager\ModuleObj',
                        array('modulePath'=>$module->getPathName(),'configPath'=>$appConP)
                    );
                }catch(\Exception $e){
                    continue;
                }
                if ( ! $m->isValid() ) {
                    continue;
                }
                $modules[] = $m;
            }
        }

        return array('modules' => $modules);
    }

    /**
     * Activate the given module
     *
     * A module is activated by adding it's name to the main config-array
     *
     * @uses string :module the module to activate
     *
     * @return void
     */
    public function activateAction()
    {
        $params = $this->getEvent()->getRouteMatch()->getParams();
        $config = $this->getApplicationConfigPath();
        $path   = $this->getPathForModule($params['module']);
        $module = $this->getLocator()->newInstance(
            'ModuleManager\ModuleObj',
            array('modulePath' => $path, 'configPath' => $config)
        );
        $module->activate();

        return $this->redirect()->toRoute('mm');
    }

    /**
     * Deactivate a given module
     *
     * A module is deactivated by removing it's name from the main config-array
     *
     * @uses string :module The module to deactivate
     *
     * @return void
     */
    public function deactivateAction()
    {
        $params = $this->getEvent()->getRouteMatch()->getParams();
        $config = $this->getApplicationConfigPath();
        $path   = $this->getPathForModule($params['module']);
        $module = $this->getLocator()->newInstance(
            'ModuleManager\ModuleObj',
            array('modulePath' => $path, 'configPath' => $config)
        );
        $module->deactivate();
        return $this->redirect()->toRoute('mm');
    }

    /**
     * Install a given module.
     *
     * A module is installed by adding a symbolic link from every folder in the
     * modules public-folder to an equaly named folder inside the main
     * public-folder with the name of the module in lowercase. So for a modules
     * css-folder inside {{<myModule>/public/css}} this link would be created:
     * {{<DocRoot>/css/mymodule}} so that the result of {{ls -l
     * <DocRoot>/css/mymodule}} would read {{mymodule ->
     * <pathToModuleDir>/myModule/public/css}}
     *
     * A css-file {{<myModule>/public/css/style.css}} could then be referenced
     * inside a view-script using {{$this->appendLink('stylesheet',
     * 'css/mymodule/style.css');}}
     *
     * Also all files from the modules config-directory named
     * {{module-<deCamelCasedModuleName>.(local|global).config.php.dist}} will
     * be copied into the main config-folder the name stripped of the {{.dist}}.
     *
     * These config-files will be available to editing in a later stage
     *
     * @uses string :module The name of the module to install
     *
     * @return void
     */
    public function installAction()
    {
        $params = $this->getEvent()->getRouteMatch()->getParams();
        $config = $this->getApplicationConfigPath();
        $path   = $this->getPathForModule($params['module']);
        $module = $this->getLocator()->newInstance(
            'ModuleManager\ModuleObj',
            array('modulePath' => $path, 'configPath' => $config)
        );
        $module->install();
        return $this->redirect()->toRoute('mm');
    }

    /**
     * Uninstall a given module.
     *
     * A module is uninstalled by removing all symbolic links to that module
     * from the main public-folder.
     *
     * Also all config-files will be removed from the main config-folder
     *
     * @uses string :module The name of the module to uninstall
     *
     * @return void
     */
    public function uninstallAction()
    {
        $params = $this->getEvent()->getRouteMatch()->getParams();
        $config = $this->getApplicationConfigPath();
        $path   = $this->getPathForModule($params['module']);
        $module = $this->getLocator()->newInstance(
            'ModuleManager\ModuleObj',
            array('modulePath' => $path, 'configPath' => $config)
        );
        $module->uninstall();
        return $this->redirect()->toRoute('mm');
    }

    /**
     * Remove a module completely
     *
     * The module will be deactivated, uninstalled and finally the module-folder
     * will be removed.
     *
     * @uses string :module The name of the module to remove
     *
     * @return void
     */
    public function removeAction()
    {
        return $this->redirect()->toRoute('mm');
    }

    /**
     * Get the path to the application config-file
     *
     * @return string
     */
    protected function getApplicationConfigPath()
    {
        $docRoot = $this->getEvent()->getRequest()->server()->get('DOCUMENT_ROOT');
        return $docRoot
             . DIRECTORY_SEPARATOR
             . '..'
             . DIRECTORY_SEPARATOR
             . 'config'
             . DIRECTORY_SEPARATOR
             . 'application.config.php';
    }

    /**
     * Get the path for a given module
     *
     * @param string $module The module to get the path for
     *
     * @return string
     */
    protected function getPathForModule($module)
    {
        $docRoot = $this->getEvent()->getRequest()->server()->get('DOCUMENT_ROOT');
        $appConf = include $this->getApplicationConfigPath();
        foreach ( $appConf['module_listener_options']['module_paths'] as $path ) {
            $path = realpath($docRoot . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . $path);
            if ( false === $path ) {
                continue;
            }
            if ( file_exists($path . DIRECTORY_SEPARATOR . $module) ) {
                return $path . DIRECTORY_SEPARATOR . $module;
            }
        }
        return false;
    }
}
