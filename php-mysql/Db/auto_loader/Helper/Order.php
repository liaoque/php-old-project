<?php


class Helper_Order
{
    /**
     * 外部订单号
     *
     * @param unknown_type $pay_id
     * @param unknown_type $order_sn
     * @return unknown
     */
    public static function getOrderOutsideSn($pay_id, $order_sn, $order_id)
    {
        //微信支付
        $array = array("out_trade_no" => '', "transid" => '', "pay_name" => '');
        if ($pay_id == 26) {
            $pay_info = Model_WeixinPayLog::getInstance()->getRow([
                'order_sn' => $order_sn
            ]);
            $array = array("out_trade_no" => $pay_info['out_trade_no'], "transid" => $pay_info['transid'], "pay_name" => "微信支付");
        } elseif ($pay_id == 28) {
            $pay_info = Model_CmbfqpaylogDirect::getInstance()->getRow([
                'orderid' => $order_id
            ]);
            $array = array("out_trade_no" => $pay_info['MchNtfPam'], "transid" => $pay_info['CrdBllNbr'], "pay_name" => "cmbchina");
        } elseif ($pay_id == 3) {
            $pay_info = Model_ServiceAlipay::getInstance()->getRow([
                'order_sn' => $order_sn
            ]);
            $array = array("out_trade_no" => $pay_info['out_trade_no'], "transid" => $pay_info['trade_no'], "pay_name" => "支付宝");
        }
        return $array;
    }




}
