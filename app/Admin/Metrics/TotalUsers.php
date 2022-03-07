<?php

namespace App\Admin\Metrics;

use Carbon\Carbon;
use Dcat\Admin\Widgets\Metrics\Card;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TotalUsers extends Card
{
    /**
     * 卡片底部内容.
     *
     * @var string|Renderable|\Closure
     */
    protected $footer;

    /**
     * 初始化卡片.
     */
    protected function init()
    {
        parent::init();

        $this->title('总玩家数');
        $this->dropdown([
            '7' => 'Last 7 Days',
            '30' => 'Last Month',
            '365' => 'Last Year',
        ]);
    }

    /**
     * 处理请求.
     *
     * @param Request $request
     *
     * @return void
     */
    public function handle(Request $request)
    {
        $days = $request->get('option') ?? 7;
        $old = Carbon::today()->subDay($days - 1)->toDateTimeString();
        
        $member_now = DB::table('members')
            ->where('deleted_at', NULL)
            ->count();
        $member_old = DB::table('members')
            ->whereDate('created_at', '<', $old)
            ->whereDate('deleted_at', '>=', $old)
            ->orWhere('deleted_at', NULL)
            ->count();
        

        $this->content($member_now);
        if ($member_old < $member_now) {
            $this->up(round(($member_now - $member_old) / $member_old * 100, 2));
        } elseif ($member_old > $member_now) {
            $this->down(round(($member_old - $member_now) / $member_now * 100,2));
        }
        
    }

    /**
     * @param int $percent
     *
     * @return $this
     */
    public function up($percent)
    {
        return $this->footer(
            "<i class=\"feather icon-trending-up text-success\"></i> {$percent}% Increase"
        );
    }

    /**
     * @param int $percent
     *
     * @return $this
     */
    public function down($percent)
    {
        return $this->footer(
            "<i class=\"feather icon-trending-down text-danger\"></i> {$percent}% Decrease"
        );
    }

    /**
     * 设置卡片底部内容.
     *
     * @param string|Renderable|\Closure $footer
     *
     * @return $this
     */
    public function footer($footer)
    {
        $this->footer = $footer;

        return $this;
    }

    /**
     * 渲染卡片内容.
     *
     * @return string
     */
    public function renderContent()
    {
        $content = parent::renderContent();

        return <<<HTML
<div class="d-flex justify-content-between align-items-center mt-1" style="margin-bottom: 2px">
    <h2 class="ml-1 font-lg-1">{$content}</h2>
</div>
<div class="ml-1 mt-1 font-weight-bold text-80">
    {$this->renderFooter()}
</div>
HTML;
    }

    /**
     * 渲染卡片底部内容.
     *
     * @return string
     */
    public function renderFooter()
    {
        return $this->toString($this->footer);
    }
}
