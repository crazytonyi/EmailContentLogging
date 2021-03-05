<?php

/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

$mod_strings = array(
    'LBL_MODULE_NAME' => 'Проверка на съвместимостта',
    'LBL_MODULE_NAME_SINGULAR' => 'Проверка на съвместимостта',
    'LBL_MODULE_TITLE' => 'Проверка на съвместимостта',
    'LBL_LOGFILE' => 'Журнален файл',
    'LBL_BUCKET' => 'Кофа',
    'LBL_FLAG' => 'Флаг',
    'LBL_LOGMETA' => 'Запис на данни в журналния файл',
    'LBL_ERROR' => 'Грешка',

    // Failure handling in SugarBPM upgraders
    'LBL_PA_UNSERIALIZE_DATA_FAILURE' => 'Сериализираните данни не можаха да бъдат десериализирани',
    'LBL_PA_UNSERIALIZE_OBJECT_FAILURE' => 'Сериализираните данни не можаха да бъдат десериализирани, защото съдържат препратки към обекти или класове',

    'LBL_SCAN_101_LOG' => '%s има история в Студио',
    'LBL_SCAN_102_LOG' => '%s има разширения: %s',
    'LBL_SCAN_103_LOG' => '%s съдържа персонализирани дефиниции на променливи (vardefs)',
    'LBL_SCAN_104_LOG' => '%s съдържа персонализирани дефиниции на панели (layoutdefs)',
    'LBL_SCAN_105_LOG' => '%s съдържа персонализирани дефиниции на изгледи (viewdefs)',

    'LBL_SCAN_201_LOG' => '%s не е стандартен модул',

    'LBL_SCAN_301_LOG' => '%s ще бъде изпълняван в режим на съвместимост (BWC)',
    'LBL_SCAN_302_LOG' => 'Съществува неидентифициран файл - %s не е модул създаден през опцията Създаване на модули в Sugar',
    'LBL_SCAN_303_LOG' => 'Файлът %s е с неидентифицирана структура - %s не е модул създаден през опцията Създаване на модули на Sugar',
    'LBL_SCAN_304_LOG' => 'Неидентифициран файл %s - %s не е модул създаден през опцията Създаване на модули в Sugar',
    'LBL_SCAN_305_LOG' => 'Некоректни дефиниции на променливи (vardefs) - ключ %s, стойност %s',
    'LBL_SCAN_306_LOG' => 'Некоректни дефиниции на променливи (vardefs) - полето %s сочи към празен `модул`',
    'LBL_SCAN_307_LOG' => 'Некоректни дефиниции на променливи (vardefs) - връзката %s реферира към невалидна релация',
    'LBL_SCAN_308_LOG' => 'HTML функция в дефинициите на променливи (Vardef) %s',
    'LBL_SCAN_309_LOG' => 'Некоректна md5 сума за %s',
    'LBL_SCAN_310_LOG' => 'Неизвестен файл %s/%s',
    'LBL_SCAN_311_LOG' => 'HTML функция %s в дефинициите на променливи (Vardef)  в модул $module за поле %s',
    'LBL_SCAN_312_LOG' => 'Некоректни дефиниции на променливи (vardefs) -  невалиден тип на поле &#39;%s&#39;, модул - &#39;%s&#39;',
    'LBL_SCAN_313_LOG' => 'Открита е директория с разширения %s - %s не е модул създаден през опцията Създаване на модули в Sugar',
    'LBL_SCAN_314_LOG' => "Некоректни дефиниции на променливи (vardefs) -  полето &#39;%s&#39; ключовете &#39;%s&#39; съдържат несъвместими символи - &#39;{%s}&#39;",

    'LBL_SCAN_401_LOG' => 'Намерено включване на файловете на доставчика за файлове, които са били преместени в папка доставчик:'. PHP_EOL .'%s',
    'LBL_SCAN_402_LOG' => 'Некоректен модул %s - не фигурира в beanList и във файловата система',
    'LBL_SCAN_403_LOG' => 'Намерено включване на конкретни файлове на Sugar за:' . PHP_EOL .'%s',
    'LBL_SCAN_520_LOG' => 'Открита е допълнителна логика (logic hook) при настъпване на събитие after_ui_frame',
    'LBL_SCAN_521_LOG' => 'Открита е допълнителна логика (logic hook) при настъпване на събитие after_ui_footer',
//    'LBL_SCAN_405_LOG' => 'Incompatible Integration - %s %s',
    'LBL_SCAN_406_LOG' => '%s има персонализирани изгледи',
    'LBL_SCAN_407_LOG' => '%s има персонализирани изгледи',
    'LBL_SCAN_408_LOG' => 'В %s бяха открити компоненти за създаване, дефинирани от потребителя. Те ще бъдат копирани и променени за разширение на компонента за създаване вместо по време на ъпгейда',
    'LBL_SCAN_519_LOG' => 'Открита е директория с разширения  %s',
    'LBL_SCAN_518_LOG' => 'Открит е персонализиран код (customCode) %s в %s',
    'LBL_SCAN_410_LOG' => 'Максимален брой полета - открити са повече от %s полета (%s) в %s',
    'LBL_SCAN_522_LOG' => 'Открит е метод &#39;get_subpanel_data&#39; чрез &#39;функция:&#39; в %s',
    'LBL_SCAN_412_LOG' => 'Некоректна връзка от панел %s в %s',
    'LBL_SCAN_413_LOG' => 'Открит е неизвестен widget клас: %s за %s',
    'LBL_SCAN_414_LOG' => 'Неизвестни полета се управляват от CRYS-36. Няма да бъдат извършвани повече проверки',
    'LBL_SCAN_415_LOG' => 'Некоректен файл с допълнителна логика (logic hook) в %s: %s',
    'LBL_SCAN_523_LOG' => 'Предаване на параметър "by reference" във файла с допълнителна логка (logic hook) %s, функция %s',
    'LBL_SCAN_417_LOG' => 'Несъвместим модул %s',
    'LBL_SCAN_418_LOG' => 'Открит в панел с връзка към несъществуващ модул: %s',
    'LBL_SCAN_419_LOG' => 'Некоректни дефиниции на променливи (vardefs) - ключ %s, стойност %s',
    'LBL_SCAN_420_LOG' => 'Некоректни дефиниции на променливи (vardefs) - полето %s сочи към празен `модул`',
    'LBL_SCAN_421_LOG' => 'Некоректни дефиниции на променливи (vardefs) - връзката %s реферира към невалидна релация',
    'LBL_SCAN_422_LOG' => 'Модул %s има определение на друг модул %s във файла %s',
    'LBL_SCAN_525_LOG' => 'HTML функция в дефинициите на променливи (Vardef) %s',
    'LBL_SCAN_423_LOG' => 'Некоректно описание на полета в дефинициите на променливи (vardefs) - %s реферира към некоректно поле %s',
    'LBL_SCAN_424_LOG' => 'Открит е HTML код в %s на ред %s',
    'LBL_SCAN_425_LOG' => 'Открита е функция "echo" в %s на ред %s',
    'LBL_SCAN_426_LOG' => 'Открита е функция "print" в %s на ред %s',
    'LBL_SCAN_427_LOG' => 'Открита е функция "die/exit" в %s на ред %s',
    'LBL_SCAN_428_LOG' => 'Открита е функция "print_r" в %s на ред %s',
    'LBL_SCAN_429_LOG' => 'Открита е функция "var_dump" в %s на ред %s',
    'LBL_SCAN_430_LOG' => 'Открито е буфериране на изхода (%s) в %s на ред %s',
    'LBL_SCAN_451_LOG' => 'Кодът на Номера за оторизация беше изтрит, използвайте \IdMSugarAuthenticate, \IdMSAMLAuthenticate, \IdMLDAPAuthenticate вместо това. Файлове, които използват изтрит код: ' . PHP_EOL . '%s',
    'LBL_SCAN_524_LOG' => 'Vardef HTML function %s in %s module for field %s',
    'LBL_SCAN_432_LOG' => 'Некоректни дефиниции на променливи (vardefs) -  невалиден тип на поле &#39;%s&#39;, модул - &#39;%s&#39;',
    'LBL_SCAN_526_LOG' => "Некоректни дефиниции на променливи (vardefs) - ключовете &#39;%s&#39; на полето &#39;%s&#39; съдържат несъвместими символи - &#39;%s&#39;",
    'LBL_SCAN_527_LOG' => "Името на таблицата в %s не съвпада с атрибута на таблицата във файла %s/vardefs.php",
    'LBL_SCAN_528_LOG' => 'Полето %s на модула %s има некоректна стойност за визуализация по подразбиране',
    'LBL_SCAN_529_LOG' => '%s: %s във файл %s на ред %s',
    'LBL_SCAN_530_LOG' => 'Лиспва персонализираният файл: %s',
    'LBL_SCAN_531_LOG' => 'Неизползваем драйвер на база данни: %s',
    'LBL_SCAN_532_LOG' => 'Клас в %s извиква конструктора на основния запис на запасите си като %s::%s()',
    'LBL_SCAN_533_LOG' => 'Клас в %s извиква потребителския конструктор на основния запис като %s::%s()',
    'LBL_SCAN_534_LOG' => 'Неподдържан драйвер на база данни: %s',
    'LBL_SCAN_535_LOG' => 'Unsupported method call: %s() in %s on line %s',
    'LBL_SCAN_536_LOG' => 'Unsupported property access: $%s in %s on line %s',
    'LBL_SCAN_433_LOG' => 'Намерени файлове от програмата Elastic Search %s',
    'LBL_SCAN_434_LOG' => 'Установена употреба на функции на масиви в $_SESSION във файлове: %s',
    'LBL_SCAN_435_LOG' => 'Класът SugarSession е премахнат от API, вместо това използвайте Sugarcrm\Sugarcrm\Session\SessionStorage. Файлове с неизползваем код: ' . PHP_EOL . '%s',
    'LBL_SCAN_550_LOG' => 'Use of removed Sidecar app.date APIs in %s',
    'LBL_SCAN_551_LOG' => 'Use of removed Sidecar Bean APIs in %s',
    'LBL_SCAN_560_LOG' => 'custom/modules/Quotes/quotes.js МОЖЕ да съдържа персонализации, които да не са съвместими с нови Оферти.',
    'LBL_SCAN_561_LOG' => 'custom/modules/Quotes/EditView.js МОЖЕ да съдържа персонализации, които да не са съвместими с нови Оферти.',
    'LBL_SCAN_562_LOG' => 'Use of removed Sidecar app.view.invokeParent method in %s',
    'LBL_SCAN_570_LOG' => 'Невалиден статус и тип за електронните писма: status=%s, type=%s',
    'LBL_SCAN_571_LOG' => 'Отхвърленият файл има персонални настройки: %s',
    'LBL_SCAN_572_LOG' => 'Персонализираният файл има конфликт с името: %s',
    'LBL_SCAN_573_LOG' => 'Персонализираният помощен файл има конфликт с името: %s',
    'LBL_SCAN_574_LOG' => 'Съществува потребителска директория на панела с електронни писма: %s',
    'LBL_SCAN_575_LOG' => 'Панелът Контакти за писма трябва да бъде променен, за да използва панела за архивирани писма на контакти: %s',
    'LBL_SCAN_576_LOG' => 'Бяха открити персонализации на обложката в: `%s`. Окончателната обложка може да не се получи както сте очаквали, моля, проверете персонализациите на обложката си.',
    'LBL_SCAN_580_LOG' => 'Removed jQuery function(s) detected in: `%s`.',
    'LBL_SCAN_585_LOG' => 'Открито е забранено изречение в `%s`: %s',

    'LBL_SCAN_501_LOG' => 'Липсва файлът: %s',
    'LBL_SCAN_502_LOG' => 'md5 сумата не съвпада за %s, очаквана стойност %s',
    'LBL_SCAN_503_LOG' => 'Открит е персонализиран модул, чието име съвпада с името на нов модул в Sugar 7: %s',
    'LBL_SCAN_504_LOG' => 'Липсва дефиниран тип на поле в модул %s: %s',
    'LBL_SCAN_505_LOG' => 'Промяна на типа в %s за поле %s: от %s на %s',
    'LBL_SCAN_506_LOG' => '$this се използва в %s',
    'LBL_SCAN_507_LOG' => 'Некоректно описание на полета в дефинициите на променливи (vardefs) - %s реферира към некоректно поле %s',
    'LBL_SCAN_508_LOG' => 'Открит е HTML код в %s на ред %s',
    'LBL_SCAN_509_LOG' => 'Открита е функция "echo" в %s на ред %s',
    'LBL_SCAN_510_LOG' => 'Открита е функция "print" в %s на ред %s',
    'LBL_SCAN_511_LOG' => 'Открита е функция "die/exit" в %s на ред %s',
    'LBL_SCAN_512_LOG' => 'Открита е функция "print_r" в %s на ред %s',
    'LBL_SCAN_513_LOG' => 'Открита е функция "var_dump" в %s на ред %s',
    'LBL_SCAN_514_LOG' => 'Открито е буфериране на изхода (%s) в %s на ред %s',
    'LBL_SCAN_515_LOG' => 'Грешка в скрипта: %s',
    'LBL_SCAN_516_LOG' => 'Премахнати преди файлове се реферират в: %s',
    'LBL_SCAN_517_LOG' => 'Несъвместима интеграция - %s %s',
    'LBL_SCAN_540_LOG' => 'Рестартиране на несъвместими данни за интегриране - %s %s',
    'LBL_SCAN_541_LOG' => 'Невалидна сериализация на SugarBPM - %s невалидна(и) сериализация(и) в колона %s на таблица %s: %s.',
    'LBL_SCAN_542_LOG' => 'Неправилна употреба на поле на SugarBPM - %s невалидно(и) поле(та) е/са използвано(и) в %s.',
    'LBL_SCAN_545_LOG' => 'Частично заключена група полета на SugarBPM - поле %4$s е заключено в група %s в Дефиниция на процеса %s за модула %s.',
    'LBL_SCAN_546_LOG' => 'Потребителска конфигурация на TinyMCE в Базата от знания',
    'LBL_SCAN_547_LOG' => 'Използване на премахнатия`resetLoadFlag` подпис в %s',
    'LBL_SCAN_548_LOG' => 'Използване на отхвърления `initButtons` метод в %s',
    'LBL_SCAN_549_LOG' => 'Използване на премахнатия`getField` подпис в %s',
    'LBL_SCAN_552_LOG' => 'Use of removed Underscore APIs in %s',
    'LBL_SCAN_553_LOG' => 'Use of removed Sidecar Bean APIs in %s',
    'LBL_SCAN_554_LOG' => 'Sidecar controller %s extends from removed Sidecar controller',

    'LBL_SCAN_901_LOG' => 'Версията на инсталацията е вече актуализирана до Sugar 7',
    'LBL_SCAN_903_LOG' => 'Версията за актуализация не се поддържа. Моля инсталирайте модула за актуализация SugarUpgradeWizardPrereq-to-%s',
    'LBL_SCAN_904_LOG' => 'Намерени NULL стойности в стринговете moduleList. Файл: %s, Модули: %s',
    'LBL_SCAN_999_LOG' => 'Неидентифициран проблем. Моля консултирайте се със специалист по поддръжката.',

    'LBL_SCAN_101_TITLE' => '%s има история в Студио',
    'LBL_SCAN_102_TITLE' => '%s има разширения: %s',
    'LBL_SCAN_103_TITLE' => '%s съдържа персонализирани дефиниции на променливи (vardefs)',
    'LBL_SCAN_104_TITLE' => '%s съдържа персонализирани дефиниции на панели (layoutdefs)',
    'LBL_SCAN_105_TITLE' => '%s съдържа персонализирани дефиниции на изгледи (viewdefs)',

    'LBL_SCAN_201_TITLE' => '%s не е стандартен модул',

    'LBL_SCAN_301_TITLE' => '%s ще бъде изпълняван в режим на съвместимост (BWC)',
    'LBL_SCAN_302_TITLE' => 'Съществува неидентифициран файл - %s не е модул създаден през опцията Създаване на модули в Sugar',
    'LBL_SCAN_303_TITLE' => 'Файлът %s е с неидентифицирана структура - %s не е модул създаден през опцията Създаване на модули на Sugar',
    'LBL_SCAN_304_TITLE' => 'Неидентифициран файл %s - %s не е модул създаден през опцията Създаване на модули в Sugar',
    'LBL_SCAN_305_TITLE' => 'Некоректни дефиниции на променливи (vardefs) - ключ %s, стойност %s',
    'LBL_SCAN_306_TITLE' => 'Некоректни дефиниции на променливи (vardefs) - полето %s сочи към празен `модул`',
    'LBL_SCAN_307_TITLE' => 'Некоректни дефиниции на променливи (vardefs) - връзката %s реферира към невалидна релация',
    'LBL_SCAN_308_TITLE' => 'HTML функция в дефинициите на променливи (Vardef) %s',
    'LBL_SCAN_309_TITLE' => 'Некоректна md5 сума за %s',
    'LBL_SCAN_310_TITLE' => 'Неизвестен файл %s/%s',
    'LBL_SCAN_311_TITLE' => 'HTML функция %s в дефинициите на променливи (Vardef)  в модул $module за поле %s',
    'LBL_SCAN_312_TITLE' => 'Некоректни дефиниции на променливи (vardefs) -  невалиден тип на поле &#39;%s&#39;, модул - &#39;%s&#39;',
    'LBL_SCAN_313_TITLE' => 'Открита е директория с разширения %s - %s не е модул създаден през опцията Създаване на модули в Sugar',

    'LBL_SCAN_401_TITLE' => 'Намерено включване на файловете на доставчика за файлове, които са били преместени в папка доставчик:'. PHP_EOL .'%s',
    'LBL_SCAN_402_TITLE' => 'Некоректен модул %s - не фигурира в beanList и във файловата система',
    'LBL_SCAN_403_TITLE' => 'Намерено включване на конкретни файлове на Sugar за:' . PHP_EOL .'%s',
    'LBL_SCAN_520_TITLE' => 'Открита е допълнителна логика (logic hook) при настъпване на събитие after_ui_frame',
    'LBL_SCAN_521_TITLE' => 'Открита е допълнителна логика (logic hook) при настъпване на събитие after_ui_footer',
//    'LBL_SCAN_405_TITLE' => 'Incompatible Integration - %s %s',
    'LBL_SCAN_406_TITLE' => '%s има персонализирани изгледи',
    'LBL_SCAN_407_TITLE' => '%s има персонализирани изгледи',
    'LBL_SCAN_408_TITLE' => 'Намерени са персонализирани компоненти за създаване на действия, които вече не се поддържат.',
    'LBL_SCAN_519_TITLE' => 'Открита е директория с разширения  %s',
    'LBL_SCAN_518_TITLE' => 'Открит е персонализиран код (customCode) %s в %s',
    'LBL_SCAN_410_TITLE' => 'Максимален брой полета - открити са повече от %s полета (%s) в %s',
    'LBL_SCAN_522_TITLE' => 'Открит е метод &#39;get_subpanel_data&#39; чрез &#39;функция:&#39; в %s',
    'LBL_SCAN_412_TITLE' => 'Некоректна връзка от панел %s в %s',
    'LBL_SCAN_413_TITLE' => 'Открит е неизвестен widget клас: %s за %s',
    'LBL_SCAN_414_TITLE' => 'Неизвестни полета се управляват от CRYS-36. Няма да бъдат извършвани повече проверки',
    'LBL_SCAN_415_TITLE' => 'Некоректен файл с допълнителна логика (logic hook) в %s: %s',
    'LBL_SCAN_523_TITLE' => 'Предаване на параметър "by reference" във файла с допълнителна логка (logic hook) %s, функция %s',
    'LBL_SCAN_417_TITLE' => 'Несъвместим модул %s',
    'LBL_SCAN_418_TITLE' => 'Открит в панел с връзка към несъществуващ модул: %s',
    'LBL_SCAN_419_TITLE' => 'Некоректни дефиниции на променливи (vardefs) - ключ %s, стойност %s',
    'LBL_SCAN_420_TITLE' => 'Некоректни дефиниции на променливи (vardefs) - полето %s сочи към празен `модул`',
    'LBL_SCAN_421_TITLE' => 'Некоректни дефиниции на променливи (vardefs) - връзката %s реферира към невалидна релация',
    'LBL_SCAN_422_TITLE' => 'Модул %s има определение на друг модул',
    'LBL_SCAN_525_TITLE' => 'HTML функция в дефинициите на променливи (Vardef) %s',
    'LBL_SCAN_423_TITLE' => 'Некоректно описание на полета в дефинициите на променливи (vardefs) - %s реферира към некоректно поле %s',
    'LBL_SCAN_424_TITLE' => 'Открит е HTML код в %s на ред %s',
    'LBL_SCAN_425_TITLE' => 'Открита е функция "echo" в %s на ред %s',
    'LBL_SCAN_426_TITLE' => 'Открита е функция "print" в %s на ред %s',
    'LBL_SCAN_427_TITLE' => 'Открита е функция "die/exit" в %s на ред %s',
    'LBL_SCAN_428_TITLE' => 'Открита е функция "print_r" в %s на ред %s',
    'LBL_SCAN_429_TITLE' => 'Открита е функция "var_dump" в %s на ред %s',
    'LBL_SCAN_430_TITLE' => 'Открито е буфериране на изхода (%s) в %s на ред %s',
    'LBL_SCAN_451_TITLE' => 'Кодът на Номера за оторизация беше изтрит, използвайте \IdMSugarAuthenticate, \IdMSAMLAuthenticate, \IdMLDAPAuthenticate вместо това. Файлове, които използват изтрит код: ' . PHP_EOL . '%s',
    'LBL_SCAN_524_TITLE' => 'Vardef HTML function %s in %s module for field %s',
    'LBL_SCAN_432_TITLE' => 'Некоректни дефиниции на променливи (vardefs) -  невалиден тип на поле &#39;%s&#39;, модул - &#39;%s&#39;',
    'LBL_SCAN_433_TITLE' => 'Намерени файлове от програмата Elastic Search %s',
    'LBL_SCAN_434_TITLE' => 'Установена употреба на функции на масиви в $_SESSION във файлове: %s',
    'LBL_SCAN_435_TITLE' => 'Намерена е употреба на отстранен клас от SugarSession',
    'LBL_SCAN_550_TITLE' => 'Use of removed Sidecar app.date APIs in %s',
    'LBL_SCAN_551_TITLE' => 'Use of removed Sidecar Bean APIs in %s',

    'LBL_SCAN_501_TITLE' => 'Липсва файлът: %s',
    'LBL_SCAN_502_TITLE' => 'md5 сумата не съвпада за %s, очаквана стойност %s',
    'LBL_SCAN_503_TITLE' => 'Открит е персонализиран модул, чието име съвпада с името на нов модул в Sugar 7: %s',
    'LBL_SCAN_504_TITLE' => 'Липсва дефиниран тип на поле в модул %s: %s',
    'LBL_SCAN_505_TITLE' => 'Промяна на типа в %s за поле %s: от %s на %s',
    'LBL_SCAN_506_TITLE' => '$this се използва в %s',
    'LBL_SCAN_507_TITLE' => 'Некоректно описание на полета в дефинициите на променливи (vardefs) - %s реферира към некоректно поле %s',
    'LBL_SCAN_508_TITLE' => 'Открит е HTML код в %s на ред %s',
    'LBL_SCAN_509_TITLE' => 'Открита е функция "echo" в %s на ред %s',
    'LBL_SCAN_510_TITLE' => 'Открита е функция "print" в %s на ред %s',
    'LBL_SCAN_511_TITLE' => 'Открита е функция "die/exit" в %s на ред %s',
    'LBL_SCAN_512_TITLE' => 'Открита е функция "print_r" в %s на ред %s',
    'LBL_SCAN_513_TITLE' => 'Открита е функция "var_dump" в %s на ред %s',
    'LBL_SCAN_514_TITLE' => 'Открито е буфериране на изхода (%s) в %s на ред %s',
    'LBL_SCAN_515_TITLE' => 'Грешка в скрипта: %s',
    'LBL_SCAN_517_TITLE' => 'Несъвместима интеграция - %s %s',
    'LBL_SCAN_526_TITLE' => "Некоректни дефиниции на променливи (vardefs) - ключовете &#39;%s&#39; на полето &#39;%s&#39; съдържат несъвместими символи - &#39;%s&#39;",
    'LBL_SCAN_528_TITLE' => 'Полето %s на модула %s има некоректна стойност за визуализация по подразбиране',
    'LBL_SCAN_529_TITLE' => '%s: %s във файл %s на ред %s',
    'LBL_SCAN_530_TITLE' => 'Лиспва персонализираният файл: %s',
    'LBL_SCAN_531_TITLE' => 'Неизползваем драйвер на база данни: %s',
    'LBL_SCAN_532_TITLE' => 'Обаждане на PHP4 конструктора на основния запис на запасите в %s',
    'LBL_SCAN_533_TITLE' => 'Обаждане на потребителския PHP4 конструктор на основния запис в %s',
    'LBL_SCAN_534_TITLE' => 'Неподдържан драйвер на база данни: %s',
    'LBL_SCAN_535_TITLE' => 'Unsupported method call: %s()',
    'LBL_SCAN_536_TITLE' => 'Unsupported property access: $%s',
    'LBL_SCAN_540_TITLE' => 'Рестартиране на несъвместими данни за интегриране - %s %s',
    'LBL_SCAN_541_TITLE' => 'Невалидна сериализация на SugarBPM - %s невалидна(и) сериализация(и) в колона %s на таблица %s: %s',
    'LBL_SCAN_542_TITLE' => 'Неправилна употреба на поле на SugarBPM - %s невалидно(и) поле(та) е/са използвано(и) в %s.',
    'LBL_SCAN_545_TITLE' => 'Частично заключена група полета на SugarBPM - модул %3$s: група %s е частично заключена в Дефиниция на процеса %s.',
    'LBL_SCAN_546_TITLE' => 'Потребителска конфигурация на TinyMCE в Базата от знания',
    'LBL_SCAN_547_TITLE' => 'Използване на премахнатия`resetLoadFlag` подпис в %s',
    'LBL_SCAN_548_TITLE' => 'Използване на отхвърления `initButtons` метод в %s',
    'LBL_SCAN_549_TITLE' => 'Използване на премахнатия`getField` подпис в %s',
    'LBL_SCAN_552_TITLE' => 'Use of removed Underscore APIs in %s',
    'LBL_SCAN_553_TITLE' => 'Use of removed Sidecar Bean APIs in %s',
    'LBL_SCAN_554_TITLE' => 'Sidecar controller %s extends from removed Sidecar controller',
    'LBL_SCAN_570_TITLE' => 'В електронните писма са открити неочаквани стойности',
    'LBL_SCAN_571_TITLE' => 'Персонализираният файл съдържа код, който е отхвърлен',
    'LBL_SCAN_572_TITLE' => 'Има конфликт с името на персонализиран файл',
    'LBL_SCAN_573_TITLE' => 'Има конфликт с името на персонализиран помощен файл',
    'LBL_SCAN_574_TITLE' => 'Има персонализации на панела на писмата',
    'LBL_SCAN_575_TITLE' => 'Има персонализации на панела на контактите в писмата',
    'LBL_SCAN_576_TITLE' => 'Бяха открити персонализации на обложката',
    'LBL_SCAN_580_TITLE' => 'Removed jQuery function(s) detected',
    'LBL_SCAN_585_TITLE' => 'Открити са забранени изречения',

    'LBL_SCAN_901_TITLE' => 'Версията на инсталацията е вече актуализирана до Sugar 7',
    'LBL_SCAN_903_TITLE' => 'Версията за актуализация не се поддържа',
    'LBL_SCAN_904_TITLE' => 'Намерени NULL стойности в стринговете moduleList',
    'LBL_SCAN_999_TITLE' => 'Неидентифициран проблем. Моля консултирайте се със специалист по поддръжката.',

    'LBL_SCAN_101_DESCR' => 'В инсталацията са открити персонализации, извършени през Студио. Не очакваме проблеми с тези персонализации и те са прехвърлени в Sugar7',
    'LBL_SCAN_102_DESCR' => 'В инсталацията са открити персонализации, извършени през Студио. Не очакваме проблеми с тези персонализации и те са прехвърлени в Sugar7',
    'LBL_SCAN_103_DESCR' => 'В инсталацията са открити персонализации, извършени през Студио. Не очакваме проблеми с тези персонализации и те са прехвърлени в Sugar7',
    'LBL_SCAN_104_DESCR' => 'В инсталацията са открити персонализации, извършени през Студио. Не очакваме проблеми с тези персонализации и те са прехвърлени в Sugar7',
    'LBL_SCAN_105_DESCR' => 'В инсталацията са открити персонализации, извършени през Студио. Не очакваме проблеми с тези персонализации и те са прехвърлени в Sugar7',

    'LBL_SCAN_201_DESCR' => 'В инсталацията са открити персонализации, извършени през Студио. Не очакваме проблеми с тези персонализации и те са прехвърлени в Sugar7',

    'LBL_SCAN_301_DESCR' => 'Открити бяха персонализации, които не са мигрирани в Sugar7. Модулът (%s) ще продължи да бъде достъпен, но ще бъде изпълняван в режим на съвместимост.',
    'LBL_SCAN_302_DESCR' => 'Открити са неизвестни файлове, които не са мигрирани в Sugar7. Модулът (%s) ще продължи да бъде достъпен, но ще бъде изпълняван в режим на съвместимост в Sugar7.',
    'LBL_SCAN_303_DESCR' => 'Открити файлове с дефиниции на форми, които не са мигрирани в Sugar7. Модулът (%s) ще продължи да бъде достъпен, но ще бъде изпълняван в режим на съвместимост в Sugar7.',
    'LBL_SCAN_304_DESCR' => 'Открити са неизвестни файлове  (%s), които не са мигрирани в Sugar7. Модулът (%s) ще продължи да бъде достъпен, но ще бъде изпълняван в режим на съвместимост в Sugar7.',
    'LBL_SCAN_305_DESCR' => 'Открити са некоректни дефиниции на променливи (%s: %s), които не са мигрирани в Sugar7. Персонализациите ще продължат да бъдат достъпни, но ще бъдат изпълнявани в режим на съвместимост в Sugar7.',
    'LBL_SCAN_306_DESCR' => 'Открити са некоректни дефиниции с променливи (vardefs), които не са актуализирани до Sugar7. Полето (%s) реферира към празен `модул`. Персонализацията ще продължи да бъде достъпна, но ще бъде изпълнява в режим на съвместимост.',
    'LBL_SCAN_307_DESCR' => 'Открити бяха некоректни дефиниции с променливи (vardefs), които не са мигрирани в Sugar7. Връзката (%s) реферира невалидна релация. Персонализацията ще продължи да бъде достъпна, но ще бъде изпълнява в режим на съвместимост.',
    'LBL_SCAN_308_DESCR' => 'Открити бяха персонализации, които не са мигрирани в Sugar7. Модулът (%s) ще продължи да бъде достъпен, но ще бъде изпълняван в режим на съвместимост.',
    'LBL_SCAN_309_DESCR' => 'md5 сумата не съвпада за %s не съвпада с тази на оригиналния файл. Файлът вероятно е бил модифициран в последствие и не е актуализиран до Sugar7',
    'LBL_SCAN_310_DESCR' => 'Открити бяха неизвестни файлове с изгледи, които не са мигрирани в Sugar7. Модулът (%s) ще продължи да бъде достъпен, но ще бъде изпълняван в режим на съвместимост.',
    'LBL_SCAN_311_DESCR' => 'Открити бяха персонализации, които не са мигрирани в Sugar7. Модулът (%s) ще продължи да бъде достъпен, но ще бъде изпълняван в режим на съвместимост.',
    'LBL_SCAN_312_DESCR' => 'Открити бяха некоректни дефиниции с променливи (vardefs), които не са мигрирани в Sugar7. Некоректна дефиниция: типът на полето &#39;name&#39; е невалиден &#39;%s&#39; за модул &#39;%s&#39;.  Персонализацията ще продължи да бъде достъпна, но ще бъде изпълнява в режим на съвместимост.',
    'LBL_SCAN_313_DESCR' => 'Открита е директория за разширения -  %s не е модул създаден чрез опцията Създаване на модули в Sugar. Модулът ще продължи да бъде достъпен, но само в режим на съвместимост.',

    'LBL_SCAN_401_DESCR' => 'Персонализиран файл реферира файл, който е преместен в папка vendor. Ние опитахме да приложим коригиращо действие и не се налагат допълнителни действия.',
    'LBL_SCAN_402_DESCR' => 'Некоректен модул %s - не фигурира в beanList и във файловата система',
    'LBL_SCAN_403_DESCR' => 'Някои от файловете на Sugar са с променено местоположение в Sugar7. Ние трябва да коригираме пътищата при тяхното използване в системата.',
    'LBL_SCAN_520_DESCR' => 'Тази допълнителна логика ( logic hook) не се поддръжа в Sugar 7',
    'LBL_SCAN_521_DESCR' => 'Тази допълнителна логика ( logic hook) не се поддръжа в Sugar 7',
//    'LBL_SCAN_405_DESCR' => 'Package detected which has been blacklisted as not supported in Sugar 7',
    'LBL_SCAN_406_DESCR' => 'СтандартенSugar модул съдържа персонализирани изгледи в custom/modules/%s/views',
    'LBL_SCAN_407_DESCR' => 'Стандартен Sugar модул съдържа персонализирани изгледи в custom/modules/%s/views',
    'LBL_SCAN_408_DESCR' => 'В %s бяха открити компоненти за създаване, дефинирани от потребителя. Те ще бъдат копирани и променени за разширение на компонента за създаване вместо по време на ъпгейда',
    'LBL_SCAN_519_DESCR' => 'Стандартен Sugar модул съдържа едно от разширенията, които не се поддържат при актуализация на версията. Такива разширения са персонализирано рутиране, контрол на достъпа, Javascript и др.',
    'LBL_SCAN_518_DESCR' => 'Дефинициите на променливи (verdefs) съдържат customCode, който знаем как да бъде конвертиран',
    'LBL_SCAN_410_DESCR' => 'Прекалено много полета в изгледа',
    'LBL_SCAN_522_DESCR' => 'Данните в панела се взимат с помощта на функция. Тази функционалност все още не се поддържа при актуализация на версията.',
    'LBL_SCAN_412_DESCR' => 'Панелът реферира към връзка, която не съществува или не е коректно дефинирана.',
    'LBL_SCAN_413_DESCR' => 'Полето реферира към "widget" клас, за който не съществува кореспондиращ файл с описание на класа.',
    'LBL_SCAN_414_DESCR' => 'Неизвестни полета се управляват от CRYS-36. Няма да бъдат извършвани повече проверки',
    'LBL_SCAN_415_DESCR' => 'Допълнителна логика (logic hook) реферира файл, който не съществува.',
    'LBL_SCAN_523_DESCR' => 'Файл с допълнителна логика използва предаване на параметър "by reference", което може да доведе до визуализация на съобщения за грешка (и по този начин да нарушу функционалността на протокола REST)',
    'LBL_SCAN_417_DESCR' => 'Открити са полета тип "Feeds" или "iFrame", който не се поддържат повече.',
    'LBL_SCAN_418_DESCR' => 'Панелът реферира към модул, който не съществува',
    'LBL_SCAN_419_DESCR' => 'Ключ в дефинициите с променливи (vardefs) не съответства на посоченото име',
    'LBL_SCAN_420_DESCR' => 'Дефинициите с променливи (vardefs) съдържат полета рефериращи релация, която не може да заредена коректно',
    'LBL_SCAN_421_DESCR' => 'Дефинициите с променливи (vardefs) съдържат връзка към поле, което не може да бъде заредено коректно',
    'LBL_SCAN_422_DESCR' => 'Модул %s има определение на друг модул %s във файла %s',
    'LBL_SCAN_525_DESCR' => 'Дефинициите с променливи (vardefs) съдържа описание на поле, чиято стойност се изчислява чрез HTML функция. Тази функционалност не се поддържа в Sugar7',
    'LBL_SCAN_423_DESCR' => 'Дефинициите с променливи (vardefs) съдържа описание на поле, състоящо се от няколко полета. Едно от съставляващите полета не съществува.',
    'LBL_SCAN_424_DESCR' => 'Файл съдържа HTML код (inline HTML)',
    'LBL_SCAN_425_DESCR' => 'Кодът съдържа функция за генериране на изход',
    'LBL_SCAN_426_DESCR' => 'Кодът съдържа функция за генериране на изход',
    'LBL_SCAN_427_DESCR' => 'Кодът съдържа функция за генериране на изход',
    'LBL_SCAN_428_DESCR' => 'Кодът съдържа функция за генериране на изход. Имайте в предвид, че print_r(..., true) са разрешени.',
    'LBL_SCAN_429_DESCR' => 'Кодът съдържа функция за генериране на изход',
    'LBL_SCAN_430_DESCR' => 'Кодът съдържа функция за генериране на изход',
    'LBL_SCAN_451_DESCR' => 'Кодът на Номера за оторизация беше изтрит, използвайте \IdMSugarAuthenticate, \IdMSAMLAuthenticate, \IdMLDAPAuthenticate вместо това',
    'LBL_SCAN_524_DESCR' => 'Поле е дефинирано като резултат от изпълнение на функция, която генерира HTML код и не може да бъде автоматично преобразувано (знаем как да конвертираме полета като електронна поща и валута)',
    'LBL_SCAN_432_DESCR' => 'Поле &#39;name&#39; е с тип различен от name, fullname, varchar или id',
    'LBL_SCAN_433_DESCR' => 'Намерени файлове от програмата Elastic Search %s',
    'LBL_SCAN_434_DESCR' => 'Установена употреба на функции на масиви в $_SESSION във файлове: %s',
    'LBL_SCAN_550_DESCR' => 'Use of removed Sidecar app.date APIs in %s, this code will be migrated by upgrade scripts',
    'LBL_SCAN_551_DESCR' => 'Use of removed Sidecar Bean APIs in %s, this code will be migrated by upgrade scripts',

    'LBL_SCAN_501_DESCR' => 'Основен файл на системата не съществува в тази инсталация',
    'LBL_SCAN_502_DESCR' => 'Основен файл на системата е бил модифициран в тази инсталация',
    'LBL_SCAN_503_DESCR' => 'Персонализиран модул има име, което съвпада с името на един новите Sugar модули',
    'LBL_SCAN_504_DESCR' => 'Описание на поле в дефинициите на променливи (vardefs) няма посочен тип',
    'LBL_SCAN_505_DESCR' => 'Типът на поле е сменен от "Non-blob" на "blob". Операцията не е разрешена защото полетата от тип "blob" не могат да бъдат индексирани и е възможно да съществуват филтри, които разчитат полето да бъде индексирано.',
    'LBL_SCAN_506_DESCR' => 'Променливата $this се използва с файловете с метаданни. Тя няма да връща коректен резултат, защото в Sugar7 тези данни се зареждат в различен контекст.',
    'LBL_SCAN_507_DESCR' => 'Дефинициите с променливи (vardefs) съдържа описание на поле, състоящо се от няколко полета. Едно от съставляващите полета не съществува.',
    'LBL_SCAN_508_DESCR' => 'Файл съдържа HTML код (inline HTML)',
    'LBL_SCAN_509_DESCR' => 'Кодът съдържа функция за генериране на изход',
    'LBL_SCAN_510_DESCR' => 'Кодът съдържа функция за генериране на изход',
    'LBL_SCAN_511_DESCR' => 'Кодът съдържа функция за генериране на изход',
    'LBL_SCAN_512_DESCR' => 'Кодът съдържа функция за генериране на изход. Имайте в предвид, че print_r(..., true) са разрешени.',
    'LBL_SCAN_513_DESCR' => 'Кодът съдържа функция за генериране на изход',
    'LBL_SCAN_514_DESCR' => 'Кодът съдържа функция за генериране на изход',
    'LBL_SCAN_515_DESCR' => 'Възникна грешка при изпълнение на скрипта з апроверка. Това означава, че файлът instaScannerMeta.phpnce вероятно съдържа некоректен PHP код, който скриптът се е опитал да изпълни.',
    'LBL_SCAN_517_DESCR' => 'Открит е инсталиран пакет, който не се поддържа в Sugar 7',
    'LBL_SCAN_526_DESCR' => "Този списък съдържа стойности на имена, които не позволяват актуализация на версията.",
    'LBL_SCAN_528_DESCR' => 'Поле от тип Дата/Дата&Час/Време има некоректна стойност за визуализация по подразбиране като -няма-',
    'LBL_SCAN_529_DESCR' => 'Могат да възникнат PHP грешки при откриване на некоректен синтаксис или проблеми при изпълнението на кода.',
    'LBL_SCAN_530_DESCR' => 'Един от персонализираните файлове не е наличен в инсталацията, но се използва в кода.',
    'LBL_SCAN_531_DESCR' => '%s Драйверът на базата данни е неизползваем. Моля, помислете за използването на %s вместо него.',
    'LBL_SCAN_532_DESCR' => 'Клас, обявен в %s, извиква конструктора на основния запис на запасите си като %s::%s()',
    'LBL_SCAN_533_DESCR' => 'Клас, обявен в %s, извиква потребителския конструктор на основния си запис като %s::%s()',
    'LBL_SCAN_534_DESCR' => '%s Драйверът на базата данни не се поддържа повече. Моля, помислете за използването на %s вместо него.',
    'LBL_SCAN_535_DESCR' => 'A call to unsupported method %s() found in %s on line %d',
    'LBL_SCAN_536_DESCR' => 'Access to an unsupported property $%s found in %s on line %d',
    'LBL_SCAN_540_DESCR' => 'Открит е пакет, който е в черния списък, тъй като не се поддържа в целевата версия на Sugar.  Тези пакети трябва да бъдат деинсталирани И изтрити преди актуализиране.  Моля, обърнете внимание, че деинсталирането на тези пакети ще премахне таблиците и данните, генерирани от пакета, както и използването на модулите на пакетите.',
    'LBL_SCAN_541_DESCR' => 'В таблиците Ви за Управление на процеса са открити данни, които не могат да се десериализират или конвертират',
    'LBL_SCAN_542_DESCR' => 'В Бизнес правилата за управление на процеса и/или Действията Ви са открити невалидни полета. Те трябва да се отстранят от Бизнес правилата и/или Действията, за да може да се направи ъпгейд.',
    'LBL_SCAN_545_DESCR' => 'Груповото поле е частично заключено от Дефиницията на процеса. Тези полета трябва да се отключат в Дефиницията на процеса, за да може актуализацията да продължи.',
    'LBL_SCAN_546_DESCR' => 'Невъзможност за прехвърляне на потребителската конфигурация на TinyMCE в Базата от знания. '
        . 'Параметърът "tinyConfig" в %s файла ще бъде изтрит. '
        . 'Ако имате персонализации на TinyMCE трябва да ги съхраните преди актуализацията '
        . 'и да ги добавите ръчно след актуализацията.',
    'LBL_SCAN_547_DESCR' => 'Използване на премахнатия`resetLoadFlag` подпис в %s',
    'LBL_SCAN_548_DESCR' => 'Използване на отхвърления `initButtons` метод в %s',
    'LBL_SCAN_549_DESCR' => 'Използване на премахнатия`getField` подпис в %s',
    'LBL_SCAN_552_DESCR' => 'Use of removed Underscore APIs in %s',
    'LBL_SCAN_553_DESCR' => 'Use of removed Sidecar Bean APIs in %s',
    'LBL_SCAN_554_DESCR' => 'Sidecar controller %s extends from removed Sidecar controller',

    'LBL_SCAN_901_DESCR' => 'Версията на инсталацията е вече актуализирана до Sugar 7',
    'LBL_SCAN_903_DESCR' => 'Версията за актуализация не се поддържа. Моля инсталирайте модула за актуализация SugarUpgradeWizardPrereq-to-%s',
    'LBL_SCAN_904_DESCR' => 'Файл: %s, Модули: %s',
    'LBL_SCAN_999_DESCR' => 'Неидентифициран проблем. Моля консултирайте се със специалист по поддръжката.',

    'LBL_SCAN_577_TITLE' => 'Несъвместимо съпоставяне на база данни',
    'LBL_SCAN_577_LOG' => "Съпоставянето '%s' е несъвместимо с набора знаци на '%s'",
    'LBL_SCAN_577_DESCR' => "Изберете друго сравняване в регионалните настройки или премахнете конфигурацията 'dbconfigoption.collation', за да използвате сравняването по подразбиране.",

    'LBL_SCAN_578_TITLE' => 'Неуспешно премахване на таблицата на временната база данни: %s',
    'LBL_SCAN_578_LOG' => 'Неуспешно премахване на таблицата на временната база данни: %s',
    'LBL_SCAN_578_DESCR' => 'Временна таблица, създадена за проверка на преобразуването на набора от знаци не беше изтрита по време на актуализацията и ще трябва да се изтрие ръчно',

    'LBL_SCAN_579_TITLE' => 'Неуспешно изпълнение на преобразуването на набора от знаци / съпоставянето: (%s) в таблица: %s',
    'LBL_SCAN_579_LOG' => 'Неуспешно изпълнение на преобразуването на набора от знаци / съпоставянето: (%s) в таблица: %s',
);
