<?php
namespace Stathmos\ExtendedOrdersGrid\Plugin;

use Zend_Db_Select;

class Quotegrid
{

    public static $table = 'quote';
    public static $leftJoinTable = 'catalog_product_entity_varchar';

    public function afterSearch($intercepter, $collection)
    {

        if ($collection->getMainTable() === $collection->getConnection()->getTableName(self::$table)) {
            $where = $collection->getSelect()->getPart(Zend_Db_Select::WHERE);
            $whereConditionData = array();
            if(!empty($where)){
                foreach ($where as $key => $whereCondition) {
                    $flag = 0;
                    if (strpos($whereCondition, '`entity_id`') !== false) {
                        $whereConditionData[] = str_replace('`entity_id`',"`main_table`.`entity_id`", $whereCondition);
                        $flag = 1;
                    }
                    if(strpos($whereCondition, '`ndc`') !== false){
                        $whereConditionData[] = str_replace('`ndc`',"`cpev`.`value`", $whereCondition);
                        $flag = 1;
                    }
                    if($flag == 0){
                        $whereConditionData[] = $whereCondition;
                    }
                }
            }
            if(empty($whereConditionData)){
                $whereConditionData = $where;
            }
            $collection->getSelect()->setPart(Zend_Db_Select::WHERE, $whereConditionData);
        }
        return $collection;
    }
}
