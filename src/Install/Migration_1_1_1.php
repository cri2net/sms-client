<?php

namespace cri2net\sms_client\Install;

use \Exception;
use cri2net\php_pdo_db\PDO_DB;
use \Placebook\Framework\Core\Install\MigrationInterface;

class Migration_1_1_1 implements MigrationInterface
{
    public static function up()
    {
        $prefix = (defined('TABLE_PREFIX')) ? TABLE_PREFIX : '';

        try {
            switch (PDO_DB::getParams('type')) {
                case 'pgsql':
                    PDO_DB::query("ALTER TABLE {$prefix}sms ADD COLUMN alfaname character varying(50)");
                    break;

                case 'mysql':
                default:
                    PDO_DB::query("ALTER TABLE {$prefix}sms ADD COLUMN alfaname VARCHAR(50) NULL AFTER additional");
            }

        } catch (Exception $e) {
            throw $e;
        }
    }

    public static function down()
    {
        $prefix = (defined('TABLE_PREFIX')) ? TABLE_PREFIX : '';

        try {
            PDO_DB::query("ALTER TABLE {$prefix}sms DROP COLUMN alfaname");

        } catch (Exception $e) {
            throw $e;
        }
    }
}
