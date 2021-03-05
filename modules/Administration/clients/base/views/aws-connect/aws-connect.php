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
$viewdefs['Administration']['base']['view']['aws-connect'] = [
    'template' => 'record',
    'label' => 'LBL_AWS_CONNECT_TITLE',
    'templateMeta' => [
        'useTabs' => true,
    ],
    'panels' => [
        [
            'name' => 'panel_1',
            'label' => 'LBL_AWS_PANEL_GENERAL',
            'columns' => 1,
            'labelsOnTop' => true,
            'placeholders' => true,
            'newTab' => true,
            'panelDefault' => 'expanded',
            'fields' => [
                [
                    'name' => 'instance_info',
                    'type' => 'sub-title',
                    'text' => 'LBL_AWS_INSTANCE_INFO',
                ],
                [
                    'name' => 'aws_connect_url',
                    'type' => 'url',
                    'label' => 'LBL_AWS_CONNECT_URL',
                    'placeholder' => 'LBL_AWS_CONNECT_URL',
                    'span' => 4,
                    'labelSpan' => 3,
                ],
                [
                    'name' => 'aws_connect_instance_name',
                    'type' => 'text',
                    'label' => 'LBL_AWS_CONNECT_INST_NAME',
                    'placeholder' => 'LBL_AWS_CONNECT_INST_NAME',
                    'span' => 4,
                    'labelSpan' => 3,
                ],
                [
                    'name' => 'aws_connect_region',
                    'type' => 'text',
                    'label' => 'LBL_AWS_CONNECT_REGION',
                    'placeholder' => 'LBL_AWS_CONNECT_REGION',
                    'required' => true,
                    'span' => 4,
                    'labelSpan' => 3,
                ],
                [
                    'name' => 'aws_connect_identity_provider',
                    'type' => 'enum',
                    'label' => 'LBL_AWS_CONNECT_IDENTITY',
                    'options' => 'aws_connect_identity_dom',
                    'required' => true,
                    'default' => 'Connect',
                    'span' => 4,
                    'labelSpan' => 3,
                ],
                [
                    'name' => 'aws_login_url',
                    'type' => 'url',
                    'label' => 'LBL_AWS_LOGIN_URL',
                    'required' => true,
                    'span' => 4,
                    'labelSpan' => 3,
                ],
            ],
            'helpLabels' => [
                [
                    'name' => 'LBL_AWS_CONNECT_URL',
                    'text' => 'LBL_AWS_CONNECT_URL_HELP',
                ],
                [
                    'name' => 'LBL_AWS_CONNECT_INST_NAME',
                    'text' => 'LBL_AWS_CONNECT_INST_NAME_HELP_TEXT',
                ],
                [
                    'name' => 'LBL_AWS_CONNECT_REGION',
                    'text' => 'LBL_AWS_CONNECT_REGION_HELP_TEXT',
                ],
                [
                    'name' => 'LBL_AWS_CONNECT_IDENTITY',
                    'text' => 'LBL_AWS_CONNECT_IDENTITY_HELP_TEXT',
                ],
            ],
        ],
        [
            'name' => 'panel_2',
            'label' => 'LBL_AWS_PANEL_PORTAL_CHAT',
            'columns' => 1,
            'labelsOnTop' => true,
            'placeholders' => true,
            'newTab' => true,
            'panelDefault' => 'expanded',
            'fields' => [
                [
                    'name' => 'settings',
                    'type' => 'sub-title',
                    'text' => 'LBL_AWS_PORTAL_CHAT_SETTINGS',
                ],
                [
                    'name' => 'aws_connect_enable_portal_chat',
                    'type' => 'bool',
                    'label' => 'LBL_AWS_PORTAL_ENABLE_CHAT',
                    'placeholder' => 'LBL_AWS_PORTAL_ENABLE_CHAT',
                    'span' => 8,
                    'labelSpan' => 4,
                    'default' => 0,
                ],
                [
                    'name' => 'aws_connect_api_gateway_url',
                    'type' => 'url',
                    'label' => 'LBL_AWS_PORTAL_API_GATEWAY',
                    'placeholder' => 'LBL_AWS_PORTAL_API_GATEWAY',
                    'required' => true,
                    'span' => 6,
                    'labelSpan' => 4,
                ],
                [
                    'name' => 'aws_connect_contact_flow_id',
                    'type' => 'text',
                    'label' => 'LBL_AWS_PORTAL_CONTACT_FLOW_ID',
                    'placeholder' => 'LBL_AWS_PORTAL_CONTACT_FLOW_ID',
                    'required' => true,
                    'span' => 6,
                    'labelSpan' => 4,
                ],
                [
                    'name' => 'aws_connect_instance_id',
                    'type' => 'text',
                    'label' => 'LBL_AWS_CONNECT_INST_ID',
                    'placeholder' => 'LBL_AWS_CONNECT_INST_ID',
                    'required' => true,
                    'span' => 6,
                    'labelSpan' => 4,
                ],
                [
                    'name' => 'aws_chat_header',
                    'type' => 'sub-title',
                    'text' => 'LBL_AWS_CHAT_HEADER',
                ],
                [
                    'name' => 'aws_header_image_url',
                    'type' => 'image-url',
                    'label' => 'LBL_AWS_CHAT_IMAGE_URL',
                    'placeholder' => 'LBL_AWS_CHAT_URL_TO_FILE',
                    'dbType' => 'varchar',
                    'default' => 'themes/default/images/company_logo.png',
                    'len' => 255,
                    'span' => 6,
                    'labelSpan' => 4,
                ],
                [
                    'name' => 'aws_header_title',
                    'type' => 'text',
                    'label' => 'LBL_AWS_CHAT_TITLE',
                    'placeholder' => 'LBL_AWS_CHAT_TITLE',
                    'default' => 'LBL_AWS_CHAT_HEADER_TITLE_DEFAULT',
                    'span' => 6,
                    'labelSpan' => 4,
                ],
                [
                    'name' => 'aws_header_title_color',
                    'type' => 'colorpicker',
                    'label' => 'LBL_AWS_CHAT_TITLE_COLOR',
                    'default' => '#FFFFFF',
                    'span' => 6,
                    'labelSpan' => 4,
                ],
                [
                    'name' => 'aws_header_subtitle',
                    'type' => 'text',
                    'label' => 'LBL_AWS_CHAT_HEADER_SUBTITLE',
                    'placeholder' => 'LBL_AWS_CHAT_HEADER_SUBTITLE',
                    'default' => 'LBL_AWS_CHAT_HEADER_SUBTITLE_DEFAULT',
                    'span' => 6,
                    'labelSpan' => 4,
                ],
                [
                    'name' => 'aws_header_subtitle_color',
                    'type' => 'colorpicker',
                    'label' => 'LBL_AWS_CHAT_HEADER_SUBTITLE_COLOR',
                    'default' => '#FFFFFF',
                    'span' => 6,
                    'labelSpan' => 4,
                ],
                [
                    'name' => 'aws_header_background_color',
                    'type' => 'colorpicker',
                    'label' => 'LBL_AWS_CHAT_HEADER_BACKGROUND_COLOR',
                    'default' => '#265A8D',
                    'span' => 6,
                    'labelSpan' => 4,
                ],
                [
                    'name' => 'aws_chat_footer',
                    'type' => 'sub-title',
                    'text' => 'LBL_AWS_CHAT_FOOTER',
                ],
                [
                    'name' => 'aws_footer_title',
                    'type' => 'text',
                    'label' => 'LBL_AWS_CHAT_TITLE',
                    'placeholder' => 'LBL_AWS_CHAT_TITLE',
                    'span' => 6,
                    'labelSpan' => 4,
                ],
                [
                    'name' => 'aws_footer_title_color',
                    'type' => 'colorpicker',
                    'label' => 'LBL_AWS_CHAT_TITLE_COLOR',
                    'default' => '#9AA5AD',
                    'span' => 6,
                    'labelSpan' => 4,
                ],
                [
                    'name' => 'aws_chat_end_button',
                    'type' => 'sub-title',
                    'text' => 'LBL_AWS_CHAT_END_BUTTON',
                ],
                [
                    'name' => 'aws_end_chat_button_text_color',
                    'type' => 'colorpicker',
                    'label' => 'LBL_AWS_CHAT_TEXT_COLOR',
                    'default' => '#FFFFFF',
                    'span' => 6,
                    'labelSpan' => 4,
                ],
                [
                    'name' => 'aws_end_chat_button_size',
                    'inline' => true,
                    'type' => 'fieldset',
                    'label' => 'LBL_AWS_CHAT_END_BUTTON_SIZE',
                    'span' => 4,
                    'labelSpan' => 4,
                    'fields' => [
                        [
                            'type' => 'label',
                            'default_value' => 'LBL_AWS_CHAT_END_BUTTON_SIZE_TEXT1',
                        ],
                        [
                            'name' => 'aws_end_chat_button_height',
                            'text' => 'LBL_CHART_CONFIG_TICK_WRAP',
                            'type' => 'enum',
                            'options' => 'aws_end_chat_button_height',
                            'default' => '40',
                        ],
                        [
                            'type' => 'label',
                            'default_value' => 'LBL_AWS_CHAT_END_BUTTON_SIZE_TEXT2',
                        ],
                        [
                            'name' => 'aws_end_chat_button_width',
                            'text' => 'LBL_CHART_CONFIG_TICK_ROTATE',
                            'type' => 'enum',
                            'options' => 'aws_end_chat_button_width',
                            'default' => '140',
                        ],
                        [
                            'type' => 'label',
                            'default_value' => 'LBL_AWS_CHAT_END_BUTTON_SIZE_TEXT3',
                        ],
                    ],
                ],
                [
                    'name' => 'aws_end_chat_button_fill',
                    'type' => 'colorpicker',
                    'label' => 'LBL_AWS_CHAT_END_BUTTON_FILL',
                    'default' => '#0679C8',
                    'span' => 6,
                    'labelSpan' => 4,
                ],
                [
                    'name' => 'aws_chat_messages',
                    'type' => 'sub-title',
                    'text' => 'LBL_AWS_CHAT_MESSAGES',
                ],
                [
                    'name' => 'aws_message_text_color',
                    'type' => 'colorpicker',
                    'label' => 'LBL_AWS_CHAT_TEXT_COLOR',
                    'default' => '#000000',
                    'span' => 6,
                    'labelSpan' => 4,
                ],
                [
                    'name' => 'aws_message_customer_bubble_color',
                    'type' => 'colorpicker',
                    'label' => 'LBL_AWS_CHAT_CUSTOMER_BUBBLE_COLOR',
                    'default' => '#DAEBF7',
                    'span' => 6,
                    'labelSpan' => 4,
                ],
                [
                    'name' => 'aws_message_agent_bubble_color',
                    'type' => 'colorpicker',
                    'label' => 'LBL_AWS_CHAT_AGENT_BUBBLE_COLOR',
                    'default' => '#D5DCE0',
                    'span' => 6,
                    'labelSpan' => 4,
                ],
            ],
            'helpLabels' => [
                [
                    'name' => 'LBL_AWS_PORTAL_API_GATEWAY',
                    'text' => 'LBL_AWS_PORTAL_API_GATEWAY_HELP_TEXT',
                ],
                [
                    'name' => 'LBL_AWS_PORTAL_CONTACT_FLOW_ID',
                    'text' => 'LBL_AWS_PORTAL_CONTACT_FLOW_ID_HELP_TEXT',
                ],
                [
                    'name' => 'LBL_AWS_CONNECT_INST_ID',
                    'text' => 'LBL_AWS_INSTANCE_ID_HELP_TEXT',
                ],
            ],
        ],
    ],
];