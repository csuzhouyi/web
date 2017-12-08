<?php
/**
 * wechat php test
 */

//define your token
define("TOKEN", "weixin");
$wechatObj = new wechatCallbackapiTest();
//$echoStr = $_GET["echostr"];
if(  isset($_GET["echostr"]) ){
    $wechatObj->valid();
}

else{
    $wechatObj->responseMsg();
}

class wechatCallbackapiTest
{
    public function valid()
    {
        $echoStr = $_GET["echostr"];

        //valid signature , option
        if($this->checkSignature()){
            echo $echoStr;
            exit;
        }
    }

    private function checkSignature()
    {
        // you must define TOKEN by yourself
        if (!defined("TOKEN")) {
            throw new Exception('TOKEN is not defined!');
        }

        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];

        $token = TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        // use SORT_STRING rule
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }

    public function responseMsg()
    {
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        if (!empty($postStr)){
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $RX_TYPE = trim($postObj->MsgType);

            switch ($RX_TYPE)
            {
                case "text":
                    $resultStr = $this->receiveText($postObj);
                    break;
                case "event":
                    $resultStr = $this->receiveEvent($postObj);
                    break;
                default:
                    $resultStr = "";
                    break;
            }
            echo $resultStr;
        }else {
            echo "";
            exit;
        }
    }

    private function receiveText($object)
    {
        $funcFlag = 0;
        $str = $object->Content;
        $bjm="";
        $openid = $object->FromUserName;


        $contentStr = "hello!~\n" . $this->getWeb();


        $resultStr = $this->transmitText($object, $contentStr, $funcFlag);


        return $resultStr;
    }










    private function receiveEvent($object){
        $content="";
        switch($object->Event){
            case "subscribe":
                $content ="欢迎关注zyssr" ;
            case "CLICK":
                if($object->EventKey == "jrbj"){
                    $content = "加入班级请输入 bd+班级名
创建一个班级请输入 cj+班级名";

                }
                elseif($object->EventKey == "qfxx"){
                    $content = "班级消息推送
如需要推送班级消息，请发送ts+消息，即可完成班级消息推送
Tips:使用本功能前，请先注册并加入或者创建班级";
                }
                break;
        }

        $result = $this->transmitText($object,$content);
        return $result;
    }



    private function transmitText($object, $content, $funcFlag = 0)
    {
        $textTpl = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[text]]></MsgType>
<Content><![CDATA[%s]]></Content>
<FuncFlag>%d</FuncFlag>
</xml>";
        $resultStr = sprintf($textTpl, $object->FromUserName, $object->ToUserName, time(), $content, $funcFlag);
        return $resultStr;
    }

    private function transmitNews($object, $arr_item, $funcFlag = 0)
    {
        //首条标题28字，其他标题39字
        if(!is_array($arr_item))
            return;

        $itemTpl = "    <item>
        <Title><![CDATA[%s]]></Title>
        <Description><![CDATA[%s]]></Description>
        <PicUrl><![CDATA[%s]]></PicUrl>
        <Url><![CDATA[%s]]></Url>
    </item>
";
        $item_str = "";
        foreach ($arr_item as $item)
            $item_str .= sprintf($itemTpl, $item['Title'], $item['Description'], $item['PicUrl'], $item['Url']);

        $newsTpl = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[news]]></MsgType>
<Content><![CDATA[]]></Content>
<ArticleCount>%s</ArticleCount>
<Articles>
$item_str</Articles>
<FuncFlag>%s</FuncFlag>
</xml>";

        $resultStr = sprintf($newsTpl, $object->FromUserName, $object->ToUserName, time(), count($arr_item), $funcFlag);
        return $resultStr;
    }



    private function getAccess_token(){
        $appid = "wx7c65473e9fce194b";
        $appsecret = "9f6b523a693d714c0f52c0b7066ca4cc";

        $access_token = "";
        $url="https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appid&secret=$appsecret";

        $access_token_json = $this->https_request($url);

        $access_token_array = json_decode($access_token_json,true);
        $access_token = $access_token_array['access_token'];
        return $access_token;
    }





    function https_request($url){
        $curl = curl_init();
        curl_setopt($curl,CURLOPT_URL,$url);
        curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,FALSE);
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
        $data = curl_exec($curl);
        if(curl_errno($curl)) {
            return 'ERROR'.curl_error($curl);
        }
        curl_close($curl);
        return $data;

    }


    function https_request2($url,$data){
        $curl = curl_init();
        curl_setopt($curl,CURLOPT_URL,$url);
        curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,FALSE);
        curl_setopt($curl,CURLOPT_POST,1);
        curl_setopt($curl,CURLOPT_POSTFIELDS,$data);
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
        $result = curl_exec($curl);
        if(curl_errno($curl)) {
            return 'ERROR'.curl_error($curl);
        }
        curl_close($curl);
        return $result;


    }

    function getWeb(){
        $con = mysqli_connect("localhost","root","789789789");
        if (!$con)
        {
            die('Could not connect: ' . mysqli_error());
        }
        mysqli_select_db($con, 'som');
        //解决中文乱码
        mysqli_query($con, "set character set 'utf8'");


        $result = mysqli_query($con , "SELECT * FROM baiduyungx");

        $contentStr = "";
        while($row = mysqli_fetch_array($result))
        {
            $contentStr += $row['title'] . ": " . $row['web']."\n";
        }



        //关闭数据库连接
        mysqli_close($con);
        return $contentStr;
    }



}

?>