<?php

namespace App\Admin\Forms;

use App\Models\Alias;
use Dcat\Admin\Contracts\LazyRenderable;
use Dcat\Admin\Traits\LazyWidget;
use Dcat\Admin\Widgets\Form;

class ResetMemberName extends Form implements LazyRenderable
{
    use LazyWidget;
    
    /**
     * Handle the form request.
     *
     * @param array $input
     *
     * @return mixed
     */
    public function handle(array $input)
    {
        $name = $input['name'] ?? null;
        return $this->response()->success('修改成功')->refresh();
    }

    /**
     * Build a form here.
     */
    public function form()
    {
        // 获取外部传递参数
        $id = $this->payload['id'] ?? null;

        $alias = Alias::select('id', 'name')->where('member_id', $id)->get()->toarray();
        $names = array();
        foreach ($alias as $value) {
            // 曾用名去重
            if (!array_search($value['name'], $names)) {
                // 当前名称去重
                if ($this->payload['name'] != $value['name']) {
                    $names[$value['id']] = $value['name'];
                }
            }   
        }
        $this->select('name')
            ->options($names)
            ->required();
    }
}
