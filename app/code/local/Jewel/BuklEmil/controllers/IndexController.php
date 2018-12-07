<?php
class Jewel_BuklEmil_IndexController extends Mage_Core_Controller_Front_Action{
    public function IndexAction() {
        # https://stackoverflow.com/questions/7500038/how-to-use-the-email-template-in-magento
        # http://blog.chapagain.com.np/magento-send-transactional-email/
	  $this->loadLayout();   
	  $this->getLayout()->getBlock("head")->setTitle($this->__("Send Bulk Emails"));
	        $breadcrumbs = $this->getLayout()->getBlock("breadcrumbs");
      $breadcrumbs->addCrumb("home", array(
                "label" => $this->__("Home Page"),
                "title" => $this->__("Home Page"),
                "link"  => Mage::getBaseUrl()
		   ));

      $breadcrumbs->addCrumb("send bulk emails", array(
                "label" => $this->__("Send Bulk Emails"),
                "title" => $this->__("Send Bulk Emails")
		   ));

      $this->renderLayout(); 
	  
    }
}