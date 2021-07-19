<?php

namespace cri2net\sms_client;

interface SMSInterface
{
    /**
     * метод для проверки остатка на балансе в аккаунте на sms шлюзе
     * @return double
     */
    public function getBalance();
    
    /**
     * Проверка статуса отправки sms
     * @param  mixed  $campaignID ID отправки на sms шлюзе
     * @param  string $recipient  номер телефона получателя
     * @return mixed
     */
    public function checkStatus($campaignID, $recipient);
    
    /**
     * Отправка sms
     * @param  string $recipient Номер получателя в международном формате
     * @param  string $text      Текс сообщения
     * @return array             Детали об отправке
     */
    public function sendSMS($recipient, $text);

    /**
     * В силу того, что некоторые SMS шлюзы могут не работать с международным форматом номера телефона,
     * необходим этот метод, который вернёт номер в приемлимом для шлюза формате
     * 
     * @param  string $international_phone Номер телефона в международном формате
     * @return string                      Преобразованный номер
     */
    public function processPhone($international_phone);

    /**
     * Метод проверяет статусы всех сообщений, которые находятся в незавершённом состоянии.
     * Предназначен для вызова из крона
     * Класс для каждого шлюза должен обрабатывать только свои сообщения
     * @return void
     */
    public function checkStatusByCron();
}
