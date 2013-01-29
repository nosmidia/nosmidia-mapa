<?php

/**
 * @package Project
 * @subpackage Mail
 */
require_once 'Zend/Mail.php';
require_once 'Zend/Layout.php';

class Project_Mail extends Zend_Mail
{

    protected $_view;
    protected $_scriptName;
    protected $_scriptPaths = array();
    protected $_debug = false;

    public function __construct($charset = 'iso-8859-1')
    {
        $this->_charset = 'utf-8';
    }

    public function setView($view)
    {
        if($view instanceof Zend_View_Abstract)
        {
            $this->_view = $view;
        }
        elseif(is_string($view))
        {
            $this->_scriptName = $view . '.phtml';

            $viewObj = new Zend_View();
            if(empty($this->_scriptPaths))
            {
                $viewObj->addScriptPath(APPLICATION_PATH . '/views/scripts/email');
            }
            else
            {
                foreach($this->_scriptPaths as $scriptPath) {
                    $viewObj->addScriptPath($scriptPath);
                }
            }

            $this->_view = $viewObj;
        }

        return $this;
    }

    public function setDebug($flag = true)
    {
        $this->_debug = $flag;
        return $this;
    }

    public function setBodyHtml($html = '', $charset = null, $encoding = Zend_Mime::ENCODING_QUOTEDPRINTABLE)
    {
        if($this->_view instanceof Zend_View_Abstract)
        {

            return parent::setBodyHtml($this->_view->render($this->_scriptName), $charset, $encoding);
        }
        else
        {
            return parent::setBodyHtml($html, $charset, $encoding);
        }
    }

    public function send($transport = null)
    {
        if($this->_debug)
        {
            $layout = Zend_Layout::getMvcInstance();
            if(is_object($layout instanceof Zend_Layout))
            {
                $mvcInstance->disableLayout();
            }
            echo $this->_view->render($this->_scriptName);
            exit();
        }
        if(!$this->getBodyHtml())
        {
            $this->setBodyHtml();
        }
		
        if('production' != APPLICATION_ENV)
        {
            $this
				->clearRecipients()
                ->addTo('contato@nosmidia.com.br');
        }


        return parent::send($transport);
    }

    public function setViewParams(array $params)
    {
        foreach($params as $key => $param) {
            $this->_view->$key = $param;
        }
        return $this;
    }

    public function clearScriptPaths()
    {
        $this->_scriptPaths = array();
        return $this;
    }

    public function setScriptPaths(array $scriptPaths)
    {
        $this->_scriptPaths = $scriptPaths;
        return $this;
    }

    public function setScriptPath($scriptPath)
    {
        $this->clearScriptPaths();
        $this->_scriptPaths[] = $scriptPath;
        return $this;
    }

    public function addScriptPath($scriptPath)
    {
        $this->_scriptPaths[] = $scriptPath;
        return $this;
    }

    public function getScriptPaths(array $scriptPaths)
    {
        return $this->_scriptPaths;
    }

}
