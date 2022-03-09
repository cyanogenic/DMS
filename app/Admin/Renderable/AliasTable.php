<?php

namespace App\Admin\Renderable;

use App\Models\Alias;
use Dcat\Admin\Support\LazyRenderable;
use Dcat\Admin\Widgets\Table;

class AliasTable extends LazyRenderable
{
    public function render()
    {
        $id = $this->key;

        $data = Alias::where('member_id', $id)
            ->get(['name', 'updated_at'])
            ->toArray();

        $titles = [
            '曾用名',
            '更新时间',
        ];

        return Table::make($titles, $data);
    }
}
