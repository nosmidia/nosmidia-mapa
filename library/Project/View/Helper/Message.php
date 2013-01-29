<?php

class Project_View_Helper_Message
{

    private $_session;

    public function message()
    {
        $this->_session = new Zend_Session_Namespace('responseMessages');
        return $this;
    }

    public function show($message, $type)
    {
        $this->_session->show 	= true;
        $this->_session->message 	= $message;
        $this->_session->type 		= $type;
    }

    public function getMessage()
    {
        return $this->_session;
    }

    public function clear()
    {
        $this->_session->show = false;
        unset($this->_session->message);
        unset($this->_session->type);
       
    }

}
