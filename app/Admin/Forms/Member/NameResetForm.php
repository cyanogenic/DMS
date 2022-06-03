<?php

namespace App\Admin\Forms\Member;

use App\Models\Alias;
use App\Models\Member;
use Dcat\Admin\Contracts\LazyRenderable;
use Dcat\Admin\Traits\LazyWidget;
use Dcat\Admin\Widgets\Form;

class NameResetForm extends Form implements LazyRenderable
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
        // 更新Alias表
        Alias::create([
            'member_id' => $this->payload['id'],
            'name' => $this->payload['name'],
        ]);
        // 改回曾用名
        $member = Member::find($this->payload['id']);
        $member->update(['name' => $input['alias']]);
        return $this->response()->success('修改成功')->refresh();
    }

    /**
     * Build a form here.
     */
    public function form()
    {
        $alias = Alias::select('name')->where('member_id', $this->payload['id'])->get()->toarray();
        $options = array();
        foreach ($alias as $value) {
            // 曾用名去重
            if (!array_search($value['name'], $options)) {
                // 当前名称去重
                if ($this->payload['name'] != $value['name']) {
                    $options[$value['name']] = $value['name'];
                }
            }   
        }
        $this->select('alias')
            ->options($options)
            ->required();
    }
}
