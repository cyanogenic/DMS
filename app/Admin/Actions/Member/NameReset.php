<?php

namespace App\Admin\Actions\Member;

use App\Admin\Forms\Member\NameResetForm;
use Dcat\Admin\Widgets\Modal;
use Dcat\Admin\Grid\RowAction;

class NameReset extends RowAction
{
    public function render()
    {
        // 实例化表单类并传递自定义参数
        $form = NameResetForm::make()->payload([
            'id' => $this->getKey(),
            'name' => $this->row->name,
        ]);
        
        return Modal::make()
            ->lg()
            ->title($this->row->name . '的曾用名')
            ->body($form)
            ->button('<a><i class="feather icon-edit-1"></i> 改回曾用名</a>');
    }
}
