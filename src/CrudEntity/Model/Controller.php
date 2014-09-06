<?php

namespace CrudEntity\Model;

use Zend\Code\Generator;
use Zend\Filter\Word\CamelCaseToDash as CamelCaseToDashFilter;

class Controller
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $module;

    /**
     * @var string
     */
    private $path = ".";

    /**
     * @var string
     */
    private $pathFull;

    /**
     * @var array
     */
    private $arrayMethods;

    /**
     * @var Generator\ClassGenerator
     */
    private $generator;

    /**
     * @var boolean
     */
    private $fileExist = false;

    /**
     * Método construtor
     * @param string $name   Nome da API
     * @param string $module Nome do módulo
     * @param string $path   Caminho para o módulo
     */
    public function __construct($name, $module, $path)
    {
        $this->setpath($path);
        $this->setModule($module);
        $this->setName($name);

        $ucName     = ucfirst($this->name);
        $this->pathFull   = $this->path . '/module/' . $this->module . '/src/' . $this->module . '/Controller/' . $ucName.'Controller.php';
        $controller = $ucName . 'Controller';

        // Gerar um controller com a classe abstrata de webservice
        $this->generator = new Generator\ClassGenerator();
        $this->generator->setNamespaceName(ucfirst($this->module) . '\Controller')
             ->addUse('Zend\Mvc\Controller\AbstractRestfulController')
             ->addUse('Zend\View\Model\JsonModel');

        // adicionar os métodos get, getlist, create, update, delete
        $this->generator->setName($controller)
             ->setExtendedClass('AbstractRestfulController');
    }

    /**
     * Método para gerar o controller
     * @param  string $name   Nome do controller
     * @param  string $module Nome do Módulo
     * @param  string $path   Caminho dos arquivos
     * @return void
     */
    public function generate($name = null, $module = null, $path = null)
    {
        $this->setpath($path);
        $this->setModule($module);
        $this->setName($name);

        $this->generator->addmethods($this->arrayMethods);

        $file = new Generator\FileGenerator(
            array(
                'classes'  => array($this->generator),
            )
        );

        $filter = new CamelCaseToDashFilter();
        $viewfolder = strtolower($filter->filter($this->module));

        return file_put_contents($this->pathFull, $file->generate());
    }

    /**
     * Método para adicionar métodos no controller a ser gerado
     * @param Generator\MethodGenerator $method Classe MethodGenerator
     */
    public function addMethod(Generator\MethodGenerator $method)
    {
        $this->arrayMethods[] = $method;
    }

    /**
     * Method for add array methods
     * @param array $methods Array of methods
     */
    public function addMethods(array $methods)
    {
        foreach ($methods as $method) {
            $this->addMethod($method);
        }
    }

    /**
     * Método para setar o nome do controller
     * @param string $name Nome
     */
    public function setName($name)
    {
        if (empty($name)) {
            return;
        }

        if (file_exists($this->path."/module/" . ucfirst($this->module) ."/src/" . ucfirst($this->module) . "/Controller/" . ucfirst($name) . "Controller.php")) {
            $this->fileExist = true;
        }

        $this->name = $name;
    }

    /**
     * Método para setar o módulo do controller
     * @param string $module Nome do módulo
     */
    public function setModule($module)
    {
        if (empty($module)) {
            return;
        }

        if (!file_exists($this->path."/module") || !file_exists($this->path."/config/application.config.php")) {
            throw new \Exception("O diretório " . $this->path . " não é um módulo ZF2.");
        }

        $this->module = $module;
    }

    /**
     * Método para setar o caminho do módulo
     * @param string $path Caminho do módulo
     */
    public function setPath($path = ".")
    {
        if (empty($path)) {
            return;
        }

        $this->path = $path;
    }

    /**
     * Method for valid file exist
     * @return boolean If any file
     */
    public function isControllerExist()
    {
        return $this->fileExist;
    }

    public function getName()
    {
        return $this->name;
    }
}
