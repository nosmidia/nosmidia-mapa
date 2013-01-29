<?php

class Project_View_Helper_Path
{

    public $_front;

    public function path()
    {
        $this->_front = Zend_Controller_Front::getInstance()->getRequest();
        return $this;
    }

    public function getModule()
    {
        return strtolower($this->_front->getModuleName());
    }

    public function getController()
    {
        return strtolower($this->_front->getControllerName());
    }

    public function getAction()
    {
        return strtolower($this->_front->getActionName());
    }

    public function isController($index = null)
    {
        if($index)
        {
            if(strtolower($this->_front->getControllerName()) == strtolower($index))
                return true;
            else
                return false;
        }else
        {
            return false;
        }
    }

    public function isModule($index = null)
    {
        if($index)
        {
            if(strtolower($this->_front->getModuleName()) == strtolower($index))
                return true;
            else
                return false;
        }else
        {
            return false;
        }
    }

    public function isAction($index = null)
    {
        if($index)
        {
            if(strtolower($this->_front->getActionName()) == strtolower($index))
                return true;
            else
                return false;
        }else
        {
            return false;
        }
    }

}