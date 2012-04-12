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

use \InvalidArgumentException;

/**
 * A class representing a module
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
class ModuleObj
{
    /**
     * The path to the module directory.
     *
     * @var string $path
     */
    protected $path = null;

    /**
     * The path to the application-configuration.
     *
     * @var string $config
     */
    protected $config = null;

    /**
     * Create the instance
     *
     * @param string $modulePath The path to the modules directory
     * @param string $configPath The path to the application configuration file
     *
     * @throws \InvalidArgumentException when the given string does not
     *          reference a valid filesystem-path containing a Module.php-file
     * @return void
     */
    public function __construct($modulePath = null, $configPath = null)
    {
        if ( null !== $modulePath ) {
            $this->setModulePath($modulePath);
        }
        if ( null !== $configPath ) {
            $this->setConfigPath($configPath);
        }
    }

    /**
     * Set the path to the configuration file
     *
     * @param string $configPath The path to the configuration-file
     *
     * @return ModuleObj
     */
    public function setConfigPath($configPath)
    {
        $configPath = (string) $configPath;
        $this->config = $configPath;
        return $this;
    }

    /**
     * Set the path to this module
     *
     * @param string $modulePath The path to the modules folder
     *
     * @throws InvalidArgumentException on giving an invalid module-path
     * @return Module
     */
    public function setModulePath($modulePath)
    {
        $modulePath = (string) $modulePath;
        if ( ! file_exists($modulePath) ) {
            throw new InvalidArgumentException('The given Path does not exist');
        }
        if ( ! file_exists($modulePath . DIRECTORY_SEPARATOR . 'Module.php') ) {
            throw new InvalidArgumentException(
                sprintf('The given path %s does not contain a Module.php-file', $modulePath)
            );
        }
        $this->path = $modulePath;
        return $this;
    }

    /**
     * Get the name of the module
     *
     * @return string
     */
    public function getName()
    {
        return basename($this->path);
    }

    /**
     * is this module active
     *
     * @return boolean
     */
    public function isActive()
    {
        $config = $this->getApplicationConfig();
        if ( in_array($this->getName(), $config['modules']) ) {
            return true;
        }
        return false;
    }

    /**
     * Is this module installed
     *
     * @return boolean
     */
    public function isInstalled()
    {
        if ( 0 < count($this->getUninstalledConfigFiles()) ) {
            return false;
        }
        if ( 0 < count($this->getUnlinkedPubFolders()) ) {
            return false;
        }
        return true;
    }

    /**
     * Get a list of uninstalled Config-files
     *
     * @return array
     */
    protected function getUninstalledConfigFiles()
    {
        $array = array();
        $autoloadDir = dirname(realpath($this->config)) . DIRECTORY_SEPARATOR . 'autoload';
        $configDir   = $this->path . DIRECTORY_SEPARATOR . 'config';
        $iterator = new \DirectoryIterator($configDir);
        foreach ( $iterator as $configFile ) {
            if ( $configFile->isDot() ) {
                continue;
            }
            if ( 0 === strpos($configFile->getFileName(), '.') ) {
                continue;
            }
            if ( 'module.config.php' == $configFile->getFileName() ) {
                continue;
            }
            if ( ! preg_match(
                '/^(?P<configFile>module\..*(local|global)?\.config\.php)\.dist$/i',
                $configFile->getFileName(),
                $result
            ) ) {
                continue;
            }
            if ( ! file_exists($autoloadDir . DIRECTORY_SEPARATOR . $result['configFile']) ) {
                $array[] = $result['configFile'];
            }
        }
        return $array;
    }
    /**
     * Get a list of installed Config-files
     *
     * @return array
     */
    protected function getInstalledConfigFiles()
    {
        $array = array();
        $autoloadDir = dirname(realpath($this->config)) . DIRECTORY_SEPARATOR . 'autoload';
        $configDir   = $this->path . DIRECTORY_SEPARATOR . 'config';
        $iterator = new \DirectoryIterator($configDir);
        foreach ( $iterator as $configFile ) {
            if ( $configFile->isDot() ) {
                continue;
            }
            if ( 0 === strpos($configFile->getFileName(), '.') ) {
                continue;
            }
            if ( 'module.config.php' == $configFile->getFileName() ) {
                continue;
            }
            if ( ! preg_match(
                '/^(?P<configFile>module\..*(local|global)?\.config\.php)\.dist$/i',
                $configFile->getFileName(),
                $result
            ) ) {
                continue;
            }
            if ( file_exists($autoloadDir . DIRECTORY_SEPARATOR . $result['configFile']) ) {
                $array[] = $result['configFile'];
            }
        }
        return $array;
    }

    /**
     * Get unlinked public folders
     *
     * @return array
     */
    protected function getUnlinkedPubFolders()
    {
        $array = array();
        $mainPublic = realpath(dirname($this->config) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'public');
        $myPublic   = realpath($this->path . DIRECTORY_SEPARATOR . 'public');
        if ( ! $myPublic ) {
            return $array;
        }
        $it = new \DirectoryIterator($myPublic);
        foreach ( $it as $pubFolder ) {
            if ( $pubFolder->isDot() ) {
                continue;
            }
            if ( 0 === strpos($pubFolder->getFileName(), '.') ) {
                continue;
            }
            $link = $mainPublic
                  . DIRECTORY_SEPARATOR
                  . $pubFolder->getFileName()
                  . DIRECTORY_SEPARATOR
                  . strtolower($this->getName());
            $target = $pubFolder->getPathname();
            if ( ! is_link($link) || ! $target == readlink($link) ) {
                $array[$target] = $link;
            }
        }
        return $array;
    }

    /**
     * Get linked public folders
     *
     * @return array
     */
    protected function getLinkedPubFolders()
    {
        $array = array();
        $mainPublic = dirname($this->config)
                    . DIRECTORY_SEPARATOR
                    . '..'
                    . DIRECTORY_SEPARATOR
                    . 'public';
        $mainPublic = realpath($mainPublic);
        $myPublic   = realpath($this->path . DIRECTORY_SEPARATOR . 'public');
        if ( ! $myPublic ) {
            return $array;
        }
        $it = new \DirectoryIterator($myPublic);
        foreach ( $it as $pubFolder ) {
            if ( $pubFolder->isDot() ) {
                continue;
            }
            if ( 0 === strpos($pubFolder->getFileName(), '.') ) {
                continue;
            }
            $link = $mainPublic
                  . DIRECTORY_SEPARATOR
                  . $pubFolder->getFileName()
                  . DIRECTORY_SEPARATOR
                  . strtolower($this->getName());
            $target = $pubFolder->getPathname();
            if ( is_link($link) && $target == readlink($link) ) {
                $array[$target] = $link;
            }
        }
        return $array;
    }

    /**
     * Check whether this is a valid object
     *
     * @return bool
     */
    public function isValid()
    {
        if ( null === $this->path ) {
            return false;
        }
        return true;
    }

    /**
     * Get the default application-config
     *
     * @return array
     */
    protected function getApplicationConfig()
    {
        return include $this->config;
    }

    /**
     * write the application-config to disk
     *
     * @param array $config The configuration array.
     *
     * @return ModuleObj
     */
    protected function writeConfig($config)
    {
        $conf = $this->config;
        $tokens = token_get_all(file_get_contents($conf));
        $result = '<?php' . "\n" . 'return ' . self::walker($config) . ';';
        $fh = fopen($this->config, 'w+');
        fwrite($fh, $result);
        fclose($fh);
        error_log($result);
        return $this;
    }

    /**
     * Add the given module to the module list
     *
     * @return ModuleObj
     */
    public function activate()
    {
        $this->install();
        $config = $this->getApplicationConfig();
        $module = $this->getName();
        if ( ! in_array($module, $config['modules']) ) {
            $config['modules'][] = $this->getName();
        }
        $this->writeConfig($config);
        return $this;
    }

    /**
     * Remove the given module from the module list
     *
     * @return ModuleObj
     */
    public function deactivate()
    {
        $config = $this->getApplicationConfig();
        $module = $this->getName();
        if ( in_array($module, $config['modules']) ) {
            $id = array_search($module, $config['modules']);
            unset($config['modules'][$id]);
        }
        $this->writeConfig($config);
        return $this;
    }

    /**
     * Install the module
     *
     * @return ModuleObj
     */
    public function install()
    {
        foreach ( $this->getUninstalledConfigFiles() as $key => $val ) {
            copy(
                $this->path . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . $val . '.dist',
                dirname($this->config) . DIRECTORY_SEPARATOR . 'autoload' . DIRECTORY_SEPARATOR . $val
            );
        }
        foreach ( $this->getUnlinkedPubFolders() as $key => $val ) {
            symlink($key, $val);
        }
        return $this;
    }

    /**
     * Uninstall a module
     *
     * @return ModuleObj
     */
    public function uninstall()
    {
        $this->deactivate();
        foreach ( $this->getInstalledConfigFiles() as $val ) {
            unlink(dirname($this->config) . DIRECTORY_SEPARATOR . 'autoload' . DIRECTORY_SEPARATOR . $val);
        }
        foreach ( $this->getLinkedPubFolders() as $val ) {
            unlink($val);
        }
        return $this;
    }

    /**
     * function to walk through the array
     *
     * @param array $array The Array to walk through
     * @param int   $i     The Indentation level
     *
     * @return string
     */
    protected static function walker($array, $i = 0)
    {
        $item = array('array(');
        foreach ( $array as $key => $value ) {
            $strKey = '';
            if ( ! is_int($key) ) {
                $strKey = '\'' . $key . '\'=>';
            }
            if ( is_Array($value) ) {
                $item[] = str_repeat(' ', ($i+1) * 4) . $strKey . self::walker($value, $i+1) . ',';
            } else if ( is_bool($value) ) {
                if ( true == $value ) {
                    $value = 'true';
                } else {
                    $value = 'false';
                }
                $item[] = str_repeat(' ', ($i+1) * 4) . $strKey . $value . ',';
            } else {
                $item[] = str_repeat(' ', ($i+1) * 4) . $strKey . '\'' . $value . '\',';
            }
        }
        $item[] = str_repeat(' ', $i * 4) . ')';
        return implode("\n", $item);
    }
}

