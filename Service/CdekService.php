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
        1 => 'Экспресс лайт дверь-дверь',
        3 => 'Супер-экспресс до 18',
        5 => 'Экономичный экспресс склад-склад',
        10 => 'Экспресс лайт склад-склад',
        11 => 'Экспресс лайт склад-дверь',
        12 => 'Экспресс лайт дверь-склад',
        15 => 'Экспресс тяжеловесы склад-склад',
        16 => 'Экспресс тяжеловесы склад-дверь',
        17 => 'Экспресс тяжеловесы дверь-склад',
        18 => 'Экспресс тяжеловесы дверь-дверь',
        57 => 'Супер-экспресс до 9',
        58 => 'Супер-экспресс до 10',
        59 => 'Супер-экспресс до 12',
        60 => 'Супер-экспресс до 14',
        61 => 'Супер-экспресс до 16',
        62 => 'Магистральный экспресс склад-склад',
        63 => 'Магистральный супер-экспресс склад-склад',
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
        301 => 'До постомата InPost дверь-склад',
        302 => 'До постомата InPost склад-склад',
    ];

    public $tariffPriorityList = [];

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
        $this->initTariffPriorityList();
        $calculator = new CdekCalculator($this->account, $this->password, $this->tariffPriorityList);
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

    private function initTariffPriorityList()
    {
        $priority = 0;
        foreach (self::$tariffs as $id => $name) {
            $this->tariffPriorityList[] = [
                'priority' => $priority,
                'id' => $id,
            ];
            $priority++;
        }
    }
}
