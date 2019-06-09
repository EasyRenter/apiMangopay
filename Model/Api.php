<?php

// List of Mangopay API Call in PHP / Curl


class Easyrenter_Mangopay_Model_Api
{

	// Any action with Mangopay need to be initiated by getting a token
	public function getToken(){

		// Get admin conf in ../Helper/Data.php
		$urlKey = "oauth/token/";
		$apiInfos = Mage::helper("Easyrenter_Mangopay")->getApiInfos($urlKey, false);


		// Curl authentification
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_HTTPHEADER, array(
			"Authorization: Basic ".$apiInfos["encode_id"],
			"Content-Type: application/json; charset=utf-8",
			"Accept:application/json, text/javascript, */*; q=0.01"
		));
		curl_setopt($curl, CURLOPT_URL, $apiInfos["url"]);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt($curl, CURLOPT_VERBOSE, TRUE);
		curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, '{
			"grant_type" : "client_credentials",
		}');

		$result = curl_exec($curl);

	    if (curl_errno($curl)) {
	        print curl_error($curl);
	    } else {
	        curl_close($curl);
	    }

	    return $result;
	}

	// Function to create a Mangopay user
	public function createUser($token, $customer, $order) {

		// Get admin conf in ../Helper/Data.php
		$urlKey = "users/natural/";
		$apiInfos = Mage::helper("Easyrenter_Mangopay")->getApiInfos($urlKey, true);

		// Get Magento customer physical adress and date of birth
		$address = $order->getBillingAddress();
    	$customerDob = $customer["dob"];
    	$customerDob = strtotime($customerDob);
    	$customerStreet = $address->getStreet();

		// Curl authentification
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_HTTPHEADER, array(
			"Authorization: Bearer ".$token,
			"Content-Type: application/json; charset=utf-8",
			"Accept:application/json, text/javascript, */*; q=0.01"
		));
		curl_setopt($curl, CURLOPT_URL, $apiInfos["url"]);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt($curl, CURLOPT_VERBOSE, TRUE);
		curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, '{
			"Id": "'.$customer->getId().'",
			"CreationDate": "'.date("Y-m-d H:i:s", time()).'",
			"Tag": "-",
			"PersonType": "NATURAL",
			"Email": "'.$customer->getEmail().'",
			"KYCLevel": "REGULAR",
			"FirstName": "'.$customer->getFirstname().'",
			"LastName": "'.$customer->getLastname().'",
			"Address": {
			"AddressLine1": "'.$customerStreet[0].'",
			"AddressLine2": "-",
			"City": "'.$address->getCity().'",
			"Region": "-",
			"PostalCode": "'.$address->getPostcode().'",
			"Country": "'.$address->getCountry().'"
			},
			"Birthday": '.$customerDob.',
			"Nationality": "'.$address->getCountryId().'",
			"CountryOfResidence": "'.$address->getCountryId().'",
			"Occupation": "-",
			"IncomeRange": 2,
			"ProofOfAddress": "-",
			"ProofOfIdentity": "-",
			"Capacity": "NORMAL"
			}');

		$result = curl_exec($curl);

	    if (curl_errno($curl)) {
	        print curl_error($curl);
	    } else {
	        curl_close($curl);
	    }

	    // Mangopay user ID save in the Magento customer to be use later
	    $arrResult=json_decode($result);
	    $customerMangopayId = $arrResult->Id;
		$customer->setData("mangopayclientid", $customerMangopayId);
		$customer->save();

	    return $result;
	}

	// Function to get Mangopay user with user ID
	public function getUser($token, $customerMangopayId){


		// Get admin conf in ../Helper/Data.php
		$urlKey = "users/".$customerMangopayId;
		$apiInfos = Mage::helper("Easyrenter_Mangopay")->getApiInfos($urlKey, true);


		// Curl authentification
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_HTTPHEADER, array(
			"Authorization: Bearer ".$token,
			"Content-Type: application/json; charset=utf-8",
			"Accept:application/json, text/javascript, */*; q=0.01"
		));
		curl_setopt($curl, CURLOPT_URL, $apiInfos["url"]);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);


		$result = curl_exec($curl);

	    if (curl_errno($curl)) {
	        print curl_error($curl);
	    } else {
	        curl_close($curl);
	    }

	    return $result;
	}

	// Function to update Mangopay user
	public function upgradeUser($token, $userId, $customer, $order) {

		// Get admin conf in ../Helper/Data.php
		$urlKey = "users/natural/".$userId;
		$apiInfos = Mage::helper("Easyrenter_Mangopay")->getApiInfos($urlKey, true);

		//Récupération adresse physique et date de naissance
		$address = $order->getBillingAddress();
    	$customerDob = $customer["dob"];
    	$customerDob = strtotime($customerDob);
    	$customerStreet = $address->getStreet();

		// Curl authentification
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_HTTPHEADER, array(
			"Authorization: Bearer ".$token,
			"Content-Type: application/json; charset=utf-8",
			"Accept:application/json, text/javascript, */*; q=0.01"
		));
		curl_setopt($curl, CURLOPT_URL, $apiInfos["url"]);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
		curl_setopt($curl, CURLOPT_POSTFIELDS, '{
			"Tag": "-",
			"FirstName": "'.$customer->getFirstname().'",
			"LastName": "'.$customer->getLastname().'",
			"Address": {
			"AddressLine1": "'.$customerStreet[0].'",
			"AddressLine2": "-",
			"City": "'.$address->getCity().'",
			"Region": "-",
			"PostalCode": "'.$address->getPostcode().'",
			"Country": "'.$address->getCountry().'"
			},
			"Birthday": '.$customerDob.',
			"Nationality": "'.$address->getCountryId().'",
			"CountryOfResidence": "'.$address->getCountryId().'",
			"Occupation": "-",
			"IncomeRange": 2,
			"Email": "'.$customer->getEmail().'"
		}');

		$result = curl_exec($curl);

	    if (curl_errno($curl)) {
	        print curl_error($curl);
	    } else {
	        curl_close($curl);
	    }

	    return $result;
	}

	// Function to create a wallet into the Mangopay user
	public function createUserWallet($token, $userId) {


		// Get admin conf in ../Helper/Data.php
		$urlKey = "wallets/";
		$apiInfos = Mage::helper("Easyrenter_Mangopay")->getApiInfos($urlKey, true);

		// Curl authentification
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_HTTPHEADER, array(
			"Authorization: Bearer ".$token,
			"Content-Type: application/json; charset=utf-8",
			"Accept:application/json, text/javascript, */*; q=0.01"
		));
		curl_setopt($curl, CURLOPT_URL,  $apiInfos["url"]);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt($curl, CURLOPT_VERBOSE, TRUE);
		curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, '{
			"Tag": "-",
			"Owners": [ "'.$userId.'" ],
			"Description": "Wallet #'.$userId.'",
			"Currency": "EUR"
			}');

		$result = curl_exec($curl);

	    if (curl_errno($curl)) {
	        print curl_error($curl);
	    } else {
	        curl_close($curl);
	    }

	    return $result;
	}

	// Function to get the data wallet from a specific Mangopay user
	public function getWallet($token, $userId){


		// Get admin conf in ../Helper/Data.php
		$urlKey = "users/".$userId."/wallets/";
		$apiInfos = Mage::helper("Easyrenter_Mangopay")->getApiInfos($urlKey, true);


		// Curl authentification
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_HTTPHEADER, array(
			"Authorization: Bearer ".$token,
			"Content-Type: application/json; charset=utf-8",
			"Accept:application/json, text/javascript, */*; q=0.01"
		));
		curl_setopt($curl, CURLOPT_URL, $apiInfos["url"]);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);


		$result = curl_exec($curl);

	    if (curl_errno($curl)) {
	        print curl_error($curl);
	    } else {
	        curl_close($curl);
	    }

	    return $result;
	}

	// Function to prepare the bank card saving
	public function registerCard($token, $userId, $userWalletId) {

		// Get admin conf in ../Helper/Data.php
		$urlKey = "cardregistrations/";
		$apiInfos = Mage::helper("Easyrenter_Mangopay")->getApiInfos($urlKey, true);

		// Curl authentification
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_HTTPHEADER, array(
			"Authorization: Bearer ".$token,
			"Content-Type: application/json; charset=utf-8",
			"Accept:application/json, text/javascript, */*; q=0.01"
		));
		curl_setopt($curl, CURLOPT_URL, $apiInfos["url"]);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt($curl, CURLOPT_VERBOSE, TRUE);
		curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, '{
			"Tag": "-",
			"UserId": "'.$userId.'",
			"Currency": "EUR",
			"CardType": "CB_VISA_MASTERCARD"
		}');

		$result = curl_exec($curl);

	    if (curl_errno($curl)) {
	        print curl_error($curl);
	    } else {
	        curl_close($curl);
	    }

	    return $result;
	}

	// Function to create the bank card into the wallet of a specific Mangopay user
	public function createCard($token, $registerCardId, $RegistrationData) {

		// Get admin conf in ../Helper/Data.php
		$urlKey = "CardRegistrations/".$registerCardId;
		$apiInfos = Mage::helper("Easyrenter_Mangopay")->getApiInfos($urlKey, true);

		// Curl authentification
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_HTTPHEADER, array(
			"Authorization: Bearer ".$token,
			"Content-Type: application/json; charset=utf-8",
			"Accept:application/json, text/javascript, */*; q=0.01"
		));
		curl_setopt($curl, CURLOPT_URL, $apiInfos["url"]);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt($curl, CURLOPT_VERBOSE, TRUE);
		curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, '{

			"Tag": "-",
			"RegistrationData": "data='.$RegistrationData.'"

		}');

		$result = curl_exec($curl);

	    if (curl_errno($curl)) {
	        print curl_error($curl);
	    } else {
	        curl_close($curl);
	    }

	    return $result;
	}

	// Function to create the bank preauthorisation into the wallet of a specific Mangopay user
	public function createPreautorisation($token, $userId, $CardId, $orderId, $formUrl) {

		// Get admin conf in ../Helper/Data.php
		$urlKey = "preauthorizations/card/direct";
		$apiInfos = Mage::helper("Easyrenter_Mangopay")->getApiInfos($urlKey, true);

		//Récupérer certaines variables
		$order = Mage::getModel('sales/order')->load($orderId, 'increment_id');
		$orderGrandTotal = $order->getGrandTotal();
        $orderGrandTotalFormated = substr($orderGrandTotal, 0, -2);
        $orderGrandTotalFormated = str_replace(".","",$orderGrandTotalFormated);
        //$orderGrandTotalFormated = "100";
		$address = $order->getBillingAddress();
    	$customerStreet = $address->getStreet();

		// Curl authentification
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_HTTPHEADER, array(
			"Authorization: Bearer ".$token,
			"Content-Type: application/json; charset=utf-8",
			"Accept:application/json, text/javascript, */*; q=0.01"
		));
		curl_setopt($curl, CURLOPT_URL, $apiInfos["url"]);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt($curl, CURLOPT_VERBOSE, TRUE);
		curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, '{
			"Tag": "'.$orderId.',reservation",
			"AuthorId": "'.$userId.'",
			"CardId": "'.$CardId.'",
			"DebitedFunds": {
			"Currency": "EUR",
			"Amount": '.$orderGrandTotalFormated.'
			},
			"Billing": {
			"Address": {
			"AddressLine1": "'.$customerStreet[0].'",
			"AddressLine2": "-",
			"City": "'.$address->getCity().'",
			"Region": "-",
			"PostalCode": "'.$address->getPostcode().'",
			"Country": "'.$address->getCountryId().'"
			}
			},
			"SecureMode": "DEFAULT",
			"SecureModeReturnURL": "'.$formUrl.'"
		}');

		$result = curl_exec($curl);

	    if (curl_errno($curl)) {
	        print curl_error($curl);
	    } else {
	        curl_close($curl);
	    }

	    return $result;
	}

	// Function to get a deposite - it is specific for rental business
	public function createPreautorisationCaution($token, $userId, $CardId, $orderId, $formUrl) {

		// Get admin conf in ../Helper/Data.php
		$urlKey = "preauthorizations/card/direct";
		$apiInfos = Mage::helper("Easyrenter_Mangopay")->getApiInfos($urlKey, true);

		//Récupérer certaines variables
		$order = Mage::getModel('sales/order')->load($orderId, 'increment_id');
		$orderAllItems = $order->getAllItems();
	    foreach ($orderAllItems as $item) {

	    	$product = Mage::getModel('catalog/product')->load($item->getProductId());
			//$productCaution = $product->getData('franchise_laparisienne');
			$productCaution = $product->getData('caution');
		}

		$_productCaution = ($productCaution*100);

		//Etablir un valeur par defaut de la caution si elle est vide ou inf à 1
		if ($_productCaution<1 || $_productCaution=="") {
			$_productCaution = 150000;
		}

		$address = $order->getBillingAddress();
    	$customerStreet = $address->getStreet();

		// Curl authentification
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_HTTPHEADER, array(
			"Authorization: Bearer ".$token,
			"Content-Type: application/json; charset=utf-8",
			"Accept:application/json, text/javascript, */*; q=0.01"
		));
		curl_setopt($curl, CURLOPT_URL, $apiInfos["url"]);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt($curl, CURLOPT_VERBOSE, TRUE);
		curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, '{
			"Tag": "'.$orderId.',caution",
			"AuthorId": "'.$userId.'",
			"CardId": "'.$CardId.'",
			"DebitedFunds": {
			"Currency": "EUR",
			"Amount": '.$_productCaution.'
			},
			"Billing": {
			"Address": {
			"AddressLine1": "'.$customerStreet[0].'",
			"AddressLine2": "-",
			"City": "'.$address->getCity().'",
			"Region": "-",
			"PostalCode": "'.$address->getPostcode().'",
			"Country": "'.$address->getCountryId().'"
			}
			},
			"SecureMode": "DEFAULT",
			"SecureModeReturnURL": "'.$formUrl.'"
		}');

		$result = curl_exec($curl);

	    if (curl_errno($curl)) {
	        print curl_error($curl);
	    } else {
	        curl_close($curl);
	    }

	    return $result;
	}

	// Function to cancel a specific preauthorisation
	public function cancelPreautorisation($token, $preautorisationId) {

		// Get admin conf in ../Helper/Data.php
		$urlKey = "preauthorizations/".$preautorisationId;
		$apiInfos = Mage::helper("Easyrenter_Mangopay")->getApiInfos($urlKey, true);


		// Curl authentification
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_HTTPHEADER, array(
			"Authorization: Bearer ".$token,
			"Content-Type: application/json; charset=utf-8",
			"Accept:application/json, text/javascript, */*; q=0.01"
		));
		curl_setopt($curl, CURLOPT_URL, $apiInfos["url"]);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
		curl_setopt($curl, CURLOPT_POSTFIELDS, '{
			"Tag": "'.$preautorisationId.',canceled",
			"PaymentStatus": "CANCELED"
		}');

		$result = curl_exec($curl);

	    if (curl_errno($curl)) {
	        print curl_error($curl);
	    } else {
	        curl_close($curl);
	    }

	    return $result;
	}

	// Function to view a spectific preauthorisation
	public function viewPreautorisation($token, $preAuthorizationId){


		// Get admin conf in ../Helper/Data.php
		$urlKey = "preauthorizations/".$preAuthorizationId;
		$apiInfos = Mage::helper("Easyrenter_Mangopay")->getApiInfos($urlKey, true);


		// Curl authentification
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_HTTPHEADER, array(
			"Authorization: Bearer ".$token,
			"Content-Type: application/json; charset=utf-8",
			"Accept:application/json, text/javascript, */*; q=0.01"
		));
		curl_setopt($curl, CURLOPT_URL, $apiInfos["url"]);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);


		$result = curl_exec($curl);

	    if (curl_errno($curl)) {
	        print curl_error($curl);
	    } else {
	        curl_close($curl);
	    }

	    return $result;
	}

	// Function to view all the preauthorisations of a specific Mangopay user
	public function viewAllPreautorisation($token, $userId){


		// Get admin conf in ../Helper/Data.php
		$urlKey = "users/".$userId."/preauthorizations?Per_Page=100&Sort=CreationDate:DESC";
		$apiInfos = Mage::helper("Easyrenter_Mangopay")->getApiInfos($urlKey, true);


		// Curl authentification
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_HTTPHEADER, array(
			"Authorization: Bearer ".$token,
			"Content-Type: application/json; charset=utf-8",
			"Accept:application/json, text/javascript, */*; q=0.01"
		));
		curl_setopt($curl, CURLOPT_URL, $apiInfos["url"]);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);


		$result = curl_exec($curl);

	    if (curl_errno($curl)) {
	        print curl_error($curl);
	    } else {
	        curl_close($curl);
	    }

	    return $result;
	}

	// Function to get the money of a specific bank preauthorisation
	public function createPayingPreautorisation($token, $userId, $walletId, $orderId, $preAuthorizationId) {

		// Get admin conf in ../Helper/Data.php
		$urlKey = "payins/preauthorized/direct/";
		$apiInfos = Mage::helper("Easyrenter_Mangopay")->getApiInfos($urlKey, true);

		// Get specific order datas to complete the call 
		$order = Mage::getModel('sales/order')->load($orderId, 'increment_id');
		$orderGrandTotal = $order->getGrandTotal();
        $orderGrandTotalFormated = substr($orderGrandTotal, 0, -2);
        $orderGrandTotalFormated = str_replace(".","",$orderGrandTotalFormated);

		// Curl authentification
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_HTTPHEADER, array(
			"Authorization: Bearer ".$token,
			"Content-Type: application/json; charset=utf-8",
			"Accept:application/json, text/javascript, */*; q=0.01"
		));
		curl_setopt($curl, CURLOPT_URL, $apiInfos["url"]);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt($curl, CURLOPT_VERBOSE, TRUE);
		curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, '{
			"Tag": "'.$orderId.',paiement",
			"AuthorId": "'.$userId.'",
			"CreditedUserId": "'.$userId.'",
			"CreditedWalletId": "'.$walletId.'",
			"DebitedFunds": {
			"Currency": "EUR",
			"Amount": '.$orderGrandTotalFormated.'
			},
			"Fees": {
			"Currency": "EUR",
			"Amount": 0
			},
			"PreauthorizationId": "'.$preAuthorizationId.'"
		}');

		$result = curl_exec($curl);

	    if (curl_errno($curl)) {
	        print curl_error($curl);
	    } else {
	        curl_close($curl);
	    }

	    return $result;
	}

	// Same function as createPayingPreautorisation() but for a deposite - use if there is a disaster
	public function createPayingPreautorisationCaution($token, $userId, $walletId, $orderId, $preAuthorizationId, $preAuthorizationDebitedFunds) {

		// Get admin conf in ../Helper/Data.php
		$urlKey = "payins/preauthorized/direct/";
		$apiInfos = Mage::helper("Easyrenter_Mangopay")->getApiInfos($urlKey, true);


		// Curl authentification
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_HTTPHEADER, array(
			"Authorization: Bearer ".$token,
			"Content-Type: application/json; charset=utf-8",
			"Accept:application/json, text/javascript, */*; q=0.01"
		));
		curl_setopt($curl, CURLOPT_URL, $apiInfos["url"]);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt($curl, CURLOPT_VERBOSE, TRUE);
		curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, '{


			"Tag": "'.$orderId.',paiementCaution",
			"AuthorId": "'.$userId.'",
			"CreditedUserId": "'.$userId.'",
			"CreditedWalletId": "'.$walletId.'",
			"DebitedFunds": {
			"Currency": "EUR",
			"Amount": '.$preAuthorizationDebitedFunds.'
			},
			"Fees": {
			"Currency": "EUR",
			"Amount": 0
			},
			"PreauthorizationId": "'.$preAuthorizationId.'"

		}');

		$result = curl_exec($curl);

	    if (curl_errno($curl)) {
	        print curl_error($curl);
	    } else {
	        curl_close($curl);
	    }

	    return $result;
	}

	// Function to transfer a money from a specific user wallet to the seller user wallet + client (you) commission
	public function transferFinLocation($token, $userLocataireId, $walletLocataireId, $userLoueurId, $walletLoueurId, $orderId) {

		// Get admin conf in ../Helper/Data.php
		$urlKey = "transfers/";
		$apiInfos = Mage::helper("Easyrenter_Mangopay")->getApiInfos($urlKey, true);

		$order = Mage::getModel('sales/order')->load($orderId, 'increment_id');
		$orderGrandTotal = $order->getGrandTotal();
        $orderGrandTotalFormated = substr($orderGrandTotal, 0, -2);
        $orderGrandTotalFormated = str_replace(".","",$orderGrandTotalFormated);

        // I did feel the need to make the Mangopay client commission rate manageable 
        $commissionEasyrenter = $orderGrandTotalFormated*0.28;

		// Curl authentification
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_HTTPHEADER, array(
			"Authorization: Bearer ".$token,
			"Content-Type: application/json; charset=utf-8",
			"Accept:application/json, text/javascript, */*; q=0.01"
		));
		curl_setopt($curl, CURLOPT_URL, $apiInfos["url"]);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt($curl, CURLOPT_VERBOSE, TRUE);
		curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, '{
			"Tag": "'.$orderId.',transfer",
			"AuthorId": "'.$userLocataireId.'",
			"CreditedUserId": "'.$userLoueurId.'",
			"DebitedFunds": {
			"Currency": "EUR",
			"Amount": '.$orderGrandTotalFormated.'
			},
			"Fees": {
			"Currency": "EUR",
			"Amount": '.$commissionEasyrenter.'
			},
			"DebitedWalletId": "'.$walletLocataireId.'",
			"CreditedWalletId": "'.$walletLoueurId.'"
		}');

		$result = curl_exec($curl);

	    if (curl_errno($curl)) {
	        print curl_error($curl);
	    } else {
	        curl_close($curl);
	    }

	    return $result;
	}

	// Same function as transferFinLocation() but for a deposite - use if there is a disaster
	// The best legal way is to use a Mangopay legal user of yur company, instead of the Mangopay client
	public function transferCaution($token, $userId, $walletId, $orderId, $preAuthorizationDebitedFunds) {

		// Get admin conf in ../Helper/Data.php
		$urlKey = "transfers/";
		$apiInfos = Mage::helper("Easyrenter_Mangopay")->getApiInfos($urlKey, true);


		// Curl authentification
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_HTTPHEADER, array(
			"Authorization: Bearer ".$token,
			"Content-Type: application/json; charset=utf-8",
			"Accept:application/json, text/javascript, */*; q=0.01"
		));
		curl_setopt($curl, CURLOPT_URL, $apiInfos["url"]);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt($curl, CURLOPT_VERBOSE, TRUE);
		curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, '{


			"Tag": "'.$orderId.',transfer_caution",
			"AuthorId": "'.$userId.'",
			"CreditedUserId": "'.$apiInfos["idUserLegal"].'",
			"DebitedFunds": {
			"Currency": "EUR",
			"Amount": '.$preAuthorizationDebitedFunds.'
			},
			"Fees": {
			"Currency": "EUR",
			"Amount": 0
			},
			"DebitedWalletId": "'.$walletId.'",
			"CreditedWalletId": "'.$apiInfos["idWalletLegal"].'"

		}');

		$result = curl_exec($curl);

	    if (curl_errno($curl)) {
	        print curl_error($curl);
	    } else {
	        curl_close($curl);
	    }

	    return $result;
	}


	// Function use during the payment to check if the user already exist
	public function verifyUser($token, $customerMangopayId, $customerEmail){


		// Get admin conf in ../Helper/Data.php
		$urlKey = "users/".$customerMangopayId;
		$apiInfos = Mage::helper("Easyrenter_Mangopay")->getApiInfos($urlKey, true);


		// Curl authentification
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_HTTPHEADER, array(
			"Authorization: Bearer ".$token,
			"Content-Type: application/json; charset=utf-8",
			"Accept:application/json, text/javascript, */*; q=0.01"
		));
		curl_setopt($curl, CURLOPT_URL, $apiInfos["url"]);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);


		$result = curl_exec($curl);
		$arrResult=json_decode($result);
		$_customerMangopayId = $arrResult->Id;
		$_customerEmail = $arrResult->Email;

		if($_customerMangopayId == $customerMangopayId && $_customerEmail == $customerEmail){
			$_result = 1;
		}else{
			$_result = 0;
		}

	    if (curl_errno($curl)) {
	        print curl_error($curl);
	    } else {
	        curl_close($curl);
	    }

	    return $_result;
	}

	// Function use during the payment to check if a bank card already exist in the user
	public function verifyCards($token, $customerMangopayId){


		// Get admin conf in ../Helper/Data.php
		$urlKey = "users/".$customerMangopayId."/cards/?Per_Page=100&Sort=CreationDate:DESC";
		$apiInfos = Mage::helper("Easyrenter_Mangopay")->getApiInfos($urlKey, true);


		// Curl authentification
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_HTTPHEADER, array(
			"Authorization: Bearer ".$token,
			"Content-Type: application/json; charset=utf-8",
			"Accept:application/json, text/javascript, */*; q=0.01"
		));
		curl_setopt($curl, CURLOPT_URL, $apiInfos["url"]);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);


		$result = curl_exec($curl);

	    if (curl_errno($curl)) {
	        print curl_error($curl);
	    } else {
	        curl_close($curl);
	    }

	    return $result;
	}

	// Function use to desactivation a object bank card -
	// usefull to avoid clones or to give the possibility of deleting card in the customer account
	public function desactivateCard($token, $CardId) {

		// Get admin conf in ../Helper/Data.php
		$urlKey = "cards/".$CardId;
		$apiInfos = Mage::helper("Easyrenter_Mangopay")->getApiInfos($urlKey, true);


		// Curl authentification
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_HTTPHEADER, array(
			"Authorization: Bearer ".$token,
			"Content-Type: application/json; charset=utf-8",
			"Accept:application/json, text/javascript, */*; q=0.01"
		));
		curl_setopt($curl, CURLOPT_URL, $apiInfos["url"]);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
		curl_setopt($curl, CURLOPT_POSTFIELDS, '{
			"Active": false
		}');

		$result = curl_exec($curl);

	    if (curl_errno($curl)) {
	        print curl_error($curl);
	    } else {
	        curl_close($curl);
	    }

	    return $result;
	}

}
?>
