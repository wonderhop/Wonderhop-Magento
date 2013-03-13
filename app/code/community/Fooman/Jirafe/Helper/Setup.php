<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @package     Fooman_Jirafe
 * @copyright   Copyright (c) 2010 Jirafe Inc (http://www.jirafe.com)
 * @copyright   Copyright (c) 2010 Fooman Limited (http://www.fooman.co.nz)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Fooman_Jirafe_Helper_Setup extends Mage_Core_Helper_Abstract
{

    public function getDbSchema ($version, $returnComplete=false)
    {
        $instructions = array();
        $currentConfigVersion = (string) Mage::getConfig()->getModuleConfig('Fooman_Jirafe')->version;
        switch ($version) {
            case $currentConfigVersion:
            case '1.1.1':
            case '1.1.0':

                if(version_compare(Mage::getVersion(),'1.4.1.0') >= 0){
                    $instructions = array_merge(
                        $instructions,
                        array(
                            array("type" =>"sql-column", "table" =>"sales/order", "name" =>"jirafe_orig_visitor_id","params" =>"varchar(255) DEFAULT NULL")
                        )
                    );
                } else {
                    $instructions = array_merge(
                        $instructions,
                        array(
                            array("type" =>"eav-attribute", "entity" =>"order", "name" =>"jirafe_orig_visitor_id","params" =>array('type' => 'varchar','label' => 'Jirafe Original Visitor Id','required'=>0,'global'=>1,'visible'=>0))
                        )
                    );
                }

                $instructions = array_merge(
                    $instructions,
                    array(
                        array("type" =>"sql-column", "table" =>"sales_flat_quote", "name" =>"jirafe_visitor_id","params" =>"varchar(255) DEFAULT NULL"),
                        array("type" =>"sql-column", "table" =>"sales_flat_quote", "name" =>"jirafe_orig_visitor_id","params" =>"varchar(255) DEFAULT NULL")
                    )
                );
                if(!$returnComplete) {
                    break;
                }
                //nobreak intentionally;
            case '1.0.0':
            case '0.6.1':
            case '0.6.0':
            case '0.5.4':
            case '0.5.3':
            case '0.5.2':
            case '0.5.1':
            case '0.5.0':

                if(version_compare(Mage::getVersion(),'1.4.1.0') >= 0){
                    $instructions = array_merge(
                        $instructions,
                        array(
                            array("type" =>"sql-column", "table" =>"sales/creditmemo", "name" =>"jirafe_export_status","params" =>"int(5) DEFAULT 0")
                        )
                    );
                } else {
                    $instructions = array_merge(
                        $instructions,
                        array(
                            array("type" =>"eav-attribute", "entity" =>"creditmemo", "name" =>"jirafe_export_status","params" =>array('type' => 'int','label' => 'Jirafe Export Status','required'=>0,'global'=>1,'visible'=>0,'default'=>0))
                        )
                    );
                }
                $instructions = array_merge(
                    $instructions,
                    array(
                        array("type" => "table", "name" => "foomanjirafe_event", "items" =>
                            array(
                                array("sql-column", "id", "int(12) unsigned NOT NULL auto_increment"),
                                array("sql-column", "version", "int(12) unsigned NOT NULL"),
                                array("sql-column", "site_id", "int(12) unsigned NOT NULL"),
                                array("sql-column", "created_at", "timestamp NOT NULL default CURRENT_TIMESTAMP"),
                                array("sql-column", "generated_by_jirafe_version", "varchar(128)"),
                                array("sql-column", "action", "varchar(128)"),
                                array("sql-column", "event_data", "text"),
                                array("key", "PRIMARY KEY", "id")
                            )
                        ),
                        array("type"=> "index", "table"=>"foomanjirafe_event", "name"=>"CREATE UNIQUE INDEX `IDX_JIRAFE_EVENT`", "on"=>
                            array('version','site_id')
                        )
                    )
                );
                if (!$returnComplete) {
                    break;
                }
                //nobreak intentionally;
            case '0.4.0':
                $instructions = array_merge(
                    $instructions,
                    array(
                        array("type" => "sql-column", "table" => "admin_user", "name" => "jirafe_optin_answered", "params" => "tinyint(1) DEFAULT 0"),
                        array("type" => "sql-column", "table" => "admin_user", "name" => "jirafe_enabled", "params" => "tinyint(1) DEFAULT 0")
                    )
                );
                if(!$returnComplete) {
                    break;
                }
                //nobreak intentionally;
            case '0.3.6':
            case '0.3.5':
            case '0.3.4':
            case '0.3.3':
            case '0.3.2':
            case '0.3.1':
            case '0.3.0':
            case '0.2.15':
            case '0.2.14':
            case '0.2.13':
            case '0.2.12':
            case '0.2.11':
            case '0.2.10':
            case '0.2.8':
            case '0.2.7':
                $instructions = array_merge(
                        $instructions,
                            array(
                                array("type" =>"sql-column-delete", "table" =>"admin_user", "name" =>"jirafe_also_send_to","params" =>"")
                                )
                        );
                if(!$returnComplete) {
                    break;
                }
                //nobreak intentionally;
            case '0.2.6':
                if(version_compare(Mage::getVersion(),'1.4.1.0') >= 0){
                    $instructions = array_merge(
                            $instructions,
                                array(
                                    array("type" =>"sql-column", "table" =>"sales/order", "name" =>"jirafe_visitor_id","params" =>"varchar(255) DEFAULT NULL"),
                                    array("type" =>"sql-column", "table" =>"sales/order", "name" =>"jirafe_attribution_data","params" =>"text DEFAULT NULL"),
                                    array("type" =>"sql-column", "table" =>"sales/order", "name" =>"jirafe_export_status","params" =>"int(5) DEFAULT 0"),
                                    array("type" =>"sql-column", "table" =>"sales/order", "name" =>"jirafe_placed_from_frontend","params" =>"tinyint(1) DEFAULT 0"),
                                    )
                            );
                } else {
                    $instructions = array_merge(
                            $instructions,
                                array(
                                    array("type" =>"eav-attribute", "entity" =>"order", "name" =>"jirafe_visitor_id","params" =>array('type' => 'varchar','label' => 'Jirafe Visitor Id','required'=>0,'global'=>1,'visible'=>0)),
                                    array("type" =>"eav-attribute", "entity" =>"order", "name" =>"jirafe_attribution_data","params" =>array('type' => 'text','label' => 'Jirafe Attribution Data','required'=>0,'global'=>1,'visible'=>0)),
                                    array("type" =>"eav-attribute", "entity" =>"order", "name" =>"jirafe_export_status","params" =>array('type' => 'int','label' => 'Jirafe Export Status','required'=>0,'global'=>1,'visible'=>0,'default'=>0)),
                                    array("type" =>"eav-attribute", "entity" =>"order", "name" =>"jirafe_placed_from_frontend","params" =>array('type' => 'int','label' => 'Placed from Frontend','required'=>0,'global'=>1,'visible'=>0,'default'=>0)),
                                    )
                            );
                }
                if(!$returnComplete) {
                    break;
                }
                //nobreak intentionally;
            case '0.2.5':
                $instructions = array_merge(
                        $instructions,
                            array(
                                array("type" =>"sql-column", "table" =>"admin_user", "name" =>"jirafe_dashboard_active","params" =>"tinyint(1) DEFAULT 1")
                                )
                        );
                if(!$returnComplete) {
                    break;
                }
                //nobreak intentionally;
            case '0.2.4':
            case '0.2.3':
            case '0.2.2':
            case '0.2.1':
            case '0.2.0':
                $instructions = array_merge(
                        $instructions,
                            array(
                                array("type" =>"sql-column", "table" =>"admin_user", "name" =>"jirafe_send_email_for_store","params" =>"varchar(255)"),
                                array("type" =>"sql-column", "table" =>"admin_user", "name" =>"jirafe_send_email","params" =>"tinyint(1) DEFAULT 0"),
                                array("type" =>"sql-column", "table" =>"admin_user", "name" =>"jirafe_email_report_type","params" =>"varchar(255) DEFAULT 'simple'"),
                                array("type" =>"sql-column", "table" =>"admin_user", "name" =>"jirafe_user_id","params" =>"varchar(255)"),
                                array("type" =>"sql-column", "table" =>"admin_user", "name" =>"jirafe_user_token","params" =>"varchar(255)"),
                                array("type" =>"sql-column", "table" =>"admin_user", "name" =>"jirafe_email_suppress","params" =>"tinyint(1) DEFAULT 1"),
                                array("type" =>"sql-column", "table" =>"admin_user", "name" =>"jirafe_also_send_to","params" =>"text")
                                )
                        );
                if(!$returnComplete) {
                    break;
                }
                //nobreak intentionally;
            case '0.1.6':
            case '0.1.4':
            case '0.1.2':
            case '0.1.1':
                $instructions = array_merge(
                        $instructions,
                            array(
                                array("type" =>"sql-column", "table" =>"foomanjirafe_report", "name" =>"store_id","params" =>"smallint(5) AFTER `report_id`"),
                                array("type" =>"sql-column", "table" =>"foomanjirafe_report", "name" =>"store_report_date","params" =>"timestamp AFTER `generated_by_jirafe_version`")
                                )
                        );
                if(!$returnComplete) {
                    break;
                }
                //nobreak intentionally;
            case '0.1.0':
                $instructions = array_merge(
                        $instructions,
                            array(
                                array("type" => "table", "name" => "foomanjirafe_report", "items" =>
                                    array(
                                        array("sql-column", "report_id", "int(10) unsigned NOT NULL auto_increment"),
                                        array("sql-column", "created_at", "timestamp NOT NULL default CURRENT_TIMESTAMP"),
                                        array("sql-column", "generated_by_jirafe_version", "varchar(128)"),
                                        array("sql-column", "report_data", "text"),
                                        array("key", "PRIMARY KEY", "report_id")
                                        )
                                    )
                                )
                        );
                if(!$returnComplete) {
                    break;
                }
                //nobreak intentionally;
            default:
                break;
        }
        return $instructions;

    }

    public function runDbSchemaUpgrade ($installer, $version)
    {
        foreach (Mage::helper('foomanjirafe/setup')->getDbSchema($version) as $instruction) {
            switch ($instruction['type']) {
                case 'table':
                    $keys = array();
                    $columns = array();

                    foreach ($instruction['items'] as $item) {
                        switch ($item[0]) {
                            case 'sql-column':
                                $columns[] = '`'.$item[1].'` '.$item[2];
                                break;
                            case 'key':
                                $keys[] = $item[1] .' (`'.$item[2].'`)';
                                break;
                        }
                    }
                    $tableDetails = implode(",",array_merge($columns,$keys));
                    $sql = "DROP TABLE IF EXISTS `{$installer->getTable($instruction['name'])}`;\n";
                    $sql .="CREATE TABLE `{$installer->getTable($instruction['name'])}` (".$tableDetails.") ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                    try {
                        $installer->run($sql);
                    } catch (Exception $e) {
                        Mage::logException($e);
                    }
                    break;
                case 'sql-column':
                    try {
                        $return = $installer->run("
                        ALTER TABLE `{$installer->getTable($instruction['table'])}` ADD COLUMN `{$instruction['name']}` {$instruction['params']}");
                    } catch (Exception $e) {
                        Mage::logException($e);
                    }
                    break;
                case 'sql-column-delete':
                    try{
                    $return = $installer->run("
                        ALTER TABLE `{$installer->getTable($instruction['table'])}` DROP COLUMN `{$instruction['name']}`");
                    } catch (Exception $e) {
                        Mage::logException($e);
                    }
                    break;
                case 'eav-attribute':
                    try {
                        $installer->addAttribute($instruction['entity'], $instruction['name'], $instruction['params']);
                    } catch (Exception $e) {
                        Mage::logException($e);
                    }
                    break;
                case 'index':
                    try {
                        $columns = implode(',',$instruction['on']);
                        $return = $installer->run("
                            {$instruction['name']} ON `{$installer->getTable($instruction['table'])}` ({$columns})
                        ");
                    } catch (Exception $e) {
                        Mage::logException($e);
                    }
                    break;
            }

        }
    }
    
    public function resetDb ()
    {
        $currentVersion = (string) Mage::getConfig()->getModuleConfig('Fooman_Jirafe')->version;
        $installer = new Fooman_Jirafe_Model_Mysql4_Setup('foomanjirafe_setup');
        $installer->startSetup();
        foreach (Mage::helper('foomanjirafe/setup')->getDbSchema($currentVersion, true) as $instruction) {
            switch ($instruction['type']) {
                case 'table':
                    $sql = "DROP TABLE IF EXISTS `{$installer->getTable($instruction['name'])}`;\n";
                    $return = $installer->run($sql);
                    break;
                case 'sql-column':
                    try {
                        $return = $installer->run("
                            ALTER TABLE `{$installer->getTable($instruction['table'])}` DROP COLUMN `{$instruction['name']}`
                        ");
                    } catch (Exception $e) {
                        Mage::logException($e);
                    }
                    break;
                case 'eav-attribute':
                    try {
                        $return = $installer->removeAttribute($instruction['entity'], $instruction['name']);
                    } catch (Exception $e) {
                        Mage::logException($e);
                    }
                    break;
            }
        }
        $return = $installer->run("
            DELETE FROM `{$installer->getTable('core_config_data')}` WHERE path LIKE '%jirafe%'
            ");
        $return = $installer->run("
            DELETE FROM `{$installer->getTable('core_resource')}` WHERE code = 'foomanjirafe_setup'
            ");
        $installer->endSetup();
    }

}
