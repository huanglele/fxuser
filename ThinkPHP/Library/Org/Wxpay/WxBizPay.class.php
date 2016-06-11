<?php
/**
 * Author: huanglele
 * Date: 2016/6/11
 * Time: ���� 11:55
 * Description:
 */

namespace Org\Wxpay;
include_once 'WxPay.Exception.php';
include_once 'WxPay.Data.php';

class WxBizPay
{
    protected $values = array();

    public function __construct(){
        $this->values['nonce_str'] = $this->getNonceStr(32);
    }

    /**
     * ������ҵ����
     * @param $data ��Ҫ�Ĳ�������
     * @return array ִ�еĽ����
     * @throws WxPayException
     */
    public function send($data){
        $this->values['mch_appid'] = C('Wx.AppID');     //�����˺�ID
        $this->values['mchid'] = C('Wx.mch_id');     //΢��֧��������̻���
        $this->values['spbill_create_ip'] = $_SERVER['REMOTE_ADDR'];     //���ýӿڵĻ���Ip��ַ
        $this->values['check_name'] = 'NO_CHECK';     //NO_CHECK����У����ʵ���� FORCE_CHECK��ǿУ����ʵ����
        if(is_array($data)){
            foreach($data as $k=>$v){
                $this->values[$k] = $v;
            }
        }
        $this->SetSign();
        $xml = $this->ToXml();
        $url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers';
        $response = $this->postXmlCurl($xml,$url,true);

        $result = WxBizPayResults::Init($response);
        return $result;
    }

    /**
     * ��ѯһ����ҵ����״̬
     * @param $mch_billno �̻����ź�����̻�������
     * @return array ִ�н����
     * @throws WxPayException
     */
    public function query($partner_trade_no){
        $this->values['partner_trade_no'] = $partner_trade_no;
        $this->values['mch_id'] = C('Wx.mch_id');
        $this->values['appid'] = C('Wx.AppID');
        $this->SetSign();
        $xml = $this->ToXml();
        $url = '	https://api.mch.weixin.qq.com/mmpaymkttransfers/gettransferinfo';
        $response = $this->postXmlCurl($xml,$url,true);

        $result = WxBizPayResults::Init($response);
        return $result;
    }


    /**
     * ����΢��֧��������̻���
     * @param string $value
     **/
    public function SetMch_id($value)
    {
        $this->values['mch_id'] = $value;
    }

    /**
     * ����΢�ŷ���Ĺ����˺�ID
     * @param string $value
     **/
    public function SetAppid($value)
    {
        $this->values['wxappid'] = $value;
    }

    /**
     * ���ý��ܺ�����û��û���wxappid�µ�openid
     */
    public function SetOpenid($value){
        $this->values['openid'] = $value;
    }

    /**
     * ���ö����ܽ�ֻ��Ϊ���������֧�����
     * @param string $value
     **/
    public function SetAmount($value)
    {
        $this->values['amount'] = $value;
    }

    /**
     * �����̻�������
     */
    public function SetTrade_no($value){
        $this->values['partner_trade_no'] = $value;
    }

    /**
     *��ҵ����������Ϣ
     */
    public function SetDesc($value){
        $this->values['desc'] = $value;
    }


    /**
     * ����ǩ�������ǩ�������㷨
     * @param string $value
     **/
    public function SetSign()
    {
        $sign = $this->MakeSign();
        $this->values['sign'] = $sign;
        return $sign;
    }


    /**
     * ����ǩ��
     * @return ǩ����������������sign��Ա��������Ҫ����ǩ����Ҫ����SetSign������ֵ
     */
    public function MakeSign()
    {
        //ǩ������һ�����ֵ����������
        ksort($this->values);
        $string = $this->ToUrlParams();
        //ǩ�����������string�����KEY
        $string = $string . "&key=".C('Wx.key');
        //ǩ����������MD5����
        $string = md5($string);
        //ǩ�������ģ������ַ�תΪ��д
        $result = strtoupper($string);
        return $result;
    }

    /**
     * ��ȡǩ�������ǩ�������㷨��ֵ
     * @return ֵ
     **/
    public function GetSign()
    {
        return $this->values['sign'];
    }

    /**
     * �ж�ǩ�������ǩ�������㷨�Ƿ����
     * @return true �� false
     **/
    public function IsSignSet()
    {
        return array_key_exists('sign', $this->values);
    }

    /**
     * ���xml�ַ�
     * @throws WxPayException
     **/
    public function ToXml()
    {
        if(!is_array($this->values)
            || count($this->values) <= 0)
        {
            throw new WxPayException("���������쳣��");
        }

        $xml = "<xml>";
        foreach ($this->values as $key=>$val)
        {
            if (is_numeric($val)){
                $xml.="<".$key.">".$val."</".$key.">";
            }else{
                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
            }
        }
        $xml.="</xml>";
        return $xml;
    }

    /**
     * ��xmlתΪarray
     * @param string $xml
     * @throws WxPayException
     */
    public function FromXml($xml)
    {
        if(!$xml){
            throw new WxPayException("xml�����쳣��");
        }
        //��XMLתΪarray
        //��ֹ�����ⲿxmlʵ��
        libxml_disable_entity_loader(true);
        $this->values = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $this->values;
    }

    /**
     * ��ʽ��������ʽ����url����
     */
    public function ToUrlParams()
    {
        $buff = "";
        foreach ($this->values as $k => $v)
        {
            if($k != "sign" && $v != "" && !is_array($v)){
                $buff .= $k . "=" . $v . "&";
            }
        }

        $buff = trim($buff, "&");
        return $buff;
    }

    /**
     *
     * ��������ַ�����������32λ
     * @param int $length
     * @return ����������ַ���
     */
    public static function getNonceStr($length = 32)
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str ="";
        for ( $i = 0; $i < $length; $i++ )  {
            $str .= substr($chars, mt_rand(0, strlen($chars)-1), 1);
        }
        return $str;
    }

    /**
     * ��post��ʽ�ύxml����Ӧ�Ľӿ�url
     *
     * @param string $xml  ��Ҫpost��xml����
     * @param string $url  url
     * @param bool $useCert �Ƿ���Ҫ֤�飬Ĭ�ϲ���Ҫ
     * @param int $second   urlִ�г�ʱʱ�䣬Ĭ��30s
     * @throws WxPayException
     */
    private function postXmlCurl($xml, $url, $useCert = false, $second = 30)
    {
        $ch = curl_init();
        //���ó�ʱ
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);

        //��������ô�����������ô���
        if(C('Wx.CURL_PROXY_HOST') != "0.0.0.0"
            && C('Wx.CURL_PROXY_PORT') != 0){
            curl_setopt($ch,CURLOPT_PROXY, C('Wx.CURL_PROXY_HOST'));
            curl_setopt($ch,CURLOPT_PROXYPORT, C('Wx.CURL_PROXY_PORT'));
        }
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);//�ϸ�У��
        //����header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //Ҫ����Ϊ�ַ������������Ļ��
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

        if($useCert == true){
            //����֤��
            //ʹ��֤�飺cert �� key �ֱ���������.pem�ļ�
            curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
            curl_setopt($ch,CURLOPT_SSLCERT, C('Wx.SSLCERT_PATH'));
            curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
            curl_setopt($ch,CURLOPT_SSLKEY, C('Wx.SSLKEY_PATH'));
        }
        //post�ύ��ʽ
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        //����curl
        $data = curl_exec($ch);
        //���ؽ��
        if($data){
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            curl_close($ch);
            throw new WxPayException("curl����������:$error");
        }
    }
}