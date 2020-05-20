<?php

namespace app\common\validate;

use think\Validate;

class UserExchange extends Validate
{
    // 验证规则
    protected $rule = [
        ['id', 'require|number|gt:0', 'ID不能为空|ID必须是数字|ID格式不正确'],
        ['user_id', 'require|number|max:11', '用户ID不能为空|用户ID必须是数字|用户ID格式不正确'],
        ['goods_id', 'number|max:11', '商品ID必须是数字|商品ID格式不正确'],
        ['content', 'max:250', '内容格式不正确'],
        ['address', 'max:150', '收货地址格式不正确'],
    ];

    protected $scene = [
        'add' => ['user_id', 'goods_id', 'content', 'address'],
        'edit' => ['user_id', 'goods_id', 'content', 'address'],
        'del' => ['id'],
    ];
}