<?php

namespace App\Admin\Controllers;

use App\Admin\Renderable\MemberTable;
use App\Models\Event;
use App\Models\Member;
use App\Models\Scoring;
use Dcat\Admin\Admin;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

class EventController extends AdminController
{
    public $url_query = array();

    public function __construct()
    {
        // 处理URL参数
        parse_str(parse_url(URL::full())['query'] ?? null, $this->url_query);
    }

    public function urlQuery($key)
    {
        return $this->url_query[$key] ?? null;
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(Event::with(['scoring', 'member', 'admin_user']), function (Grid $grid) {            
            $grid->tools(function (Grid\Tools $tools) {
                $tools->append(function () {
                    Form::dialog('创建活动记录')
                        ->click('.create-form')     // 绑定点击按钮
                        ->url('events/create')      // 表单页面链接，此参数会被按钮中的 “data-url” 属性替换
                        ->width('50%')
                        ->success('Dcat.reload()'); // 新增成功后刷新页面
                    return "
                        <button class='btn btn-primary btn-mini btn-outline'>
                            <i class='feather icon-plus-square'></i>
                            <span class='create-form'>在弹窗中新增</span>
                        </button>
                    ";
                });
            });

            // 复制按钮
            $grid->actions(function (Grid\Displayers\Actions $actions) {
                $actions->append('<a href="' . admin_url('events/create?template=') . $this->getKey() . '"><i class="feather icon-copy"></i> 复制</a>');
            });

            $grid->model()->orderBy('time', 'desc');

            $grid->column('time')->sortable()->display(function ($time) {
                return date("Y-m-d H:i", strtotime($time));
            });;
            $grid->column('scoring.name', __('计分项'))->width('10%');
            $grid->column('point');
            $grid->column('member')->width('40%')->pluck('name')->badge();
            $grid->column('comment')->width('15%');

            $grid->column('admin_user.name', __('管理员'));
            
            $grid->column('updated_at');
        });
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     *
     * @return Show
     */
    protected function detail($id)
    {
        $model = Event::with('scoring');
        return Show::make($id, $model, function (Show $show) {
            $show->field('time')->as(function ($time) {
                return date("Y-m-d H:i", strtotime($time));
            });
            $show->field('scoring.name', __('计分项'));
            $show->field('point');
            $show->field('comment');

            $show->field('created_at');
            $show->field('updated_at');

            $show->relation('member', function ($model) {
                $grid = new Grid(new Member);

                $grid->model()->join('event_member', function ($join) use ($model) {
                    $join->on('event_member.member_id', 'id')
                        ->where('event_id', '=', $model->id);
                });
            
                $grid->nickname;
                $grid->name;
                
                $grid->disableActions();
                $grid->disableRefreshButton();
                $grid->disableCreateButton();
                $grid->disableRowSelector();
                
                return $grid;
            });
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(Event::with(['scoring', 'member']), function (Form $form) {
            // 获取要复制的行的ID
            $template = Event::find($this->urlQuery('template'));

            $form->datetime('time')->format('YYYY-MM-DD HH:mm')
                ->default(date("Y-m-d H:i:s"))->required();
            // 同时取name和point
            $form->select('scoring_id', __('计分项'))->options(Scoring::all()->pluck('name', 'id'))
                ->required()
                ->default($template ? $template->scoring_id : null);
            $form->radio('custom_point')
                ->when(1, function (Form $form) { $form->number('point'); })
                ->options([ 0 => '否', 1 => '是' ])
                ->default(0, true);

            $form->multipleSelectTable('member', __('参与玩家'))
                ->title('参与玩家')
                ->dialogWidth('75%')
                ->from(MemberTable::make())
                ->model(Member::class, 'id', 'name')
                ->required()
                ->customFormat(function ($v) {
                    if (!$v) return [];
                    // 这一步非常重要，需要把数据库中查出来的二维数组转化成一维数组
                    return array_column($v, 'id');
                })
                ->default($template ? array_column($template->member->toarray(), 'id') : null);
            $form->text('comment');
            $form->number('admin_user_id')->display(0);
        
            $form->saving(function (Form $form) {
                // 填充管理员ID
                if ($form->isCreating()) {  $form->admin_user_id = Admin::user()->id; }
                // 提交时填充point字段
                if (!$form->custom_point) { $form->point = DB::table('scorings')->where('id', $form->scoring_id)->value('point'); }
                // 不提交自定义分值的开关
                $form->deleteInput('custom_point');

                // TODO 快看,是脑瘫代码
                // 修改操作前先减
                DB::update(
                    'UPDATE members SET dkp = dkp - (SELECT point FROM `events` WHERE id = ?) WHERE id IN (SELECT member_id from event_member WHERE event_id = ?)',
                    [$form->getKey(), $form->getKey()]
                );
            });

            $form->saved(function (Form $form) {
                $newId = $form->getKey();
                // 新增或修改后重新加DKP
                DB::update(
                    'UPDATE members SET dkp = dkp + (SELECT point FROM `events` WHERE id = ?) WHERE id IN (SELECT member_id from event_member WHERE event_id = ?)',
                    [$newId, $newId]
                );
            });

            // 删除后重新计算DKP
            $form->deleting(function (Form $form) {
                // 获取待删除行数据，这里获取的是一个二维数组
                $data = $form->model()->toArray();
                //
                foreach ($data as $key => $value) {
                    DB::update(
                        'UPDATE members SET dkp = dkp - ? WHERE id IN (SELECT member_id from event_member WHERE event_id = ?)',
                        [$value["point"], $value["id"]]
                    );
                }
            });
        });
    }
}
