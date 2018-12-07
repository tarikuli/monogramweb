<?php

class Pektsekye_OptionExtended_Model_Template extends Mage_Core_Model_Abstract
{	
    public function _construct()
    {
        parent::_construct();
        $this->_init('optionextended/template');
    }

    public function getHasOptions()
    {
        return $this->getResource()->getHasOptions((int) $this->getId());
    }

}
