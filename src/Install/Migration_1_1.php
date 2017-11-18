<?php

namespace cri2net\sms_client\Install;

use \Exception;
use cri2net\php_pdo_db\PDO_DB;
use \Placebook\Framework\Core\Install\MigrationInterface;

class Migration_1_1 implements MigrationInterface
{
    public static function up()
    {
        $prefix = (defined('TABLE_PREFIX')) ? TABLE_PREFIX : '';
        $pdo = PDO_DB::getPDO();

        try {
            $pdo->beginTransaction();
            
            $pdo->query(
                "CREATE TABLE IF NOT EXISTS {$prefix}sms (
                    id int(11) NOT NULL AUTO_INCREMENT,
                    `status` enum('new','sending','complete','cancel','fail') NOT NULL DEFAULT 'new',
                    `to` varchar(100) NOT NULL,
                    created_at double NOT NULL,
                    updated_at double NOT NULL,
                    send_at double DEFAULT NULL,
                    min_sending_time double NOT NULL,
                    replace_data text,
                    raw_text text,
                    processing varchar(50) DEFAULT NULL,
                    processing_data mediumtext,
                    processing_status varchar(50) DEFAULT NULL,
                    additional MEDIUMTEXT DEFAULT NULL,
                    PRIMARY KEY (id),
                    KEY `status` (`status`,min_sending_time),
                    KEY processing (processing)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
            );

            $pdo->commit();

        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function down()
    {
        $prefix = (defined('TABLE_PREFIX')) ? TABLE_PREFIX : '';
        $pdo = PDO_DB::getPDO();

        try {

            $pdo->beginTransaction();
            $pdo->query("DROP TABLE IF EXISTS {$prefix}sms");
            $pdo->commit();

        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
