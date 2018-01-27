<?php

namespace cri2net\sms_client;

use cri2net\php_pdo_db\PDO_DB;

class Sender
{
    protected $instances = [];

    private $is_sorted = true;

    public function addInstance(SMSInterface $instance)
    {
        $this->instances[] = $instance;
        $this->is_sorted = false;
    }

    public function getInstances()
    {
        return $this->instances;
    }

    public function sort($type = '')
    {
        $items = [];
        foreach ($this->getInstances() as $item) {
            $items[] = [
                'instance' => $item,
                'balance'  => $item->getBalance(),
            ];
        }

        switch ($type) {
            case 'balance':
            default:
                usort($items, function($a, $b) {
                    return $b['balance'] - $a['balance'];
                });
                break;
        }

        $this->instance = [];
        foreach ($items as $item) {
            $this->instance[] = $item['instance'];
        }
    }

    /**
     * Выбор наилучшего шлюза для отправки
     * @param  string $recipient Номер получателя в международном формате
     * @return SMSInterface      Самый дешёвый шлюз
     */
    public function getBestInstance($recipient)
    {
        if (!$this->is_sorted) {
            $this->sort();
        }

        foreach ($this->instances as $item) {

            if (preg_match('/\+380/', $recipient)) {

                // для Украины самый дешёвый sms-fly.com
                if (in_array($item->getProcessingKey(), ['sms_fly'])) {
                    return $item;
                }
            } elseif (preg_match('/\+35987/', $recipient)) {

                // для Болгарии (Vivacom) самый дешёвый letsads.com
                if (in_array($item->getProcessingKey(), ['letsads'])) {
                    return $item;
                }
            } elseif (preg_match('/\+359/', $recipient)) {

                // для Болгарии (остальные) самый дешёвый bsg.world
                if (in_array($item->getProcessingKey(), ['bgs_world'])) {
                    return $item;
                }
            }
        }

        // если попали сюда, значит нет подходящих под страницу шлюзов. Тогда отдаю тот, где больше денег
        foreach ($this->instances as $item) {
            return $item;
        }
    }
}
