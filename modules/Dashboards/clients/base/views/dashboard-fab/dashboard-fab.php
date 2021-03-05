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
$viewdefs['Dashboards']['base']['view']['dashboard-fab'] = [
    'icon' => 'fab-icon',
    'buttons' => [
        [
            'name' => 'add_button',
            'type' => 'rowaction',
            'icon' => 'new-dashboard-icon',
            'label' => 'LBL_DASHBOARD_CREATE',
            'showOn' => 'view',
        ], [
            'name' => 'duplicate_button',
            'type' => 'rowaction',
            'icon' => 'duplicate-dashboard-icon',
            'label' => 'LBL_DASHBOARD_DUPLICATE',
            'acl_module' => 'Dashboards',
            'acl_action' => 'create',
            'showOn' => 'view',
        ], [
            'name' => 'delete_button',
            'type' => 'rowaction',
            'icon' => 'delete-dashboard',
            'label' => 'LBL_DASHBOARD_DELETE',
            'acl_action' => 'delete',
            'showOn' => 'view',
        ], [
            'name' => 'collapse_button',
            'type' => 'rowaction',
            'icon' => 'collapse-dashlets',
            'label' => 'LBL_DASHLET_MINIMIZE_ALL',
            'showOn' => 'view',
        ], [
            'name' => 'expand_button',
            'type' => 'rowaction',
            'icon' => 'expand-dashlets',
            'label' => 'LBL_DASHLET_MAXIMIZE_ALL',
            'showOn' => 'view',
        ], [
            'name' => 'add_dashlet_button',
            'type' => 'rowaction',
            'icon' => 'add-dashlet-icon',
            'label' => 'LBL_ADD_DASHLET_BUTTON',
            'events' => [
                'click' => 'button:add_dashlet_button:click',
            ],
            'acl_action' => 'edit',
            'showOn' => 'view',
        ],
    ],
];
