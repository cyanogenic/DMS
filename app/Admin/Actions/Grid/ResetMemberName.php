<?php

namespace App\Admin\Actions\Grid;

use App\Admin\Forms\ResetMemberName as ResetMemberNameForm;
use Dcat\Admin\Widgets\Modal;
use Dcat\Admin\Grid\RowAction;

class ResetMemberName extends RowAction
{
    protected $title = '改回曾用名';

    public function render()
    {
        // 实例化表单类并传递自定义参数
        $form = ResetMemberNameForm::make()->payload([
            'id' => $this->getKey(),
            'name' => $this->row->name,
        ]);

        return Modal::make()
            ->lg()
            ->title($this->title)
            ->body($form)
            ->button($this->title);
    }
}
