# LightTucao

这是一个没有评论、点赞、阅读量干扰的私人吐槽页。

## 为什么有这个

今天早上起床时看到这样一篇推送：[保卫表达：用后端 BaaS 快速搭建专属无点赞评论版微博——b言b语](https://sspai.com/post/60024)，然后笔者就想搭一个。

然后笔者就跑去LeanCloud注册了个账号，发现国际版的LeanCloud对于开发版应用每天的请求次数只有3K，是大陆版的十分之一。虽然就算搭起来估计也不会有什么人访问，但是3K实在有点少。

想想看笔者自己有VPS为何不弄个带后端的算了，于是就拼凑了一个带后端的。

前端 [index.html](./index.html) 原始文件来源于[原作者仓库](https://github.com/daibor/nonsense.fun)，本项目该文件与原始文件之相同部分适用[该协议](https://github.com/daibor/nonsense.fun/blob/master/LICENSE)。本项目该文件与原始文件之不同部分适用本项目所使用的许可协议。

## 使用

1. 打开```config.json```配置数据库和密码，密码用于API提交新微博。

2. 若要提交新微博，使用以下API：

```
    POST /backend.php
    {
        "content":"内容",
        "password":"你设置的密码"
    }
```

3. 注意设置Web服务器规则，避免```config.json```被访问。

## 注意

代码是拼凑的，健壮性极低。Use it on your own disk.