<?php
/*
 *  BitGoSDk Class
 *  Author : gitshinesun
 */

class BitGoSDK {

    const BITGO_PRODUCTION_API_ENDPOINT = 'https://www.bitgo.com/api/v1';
    const BITGO_TESTNET_API_ENDPOINT = 'https://test.bitgo.com/api/v1';

    private $API_Endpoint = null;
    private $url = null;
    private $params = array();

    public function __construct() {
        $this->_ci = & get_instance();

        if ( ! $this->is_enabled())
        {
            log_message('error', 'cURL Class - Error.');
        }
    }

    public function is_enabled()
    {
        return function_exists('curl_init');
    }

    public function set_init($accessToken, $testNet = false)
    {
        $this->accessToken = $accessToken;
        $this->testNet = $testNet;

        if ($this->testNet) {
            $this->API_Endpoint = self::BITGO_TESTNET_API_ENDPOINT;
        } else {
            $this->API_Endpoint = self::BITGO_PRODUCTION_API_ENDPOINT;
        }
    }

    /**
     * Gets details for a transaction hash
     * 
     * @param string $tx Bitcoin transaction hash
     * @return string    Decoded JSON as a array
     */
    public function getTransactionDetails($tx) {
        $this->url = $this->API_Endpoint . '/tx/' . $tx;
        return $this->execute('GET');
    }

    /**
     * Get the list of wallets for the user
     * 
     * @return string Decoded JSON as a array
     */
    public function listWallets() {
        $this->url = $this->API_Endpoint . '/wallet';
        return $this->execute('GET');
    }

    /**
     * Gets a list of addresses which have been instantiated for a wallet using the New Address API.
     * 
     * @param string $wallet Primary bitcoin address of your BitGo wallet
     * @return string        Decoded JSON as a array
     */
    public function listWalletAddresses($wallet) {
        $this->url = $this->API_Endpoint . '/wallet/' . $wallet . '/addresses';
        return $this->execute('GET');
    }

    /**
     * Get transactions for a given wallet, ordered by reverse block height (unconfirmed transactions first).
     * 
     * @param string $wallet Primary bitcoin address of your BitGo wallet
     * @return string        Decoded JSON as a array
     */
    public function listWalletTransactions($wallet) {
        $this->url = $this->API_Endpoint . '/wallet/' . $wallet . '/tx';
        return $this->execute('GET');
    }

    /**
     * Creates a new address for an existing wallet. BitGo wallets consist of two independent chains of 
     * addresses, designated 0 and 1. The 0-chain is typically used for receiving funds, while the 1-chain is used 
     * internally for creating change when spending from a wallet. It is considered best practice to generate a 
     * new receiving address for each new incoming transaction, in order to help maximize privacy.
     * 
     * @param string $wallet Primary bitcoin address of your BitGo wallet
     * @param int $chain     0-chain is recommended if you need to receive payments
     * @return string        Decoded JSON as a array
     */
    public function createAddress($wallet, $chain) 
    {
        $this->url = $this->API_Endpoint . '/wallet/' . $wallet . '/address/' . $chain;
        /*$this->params = [
            'wallet' => $wallet,
            'chain' => $chain
        ];*/
        $this->param['wallet'] = $wallet;
        $this->param['chain'] = $chain;
        return $this->execute('POST');
    }

    /**
     * Lookup an address with balance info.
     * 
     * @param string $address Bitcoin address
     * @return string         Decoded JSON as a array
     */
    public function getAddressDetails($address)
    {
        $this->url = $this->API_Endpoint . '/address/' . $address;
        return $this->execute('GET');
    }

    /**
     * Get transactions for a given address, ordered by reverse block height.
     * 
     * @param string $address Bitcoin address
     * @return string         Decoded JSON as a array
     */
    public function getAddressTransactions($address) 
    {
        $this->url = $this->API_Endpoint . '/address/' . $address . '/tx';
        return $this->execute('GET');
    }

    /**
     * Get the list of labels for the user
     * 
     * @return string Decoded JSON as a array
     */
    public function listAllWalletsLabels() 
    {
        $this->url = $this->API_Endpoint . '/labels';
        return $this->execute('GET');
    }

    /**
     * Get the list of labels for the wallet
     * 
     * @param string $wallet Primary bitcoin address of your BitGo wallet
     * @return string        Decoded JSON as a array
     */
    public function listWalletLabels($wallet) 
    {
        $this->url = $this->API_Endpoint . '/labels/' . $wallet;
        return $this->execute('GET');
    }

    /**
     * Set a label on a specific address and associate it with a specific wallet. Labels are limited to 250 
     * characters in length. Labels cannot be set on a wallet’s first receiving address because it reserved for the 
     * wallet’s label.
     * 
     * @param string $wallet  Primary bitcoin address of your BitGo wallet
     * @param string $address Bitcoin address which you want to change label
     * @param string $label   The label which you want
     * @return string         Decoded JSON as a array
     */
    public function setLabel($wallet, $address, $label) 
    {
        $this->url = $this->API_Endpoint . '/labels/' . $wallet . '/' . $address;
        $this->params['label'] = $label;
        return $this->execute('PUT');
    }

    /**
     * Delete a label from a specific address and wallet.
     * 
     * @param string $wallet  Primary bitcoin address of your BitGo wallet
     * @param string $address Bitcoin address which you want to delete label
     * @return string         Decoded JSON as a array
     */
    public function deleteLabel($wallet, $address) 
    {
        $this->url = $this->API_Endpoint . '/labels/' . $wallet . '/' . $address;
        return $this->execute('DELETE');
    }

    /**
     * Get the list of public keychains for the user
     * 
     * @return string Decoded JSON as a array
     */
    public function listKeychain()
    {
        $this->url = $this->API_Endpoint . '/keychain';
        return $this->execute('GET');
    }

    /**
     * Lookup a keychain by xpub
     * 
     * @param string $xpub The BIP32 xpub to lookup
     * @return string      Decoded JSON as a array
     */
    public function getKeychain($xpub) 
    {
        $this->url = $this->API_Endpoint . '/keychain/' . $xpub;
        $this->params['xpub'] = $xpub;
        return $this->execute('POST');
    }

    /**
     * Creates a new keychain on BitGo’s servers and returns the public keychain to the caller.
     * 
     * @return string Decoded JSON as a array
     */
    public function createBitGoKeychain() 
    {
        $this->url = $this->API_Endpoint . '/keychain/bitgo';
        return $this->execute('POST');
    }

    private function execute($requestType) {
        $ch = curl_init($this->url);
        if ($requestType === 'GET') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        } elseif ($requestType === 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($this->params));
        } elseif ($requestType === 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        } elseif ($requestType === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($this->params));
        }
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $headers = array('Accept: application/json', 
						 'Content-Type: application/json', 
						 'Authorization: Bearer ' . $this->accessToken); 
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__) . '/cacert.pem');

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }
}
