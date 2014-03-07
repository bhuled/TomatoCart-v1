<?php
/*
  $Id: store.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 TomatoCart;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

class toC_Store_Admin {
	/**
	 * List the store
	 *
	 * @access public static
	 * @return array
	 */
	function listStores() {
		global $osC_Database;
		
		$Qstores = $osC_Database->query('select * from :table_store order by store_id');
		$Qstores->bindTable(':table_store', TABLE_STORE);
		$Qstores->execute();
		
		$stores = array();
		if ($Qstores->numberOfRows() > 0) {
			while ($Qstores->next()) {
				$stores[] = array(
						'store_id' => $Qstores->ValueInt('store_id'),
						'store_name' => $Qstores->Value('store_name'),
						'url_address' => $Qstores->Value('url_address'),
						'ssl_url_address' => $Qstores->Value('ssl_url_address'),
				);
			}
		}
		
		return $stores;
	}
	
	/**
	 * Delete the store
	 * 
	 * @access public static
	 * @param int store id
	 * @return boolean
	 */
	function deleteStore($id) {
		global $osC_Database;
		
		//delete store settings
		$Qdelete_settings = $osC_Database->query('delete from :table_configuration where store_id = :store_id');
		$Qdelete_settings->bindTable(':table_configuration', TABLE_CONFIGURATION);
		$Qdelete_settings->bindInt(':store_id', $id);
		$Qdelete_settings->execute();
		
		//delete store
		$Qdelete_store = $osC_Database->query('delete from :table_store where store_id = :store_id');
		$Qdelete_store->bindTable(':table_store', TABLE_STORE);
		$Qdelete_store->bindInt(':store_id', $id);
		$Qdelete_store->execute();
		
		if ($Qdelete_store->affectedRows() > 0) {
		  return true;
		}
		
		return false;
	}
	
	/**
	 * Get installed templates
	 *
	 * @access public static
	 * @return array
	 */
	function getTemplates() {
		global $osC_Database;
		
		$Qtemplates = $osC_Database->query('select code, title from :table_templates');
		$Qtemplates->bindTable(':table_templates', TABLE_TEMPLATES);
		$Qtemplates->execute();
		
		$templates = array();
		if ($Qtemplates->numberOfRows() > 0) {
		  while ($Qtemplates->next()) {
		  	$templates[] = array('code' => $Qtemplates->value('code'), 'title' => $Qtemplates->value('title'));
		  }
		}
		
		$Qtemplates->freeResult();
		
		return $templates;
	}
	
	/**
	 * Get installed languages
	 *
	 * @access public static
	 * @return array
	 */
	function getLanguages() {
		global $osC_Database;
		
		$Qlanguages = $osC_Database->query('select code, name from :table_languages');
		$Qlanguages->bindTable(':table_languages', TABLE_LANGUAGES);
		$Qlanguages->execute();
		
		$languages = array();
		if ($Qlanguages->numberOfRows() > 0) {
			while ($Qlanguages->next()) {
				$languages[] = array('code' => $Qlanguages->value('code'), 'name' => $Qlanguages->value('name'));
			}
		}
		
		$Qlanguages->freeResult();
		
		return $languages;
	}
	
	/**
	 * Get installed currencies
	 *
	 * @access public static
	 * @return array
	 */
	function getCurrencies() {
		global $osC_Database;
	
		$Qcurrencies = $osC_Database->query('select code, title from :table_currencies');
		$Qcurrencies->bindTable(':table_currencies', TABLE_CURRENCIES);
		$Qcurrencies->execute();
	
		$currencies = array();
		if ($Qcurrencies->numberOfRows() > 0) {
			while ($Qcurrencies->next()) {
				$currencies[] = array('code' => $Qcurrencies->value('code'), 'title' => $Qcurrencies->value('title'));
			}
		}
	
		$Qcurrencies->freeResult();
	
		return $currencies;
	}
	
	/**
	 * Save store
	 *
	 * @access public static
	 * @param array configurations of store
	 * @return boolean
	 */
	function save($configurations = array()) {
		global $osC_Database;
		
		$error = false;
		$store_id = 0;
		
		$osC_Database->startTransaction();
		
		//begin: save store
	  if (count($configurations)  > 0) {
	  	$form_to_database = array(
	  		'store_name'  => 'STORE_NAME',
  			'store_owner' => 'STORE_OWNER',
  			'store_email_address' => 'STORE_OWNER_EMAIL_ADDRESS',
  			'store_email_from' => 'EMAIL_FROM',
  			'store_address_phone' => 'STORE_NAME_ADDRESS',
  			'store_template_code' => 'DEFAULT_TEMPLATE',
  			'countries_id' => 'STORE_COUNTRY',
  			'zone_id' => 'STORE_ZONE',
  			'time_zone' => 'STORE_TIME_ZONE',
  			'language_code' => 'DEFAULT_LANGUAGE',
  			'currency_code' => 'DEFAULT_CURRENCY',
  			'maintenance_mode' => 'MAINTENANCE_MOD',
  			'display_prices_with_tax' => 'DISPLAY_PRICE_WITH_TAX',
  			'dislay_products_recursively' => 'DISPLAY_SUBCATALOGS_PRODUCTS',
  			'synchronize_cart_with_database' => 'SYNCHRONIZE_CART_WITH_DATABASE',
  			'show_confirmation_dialog' => 'ENABLE_CONFIRMATION_DIALOG',
  			'check_stock_level' => 'STOCK_CHECK',
  			'subtract_stock' => 'STOCK_LIMITED',
  			'allow_checkout' => 'STOCK_ALLOW_CHECKOUT',
  			'mark_out_of_stock' => 'STOCK_MARK_PRODUCT_OUT_OF_STOCK',
  			'stock_reorder_level' => 'STOCK_REORDER_LEVEL',
  			'stock_email_alerts' => 'STOCK_EMAIL_ALERT',
  			'check_stock_cart_synchronization' => 'CHECK_STOCKS_SYNCHRONIZE_CART_WITH_DATABASE',
  			'search_results' => 'MAX_DISPLAY_SEARCH_RESULTS',
  			'list_per_row' => 'MAX_DISPLAY_CATEGORIES_PER_ROW',
  			'new_products_listing' => 'MAX_DISPLAY_PRODUCTS_NEW',
  			'search_results_auto_completer' => 'MAX_DISPLAY_AUTO_COMPLETER_RESULTS',
  			'product_name_auto_completer' => 'MAX_CHARACTERS_AUTO_COMPLETER',
  			'width_auto_completer' => 'WIDTH_AUTO_COMPLETER'
	  	);
	  	
	  	//insert the new store
	  	if (!isset($configurations['store_id'])) {
	  	  $Qstore = $osC_Database->query('insert into :table_store (store_name, url_address, ssl_url_address) values (:store_name, :url_address, :ssl_url_address)');
	  	  $Qstore->bindTable(':table_store', TABLE_STORE);
	  	  $Qstore->bindValue(':store_name', $configurations['store_name']);
	  	  $Qstore->bindValue(':url_address', $configurations['store_url']);
	  	  
	  	  if (isset($configurations['ssl_url'])) {
	  	  	$Qstore->bindValue(':ssl_url_address', $configurations['ssl_url']);
	  	  }else {
	  	  	$Qstore->bindValue(':ssl_url_address', $configurations['store_url']);
	  	  }
	  	  
	  	  $Qstore->execute();
	  	  
	  	  $store_id = $osC_Database->nextID();
	  	  
	  	  if ($osC_Database->isError()) {
	  	    $error = true;
	  	  }
	  	}
	  	
	  	//Begin: insert store configurations
	  	if ($error === false) {
	  		foreach ($configurations as $config_key => $config_value) {
	  			//store url is already saved in the store table
	  			if ($config_key == 'store_url' || $config_key == 'ssl_url') {
	  			  continue;
	  			}
	  			
					if (isset($form_to_database[$config_key])) {
						//get configuration info
						$Qinfo = $osC_Database->query('select configuration_title, configuration_description, configuration_group_id from :table_configuration where configuration_key = :configuration_key');
						$Qinfo->bindTable(':table_configuration', TABLE_CONFIGURATION);
						$Qinfo->bindValue(':configuration_key', $form_to_database[$config_key]);
						$Qinfo->execute();
						
						$information = $Qinfo->toArray();
						
						$Qinfo->freeResult();
						
						//editing store > update configurations
  			 		if (isset($configurations['store_id'])) {
  			 			
  			 		//new store > add configurations		
  			 		}else if ($store_id > 0) {
  			 			$Qconfiguration = $osC_Database->query('insert into :table_configuration (store_id, configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id) values (:store_id, :configuration_title, :configuration_key, :configuration_value, :configuration_description, :configuration_group_id)');
  			 			$Qconfiguration->bindTable(':table_configuration', TABLE_CONFIGURATION);
  			 			$Qconfiguration->bindInt(':store_id', $store_id);
  			 			$Qconfiguration->bindValue(':configuration_title', $information['configuration_title']);
  			 			$Qconfiguration->bindValue(':configuration_key', $form_to_database[$config_key]);
  			 			$Qconfiguration->bindValue(':configuration_value', $config_value);
  			 			$Qconfiguration->bindValue(':configuration_description', $information['configuration_description']);
  			 			$Qconfiguration->bindValue(':configuration_group_id', $information['configuration_group_id']);
  			 			$Qconfiguration->execute();
  			 			
  			 			if ($osC_Database->isError()) {
  			 				$error = true;
  			 				break;
  			 			}
  			 			
  			 			if ($Qconfiguration->affectedRows() < 1) {
  			 				$error = true;
  			 				break;
  			 			}
  			 		}
					}
				//end: foreach	
	  		}
  		//end: insert store configurations
	  	}
  	//end: save store
	  }

	  if ($error === false) {
	  	$osC_Database->commitTransaction();
	  	
	  	osC_Cache::clear('configuration');
	  	
	    return true;
	  }
	  
	  $osC_Database->rollbackTransaction();
	  
	  return false;
	}
}