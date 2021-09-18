<?php


class Helper_Table
{

    /**
     * 封装表单创建
     * 自动载入 page_bar
     * 返回 list数组，用于个性化数据装载
     * @param Model_BaseInterface $model
     * @param $limit
     * @param $request
     * @param $method
     * @return int|mixed
     */
    public static function factory(Model_BaseInterface $model, $limit, $request, $method = '')
    {
        $page = intval($_GET['page']);
        unset($request['page']);
        if ($method) {
            $table = $model->$method($page, $limit, $request);
        } else {
            $table = $model->getTableList($page, $limit, $request);
        }
        $count = var_def($table['total'], 0);
        $list = var_def($table['list'], []);
        $curPage = get_cur_page($page, $count, $limit);
        $url = "?" . $_SERVER['QUERY_STRING'];
        $url = preg_replace("/&page=\d+$/is", "", $url) . "&page={page}";

        $page = new pagination($count, $limit, $curPage, $url, 2);
        $GLOBALS['smarty']->assign('page_bar', $page->myde_write());

        return $list;
    }


}
