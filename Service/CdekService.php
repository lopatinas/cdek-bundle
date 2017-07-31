<?php

namespace Lopatinas\CdekBundle\Service;

use Errogaht\CDEK\CalculatePriceDeliveryCdek;
use Errogaht\CDEK\CdekSdk;
use Lopatinas\CdekBundle\Exception\CdekException;

class CdekService
{
    /** @var CdekSdk */
    private $cdek;

    /** @var string */
    private $account;

    /** @var string */
    private $password;

    /**
     * @param $account
     */
    public function setAccount($account)
    {
        $this->account = $account;
    }

    /**
     * @param $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * Init CdekSDK object
     */
    public function init()
    {
        try {
            $this->cdek = new CdekSdk($this->account, $this->password);
        } catch (\Exception $e) {
            throw new CdekException($e->getMessage(), $e->getCode(), $e->getPrevious());
        }
    }

    /**
     * @param array $data
     * @return array
     */
    public function calculate(array $data)
    {
        $calculator = new CalculatePriceDeliveryCdek();
        $calculator->setAuth($this->account, $this->password);
        try {
            $calculator->setDateExecute($data['date']);
            $calculator->setReceiverCityId($data['receiverCityId']);
            $calculator->setSenderCityId($data['senderCityId']);
            $calculator->setTariffId($data['tariffId']);
            $calculator->setModeDeliveryId($data['deliveryModeId']);
            foreach ($data['items'] as $item) {
                if (!empty($item['volume'])) {
                    $calculator->addGoodsItemByVolume($item['weight'], $item['volume']);
                } else {
                    $calculator->addGoodsItemBySize($item['weight'], $item['length'], $item['width'], $item['height']);
                }
            }
        } catch (\Exception $e) {
            throw new CdekException($e->getMessage());
        }
        if (!$calculator->calculate()) {
            throw new CdekException($calculator->getError(), 400);
        }

        return $calculator->getResult();
    }
}
