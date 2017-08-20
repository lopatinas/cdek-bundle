<?php

namespace Lopatinas\CdekBundle\Service;

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

    public static $tariffs = [
        136 => 'Посылка склад-склад',
        137 => 'Посылка склад-дверь',
        138 => 'Посылка дверь-склад',
        139 => 'Посылка дверь-дверь',
        233 => 'Экономичная посылка склад-дверь',
        234 => 'Экономичная посылка склад-склад',
        291 => 'CDEK Express склад-склад',
        293 => 'CDEK Express дверь-дверь',
        294 => 'CDEK Express склад-дверь',
        295 => 'CDEK Express дверь-склад',
    ];

    public static $tariffPriorityList = [
        ['priority' => 0, 'id' => 136],
        ['priority' => 1, 'id' => 137],
        ['priority' => 2, 'id' => 138],
        ['priority' => 3, 'id' => 139],
        ['priority' => 4, 'id' => 233],
        ['priority' => 5, 'id' => 234],
        ['priority' => 6, 'id' => 291],
        ['priority' => 7, 'id' => 293],
        ['priority' => 8, 'id' => 294],
        ['priority' => 9, 'id' => 295],
    ];

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
        $calculator = new CdekCalculator($this->account, $this->password, self::$tariffPriorityList);
        $result = $calculator->calculate($data);
        if (!isset($result['result']) || empty($result['result'])) {
            $exception = null;
            foreach ($result['error'] as $error) {
                $exception = new CdekException($error['text'], $error['code'], $exception);
            }
            throw $exception;
        }

        return $result;
    }

    /**
     * @param int $tariffId
     * @return bool|string
     */
    public static function getTariffName($tariffId)
    {
        if (!isset(self::$tariffs[$tariffId])){
            return false;
        }

        return self::$tariffs[$tariffId];
    }
}
