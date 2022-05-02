# LightTucao

这是一个没有评论、点赞、阅读量干扰的私人吐槽页。

## 为什么有这个

灵感来源：[保卫表达：用后端 BaaS 快速搭建专属无点赞评论版微博——b言b语](https://sspai.com/post/60024)，然后笔者就想搭一个。

笔者编写的早期版本是带有后端的版本，现改成了基于Cloudflare Worker的版本。

前端 [index.html](./index.html) 原始文件来源于[原作者仓库](https://github.com/daibor/nonsense.fun)，本项目该文件与原始文件之相同部分适用[该协议](https://github.com/daibor/nonsense.fun/blob/master/LICENSE)。本项目该文件与原始文件之不同部分适用本项目所使用的许可协议。

## 使用

1. 将仓库内的[Cloudflare Worker](https://github.com/SamLangTen/LightTucao/tree/master/backend/cloudflare-worker)脚本部署到Cloudflare上，并配置好Cloudflare Worker KV和路由。
2. 将前端```index.html```部署到任意的静态页面托管服务上。
3. 需要在Cloudflare Worker KV的数据表内新建名为token的KV，其值为提交和删除微博所使用的Token。
4. 若要提交新微博，使用以下API，并配置HTTP Authorization头为Basic Token：
```
    POST /tucao
    {
        "content":"内容"
    }
```
5. 若要删除微博，使用以下API，并配置HTTP Authorization头为Basic Token：
```
    DELETE /tucao
    {
        "id":微博id
    }
```

## 注意

代码是拼凑的，健壮性极低。Use it on your own disk.
详情请看笔者博文：[记一次从LNMP迁移到Serverless](https://blog.samersions.net/article/a-journal-from-lnmp-to-serverless/)
