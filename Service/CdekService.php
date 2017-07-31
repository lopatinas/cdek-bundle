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
        $calculator = new CdekCalculator($this->account, $this->password);
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
}
