<?php


class Model_Activity extends Model_Base
{
    public $tableName = 'activity';
    public $pkId = 'id';


    public function listActivity(){
        $res = self::getAll();
        if($res){
            $res = array_column($res, null, 'id');
        }
        return $res;
    }

    public function getTableList($page = 0, $limit = 20, $where = [], $order = '')
    {
//        定义查询变量
        $searchWhere = $where;
        if (!empty($searchWhere['where'])) {
            $searchWhere = $searchWhere['where'];
        }
        $activityId = var_def($searchWhere['activity_id'], '0');
        $searchType = var_def($searchWhere['search_type'], '');
        $keyword = var_def($searchWhere['keyword'], '');
        $timeStart = var_def($searchWhere['date_start'], '0');
        $timeeEnd = var_def($searchWhere['date_end'], '0');

//        最后汇总
        $where = [];
        if ($activityId) {
            $where['activity_id'] = $activityId;
        }

        if($timeStart){
            $where['created >='] = strtotime($timeStart);
        }

        if($timeeEnd){
            $where['created <='] = strtotime($timeeEnd);
        }
        if ($keyword) {
            switch ($searchType) {
                case 1:
                default:
                    $all = self::getInstance()->getAll([
                        "title like '{$keyword}%'"
                    ], 'id');
                    if ($all) {
                        $all = array_column($all, 'id');
                    } else {
                        $all = -1;
                    }
                    $where['id'] = $all;
                    break;
            }
        }


//        最后汇总
        $searchWhere = [
            'where' => $where
        ];

        $page = $page - 1 < 0 ? 0 : $page - 1;
        $searchWhere['limit'] = [$page * $limit, $limit];
        $searchWhere['order'] = $order;

        $total = $this->count($searchWhere['where']);
        $info = $this->getAll($searchWhere);
        return [
            'total' => $total,
            'list' => empty($info) ? [] : $info
        ];
    }




}
