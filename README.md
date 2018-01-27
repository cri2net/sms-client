# README

Эта библиотека предназначена как основа для других библиотек, которые отправляют SMS через сторонний SMS шлюз.

На  текущий момент поддерживаются следующие SMS шлюзы:
- sms-fly.com через пакет [cri2net/sms-fly](https://packagist.org/packages/cri2net/sms-fly)

# Установка
## Установка библиотеки
В базовом случае библиотека установится сама при установке библиотеки для конкретного шлюза. Но всегда можно выполнить установку через команду:
```
composer require cri2net/sms-client
```
## Таблица в БД
Для лучшей интеграции будет удобно создать таблицу в базе данных. Однако это не обязательно для работы.

Не забывайте просматривать файл install.sql при обновлениях версии.

Также, поддерживаются автоматические SQL миграции на основе пакета [placebook/framework-selfupdate](https://packagist.org/packages/placebook/framework-selfupdate)

Текущее содержимое:
``` sql
-- version 1.0.0
CREATE TABLE IF NOT EXISTS `sms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` enum('new','sending','complete','cancel','fail') NOT NULL DEFAULT 'new',
  `to` varchar(100) NOT NULL,
  `created_at` double NOT NULL,
  `updated_at` double NOT NULL,
  `send_at` double DEFAULT NULL,
  `min_sending_time` double NOT NULL,
  `replace_data` text,
  `raw_text` text,
  `processing` varchar(50) DEFAULT NULL,
  `processing_data` mediumtext,
  `processing_status` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `status` (`status`,`min_sending_time`),
  KEY `processing` (`processing`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- version 1.0.1
ALTER TABLE `sms`
  ADD COLUMN `additional` MEDIUMTEXT NULL AFTER `processing_status`;

-- version 1.1.1
ALTER TABLE `sms`
  ADD COLUMN alfaname VARCHAR(50) NULL AFTER additional;
```

### Описание полей:
- **id**: Идентификатор записи
- **status**: Статус отправки
- **to**: Номер получателя в международном формате
- **created_at**: unix время создания записи
- **updated_at**: unix время последнего изменения записи
- **send_at**: unix время фактической отправки sms
- **min_sending_time**: unix время минимального момента времени, когда можно отправлять смс - для отложенной отправки
- **replace_data**: JSON строка с правилами замен
- **raw_text**: текст сообщения, есть поддержка "макросов"
- **processing**: ключ (название) sms шлюза, который отправил (или должен отправить) sms
- **processing_data**: JSON с данными для шлюза
- **processing_status**: статус отправки, полученный от шлюза
- **additional**: предназначено для дополнительной информации в JSON. Библиотека не использует это поле и логика его работы целиком зависит от пользователей библиотеки

# Использование
## Описание методов
Библиотека имеет лишь интерфейс и абстрактный класс, так что нельзя создать экземпляр предоставляемого класса напрямую и использовать его.

Тут приведены лишь базовые методы, в том числе абстрактные, которые будут доступны в классе конкретного sms шлюза. Если метод будет переопределён в дочернем классе, об этом будет написано в описании к дочернему классу

- **getBalance()** — получение текущего остатка денег в аккаунте на шлюзе
- **checkStatus($campaignID, $recipient)** — проверка текущего статуса отправленного сообщения по ID кампании отправки (от шлюза) и номеру телефона получателя в международном формате
- **sendSMS($recipient, $text)** — отправка sms, параметры: номер в международном формате и текст
- **processPhone($international_phone)** — В силу того, что некоторые SMS шлюзы могут не работать с международным форматом номера телефона, этот метод адаптирует номер в международном формате к тому виду, в котором его может использовать шлюз
- **checkStatusByCron()** — метод предназначен для проверки статусов отправленных сообщений по расписанию (через crontab)
- **sendSmsByCron()** — отправка подготовленных sms, которые сохранены в БД


### Выбор оптимального шлюза из всех доступных
```php

use cri2net\sms_client\Sender;
use cri2net\sms_fly\SMS_fly;

$sender = new Sender();

// один доступный аккаунт
$sms1 = new SMS_fly('380000000001', 'pass1');
$sms1->alfaname = 'Alfaname';
$sender->addInstance($sms1);

// ещё один аккаунт
$sms2 = new SMS_fly('380000000002', 'pass2');
$sms1->alfaname = 'Alfaname';
$sender->addInstance($sms2);

$to = '380940000001';
$best = $sender->getBestInstance($to);
$data = $best->sendSMS($to, 'Hello!');

```