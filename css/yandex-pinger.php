<?php
/*
Plugin Name: Яндекс.ПДС Пингер
Description: Плагин оповещает сервис Яндекс.Поиск для сайта о новых и измененных документах.
Version: 1.5

*/

class YandexPinger{
    private $key = '';
    private $login ='';
    private $searchId = 0;
    private $pluginId = 5;
    private $modx;

    public function __construct(&$modx, $scriptProperties = array())
    {
        $this->modx = $modx;
        $this->key = $modx->getOption('yandex_pinger_key',$scriptProperties, 0);
        $this->login = $modx->getOption('yandex_pinger_login',$scriptProperties, 0);
        $this->searchId = $modx->getOption('yandex_pinger_searchid',$scriptProperties, 0);
    }

    public function get_date($date)
    {
        $delta = 0;
        $delta = $date - time();
        return $delta;
    }

    public function ping($url, $pub_date)
    {
        $version = $this->modx->getVersionData();
        $postdata = http_build_query(array(
            'key' => urlencode($this->key),
            'login' => urlencode($this->login),
            'search_id' => urlencode($this->searchId),
            'pluginid' => urlencode($this->pluginId),
            'cmsver' => $version['full_appname']."_v1.5",
            'publishdate' => $this -> get_date($pub_date),
            'urls' => $url
        ));

        $host = 'site.yandex.ru';
        $length = strlen($postdata);

        $out = "POST /ping.xml HTTP/1.0\n";
        $out.= "HOST: ".$host."\n";
        $out.= "Content-Type: application/x-www-form-urlencoded\n";
        $out.= "Content-Length: ".$length."\n\n";
        $out.= $postdata."\n\n";


        try{
            $errno='';
            $errstr = '';
            $result = '';
            $socket = @fsockopen($host, 80, $errno, $errstr, 30);
            if($socket){
                if(!fwrite($socket, $out)){
                    throw new Exception("unable to write");
                } else {
                    while ($in = @fgets ($socket, 1024)){
                        $result.=$in;
                    }
                }
            } else {
                throw new Exception("unable to create socket");
            }
            fclose($socket);

            $result_xml = array();
            preg_match('/(<.*>)/u', $result, $result_xml);

            if (!count($result_xml)) {
                return false;
            }

            $result = array_pop($result_xml);

            $xml = simplexml_load_string($result);

            if($message = $this->getStatusMessage($xml)) {
                $Setting = $this->modx->getObject('modSystemSetting', 'yandex_pinger_message');
                $Setting->set('value', $message);
                $Setting->save();
            }
            return true;

        } catch(exception $e) {
            return false;
        }
    }

    private function getStatusMessage($extXMLResp)
    {
        if (!$extXMLResp instanceof SimpleXMLElement) {
            throw new InvalidArgumentException("Invalid response");
        }

        if ($this->isResponseValid($extXMLResp)) {
            return "Yandex. Последний принятый адрес: <a href=".$extXMLResp->added->url." target='_blank'>".$extXMLResp->added->url."</a>"; 
        }

        $errorCode = $this->getErrorCodeFromResp($extXMLResp);

        switch ($errorCode) {

            case "ILLEGAL_VALUE_TYPE":
            case "SEARCH_NOT_OWNED_BY_USER":
            case "NO_SUCH_USER_IN_PASSPORT":
                return "Один или несколько параметров в настройках плагина указаны неверно - ключ (key), "
                    ." логин (login) или ID поиска (searchid).";

            case "TOO_DELAYED_PUBLISH":
                return "Максимальный срок отложенной публикации - 6 месяцев.";

            case "USER_NOT_PERMITTED":
                $errorparam = (string)$extXMLResp->error->param;
                $errorvalue = (string)$extXMLResp->error->value;

                if ($errorparam == "key") {
                    return "Неверный ключ (key) " . $errorvalue . ". Проверьте настройки плагина.";
                } elseif ($errorparam == "ip") {
                    return "Запрос приходит с IP адреса "
                        . $errorvalue . ", который не указан в списке адресов в настройках вашего поиска";
                } else {
                    return "Запрос приходит с IP адреса, который не указан в списке адресов в настройках вашего "
                        . " поиска, либо Вы указали неправильный ключ (key) в настройках плагина.";
                }

            case "NOT_CONFIRMED_IN_WMC":
                return  "Сайт не подтвержден в сервисе Яндекс.Вебмастер для указанного имени пользователя.";

            case "OUT_OF_SEARCH_AREA":
                return "Адрес " . $extXMLResp->invalid->url . " не принадлежит области поиска вашей поисковой площадки.";

            case "MALFORMED_URLS":
                return "Невозможно принять некорректный адрес: " . $extXMLResp->invalid->url;

            default:
                return $errorCode;
        }
    }

    private function getErrorCodeFromResp($extXMLResp)
    {
        if ($this->isErrorsExistsInResp($extXMLResp)) {
            return (string)$extXMLResp->error->code;
        } elseif ($this->isSourceRequestInvalid($extXMLResp)) {
            return (string)$extXMLResp->invalid["reason"];
        }
    }

    private function isErrorsExistsInResp($extXMLResp)
    {
        return isset($extXMLResp->error)
            && isset($extXMLResp->error->code)
            && $extXMLResp->error->code;
    }

    private function isSourceRequestInvalid($extXMLResp)
    {
        return isset($extXMLResp->invalid);
    }

    private function isResponseValid($extXMLResp)
    {
        return isset($extXMLResp->added)
            && isset($extXMLResp->added['count'])
            && $extXMLResp->added['count'] > 0
            && !$this->isErrorsExistsInResp($extXMLResp)
            && !$this->isSourceRequestInvalid($extXMLResp);
    }
}


$id = $scriptProperties['id'];
$resource = $modx->getObject('modResource',$id);
$published = $resource->get('published');
$pub_date = $resource->get('pub_date');

$pinger = new YandexPinger($modx, $scriptProperties);

if($published == 1 || $pub_date > 0){
    $url = $modx->makeUrl($id, '', '', 'full');

    switch ($modx->event->name) {
        case 'OnDocPublished':
        case 'OnDocFormSave':
            $pinger->ping($url, $pub_date);
            break;
    }
}