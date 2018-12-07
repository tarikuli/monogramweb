<?php



class Pektsekye_OptionExtended_Model_Convert_Adapter_Values
    extends Mage_Dataflow_Model_Convert_Parser_Csv
{
    
    
    public function parse()
    {
  
        setlocale(LC_ALL, Mage::app()->getLocale()->getLocaleCode().'.UTF-8');

        $fDel = $this->getVar('delimiter', ',');
        $fEnc = $this->getVar('enclose', '"');
        if ($fDel == '\t') {
            $fDel = "\t";
        }

        $batchModel = $this->getBatchModel();
        $batchIoAdapter = $this->getBatchModel()->getIoAdapter();

        $batchIoAdapter->open(false);

        $fieldNames = array();
        foreach ($batchIoAdapter->read(true, $fDel, $fEnc) as $v) {
            $fieldNames[$v] = $v;
        }
        
        
        $resource = Mage::getSingleton('core/resource'); 
        $read = $resource->getConnection('core_read');						
        $write = $resource->getConnection('core_write');


        $optionRows = $read->fetchAssoc("SELECT code, option_id, product_id FROM {$resource->getTableName('optionextended/option')}");
				

        $r = $read->fetchRow("SHOW TABLE STATUS LIKE '{$resource->getTableName('catalog/product_option_type_value')}'");
        $nextOptionTypeId = $r['Auto_increment'];
        $r = $read->fetchRow("SHOW TABLE STATUS LIKE '{$resource->getTableName('optionextended/value')}'");
        $nextOxValueId = $r['Auto_increment'];


        $toProductOptionTypeValueTable = "INSERT INTO `{$resource->getTableName('catalog/product_option_type_value')}` (`option_type_id`,`option_id`,`sku`,`sort_order`) VALUES ";
        $toProductOptionTypeTitleTable = "INSERT INTO `{$resource->getTableName('catalog/product_option_type_title')}` (`option_type_id`,`title`) VALUES ";
        $toProductOptionTypePriceTable = "INSERT INTO `{$resource->getTableName('catalog/product_option_type_price')}` (`option_type_id`,`price`,`price_type`) VALUES ";
        $toOptionextendedValueTable = "INSERT INTO `{$resource->getTableName('optionextended/value')}` (`ox_value_id`,`option_type_id`,`product_id`,`row_id`,`children`,`image`) VALUES ";
        $toOptionextendedValueDescriptionTable = "INSERT INTO `{$resource->getTableName('optionextended/value_description')}` (`ox_value_id`,`description`) VALUES ";

        $oIds   = array();
        $images = array();
        $rowIds = array(); 
        $toPOTVT=$toPOTTT=$toPOTPT=$toOVT=$toOVDT='';
        
        $countRows = 0;
        $skippedRows = 0;        
        while (($csvData = $batchIoAdapter->read(true, $fDel, $fEnc)) !== false) {
            if (count($csvData) == 1 && $csvData[0] === null) {
                continue;
            }
            
            if ($skippedRows > 100){
						 $this->addException(Mage::helper('optionextended')->__('Too many rows to skip. Stop import process.'), Mage_Dataflow_Model_Convert_Exception::FATAL);
						 break;
						}
						
            $d = array();
            $countRows ++; $i = 0;
            foreach ($fieldNames as $field) {
                $d[$field] = isset($csvData[$i]) ? $csvData[$i] : null;
                $i ++;
            }

			      if (empty($d['option_code'])){
              $this->addException(Mage::helper('optionextended')->__('Skip import row, required field "%s" not defined', 'option_code'), Mage_Dataflow_Model_Convert_Exception::FATAL);           
              $skippedRows++;
              continue;
            }           

			      if (!isset($optionRows[$d['option_code']])){
              $this->addException(Mage::helper('optionextended')->__('Skip import row, option with code "%s" does not exist', $d['option_code']), Mage_Dataflow_Model_Convert_Exception::FATAL);
              $skippedRows++;
              continue;        
            }
            
            $optionId = $optionRows[$d['option_code']]['option_id'];             
            $productId = $optionRows[$d['option_code']]['product_id'];
            
			      if (empty($d['row_id'])){
              $this->addException(Mage::helper('optionextended')->__('Skip import row, required field "%s" not defined', 'row_id'), Mage_Dataflow_Model_Convert_Exception::FATAL);           
              $skippedRows++;
              continue;        
            }
            
			      if (isset($rowIds[$productId][$d['row_id']])){
              $this->addException(Mage::helper('optionextended')->__('Skip import row, option value with %s "%s" for product #%s has been already imported', 'row_id', $d['row_id'], $productId), Mage_Dataflow_Model_Convert_Exception::FATAL);         
              $skippedRows++;
              continue;        
            }            
            $rowIds[$productId][$d['row_id']] = 1;

			      if (!isset($d['title']) || $d['title'] == ''){
              $this->addException(Mage::helper('optionextended')->__('Skip import row, required field "%s" not defined', 'title'), Mage_Dataflow_Model_Convert_Exception::FATAL);           
              $skippedRows++;
              continue;        
            }
            
			      if (!empty($d['image']) && !isset($images[$d['image']])){
				      $image = substr($d['image'], 0, 1) != '/' ? '/' . $d['image'] : $d['image'];
				      if (!file_exists($this->_getMadiaConfig()->getMediaPath($image))) {								
					      $file = Mage::getBaseDir('media') . DS . 'import' . $image; 
					      $file = realpath($file);
					      if (!$file || !file_exists($file)) {
						      $this->addException(Mage::helper('optionextended')->__('Skip import row, image "%s" does not exist in the media/import directory.', $d['image']), Mage_Dataflow_Model_Convert_Exception::FATAL);												
                  $skippedRows++;
                  continue;						
					      } else {
						      $pathinfo = pathinfo($file);
						      if (!isset($pathinfo['extension']) || !in_array(strtolower($pathinfo['extension']), array('jpg','jpeg','gif','png'))) {
							      $this->addException(Mage::helper('optionextended')->__('Skip import row, image "%s" has invalid file type. Valid types are: jpg, jpeg, gif, png.', $d['image']), Mage_Dataflow_Model_Convert_Exception::FATAL);				
                    $skippedRows++;
                    continue;							
						      } else {
							      if ($newFile = $this->moveImageFromImport($file, $pathinfo['basename'])){
								      $image = $newFile;
							      } else {
								      $this->addException(Mage::helper('optionextended')->__('Skip import row, failed to move file: %s', $d['image']), Mage_Dataflow_Model_Convert_Exception::FATAL);											
                      $skippedRows++;
                      continue;								
							      }
						      }
					      }
				      }
              $images[$d['image']] = $image;				      
			      }
			      


            $sku = $write->quote($d['sku']);
            $sortOrder = (int) $d['sort_order']; 
            $title = $write->quote($d['title']);		  
            $price = $write->quote($d['price']);
            $priceType = $write->quote($d['price_type']);
            $rowId = (int) $d['row_id'];		     
            $children = $write->quote($d['children']);
            $image = isset($images[$d['image']]) ? $write->quote($images[$d['image']]) : "''";
            $description = $write->quote($d['description']);

            $toPOTVT .= ($toPOTVT != '' ? ',' : '') . "({$nextOptionTypeId},{$optionId},{$sku},{$sortOrder})";
            $toPOTTT .= ($toPOTTT != '' ? ',' : '') . "({$nextOptionTypeId},{$title})"; 
            $toPOTPT .= ($toPOTPT != '' ? ',' : '') . "({$nextOptionTypeId},{$price},{$priceType})";  
            $toOVT   .= ($toOVT != '' ? ',' : '') . "({$nextOxValueId},{$nextOptionTypeId},{$productId},{$rowId},{$children},{$image})";
            $toOVDT  .= ($toOVDT != '' ? ',' : '') . "({$nextOxValueId},{$description})";

            $oIds[$optionId] = 1;
            $nextOptionTypeId++;
            $nextOxValueId++;
            
 
        }
        
        $importedRows = $countRows - $skippedRows;

        if ($importedRows > 0){     
          $write->raw_query("DELETE FROM `{$resource->getTableName('catalog/product_option_type_value')}` WHERE `option_id` IN (" . implode(',', array_keys($oIds)) .")");		    	
  	  	

          $write->raw_query($toProductOptionTypeValueTable . $toPOTVT);
          $write->raw_query($toProductOptionTypeTitleTable . $toPOTTT);
          $write->raw_query($toProductOptionTypePriceTable . $toPOTPT);
          $write->raw_query($toOptionextendedValueTable . $toOVT);
          $write->raw_query($toOptionextendedValueDescriptionTable . $toOVDT);    	  	      
        }

          
        if ($skippedRows == 0)     
          $this->addException(Mage::helper('optionextended')->__('Imported %d rows.',$countRows));
        else 
          $this->addException(Mage::helper('optionextended')->__('Imported %d rows of %d',$importedRows,$countRows));   


        return $this;

    }


    /**
     * Move image from media/import directory to normal
     *
     * @param string $file
     * @return string
     */
    protected function moveImageFromImport($file, $basename)
    {
		  
        $fileName       = Varien_File_Uploader::getCorrectFileName($basename);
        $dispretionPath = Varien_File_Uploader::getDispretionPath($fileName);
        $fileName       = $dispretionPath . DS . $fileName;

        $fileName = $dispretionPath . DS
                  . Varien_File_Uploader::getNewFileName($this->_getMadiaConfig()->getMediaPath($fileName));

        $ioAdapter = new Varien_Io_File();
        $ioAdapter->setAllowCreateFolders(true);
        $distanationDirectory = dirname($this->_getMadiaConfig()->getMediaPath($fileName));

        try {
            $ioAdapter->open(array(
                'path'=>$distanationDirectory
            ));
				 $ioAdapter->cp($file, $this->_getMadiaConfig()->getMediaPath($fileName));
				 $ioAdapter->chmod($this->_getMadiaConfig()->getMediaPath($fileName), 0777);
        }
        catch (Exception $e) {
            return false;
        }

        return str_replace(DS, '/', $fileName);
    }
	
    /**
     * Retrive media config
     *
     * @return Mage_Catalog_Model_Product_Media_Config
     */
    protected function _getMadiaConfig()
    {
        return Mage::getSingleton('catalog/product_media_config');
    }	
	 
}
