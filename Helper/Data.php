<?php


class Easyrenter_Mangopay_Helper_Data extends Mage_Core_Helper_Abstract
{

	// Function to get admin Mangopay configurations
	public function getApiInfos($urlKey, $needAdmin){

		// Get admin to know if you are using the Mangopay test API ou the prod API
		$apistate=Mage::getStoreConfig('mangopay/api/apistate');
		

		if($apistate == "api_prod"){

			// Get the prod Mangopay access datas
			$adminId = Mage::getStoreConfig('mangopay/api/idadminprod');
			$key = Mage::getStoreConfig('mangopay/api/keyprod');
			$baseUrl = Mage::getStoreConfig('mangopay/api/urlprod');
			$idUserLegal = Mage::getStoreConfig('mangopay/api/idusercautionprod');
			$idWalletLegal = Mage::getStoreConfig('mangopay/api/idwalletcautionprod');

		} else {

			// Get the test Mangopay access datas
			$adminId = Mage::getStoreConfig('mangopay/api/idadmin');
			$key = Mage::getStoreConfig('mangopay/api/keytest');
			$baseUrl = Mage::getStoreConfig('mangopay/api/urltest');
			$idUserLegal = Mage::getStoreConfig('mangopay/api/idusercautiontest');
			$idWalletLegal = Mage::getStoreConfig('mangopay/api/idwalletcautiontest');
		}

		// If you have the Mangopay token
		if($needAdmin) {
			$urlRequest = $baseUrl . $adminId . '/' . $urlKey;
		} else {
			$urlRequest = $baseUrl . $urlKey;
		}

		// Key encoding to get the Mangopay token
		$encodeId = $adminId.":".$key;
		$encodeId = base64_encode($encodeId);

		return["url"=>$urlRequest, "encode_id"=>$encodeId, "idUserLegal"=>$idUserLegal, "idWalletLegal"=>$idWalletLegal];
	}

}
?>
