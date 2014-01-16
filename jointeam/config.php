<?php
if (!defined("FMJoinTeam")) die("hacking_attempt");
#######################################
############ Базы данных ##############
#######################################
// БД с аккаунтами пользователей
$db_u_host = "";    // Хост БД
$db_u_user = "";    // Имя юзера БД
$db_u_pass = "";    // Пароль юзера БД
$db_u_name = "";    // Имя БД
$db_u_charset = "cp1251";    // Кодировка БД
// БД с таблицами плагинов
$db_host = "";    // Хост БД
$db_user = "";    // Имя юзера БД
$db_pass = "";    // Пароль юзера БД
$db_name = "";    // Имя БД
$db_charset = "cp1251";    // Кодировка БД



#######################################
############## Таблицы ################
#######################################
$ankets_tbl = "fm_ankets";    // Таблицы с анкетами пользователей
$qq_tbl = "fm_qq";    // Таблица с вопросами анкеты
$votes_tbl = "fm_votes";    // Таблица с голосами пользователями 
$ban_tbl = array("banlist","name","type");    // Таблица с плагином банов. 1 - НАЗВАНИЕ ТАБЛИЦЫ, 2 - КОЛОНКА С ИМЕНЕМ, 3 - КОЛОНКА С ТИПОМ НАРУШЕНИЯ
$pex_inh = "permissions_inheritance";    // Таблица PEX: inheritance
$moder_group = "Moderator";    // Название группы PEX с правами модераторов
$pt_tbl = "playtime";    // Таблица с игровым временем



#######################################
############# Настройки ###############
#######################################
$active = true;    // Активно ли заполнение анкеты
$check_versions = true;    // Проверять, не устарела ли версия сркипта
$debug = true;    // Включить вывод ошибок. Выключить после полной настройки скрипта!
$secret_token = "cefb655b1850ef2571bb20f38fc054ee";    // Секретный ключ. ОБЯЗАТЕЛЬНО СМЕНИТЬ НА СВОЙ! Можно использовать любые символы
$language = "ru";    // Язык сообщений. На данные момент доступны: "ru";

$not_allowed_groups = array('moder', 'helper');    // Группы, которым нельзя заполнять анкету.

$allow_vote = true;    // Активна ли функция, где игроки могут голосовать "за" или "против" других игроков
$max_for_vote = 1;    // Максимальное количество голосований "За" для игрока
$max_against_vote = 1;    // Максимальное количество голосований "Против" для игрока

$recaptcha = true;    // Выводить ли капчу после формы
$publickey = "6Le2wewSAAAAAK-MG6rQPzNLWCHF2tEjnLPkcys2";    // Ваш публичный ключ, можно найти здесь: http://google.com/recaptcha
$privatekey = "6Le2wewSAAAAAMC9zoXa2qcxgAWFF0VkWmEZefXZ";    // Ваш приватный ключ reCAPTCHA

$if_banned = false;    // Блокировать подачу заявки, если игрок забанен.
$min_ptime = 0;    // Минимальное количество отиграного времени для подачи заявки. Указывать в минутах. (1 день=1440мин, 1 неделя=10080мин, 1 месяц=302400мин). Для отключения впишите 0
$min_regdate = 2;    // Минимальное количество дней назад, когда игрок зарегистрировался. Для отключения впишите 0
?>