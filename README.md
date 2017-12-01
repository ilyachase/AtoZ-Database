Деплой
============================

1. ```git clone```
2. ```composer install```
3. Юзера веб-сервеиса сделать владельцем всего кроме, кроме .git/* , а также ```chmod +x ./yii```
4. Создать базу, данные доступа прописать в ```config/db.php```
5. Почту для ошибок прописать в ```config/web.php:40``` и ```config/console.php:23```
6. Переменную YII_DEBUG выставить в false в ```yii:11``` и ```web/index.php:4```
7. ```./yii migrate```
8. В крон: ```* * * * * /%path_to_yii%/yii report --debug```
9. ???
10. PROFIT!
