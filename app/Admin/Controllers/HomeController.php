<?php

namespace App\Admin\Controllers;

use App\Admin\Metrics\ActiveMembers;
use App\Admin\Metrics\TotalUsers;
use App\Http\Controllers\Controller;
use Dcat\Admin\Layout\Column;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Layout\Row;

class HomeController extends Controller
{
    public function index(Content $content)
    {
        return $content
            ->header('主页')
            ->description('数据统计')
            ->body(function (Row $row) {
                $row->column(6, function (Column $column) {
                    // $column->row(Dashboard::title());
                    $column->row(new ActiveMembers());
                    // $column->row(new Examples\ProductOrders());
                });

                $row->column(6, function (Column $column) {
                    $column->row(function (Row $row) {
                        $row->column(6, new TotalUsers());
                        // $row->column(6, new Examples\NewUsers());
                        // $row->column(6, new Examples\NewDevices());
                    });

                    // $column->row(new Examples\Sessions());
                    
                });
            });
    }
}
