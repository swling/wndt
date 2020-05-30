# wndt 核心功能
0.基于wnd frontend （已整合到本主题目录下：/core）
1.前端用户中心提供：注册登录、充值、站内信
2.付费阅读，付费下载，用户投稿并分成
3.无限可能性的后续功能开发

wndt 本身并不复杂，事实上它本质是对wnd-frontend插件的一次最简单的基础开发，是用主题的形式对插件功能的一次直观表达。真正的核心仍然在于wnd frontend
基于wnd frontend 可以轻松开发：客户管理系统，报名系统，在线教育站点等等。你可以不信，但我们正在这样做。

## add_filter add_action priority: 11

# options：

## option_name
wndt

## 主题配置
wndt_logo

### QQ登录
wndt_qq_appid
wndt_qq_appkey

### 社交登录回调网址
wndt_social_redirect_url

### 其他
wndt_icp：icp备案
wndt_wangan：网安备案
wndt_statistical_code：流量统计代码

# 主题配套插件开发
主题配套插件开发与独立开发插件遵循同样的基本原则。具体详情参看WordPress官方文档。唯一的区别是，本主题预设了插件类自动加载方法详情查看：inc/wndt-load.php

## 命名空间
Wndt\Plugin 对应WordPress插件目录 wp-content/plugins

## 插件class自动加载实例
类名: Wndt\Plugin\Wndt_Demo\Wndt_Demo
路径: /wp-content/plugins/wndt-demo/wndt-demo.php

component文件夹存储第三方组件，按通用驼峰命名规则
new Wndt\Plugin\Wndt_Demo\Component\AjaxComment;
/wp-content/plugins/wndt-demo/component/AjaxComment.php
*注意：第三方组件文件及文件目录需要区分大小写*

# 备注

