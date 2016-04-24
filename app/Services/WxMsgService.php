<?php

namespace Entrepreneur\Services;

use Entrepreneur\Repositories\RequirementRepository;
use Entrepreneur\Services\Wx\BizMsgCrypt;
use GuzzleHttp\ClientInterface;

class WxMsgService
{
    private $bizMsgCrypt;

    /**
     * Http client
     *
     * @var ClientInterface
     */
    private $httpClient;

    /**
     * @param ClientInterface $httpClient
     * @param array           $option      keys taken:
     *                                      - app_id     (string)required
     *                                      - app_secret (string)required
     *                                      - aes_key    (string)required
     */
    public function __construct(ClientInterface $httpClient, array $option)
    {
        $this->httpClient  = $httpClient;
        $this->bizMsgCrypt = new BizMsgCrypt(array_get($option, 'server_token'),
                                             array_get($option, 'aes_key'),
                                             array_get($option, 'app_id'));
    }

    public function parseRequest($xml)
    {
        return $decrypted = $this->bizMsgCrypt->decryptMsg($xml);
    }

    public function encryptResponse($response)
    {
        return $this->bizMsgCrypt->encryptMsg($response);
    }

    public function onLatestRequirementsClicked($req)
    {
        $requirementService = app(\Entrepreneur\ApplicationServices\RequirementServices::class);
        list($_, $requirements) = $requirementService->getApiList(RequirementRepository::APPROVE_STATUS, 1, 4);

        $dom = new \DOMDocument();
        $root = $dom->createElement('xml');
        $dom->appendChild($root);

        $this->createCDATAElement($root, 'ToUserName', $req->FromUserName);
        $this->createCDATAElement($root, 'FromUserName', $req->ToUserName);
        $this->createCDATAElement($root, 'CreateTime', time());
        $this->createCDATAElement($root, 'MsgType', 'text');
        if (empty($requirements)) {
            $this->createCDATAElement($root, 'Content', '暂无企业需求');
        } else {
            $text = '';
            foreach ($requirements as $index => $requirement) {
                $text .= ($index + 1) . '. ' . $requirement['title'] . "\n" .
                         $requirement['begin_time'] . ' ~ ' . $requirement['end_time'] . "\n" .
                         $requirement['intro'] .  "\n----------------------------\n";
            }
            $this->createCDATAElement($root, 'Content', trim($text));
        }

        return $dom->saveXML($root);
    }

    private function createCDATAElement($parent, $name, $value, $append = true)
    {
        $doc = ($parent instanceof \DOMDocument) ? $parent : $parent->ownerDocument;
        $ele = $doc->createElement($name);
        $ele->appendChild($doc->createCDATASection($value));

        if ($append) {
            $parent->appendChild($ele);
        }

        return $ele;
    }


    public function checkEchoSignature($signature, $timestamp, $nonce)
    {
        $tmpArr = array($this->serverToken, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }
}