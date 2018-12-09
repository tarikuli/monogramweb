<?php
class Jewel_BuklEmil_IndexController extends Mage_Core_Controller_Front_Action{
    public function IndexAction() {
        
        #Mage::helper('buklemil')->sendNewsletterMail();
        Mage::app('admin')->setUseSessionInUrl(false);   
        $test_order_ids=array(
            '100000009',
            '100000008',
            '100000007',
            '100000006',
            '100000005',
            '100000004',
            '100000003',
            '100000002',
            '100000001',
        );
        foreach($test_order_ids as $id){
            try{
                Mage::getModel('sales/order')->loadByIncrementId($id)->delete();
                echo "order #".$id." is removed".PHP_EOL;
            }catch(Exception $e){
                echo "order #".$id." could not be remvoved: ".$e->getMessage().PHP_EOL;
            }
        }
        echo "complete.";
        
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