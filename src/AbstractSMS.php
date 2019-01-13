<?php
namespace cri2net\sms_client;

use cri2net\php_pdo_db\PDO_DB;

abstract class AbstractSMS implements SMSInterface
{
    /**
     * Name of DB table for this library
     * @since 1.0.0
     * @see   install.sql
     * @var   string
     */
    public $table;

    /**
     * Имя пользователя для доступа к API
     * @var string
     * @since 1.0.0
     */
    public $login;

    /**
     * Пароль для доступа к API
     * @var string
     * @since 1.0.0
     */
    public $password;

    /**
     * Альфаимя отправителя sms
     * @var string
     * @since 1.0.0
     */
    public $alfaname = 'SMS';

    /**
     * Возвращает ссылку для обращений к API
     * @return string ссылка для работы с API
     */
    abstract public function getApiUrl();

    /**
     * Возвращает уникальный ключ шлюза.
     * Для привязки смс к шлюзу в случае работы с БД
     * @return string
     */
    abstract public function getProcessingKey();

    /**
     * метод для проверки остатка на балансе в аккаунте на sms шлюзе
     * @return double
     */
    abstract public function getBalance();

    /**
     * В силу того, что некоторые SMS шлюзы могут не работать с международным форматом номера телефона,
     * необходим этот метод, который вернёт номер в приемлимом для шлюза формате
     * 
     * @param  string $international_phone Номер телефона в международном формате
     * @return string                      Преобразованный номер
     */
    abstract public function processPhone($international_phone);

    /**
     * Отправка sms
     * @param  string $recipient Номер получателя в международном формате
     * @param  string $text      Текс сообщения
     * @return array             Детали об отправке
     */
    abstract public function checkStatus($campaignID, $recipient);

    /**
     * Метод проверяет статусы всех сообщений, которые находятся в незавершённом состоянии.
     * Предназначен для вызова из крона
     * Класс для каждого шлюза должен обрабатывать только свои сообщения
     * @return void
     */
    abstract public function checkStatusByCron();

    /**
     * Отправка sms
     * @param  string $recipient Номер получателя в международном формате
     * @param  string $text      Текс сообщения
     * @return array             Детали об отправке
     */
    abstract public function sendSMS($recipient, $text);

    /**
     * Получает из БД и отдаёт массив сообщений, которые необходимо отправить
     * @return array
     */
    public function getMessagesToSend()
    {
        if ($this->table == '') {
            return [];
        }

        $stm = PDO_DB::prepare("SELECT * FROM {$this->table} WHERE status='new' AND min_sending_time<=? AND (processing=? OR processing IS NULL)");
        $stm->execute([microtime(true), $this->getProcessingKey()]);

        return $stm->fetchAll();
    }

    /**
     * Поиск и замена "макросов" в тексте сообщения
     * В шаблоне макросы нужно обрамлять двумя парами фигурных скобок: {{replace_me}}
     * 
     * Пример:
     *     $template_text = "Добрый день, {{username}}!";
     *     $data = ['username' => 'Марк'];
     *     Результатом будет строка "Добрый день, Марк!"
     * 
     * @param  string $template_text исходный текст (шаблон)
     * @param  array  $data          массив с заменами. OPTIONAL
     * @return string                преобразованный текст
     */
    public static function fetch($template_text, $data = [])
    {
        $re1 = '.*?'; // Non-greedy match on filler
        $re2 = '(\\{{([0-9a-z_-]+)\\}})'; // Curly Braces 1

        if (preg_match_all("/".$re1.$re2."/is", $template_text, $matches)) {
            for ($i=0; $i < count($matches[1]); $i++) {
                $replace = (isset($data[strtolower($matches[2][$i])])) ? $data[strtolower($matches[2][$i])] : '';
                $template_text = str_ireplace($matches[1][$i], $replace, $template_text);
            }
        }

        return $template_text;
    }

    /**
     * Отправка всех сообщений, которые в БД лежат и готовы к отправке
     * @return void
     */
    public function sendSmsByCron()
    {
        if (empty($this->table)) {
            throw new \Exception("Поле table не задано");
        }

        $list = $this->getMessagesToSend();

        foreach ($list as $item) {

            $update = [
                'status'     => 'sending',
                'updated_at' => microtime(true),
            ];
            PDO_DB::update($update, $this->table, $item['id']);
    
            $replace          = (array)(@json_decode($item['replace_data']));
            $processing_data  = (array)(@json_decode($item['processing_data']));
            $item['raw_text'] = self::fetch($item['raw_text'], $replace);

            $update = [
                'status'     => 'complete',
                'updated_at' => microtime(true),
            ];

            try {
                
                $originalAlfaname = $this->alfaname;
                if (!empty($item['alfaname'])) {
                    $this->alfaname = $item['alfaname'];
                }

                $info = $this->sendSMS($item['to'], $item['raw_text']);
                $this->alfaname = $originalAlfaname;

                if (!isset($processing_data['first'])) {
                    $processing_data['first'] = $info;
                } else {
                    $processing_data['first']->campaignID = $info['campaignID'];
                }

                $update['send_at']           = microtime(true);
                $update['processing']        = $this->getProcessingKey();
                $update['processing_data']   = json_encode($processing_data, JSON_UNESCAPED_UNICODE);
                $update['processing_status'] = $info['status'];
                
            } catch (\Exception $e) {
                $update['status'] = 'fail';
            }

            PDO_DB::update($update, $this->table, $item['id']);
        }
    }
}
