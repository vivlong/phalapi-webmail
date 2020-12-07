# WebMail
PhalApi 2.x扩展类库，基于PHP-IMAP的邮件接收。

## 安装和配置
修改项目下的composer.json文件，并添加：  
```
    "vivlong/phalapi-webmail":"dev-master"
```
然后执行```composer update```，如果PHP版本过低，可使用```composer update --ignore-platform-reqs```。  

安装成功后，添加以下配置到./config/app.php文件：  
```php
    'Webmail' => array(
        'email' => array(
            'host' => 'imap.gmail.com',
            'protocol' => 'imap',
            'secure' => 'ssl',
            'port' => 993,
            'username' => 'xxxxx@gmail.com',
            'password' => '******',
        ),
    ),
```

## 使用
在文件中，使用邮件服务：  
```php
$webmail = new \PhalApi\Webmail\Lite(true);
```

