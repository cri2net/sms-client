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
            
            switch (PDO_DB::getParams('type')) {
                case 'pgsql':

                    PDO_DB::query("CREATE TYPE sms_status_enum AS ENUM ('new', 'sending', 'complete', 'cancel', 'fail');");

                    PDO_DB::query("CREATE SEQUENCE {$prefix}sms_seq;");
                    PDO_DB::query(
                        "CREATE TABLE {$prefix}sms (
                            id integer NOT NULL,
                            status sms_status_enum NOT NULL,
                            \"to\" character varying(100) NOT NULL,
                            created_at double precision NOT NULL,
                            updated_at double precision NOT NULL,
                            send_at double precision,
                            min_sending_time double precision NOT NULL,
                            replace_data text,
                            raw_text text,
                            processing character varying(50),
                            processing_data text,
                            processing_status character varying(50),
                            additional text,
                            PRIMARY KEY (id)
                        )
                        WITH (
                            OIDS = FALSE
                        );"
                    );
                    PDO_DB::query("ALTER SEQUENCE {$prefix}sms_seq RESTART WITH 1;");
                    PDO_DB::query("CREATE INDEX status ON {$prefix}sms (status, min_sending_time);");
                    PDO_DB::query("CREATE INDEX processing ON {$prefix}sms (processing);");
                    break;
                
                case 'mysql':
                default:
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
            }

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
            
            switch (PDO_DB::getParams('type')) {
                case 'pgsql':

                    PDO_DB::query("DROP TABLE IF EXISTS {$prefix}sms;");
                    PDO_DB::query("DROP TYPE IF EXISTS sms_status_enum;");
                    PDO_DB::query("DROP SEQUENCE IF EXISTS {$prefix}sms_seq;");
                    break;
                    
                case 'mysql':
                default:
                    PDO_DB::query("DROP TABLE IF EXISTS {$prefix}sms");
            }

            $pdo->commit();

        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
