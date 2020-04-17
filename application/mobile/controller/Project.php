<?php

namespace app\mobile\controller;

use think\Db;
use think\Validate;
use think\Request;
use app\common\lib\ReturnData;
use app\common\lib\Helper;
use app\common\logic\ProjectLogic;
use app\common\model\Project as ProjectModel;

class Project extends Common
{
    public function _initialize()
    {
        parent::_initialize();
    }

    public function getLogic()
    {
        return new ProjectLogic();
    }

    //列表
    public function index()
    {
        $id = input('id');
        $uri = $_SERVER["REQUEST_URI"]; //获取当前url的参数
        //分类
        $post = cache("mobile_project_index_post_" . md5($uri));
        if (!$post) {
            $post = logic('ProjectType')->getOne(array('id' => $id));
            cache("mobile_project_index_post_" . md5($uri), $post, 3600 * 24 * 30);
        }
        $assign_data['post'] = $post;
        //var_dump($assign_data['post']);exit;
        $pagesize = 15;
        $offset = 0;
        if (isset($_REQUEST['page'])) {
            $offset = ($_REQUEST['page'] - 1) * $pagesize;
        }
        $where['delete_time'] = 0;
        $where['add_time'] = ['<', time()];
        if ($id) {
            $where['type_id'] = $id;
        }

        $res = cache("mobile_project_index_list_" . md5($uri . '-' . $offset . '-' . json_encode($where)));
        if (!$res) {
            $res = logic('Project')->getList($where, ['status' => 'asc', 'listorder' => 'asc'], ['content'], $offset, $pagesize);
            cache("mobile_project_index_list_" . md5($uri . '-' . $offset . '-' . json_encode($where)), $res, 3600 * 24 * 30);
        }
        if ($res['list']) {
            foreach ($res['list'] as $k => $v) {

            }
        }
        $assign_data['list'] = $res['list'];
        $totalpage = ceil($res['count'] / $pagesize);
        $this->assign('totalpage', $totalpage);
        if (isset($_REQUEST['page_ajax']) && $_REQUEST['page_ajax'] == 1) {
            $html = '';
            if ($res['list']) {
                foreach ($res['list'] as $k => $v) {
                    $html .= '<a class="tier" href="' . url('project/detail') . '?id=' . $v['id'] . '">';
                    if (!empty($v['cover'])) {
                        $html .= '<div class="img-box"><img src="' . $v['cover'] . '"></div>';
                    }
                    $html .= '<div class="info-box">';
                    $html .= '<div class="ib-head"><span class="index">保</span>' . $v['title'] . '</div>';
                    $html .= '<div class="ib-body">';
                    $html .= '<div class="cl-3"><p><span class="red">' . $v['daily_interest'] . '</span>%</p><p>日化利率</p></div>';
                    $html .= '<div class="cl-3"><p><span class="red">' . round($v['min_buy_money'] * $v['daily_interest'] / 100, 2) . '</span>元</p><p>每日分红</p></div>';
                    $html .= '<div class="cl-3"><p><span class="red">' . $v['term'] . '</span>天</p><p>投资期限</p></div>';
                    $html .= '<div class="cl-3"><p>￥<span class="red">' . $v['min_buy_money'] . '</span>元</p><p>起投金额</p></div></div>';
                    if ($v['status'] != 1) {
                        $html .= '<div class="ib-foot"><div class="text"><p>项目规模：' . $v['scale'] . '万元</p><p>' . $v['dividend_mode_text'] . '</p></div><div class="other"><button class="now-btn">立即购买</button></div></div>';
                        $html .= '<div class="plan"><span>项目进度：</span><div class="plan-wrap"><div class="plan-con" style="width:' . $v['progress'] . '%;"></div></div><span class="plan-text">' . $v['progress'] . '%</span></div>';
                    } else {
                        $html .= '<div class="ib-foot"><div class="text"><p>项目规模：' . $v['scale'] . '万元</p><p>' . $v['dividend_mode_text'] . '</p></div><div class="other"><button class="now-btn" style="background-color:#888;">项目已满</button></div></div>';
                        $html .= '<div class="plan"><span>项目进度：</span><div class="plan-wrap"><div class="plan-con" style="width:100%;background-color:#888;"></div></div><span class="plan-text">100%</span></div>';
                        $html .= '<img class="over" src="/images/mobile/soldout.png">';
                    }
                    $html .= '</div></a>';
                }
            }

            exit(json_encode($html));
        }

        //dd($assign_data);
        $this->assign($assign_data);
        return $this->fetch();
    }

    //详情
    public function detail()
    {
        if (!checkIsNumber(input('id', null))) {
            Helper::http404();
        }
        $id = input('id');

        $res = logic('Project')->getOne(array('id' => $id));
        if (empty($res)) {
            Helper::http404();
        }
        $res['content'] = preg_replace('/src=\"\/uploads\/allimg/', "src=\"" . http_host() . "/uploads/allimg", $res['content']);
        $assign_data['post'] = $res;
        //dd($assign_data['post']);
        $this->assign($assign_data);
        return $this->fetch();
    }

    //详情
    public function buy()
    {
        //判断是否登录
        $this->isLogin();

        if (Helper::isPostRequest()) {
            //表单令牌验证
            /* $validate = new Validate([
                ['__token__', 'require|token', '非法提交|请不要重复提交表单'],
            ]);
            if (!$validate->check($_POST)) {
                $this->error($validate->getError());
            } */
            $user = model('User')->getOne(['id' => $this->login_info['id']]);
            if (!$user['pay_password']) {
                //$this->error('请设置支付密码', url('user/setting'));
                $res = ReturnData::create(ReturnData::FAIL, null, '请设置支付密码');
                $res['url'] = url('user/setting');
                Util::echo_json($res);
            }

            //判断支付密码
            if (isset($_POST['pay_password']) && !empty($_POST['pay_password'])) {
                $pay_password = logic('User')->passwordEncrypt($_POST['pay_password']);
                if ($pay_password != $user['pay_password']) {
                    //$this->error('支付密码不正确，请重新输入');
                    Util::echo_json(ReturnData::create(ReturnData::FAIL, null, '支付密码不正确，请重新输入'));
                }
            } else {
                //$this->error('请输入支付密码');
                Util::echo_json(ReturnData::create(ReturnData::FAIL, null, '请输入支付密码'));
            }

            //用户投资项目
            $add_data['user_id'] = $this->login_info['id'];
            $add_data['project_id'] = $_POST['id'];
            $add_data['money'] = $_POST['money'];
            $res = logic('UserProject')->add($add_data);
            if ($res['code'] != ReturnData::SUCCESS) {
                //$this->error($res['msg']);
                Util::echo_json($res);
            }

            //$this->success($res['msg'], url('user_project/index'));
            $res['url'] = url('user_project/index');
            Util::echo_json($res);
        }

        $this->is_certification();
        if (!checkIsNumber(input('id', null))) {
            Helper::http404();
        }
        $id = input('id');

        $res = logic('Project')->getOne(array('id' => $id));
        if (empty($res)) {
            Helper::http404();
        }
        $assign_data['post'] = $res;
        //dd($assign_data['post']);
        $this->assign($assign_data);
        $this->assign('id', $id);

        $res = logic('User')->getUserInfo(['id' => $this->login_info['id']]);
        $this->login_info = array_merge($this->login_info, $res);
        session('mobile_user_info', $this->login_info);
        $this->assign('login_info', $this->login_info);

        return $this->fetch();
    }
}