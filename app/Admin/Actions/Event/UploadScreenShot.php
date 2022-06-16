<?php

namespace App\Admin\Actions\Event;

use App\Models\Member;
use App\Models\OCRResult;
use Dcat\Admin\Admin;
use Dcat\Admin\Contracts\LazyRenderable;
use Dcat\Admin\Traits\LazyWidget;
use Dcat\Admin\Widgets\Form;
use Illuminate\Support\Facades\Storage;
use TencentCloud\Common\Credential;
use TencentCloud\Common\Exception\TencentCloudSDKException;
use TencentCloud\Common\Profile\ClientProfile;
use TencentCloud\Common\Profile\HttpProfile;
use TencentCloud\Ocr\V20181119\Models\GeneralAccurateOCRRequest;
use TencentCloud\Ocr\V20181119\OcrClient;

class UploadScreenShot extends Form implements LazyRenderable
{
    use LazyWidget;

    public function handle(array $input)
    {
        $filename = Storage::path('public/' . $input['filename']);

        // 校验截图MD5,并与已保存的图片比对
        $ocr = OCRResult::firstOrNew(
            [ 'md5'   => md5_file($filename) ],
            [ 'image' => $input['filename'] ],
        );

        if (! $ocr->exists) {
            // 使用腾讯云OCR识别图片
            try {
                $cred = new Credential(config('dms.ocr_secretid'), config('dms.ocr_secretkey'));
                $httpProfile = new HttpProfile();
                $httpProfile->setEndpoint("ocr.tencentcloudapi.com");
                
                $clientProfile = new ClientProfile();
                $clientProfile->setHttpProfile($httpProfile);
                $client = new OcrClient($cred, "ap-beijing", $clientProfile);
            
                $req = new GeneralAccurateOCRRequest();
                
                $params = array(
                    'ImageBase64' => base64_encode(file_get_contents($filename)),
                );
                $req->fromJsonString(json_encode($params));
            
                $resp = $client->GeneralAccurateOCR($req);

                $ocr_res = array();
                foreach ($resp->TextDetections as $textDetection) {
                    $member = Member::search($textDetection->DetectedText)->get();
                    // TODO 现阶段认为只识别到一个的情况为识别成功
                    if ($member->count() == 1) {
                        array_push($ocr_res, $member->first()->id);
                    }
                }

                $ocr->res = implode(',', $ocr_res);
                $ocr->save();

            } catch(TencentCloudSDKException $e) { return $this->response()->error($e); }

        } else {
            // 删除重复上传的文件
            Storage::disk('public')->delete($input['filename']);
        }


        return $this->response()->success('识别完成,正在跳转到活动新增页面')->location('events/create?ocr=' . $ocr->id);
    }

    public function form()
    {
        // TODO 加个示例图片
        $this->image('filename', '团队截图')->help('请上传团队截图,大小不超过5MB')
            ->name(function ($file) {
                return date('ymdhis') . '-' . Admin::user()->id . '.' . $file->guessExtension();
            })
            ->rules('required', ['required' => '请上传截图'])
            ->maxSize(5120)->autoUpload();
    }
    
}