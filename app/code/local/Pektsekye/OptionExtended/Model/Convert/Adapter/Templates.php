<?php



class Pektsekye_OptionExtended_Model_Convert_Adapter_Templates
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
        $write = $resource->getConnection('core_write');

        $toOptionextendedTemplateTable = "INSERT INTO `{$resource->getTableName('optionextended/template')}` (`title`,`code`,`is_active`) VALUES ";

        $toOT='';
        $tCodes = '';
        $codes = array(); 
                                      
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
            
            if (empty($d['code'])){ 
              $this->addException(Mage::helper('optionextended')->__('Skip import row, required field "%s" not defined', 'code'), Mage_Dataflow_Model_Convert_Exception::FATAL);
              $skippedRows++;
              continue;        
            }
            
			      if (isset($codes[$d['code']])){      
              $this->addException(Mage::helper('optionextended')->__('Skip import row, template with %s "%s" has been already imported', 'code', $d['code']), Mage_Dataflow_Model_Convert_Exception::FATAL);
              $skippedRows++;
              continue;        
            } 
            
            $codes[$d['code']] = 1; 

            if (empty($d['title'])){ 
              $this->addException(Mage::helper('optionextended')->__('Skip import row, required field "%s" not defined', 'title'), Mage_Dataflow_Model_Convert_Exception::FATAL);
              $skippedRows++;
              continue;        
            }

            $title = $write->quote($d['title']);
            $code = $write->quote($d['code']);            
            $isActive = (int) $d['is_active']; 
                        	               
	          $toOT  .= ($toOT != '' ? ',' : '') . "({$title},{$code},{$isActive})";
            $tCodes.= ($tCodes != '' ? ',' : '') . $code;	          
        }
        
        $importedRows = $countRows - $skippedRows;

        if ($importedRows > 0){    
          $write->raw_query("DELETE FROM `{$resource->getTableName('optionextended/template')}` WHERE `code` IN ({$tCodes})");		    	        	  	        	  	  	
			    $write->raw_query($toOptionextendedTemplateTable . $toOT);		    			  	      
        }

          
        if ($skippedRows == 0)     
          $this->addException(Mage::helper('optionextended')->__('Imported %d rows.', $countRows));
        else 
          $this->addException(Mage::helper('optionextended')->__('Imported %d rows of %d', $importedRows, $countRows));   


        return $this;

    }
	

	 
}
