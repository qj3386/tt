<?php

namespace app\common\validate;

use think\Validate;

class Project extends Validate
{
    // 验证规则
    protected $rule = [
        ['id', 'require|number|max:11', 'ID不能为空|ID必须是数字|ID格式不正确'],
        ['type_id', 'require|number|max:11', '栏目ID不能为空|栏目ID必须是数字|栏目ID格式不正确'],
        ['tuijian', 'number|egt:0', '推荐等级必须是数字|推荐等级格式不正确'],
        ['click', 'number|egt:0', '点击量必须是数字|点击量格式不正确'],
        ['title', 'require|max:150', '标题不能为空|标题不能超过150个字符'],
        ['litpic', 'max:150', '缩略图不能超过150个字符'],
        ['keywords', 'max:60', '关键词不能超过60个字符'],
        ['seotitle', 'max:150', 'seo标题不能超过150个字符'],
        ['description', 'max:250', '描述不能超过250个字符'],
        ['sell_point', 'max:150', '卖点不能超过150个字符'],
        ['guarantee_agency', 'require|max:150', '担保机构不能为空|担保机构不能超过150个字符'],
        ['scale', 'require|number|max:11', '项目规模不能为空|项目规模必须是数字|项目规模格式不正确'],
        ['progress', 'require|regex:/^\d{1,3}(\.\d{0,2})?$/|>=:0|<=:100', '项目进度不能为空|项目进度不正确，项目进度只能带2位小数的数字|项目进度不能小于0|项目进度不能大于100'],
        ['min_buy_money', 'require|number|max:11|gt:0', '起购金额不能为空|起购金额必须是数字|起购金额格式不正确|起购金额格式不正确'],
        ['max_buy_money', 'require|number|max:11|egt:0', '最高买入金额必须是数字|最高买入金额格式不正确|最高买入金额格式不正确'],
        ['stock', 'number|max:11', '投资人数必须是数字|投资人数格式不正确'],
        ['dividend_mode', 'require|number|max:11|egt:0', '还款方式不能为空|还款方式必须是数字|还款方式格式不正确|还款方式格式不正确'],
        ['buy_limit_num', 'require|number|max:11', '限买份数不能为空|限买份数必须是数字|限买份数格式不正确'],
        ['daily_interest', 'require|regex:/^\d{1,3}(\.\d{0,4})?$/', '日化收益不能为空|日化收益不正确'],
        ['term', 'require|number|max:11', '产品期限不能为空|产品期限必须是数字|产品期限格式不正确|产品期限格式不正确'],
        ['sale', 'number|max:11', '销量必须是数字|销量格式不正确'],
        ['distribution_yiji', 'regex:/^\d{1,3}(\.\d{0,2})?$/|>=:0|<=:100', '一级分销奖励格式不正确，一级分销奖励只能带2位小数的数字|一级分销奖励不能小于0|一级分销奖励不能大于100'],
        ['distribution_erji', 'regex:/^\d{1,3}(\.\d{0,2})?$/|>=:0|<=:100', '二级分销奖励格式不正确，二级分销奖励只能带2位小数的数字|二级分销奖励不能小于0|二级分销奖励不能大于100'],
        ['status', 'in:0,1', '投资状态：0投资中，1已投满'],
        ['user_id', 'number|max:11', '发布者ID必须是数字|发布者ID格式不正确'],
        ['shop_id', 'number|max:11', '店铺ID必须是数字|店铺ID格式不正确'],
        ['listorder', 'require|number|max:11', '排序不能为空|排序必须是数字|排序格式不正确'],
        ['add_time', 'require|number|max:11', '添加时间不能为空|添加时间格式不正确|添加时间格式不正确'],
        ['update_time', 'require|number|max:11', '更新时间不能为空|更新时间格式不正确|更新时间格式不正确'],
    ];

    protected $scene = [
        'add' => ['type_id', 'tuijian', 'click', 'title', 'litpic', 'keywords', 'seotitle', 'description', 'guarantee_agency', 'scale', 'progress', 'min_buy_money', 'max_buy_money', 'stock', 'dividend_mode', 'is_repeat', 'daily_interest', 'term', 'sale', 'status', 'user_id', 'shop_id', 'listorder', 'add_time', 'update_time'],
        'edit' => ['type_id', 'tuijian', 'click', 'title', 'litpic', 'keywords', 'seotitle', 'description', 'guarantee_agency', 'scale', 'progress', 'min_buy_money', 'max_buy_money', 'stock', 'dividend_mode', 'is_repeat', 'daily_interest', 'term', 'sale', 'status', 'user_id', 'shop_id', 'listorder', 'update_time'],
        'del' => ['id'],
    ];
}