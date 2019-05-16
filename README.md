# Rcloud-easemob-message-adapter

通过融云的服务端实时消息路由和环信的实时回调服务简单的实现双方消息互通。  
能够平滑的从环信迁移到融云，不用强制客户端升级，在迁移过程中，使新老客户端版本能够同时正常的互发消息。  

## 使用方法
代码实现比较简单，配置也相对简单些。  
1、请将 rcloud.php 中的融云 appkey 和 secret 修改为自己业务的对应值；将 easemob.php 中的 clientId 和 secret 修改为环信的 Client ID 和 Client Secret；  
2、先从融云入手，在融云开发者后台中，在 IM 服务高级功能设置中，将消息路由地址设置为本项目中 rcloud.php 文件的部署地址；  
3、同理，在环信 Console 中，在 IM --> 实时回调中，将对应 app 的回调地址修改 easemob.php 的部署地址。  


## 注意  
融云的实时消息路由和环信的实时回调服务都属于增值业务，都需要付费开通。    

