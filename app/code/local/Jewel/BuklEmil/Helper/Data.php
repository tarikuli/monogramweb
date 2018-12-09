<?php

class Jewel_BuklEmil_Helper_Data extends Mage_Core_Helper_Abstract
{

    public function sendNewsletterMail()
    {
        Mage::log('ps 111 =' . $_SERVER['HTTP_X_FORWARDED_PROTO'] . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . ' --- ' . $_SERVER['HTTP_X_FORWARDED_FOR'], null, 'system.log', true);
        $this->sendBulkEmail();
    }

    protected function sendBulkEmail()
    {
        // Transactional Email Template's ID
        $templateId = 1; // xmas_campaim
        
        // Set sender information
        $senderName = Mage::getStoreConfig('trans_email/ident_support/name');
        $senderEmail = Mage::getStoreConfig('trans_email/ident_support/email');
        $sender = array(
            'name' => $senderName,
            'email' => $senderEmail
        );
        
        // Set recepient information
        $recepientEmail = 'tatikuli@yahoo.com';
        $recepientName = 'Jewel';
        
        // Get Store ID
        $storeId = Mage::app()->getStore()->getId();
        
        // Set variables that can be used in email template
        $vars = array(
            'customerName' => 'customer@example.com', // {{var customerName}}
            'customerEmail' => 'Mr. Nil Cust'         // {{var customerEmail}}
        );
        
        $translate = Mage::getSingleton('core/translate');
        
        Mage::log('ps 111 =' . print_r($sender,TRUE), null, 'system.log', true);
        
        // Send Transactional Email
        Mage::getModel('core/email_template')->sendTransactional($templateId, $sender, $recepientEmail, $recepientName, $vars, $storeId);
        
        $translate->setTranslateInline(true);
    }
}
	 