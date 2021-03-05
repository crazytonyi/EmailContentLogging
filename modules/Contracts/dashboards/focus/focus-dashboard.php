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

return [
    'name' => 'LBL_CONTRACTS_FOCUS_DRAWER_DASHBOARD',
    'id' => 'e64eda38-13cb-11eb-9399-acde48001122',
    'metadata' => [
        'components' => [
            [
                'width' => 12,
                'rows' => [
                    // Row 1
                    [
                        [
                            'view' => [
                                'type' => 'dashablerecord',
                                'module' => 'Contracts',
                                'tabs' => [
                                    [
                                        'active' => true,
                                        'label' => 'LBL_MODULE_NAME_SINGULAR',
                                        'link' => '',
                                        'module' => 'Contracts',
                                    ],
                                ],
                            ],
                            'context' => [
                                'module' => 'Contracts',
                            ],
                            'width' => 6,
                        ],
                        [
                            'view' => [
                                'type' => 'dashablerecord',
                                'module' => 'Contracts',
                                'tabs' => [
                                    [
                                        'active' => true,
                                        'label' => 'LBL_MODULE_NAME_SINGULAR',
                                        'link' => 'opportunities',
                                        'module' => 'Opportunities',
                                    ],
                                ],
                                'tab_list' => [
                                    'opportunities',
                                ],
                            ],
                            'context' => [
                                'module' => 'Contracts',
                            ],
                            'width' => 6,
                        ],
                    ],
                    // Row 2
                    [
                        [
                            'view' => [
                                'type' => 'dashablerecord',
                                'module' => 'Contracts',
                                'tabs' => [
                                    [
                                        'active' => true,
                                        'label' => 'LBL_MODULE_NAME_SINGULAR',
                                        'link' => 'accounts',
                                        'module' => 'Accounts',
                                    ],
                                ],
                                'tab_list' => [
                                    'accounts',
                                ],
                            ],
                            'context' => [
                                'module' => 'Contracts',
                            ],
                            'width' => 6,
                        ],
                        [
                            'view' => [
                                'type' => 'dashablerecord',
                                'label' => 'LBL_RELATED_RECORDS',
                                'module' => 'Contracts',
                                'tabs' => [
                                    [
                                        'active' => true,
                                        'label' => 'LBL_MODULE_NAME_SINGULAR',
                                        'link' => 'contracts_documents',
                                        'module' => 'Documents',
                                    ],
                                    [
                                        'active' => false,
                                        'label' => 'LBL_MODULE_NAME_SINGULAR',
                                        'link' => 'notes',
                                        'module' => 'Notes',
                                    ],
                                    [
                                        'active' => false,
                                        'label' => 'LBL_MODULE_NAME_SINGULAR',
                                        'link' => 'contacts',
                                        'module' => 'Contacts',
                                    ],
                                    [
                                        'active' => false,
                                        'label' => 'LBL_MODULE_NAME_SINGULAR',
                                        'link' => 'quotes',
                                        'module' => 'Quotes',
                                    ],
                                ],
                                'tab_list' => [
                                    'contracts_documents',
                                    'notes',
                                    'contacts',
                                    'quotes',
                                ],
                            ],
                            'context' => [
                                'module' => 'Contracts',
                            ],
                            'width' => 6,
                        ],
                    ],
                ],
            ],
        ],
    ],
];
