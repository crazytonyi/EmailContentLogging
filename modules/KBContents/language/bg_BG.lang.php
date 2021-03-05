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
$mod_strings = array (
    // Dashboard Names
    'LBL_KBCONTENTS_LIST_DASHBOARD' => 'Електронно табло със списък на Базата от знания',
    'LBL_KBCONTENTS_RECORD_DASHBOARD' => 'Електронно табло със запис на Базата от знания',
    'LBL_KBCONTENTS_FOCUS_DRAWER_DASHBOARD' => 'Чекмедже Фокус на база знания',

    'LBL_MODULE_NAME' => 'База от знания',
    'LBL_MODULE_NAME_SINGULAR' => 'Материал',
    'LBL_MODULE_TITLE' => 'Материал',
    'LNK_NEW_ARTICLE' => 'Добавяне на материал',
    'LNK_LIST_ARTICLES' => 'Списък със статии',
    'LNK_KNOWLEDGE_BASE_ADMIN_MENU' => 'Настройки',
    'LBL_EDIT_LANGUAGES' => 'Редактиране на езици',
    'LBL_ADMIN_LABEL_LANGUAGES' => 'Налични езици',
    'LBL_CONFIG_LANGUAGES_TITLE' => 'Налични езици',
    'LBL_CONFIG_LANGUAGES_TEXT' => 'Конфигуриране на езици, които ще бъдат използвани в модула Бази от знания.',
    'LBL_CONFIG_LANGUAGES_LABEL_KEY' => 'Езиков код',
    'LBL_CONFIG_LANGUAGES_LABEL_NAME' => 'Езиков етикет',
    'ERR_CONFIG_LANGUAGES_DUPLICATE' => 'Не е позволено да се добавя език с ключа, който дублира съществуващия.',
    'ERR_CONFIG_LANGUAGES_EMPTY_KEY' => 'The Language Code field is empty, please enter values before saving.',
    'ERR_CONFIG_LANGUAGES_EMPTY_VALUE' => 'The Language Label field is empty, please enter values before saving.',
    'LBL_SET_ITEM_PRIMARY' => 'Задай стойността като Основна',
    'LBL_ITEM_REMOVE' => 'Премахни записите',
    'LBL_ITEM_ADD' => 'Добави записи',
    'LBL_MODULE_ID'=> 'Съдържание на Базата от знания',
    'LBL_DOCUMENT_REVISION_ID' => 'Идентификатор на Ревизията',
    'LBL_DOCUMENT_REVISION' => 'Ревизия',
    'LBL_NUMBER' => 'Номер',
    'LBL_TEXT_BODY' => 'Съдържание',
    'LBL_LANG' => 'Език',
    'LBL_PUBLISH_DATE' => 'Публикувано на',
    'LBL_EXP_DATE' => 'Валидно до',
    'LBL_DOC_ID' => 'Документ',
    'LBL_APPROVED' => 'Одобрен',
    'LBL_REVISION' => 'Ревизия',
    'LBL_ACTIVE_REV' => 'Активна ревизия',
    'LBL_IS_EXTERNAL' => 'Външен материал',
    'LBL_KBDOCUMENT_ID' => 'Идентификатор на KBDocument',
    'LBL_KBDOCUMENTS' => 'Документи в Базата от знания',
    'LBL_KBDOCUMENT' => 'Документ в Базата от знания',
    'LBL_KBARTICLE' => 'Материал',
    'LBL_KBARTICLES' => 'Материали',
    'LBL_KBARTICLE_ID' => 'Идентификатор на материала',
    'LBL_USEFUL' => 'Полезен',
    'LBL_NOT_USEFUL' => 'Безполезен',
    'LBL_RATING' => 'Рейтинг',
    'LBL_VIEWED_COUNT' => 'View Count',
    'LBL_CATEGORIES' => 'Категории на базата знания',
    'LBL_CATEGORY_NAME' => 'Категория',
    'LBL_USEFULNESS' => 'Полезност',
    'LBL_CATEGORY_ID' => 'Идентификатор на категория',
    'LBL_KBSAPPROVERS' => 'Одобрено от',
    'LBL_KBSAPPROVER_ID' => 'Одобрен от',
    'LBL_KBSAPPROVER' => 'Одобрен от',
    'LBL_KBSCASES' => 'Казуси',
    'LBL_KBSCASE_ID' => 'Казус по темата',
    'LBL_KBSCASE' => 'Казус по темата',
    'LBL_MORE_MOST_USEFUL_ARTICLES' => 'Още полезни статии, публикувани в Базата от знания...',
    'LBL_KBSLOCALIZATIONS' => 'Локализации',
    'LBL_LOCALIZATIONS_SUBPANEL_TITLE' => 'Локализации',
    'LBL_KBSREVISIONS' => 'Ревизии',
    'LBL_REVISIONS_SUBPANEL_TITLE' => 'Ревизии',
    'LBL_LISTVIEW_FILTER_ALL' => 'Всички материали',
    'LBL_LISTVIEW_FILTER_MY' => 'Моите материали',
    'LBL_CREATE_LOCALIZATION_BUTTON_LABEL' => 'Създай Локализация',
    'LBL_CREATE_REVISION_BUTTON_LABEL' => 'Създай Ревизия',
    'LBL_CANNOT_CREATE_LOCALIZATION' =>
        'Не може да се създаде нова локализация, тъй като съществува версия на локализацията за всички налични езици.',
    'LBL_SPECIFY_PUBLISH_DATE' => 'Schedule this article to be published by specifying the Publish Date. Do you wish to continue without updating a Publish Date?',
    'LBL_MODIFY_EXP_DATE_LOW' => 'Крайният срок е преди Датата на публикуване. Желаете ли да продължите без да променяте Крайния срок?',
    'LBL_PANEL_INMORELESS' => 'Полезност',
    'LBL_MORE_OTHER_LANGUAGES' => 'Още езици...',
    'EXCEPTION_VOTE_USEFULNESS_NOT_AUTHORIZED' => 'Не сте оторизиран да гласувате полезно/безполезно {moduleName}. Свържете се с администратор ако се нуждаете от достъп.',
    'LNK_NEW_KBCONTENT_TEMPLATE' => 'Създай Шаблон',
    'LNK_LIST_KBCONTENT_TEMPLATES' => 'Разгледай Шаблоните',
    'LNK_LIST_KBCATEGORIES' => 'Преглед на категориите',
    'LBL_TEMPLATES' => 'Шаблони',
    'LBL_TEMPATE_LOAD_MESSAGE' => 'Шаблонът ще презапише цялото съдържание.' .
        ' Сигурни ли сте, че желаете да използвате този шаблон?',
    'LNK_IMPORT_KBCONTENTS' => 'Импортирайте материали',
    'LBL_DELETE_CONFIRMATION_LANGUAGE' => 'Всички документи с този език ще бъдат изтрити! Сигурни ли сте, че искате да изтриете този език?',
    'LBL_CREATE_CATEGORY_PLACEHOLDER' => 'Натиснете Enter, за да създадете или Esc за отказ',
    'LBL_KB_NOTIFICATION' => 'Документът е публикуван.',
    'LBL_KB_PUBLISHED_REQUEST' => 'има документ за одобрение и публикация от Ваша страна.',
    'LBL_KB_STATUS_BACK_TO_DRAFT' => 'Статусът на документа е променен на чернови.',
    'LBL_OPERATOR_CONTAINING_THESE_WORDS' => 'с включени думи',
    'LBL_OPERATOR_EXCLUDING_THESE_WORDS' => 'без следните думи',
    'ERROR_EXP_DATE_LOW' => 'Датата на изтичане не може да бъде преди датата на публикуване.',
    'ERROR_ACTIVE_DATE_APPROVE_REQUIRED' => 'Одобреният статут изисква дата на публикуване.',
    'ERROR_ACTIVE_DATE_LOW' => 'The Publish Date must occur on a later date than today&#39;s date.',
    'ERROR_ACTIVE_DATE_EMPTY' => 'Полето на Датата на публикуване е празно.',
    'LBL_RECORD_SAVED_SUCCESS' => 'Успешно създадохте {{moduleSingularLower}} <a href="#{{buildRoute model=this}}">{{name}}</a>.', // use when a model is available
    'ERROR_IS_BEFORE' => 'Грешка. Датата на това поле не може да бъде преди датата на полето {{this}}.',
    'TPL_SHOW_MORE_MODULE' => 'Още {{module}}...',
    'LBL_LIST_FORM_TITLE' => 'Списък на база от знания',
    'LBL_SEARCH_FORM_TITLE' => 'Търсене в Базата от знания',
);
