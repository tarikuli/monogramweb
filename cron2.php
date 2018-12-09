<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Mage
 * @copyright  Copyright (c) 2006-2017 X.commerce, Inc. and affiliates (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

// Change current directory to the directory of current script
chdir(dirname(__FILE__));

require 'app/bootstrap.php';
require 'app/Mage.php';

if (!Mage::isInstalled()) {
    echo "Application is not installed yet, please complete install wizard first.";
    exit;
}

// Only for urls
// Don't remove this
$_SERVER['SCRIPT_NAME'] = str_replace(basename(__FILE__), 'index.php', $_SERVER['SCRIPT_NAME']);
$_SERVER['SCRIPT_FILENAME'] = str_replace(basename(__FILE__), 'index.php', $_SERVER['SCRIPT_FILENAME']);

Mage::app('admin')->setUseSessionInUrl(false);

umask(0);


try {
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
} catch (Exception $e) {
    Mage::printException($e);
    exit(1);
}
