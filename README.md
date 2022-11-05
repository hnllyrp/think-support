# think-support

> 基于 thinkphp 提供扩展支持，主要版本tp6.0以上

## 安装依赖
```
composer require hnllyrp/think-support
```


## 目录结构
```shell
├─config  配置文件
├─console 命令行
├─middleware 中间件
├─Providers 服务提供者
├─resources 静态资源 语言包、视图等
├─rules 验证规则
├─services 服务类
├─support
│  ├─facade
│  ├─shell
│  ├─Arr.php 常用数组函数

```

## 功能列表

- 自定义指令 基于现有数据库，批量生成数据库模型文件
> php think yrp:make:models model|entities


- 自定义指令 基于现有数据库，生成数据库字典、目录结构
> php think yrp:make:doc

> php think yrp:make:doc struct --show_file


- 自定义指令 基于现有数据库 生成 migrate文件
> php think yrp:make:migration


- 自定义指令 基于现有数据库 生成 seeder文件
> php think yrp:make:seeder

> php think yrp:make:seeder users


- 生成storage目录 软链至public目录 类似laravel
> php think storage:link



