<?php

namespace cri2net\sms_client\Install;

use cri2net\php_pdo_db\PDO_DB;
use Placebook\Framework\Core\Install\MigrationInterface;

class Migration_1_4_6 implements MigrationInterface
{
    public static function up()
    {
        if (PDO_DB::getParams('type') !== 'mysql') {
            return;
        }
        
        $prefix = (defined('TABLE_PREFIX')) ? TABLE_PREFIX : '';

        PDO_DB::query(
            "ALTER TABLE {$prefix}sms
                CHANGE `status` `status` ENUM('new','sending','complete','cancel','fail') CHARSET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'new' NOT NULL,
                CHANGE `to` `to` VARCHAR(100) CHARSET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                CHANGE replace_data replace_data TEXT CHARSET utf8mb4 COLLATE utf8mb4_general_ci NULL,
                CHANGE raw_text raw_text TEXT CHARSET utf8mb4 COLLATE utf8mb4_general_ci NULL,
                CHANGE processing processing VARCHAR(50) CHARSET utf8mb4 COLLATE utf8mb4_general_ci NULL,
                CHANGE processing_data processing_data MEDIUMTEXT CHARSET utf8mb4 COLLATE utf8mb4_general_ci NULL,
                CHANGE processing_status processing_status VARCHAR(50) CHARSET utf8mb4 COLLATE utf8mb4_general_ci NULL,
                CHANGE additional additional MEDIUMTEXT CHARSET utf8mb4 COLLATE utf8mb4_general_ci NULL,
                CHANGE alfaname alfaname VARCHAR(50) CHARSET utf8mb4 COLLATE utf8mb4_general_ci NULL,
                CHARSET=utf8mb4, COLLATE=utf8mb4_general_ci;"
        );
    }

    public static function down()
    {
        if (PDO_DB::getParams('type') !== 'mysql') {
            return;
        }

        $prefix = (defined('TABLE_PREFIX')) ? TABLE_PREFIX : '';

        PDO_DB::query(
            "ALTER TABLE {$prefix}sms
                CHANGE `status` `status` ENUM('new','sending','complete','cancel','fail') CHARSET utf8 COLLATE utf8_general_ci DEFAULT 'new' NOT NULL,
                CHANGE `to` `to` VARCHAR(100) CHARSET utf8 COLLATE utf8_general_ci NOT NULL,
                CHANGE replace_data replace_data TEXT CHARSET utf8 COLLATE utf8_general_ci NULL,
                CHANGE raw_text raw_text TEXT CHARSET utf8 COLLATE utf8_general_ci NULL,
                CHANGE processing processing VARCHAR(50) CHARSET utf8 COLLATE utf8_general_ci NULL,
                CHANGE processing_data processing_data MEDIUMTEXT CHARSET utf8 COLLATE utf8_general_ci NULL,
                CHANGE processing_status processing_status VARCHAR(50) CHARSET utf8 COLLATE utf8_general_ci NULL,
                CHANGE additional additional MEDIUMTEXT CHARSET utf8 COLLATE utf8_general_ci NULL,
                CHANGE alfaname alfaname VARCHAR(50) CHARSET utf8 COLLATE utf8_general_ci NULL,
                CHARSET=utf8, COLLATE=utf8_general_ci;"
        );
    }
}
