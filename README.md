# Corporate Game

Готовый движок для проведения корпоративных игр, основанных на принципе "кто первый ответит". Для проведения нужно:

- заранее развернуть бэкенд на хостинге (можно бесплатном, в нашем случае был Beget, держал 20 участников) с PHP 7 и MySQL
- запустить инсталляцию (/setup.game.php)
- отредактировать вьюшки под свой корпоративный стиль (все на Bootstrap 4)
- иметь в помещении Wifi или публичную скоростную сеть
- у всех участников наличие современного смартфона, оснащенного браузером с поддержкой ES5 и CSS3
- по-возможности: ноутбук/монитор, на котором будет выводится текущий статус игры 

## Принцип проведения игр

1. Подготовка к игре
   1. администратор игры переходит в `/?view=admin` и вводит пароль, заданный при установке 
   2. ![Admin screenshot](/screens/admin.png)
   3. на мониторе требуется отобразить вкладку браузера `/?view=screen`
   4. пользователи заходят по публичному адресу на главную страницу `/`

2. Новая игра
   1. Нажимая "Новая игра" администратор переводит систему в режим ожидания начала раунда.
   2. На мониторе появляется надпись "Готовимся к следующему раунду" 
   3. ![Monitor screenshot 1](/screens/monitor1.png)
   4. На экранах телефонов появляется надпись "Присоединиться". 
   5. Игроки вводят свое имя (или видят ранее введенное) и подключаются к игре. 
   6. ![Mobile screenshot 1](/screens/mobile1.png)
   7. Администратор проверяет количество игроков, указывает нужное количество команд, и нажимает кнопку "Создать команды". Имена команд даются рандомно. Участники по командам распределяются так же рандомно.
   8. Администратор может контролировать состав команд в разделе "Команды"
   9. Игроки видят свои команды и их состав
   10. ![Mobile screenshot 2](/screens/mobile2.png)
   11. Администратор переходит ко вкладке "Раунды" и создает новый раунд.

3. Старт раунда
   1. Администратор нажимает "Старт", ведущий задает вопрос.
   2. У игроков появляется кнопка "Ответить", которую они могут нажать.

4. Ответ
   1. Имя первого игрока, от кого придет ответ, отобразится на мониторе 
   2. ![Monitor screenshot 2](/screens/monitor2.png)
   3. У игрока отобразится сообщение "Можете отвечать" 
   4. ![Mobile screenshot 3](/screens/mobile3.png)

5. Правильный ответ
   1. В случае правильного ответа администратор нажимает "Правильно"
   2. Система переходит в режим ожидания следующего раунда

6. Неправильный ответ
   1. В случае неправильного ответа администратор нажимает "Продолжить раунд"
   2. На монитор выводится имя игрока, ответившего следующим по очереди
   3. У отвечающего игрока отобразится сообщение "Можете отвечать"
   4. Если ответа не было, можно дальше ожидать ответа, или администратор может завершить раунд кнопкой "Нет ответа"

7. Конец игры
   1. Во вкладке "Игра" администратор нажимает кнопку "Завершить игру"
   2. Система переходит в начальное состояние.