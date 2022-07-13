<?php

namespace App\Admin\Forms\Member;

use App\Models\Account;
use App\Models\FormerName;
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
        // 就像曾经交换过姓名
        $account = Account::find($this->payload['id']);
        $former_name = FormerName::find($input['former_name_id']);
        $old_name = $former_name->name;
        
        $former_name->name = $account->name;
        $former_name->save();
        $account->name = $old_name;
        $account->save();

        return $this->response()->success('修改成功')->refresh();
    }

    /**
     * Build a form here.
     */
    public function form()
    {
        $this->select('former_name_id')->options(FormerName::where('account_id', $this->payload['id'])->pluck('name', 'id'))->required();
    }
}
