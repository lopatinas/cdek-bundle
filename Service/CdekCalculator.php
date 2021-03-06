<?php

namespace Lopatinas\CdekBundle\Service;

use Lopatinas\CdekBundle\Exception\CdekCalculatorPayloadException;

class CdekCalculator
{
    /**
     * API version
     * @var string
     */
    private $version = "1.0";

    /**
     * API Url
     * @var string
     */
    private $jsonUrl = 'https://api.cdek.ru/calculator/calculate_price_by_json.php';

    /**
     * API Login
     * @var string
     */
    private $authLogin;

    /**
     * API Password
     * @var string
     */
    private $authPassword;

    /**
     * @var array
     */
    private $tariffList = [
        ['priority' => 0, 'id' => 62],
        ['priority' => 1, 'id' => 63],
        ['priority' => 2, 'id' => 15],
        ['priority' => 3, 'id' => 16],
        ['priority' => 4, 'id' => 17],
        ['priority' => 5, 'id' => 18],
    ];

    /**
     * CdekCalculator constructor.
     * @param $account
     * @param $password
     * @param $tariffList
     */
    public function __construct($account, $password, $tariffList = null)
    {
        $this->authLogin = $account;
        $this->authPassword = $password;
        if (is_array($tariffList) && !empty($tariffList)) {
            $this->tariffList = $tariffList;
        }
    }

    /**
     * Calculate delivery cost
     * @param array $data
     * @return mixed
     */
    public function calculate(array $data)
    {
        if (!isset($data['dateExecute']) || empty($data['dateExecute'])) {
            $data['dateExecute'] = date('Y-m-d');
        }

        if (!isset($data['senderCityId']) || empty ($data['senderCityId'])) {
            throw new CdekCalculatorPayloadException('"senderCityId" is required');
        }

        if (!isset($data['receiverCityId']) || empty ($data['receiverCityId'])) {
            throw new CdekCalculatorPayloadException('"receiverCityId" is required');
        }

        if (!isset($data['goods']) || empty($data['goods']) || !is_array($data['goods'])) {
            throw new CdekCalculatorPayloadException('"goods" is required');
        }

        foreach ($data['goods'] as $item) {
            if (!isset($item['weight']) || empty($item['weight'])) {
                throw new CdekCalculatorPayloadException('Item "weight" is required');
            }
            if ((!isset($item['volume']) || empty($item['volume'])) && (!isset($item['length']) || empty($item['length'])
                    || !isset($item['width']) || empty($item['width']) || !isset($item['height']) || empty($item['height']))
            ) {
                throw new CdekCalculatorPayloadException('Item "volume" or "length", "height" and "width" are required');
            }
        }

        $data = array_merge($data, [
            'version' => $this->version,
            'authLogin' => $this->authLogin,
            'secure' => $this->getSecureAuthPassword($data['dateExecute']),
            'tariffList' => $this->tariffList,
        ]);

        return $this->request($data);
    }

    /**
     * Get password hash
     * @param $date
     * @return string
     */
    private function getSecureAuthPassword($date)
    {
        return md5($date . '&' . $this->authPassword);
    }

    /**
     * Request to API
     * @param $data
     * @return mixed
     */
    private function request($data)
    {
        $dataString = json_encode($data);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->jsonUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);

        $result = curl_exec($ch);
        curl_close($ch);

        return json_decode($result, true);
    }
}
