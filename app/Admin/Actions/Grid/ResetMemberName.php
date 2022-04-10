<?php

namespace App\Admin\Actions\Grid;

use App\Admin\Forms\ResetMemberName as ResetMemberNameForm;
use Dcat\Admin\Widgets\Modal;
use Dcat\Admin\Grid\RowAction;

class ResetMemberName extends RowAction
{
    public function render()
    {
        // 实例化表单类并传递自定义参数
        $form = ResetMemberNameForm::make()->payload([
            'id' => $this->getKey(),
            'name' => $this->row->name,
        ]);

        if ($this->row->nickname) { $member_name = $this->row->nickname; }
        else { $member_name = $this->row->name; }
        
        return Modal::make()
            ->lg()
            ->title($member_name . '的曾用名')
            ->body($form)
            ->button('<i class="feather icon-edit-1"></i> 改回曾用名');
    }
}
