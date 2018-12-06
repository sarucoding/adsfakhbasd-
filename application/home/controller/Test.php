<?php
namespace app\home\controller;
use OSS\OssClient;
use OSS\Core\OssException;
use app\common\logic\OssLogic;

require_once './vendor/aliyun-oss-php-sdk/autoload.php';
class Test extends Base
{
    /*
     * 专题列表
     */
    public function index()
    {

        if(IS_POST){
            $accessKeyId = "LTAIcE0EsVMsClze";
            $accessKeySecret = "s0WIXcO01acjYDo2yNJbvhjpT3TKAe";
// Endpoint以杭州为例，其它Region请按实际情况填写。
            $endpoint = "http://oss-cn-beijing.aliyuncs.com";
            $bucket= "cxs365";

            $object = "library/产品2.jpg";
            $file = request()->file('myfile');
            $filePath = $file->getRealPath();

            try{
                $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint);

                $res = $ossClient->uploadFile($bucket, $object, $filePath);
            } catch(OssException $e) {
                printf(__FUNCTION__ . ": FAILED\n");
                printf($e->getMessage() . "\n");
                return;
            }
            dump($res);die;
            print(__FUNCTION__ . ": OK" . "\n");
        }else{
            return $this->fetch();
        }
    }
    public function get_list(){
        /*$OssLogic = new OssLogic();
        $list = $OssLogic->fileList('library/');dump($list);die;*/
        $accessKeyId = "LTAIcE0EsVMsClze";
        $accessKeySecret = "s0WIXcO01acjYDo2yNJbvhjpT3TKAe";
        // Endpoint以杭州为例，其它Region请按实际情况填写。
        $endpoint = "http://oss-cn-beijing.aliyuncs.com";
        $bucket= "cxs365";
        $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint);

        while (true) {
            try {
                $listObjectInfo = $ossClient->listObjects($bucket, $options);
            } catch (OssException $e) {
                printf(__FUNCTION__ . ": FAILED\n");
                printf($e->getMessage() . "\n");
                return;
            }
            // 得到nextMarker，从上一次listObjects读到的最后一个文件的下一个文件开始继续获取文件列表。
            $nextMarker = $listObjectInfo->getNextMarker();
            $listObject = $listObjectInfo->getObjectList();
            $listPrefix = $listObjectInfo->getPrefixList();

            if (!empty($listObject)) {
                print("objectList:\n");
                foreach ($listObject as $objectInfo) {
                    print($objectInfo->getKey() . "\n");
                }
            }
            if (!empty($listPrefix)) {
                print("prefixList: \n");
                foreach ($listPrefix as $prefixInfo) {
                    print($prefixInfo->getPrefix() . "\n");
                }
            }
            if ($nextMarker === '') {
                break;
            }
        }
    }

}