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
$dictionary['Quote'] = array(
    'table' => 'quotes',
    'audited' => true,
    'unified_search' => true,
    'full_text_search' => true,
    'fields' => array(
        'shipper_id' => array(
            'name' => 'shipper_id',
            'vname' => 'LBL_SHIPPER_ID',
            'type' => 'id',
            'required' => false,
            'do_report' => false,
            'reportable' => false,
        ),
        'shipper_name' => array(
            'name' => 'shipper_name',
            'rname' => 'name',
            'id_name' => 'shipper_id',
            'join_name' => 'shippers',
            'type' => 'relate',
            'link' => 'shippers',
            'table' => 'shippers',
            'isnull' => 'true',
            'module' => 'Shippers',
            'dbType' => 'varchar',
            'len' => '255',
            'vname' => 'LBL_SHIPPING_PROVIDER',
            'source' => 'non-db',
            'comment' => 'Shipper Name',
        ),
        'shippers' => array(
            'name' => 'shippers',
            'type' => 'link',
            'relationship' => 'shipper_quotes',
            'vname' => 'LBL_SHIPPING_PROVIDER',
            'source' => 'non-db',
        ),
        'taxrate_id' => array(
            'name' => 'taxrate_id',
            'vname' => 'LBL_TAXRATE_ID',
            'type' => 'id',
            'required' => false,
            'do_report' => false,
            'reportable' => false,
        ),
        'taxrate_name' => array(
            'name' => 'taxrate_name',
            'rname' => 'name',
            'id_name' => 'taxrate_id',
            'join_name' => 'taxrates',
            'type' => 'relate',
            'link' => 'taxrates',
            'table' => 'taxrates',
            'isnull' => 'true',
            'module' => 'TaxRates',
            'dbType' => 'varchar',
            'len' => '255',
            'vname' => 'LBL_TAXRATE',
            'source' => 'non-db',
            'comment' => 'Tax Rate Name',
            'massupdate' => false,
        ),
        'taxrate_value' => array(
            'name' => 'taxrate_value',
            'vname' => 'LBL_TAXRATE_VALUE',
            'dbType' => 'decimal',
            'type' => 'currency',
            'len' => '26,6',
            'default' => 0,
            'formula' => '$taxrates.value',
            'calculated' => true,
            'enforced' => true,
            'studio' => false,
            'massupdate' => false,
            'comment' => 'Tax Rate Value',
        ),
        'taxrates' => array(
            'name' => 'taxrates',
            'type' => 'link',
            'relationship' => 'taxrate_quotes',
            'vname' => 'LBL_TAXRATE',
            'source' => 'non-db',
        ),
        'show_line_nums' => array(
            'name' => 'show_line_nums',
            'vname' => 'LBL_SHOW_LINE_NUMS',
            'type' => 'bool',
            'default' => 1,
            'hideacl' => true,
            'reportable' => false,
            'massupdate' => false,
            'processes' => array(
                'types' => array(
                    'RR' => false,
                    'ALL' => true,
                ),
            ),
        ),
        'name' => array(
            'name' => 'name',
            'vname' => 'LBL_QUOTE_NAME',
            'dbType' => 'varchar',
            'type' => 'name',
            'len' => '50',
            'unified_search' => true,
            'full_text_search' => array(
                'enabled' => true,
                'searchable' => true,
                'boost' => 1.61,
            ),
            'importable' => 'required',
            'required' => true,
        ),
        'quote_type' => array(
            'name' => 'quote_type',
            'vname' => 'LBL_QUOTE_TYPE',
            'type' => 'enum',
            'dbtype' => 'varchar',
            'options' => 'quote_type_dom',
        ),
        'date_quote_expected_closed' => array(
            'name' => 'date_quote_expected_closed',
            'vname' => 'LBL_DATE_QUOTE_EXPECTED_CLOSED',
            'type' => 'date',
            'audited' => true,
            'reportable' => true,
            'importable' => 'required',
            'required' => true,
            'enable_range_search' => true,
            'options' => 'date_range_search_dom',
        ),
        'original_po_date' => array(
            'name' => 'original_po_date',
            'vname' => 'LBL_ORIGINAL_PO_DATE',
            'type' => 'date',
            'enable_range_search' => true,
            'options' => 'date_range_search_dom',
        ),
        'payment_terms' => array(
            'name' => 'payment_terms',
            'vname' => 'LBL_PAYMENT_TERMS',
            'type' => 'enum',
            'options' => 'payment_terms',
            'len' => '128',
        ),
        'date_quote_closed' => array(
            'name' => 'date_quote_closed',
            'massupdate' => false,
            'vname' => 'LBL_DATE_QUOTE_CLOSED',
            'type' => 'date',
            'reportable' => false,
            'enable_range_search' => true,
            'options' => 'date_range_search_dom',
        ),
        'date_order_shipped' => array(
            'name' => 'date_order_shipped',
            'massupdate' => false,
            'vname' => 'LBL_LIST_DATE_QUOTE_CLOSED',
            'type' => 'date',
            'reportable' => false,
            'enable_range_search' => true,
            'options' => 'date_range_search_dom',
        ),
        'order_stage' => array(
            'name' => 'order_stage',
            'vname' => 'LBL_ORDER_STAGE',
            'type' => 'enum',
            'options' => 'order_stage_dom',
            'massupdate' => false,
            'len' => 100,
        ),
        'quote_stage' => array(
            'name' => 'quote_stage',
            'vname' => 'LBL_QUOTE_STAGE',
            'type' => 'enum',
            'options' => 'quote_stage_dom',
            'len' => 100,
            'audited' => true,
            'importable' => 'required',
            'required' => true,
            'full_text_search' => array(
                'enabled' => true,
                'searchable' => false,
            ),
        ),
        'purchase_order_num' => array(
            'name' => 'purchase_order_num',
            'vname' => 'LBL_PURCHASE_ORDER_NUM',
            'type' => 'varchar',
            'len' => '50',
            'full_text_search' => array(
                'enabled' => true,
                'searchable' => true,
                'type' => 'exact',
                'boost' => 1.19,
            ),
        ),
        'quote_num' => array(
            'name' => 'quote_num',
            'vname' => 'LBL_QUOTE_NUM',
            'type' => 'int',
            'auto_increment' => true,
            'readonly' => true,
            'required' => true,
            'unified_search' => true,
            'full_text_search' => array(
                'enabled' => true,
                'searchable' => true,
                'type' => 'exact',
                'boost' => 1.17,
            ),
            'disable_num_format' => true,
            'enable_range_search' => true,
            'options' => 'numeric_range_search_dom',
        ),
        'subtotal' => array(
            'name' => 'subtotal',
            'vname' => 'LBL_SUBTOTAL',
            'dbType' => 'decimal',
            'type' => 'currency',
            'len' => '26,6',
            'related_fields' => array(
                'currency_id',
                'base_rate'
            ),
            'formula' => 'rollupCurrencySum($product_bundles, "subtotal")',
            'calculated' => true,
            'enforced' => true,
        ),
        'subtotal_usdollar' => array(
            'name' => 'subtotal_usdollar',
            'group' => 'subtotal',
            'vname' => 'LBL_SUBTOTAL_USDOLLAR',
            'dbType' => 'decimal',
            'type' => 'currency',
            'is_base_currency' => true,
            'len' => '26,6',
            'audited' => true,
            'studio' => array(
                'wirelesseditview' => false,
                'wirelessdetailview' => false,
                'wirelesslistview' => false,
                'wireless_basic_search' => false,
                'wireless_advanced_search' => false,
                'mobile' => false,
            ),
            'related_fields' => array(
                'currency_id',
                'base_rate'
            ),
            'formula' => 'ifElse(isNumeric($subtotal), currencyDivide($subtotal,$base_rate), "")',
            'calculated' => true,
            'enforced' => true,
        ),
        'shipping' => array(
            'name' => 'shipping',
            'vname' => 'LBL_SHIPPING',
            'dbType' => 'decimal',
            'type' => 'currency',
            'len' => '26,6',
            'related_fields' => array(
                'currency_id',
                'base_rate'
            ),
            'default' => '0',
            'formula' => 'ifElse(equal($shipping,""),"0.00",$shipping)',
            'calculated' => true,
        ),
        'shipping_usdollar' => array(
            'name' => 'shipping_usdollar',
            'vname' => 'LBL_SHIPPING_USDOLLAR',
            'group' => 'shipping',
            'dbType' => 'decimal',
            'type' => 'currency',
            'currency_id'=> '-99',
            'is_base_currency' => true,
            'len' => '26,6',
            'studio' => array(
                'wirelesseditview' => false,
                'wirelessdetailview' => false,
                'wirelesslistview' => false,
                'wireless_basic_search' => false,
                'wireless_advanced_search' => false,
                'mobile' => false,
            ),
            'related_fields' => array(
                'currency_id',
                'base_rate'
            ),
            'formula' => 'ifElse(isNumeric($shipping), currencyDivide($shipping, $base_rate), "")',
            'calculated' => true,
            'enforced' => true,
        ),
        'discount' => array(
            'name' => 'discount',
            'vname' => 'LBL_DISCOUNT_TOTAL',
            'dbType' => 'decimal',
            'type' => 'currency',
            'len' => '26,6',
            'default' => '0',
            'related_fields' => array(
                'currency_id',
                'base_rate'
            ),
        ),
        'deal_tot' => array(
            'name' => 'deal_tot',
            'vname' => 'LBL_DEAL_TOT',
            'dbType' => 'decimal',
            'type' => 'currency',
            'len' => '26,2',
            'formula' => 'rollupCurrencySum($product_bundles, "deal_tot")',
            'calculated' => true,
            'enforced' => true,
        ),
        'deal_tot_discount_percentage' => array(
            'name' => 'deal_tot_discount_percentage',
            'vname' => 'LBL_DEAL_TOT_PERCENTAGE',
            'dbType' => 'decimal',
            'type' => 'float',
            'len' => '26,2',
            'formula' => 'ifElse(not(equal($subtotal_usdollar, 0)), mul(divide($deal_tot_usdollar, $subtotal_usdollar),100), 0)',
            'default' => '0',
            'calculated' => true,
            'enforced' => true,
        ),
        'deal_tot_usdollar' => array(
            'name' => 'deal_tot_usdollar',
            'vname' => 'LBL_DEAL_TOT_USDOLLAR',
            'dbType' => 'decimal',
            'type' => 'currency',
            'currency_id'=> '-99',
            'is_base_currency' => true,
            'len' => '26,2',
            'studio' => array(
                'wirelesseditview' => false,
                'wirelessdetailview' => false,
                'wirelesslistview' => false,
                'wireless_basic_search' => false,
                'wireless_advanced_search' => false,
                'mobile' => false,
            ),
            'formula' => 'ifElse(isNumeric($deal_tot), currencyDivide($deal_tot, $base_rate), "")',
            'calculated' => true,
            'enforced' => true,
        ),
        'new_sub' => array(
            'name' => 'new_sub',
            'vname' => 'LBL_NEW_SUB',
            'dbType' => 'decimal',
            'type' => 'currency',
            'len' => '26,6',
            'related_fields' => array(
                'currency_id',
                'base_rate'
            ),
            'formula' => 'rollupCurrencySum($product_bundles, "new_sub")',
            'calculated' => true,
            'enforced' => true,
        ),
        'new_sub_usdollar' => array(
            'name' => 'new_sub_usdollar',
            'vname' => 'LBL_NEW_SUB_USDOLLAR',
            'dbType' => 'decimal',
            'type' => 'currency',
            'currency_id'=> '-99',
            'is_base_currency' => true,
            'len' => '26,6',
            'studio' => array(
                'wirelesseditview' => false,
                'wirelessdetailview' => false,
                'wirelesslistview' => false,
                'wireless_basic_search' => false,
                'wireless_advanced_search' => false,
                'mobile' => false,
            ),
            'related_fields' => array(
                'currency_id',
                'base_rate'
            ),
            'formula' => 'ifElse(isNumeric($new_sub), currencyDivide($new_sub, $base_rate), "")',
            'calculated' => true,
            'enforced' => true,
        ),
        'taxable_subtotal' => array(
            'name' => 'taxable_subtotal',
            'vname' => 'LBL_TAXABLE_SUBTOTAL',
            'type' => 'currency',
            'len' => '26,6',
            'disable_num_format' => true,
            'comment' => 'Rollup of product bundles taxable_subtotal values',
            'formula' => 'rollupCurrencySum($product_bundles, "taxable_subtotal")',
            'calculated' => true,
            'enforced' => true,
        ),
        'tax' => array(
            'name' => 'tax',
            'vname' => 'LBL_TAX',
            'dbType' => 'decimal',
            'type' => 'currency',
            'len' => '26,6',
            'related_fields' => array(
                'currency_id',
                'base_rate',
                'taxrate_value',
                'taxable_subtotal',
            ),
            'formula' => 'currencyMultiply($taxable_subtotal, currencyDivide($taxrate_value, "100"))',
            'default' => '0',
            'calculated' => true,
            'enforced' => true,
        ),
        'tax_usdollar' => array(
            'name' => 'tax_usdollar',
            'vname' => 'LBL_TAX_USDOLLAR',
            'dbType' => 'decimal',
            'group' => 'tax',
            'type' => 'currency',
            'is_base_currency' => true,
            'len' => '26,6',
            'audited' => true,
            'studio' => array(
                'wirelesseditview' => false,
                'wirelessdetailview' => false,
                'wirelesslistview' => false,
                'wireless_basic_search' => false,
                'wireless_advanced_search' => false,
                'mobile' => false,
            ),
            'related_fields' => array(
                'currency_id',
                'base_rate'
            ),
            'formula' => 'ifElse(isNumeric($tax), currencyDivide($tax, $base_rate), "")',
            'calculated' => true,
            'enforced' => true,
        ),
        'total' => array(
            'name' => 'total',
            'vname' => 'LBL_TOTAL',
            'dbType' => 'decimal',
            'type' => 'currency',
            'len' => '26,6',
            'formula' => 'currencyAdd(
                rollupCurrencySum($product_bundles, "new_sub"),
                ifElse(isNumeric($tax), $tax, "0"),
                ifElse(isNumeric($shipping), $shipping, "0")
            )',
            'calculated' => true,
            'enforced' => true,
        ),
        'total_usdollar' => array(
            'name' => 'total_usdollar',
            'vname' => 'LBL_TOTAL_USDOLLAR',
            'dbType' => 'decimal',
            'group' => 'total',
            'type' => 'currency',
            'currency_id'=> '-99',
            'is_base_currency' => true,
            'len' => '26,6',
            'audited' => true,
            'enable_range_search' => true,
            'options' => 'numeric_range_search_dom',
            'studio' => array(
                'wirelesseditview' => false,
                'wirelessdetailview' => false,
                'wirelesslistview' => false,
                'wireless_basic_search' => false,
                'wireless_advanced_search' => false,
                'mobile' => false,
            ),
            'related_fields' => array(
                'currency_id',
                'base_rate'
            ),
            'formula' => 'ifElse(isNumeric($total), currencyDivide($total, $base_rate), "")',
            'calculated' => true,
            'enforced' => true,
        ),
        'billing_address_street' => array(
            'name' => 'billing_address_street',
            'vname' => 'LBL_BILLING_ADDRESS_STREET',
            'type' => 'text',
            'dbType' => 'varchar',
            'group' => 'billing_address',
            'group_label' => 'LBL_BILLING_ADDRESS',
            'len' => '150',
            'full_text_search' => array(
                'enabled' => true,
                'searchable' => true,
                'boost' => 0.24,
            ),
        ),
        'billing_address_city' => array(
            'name' => 'billing_address_city',
            'vname' => 'LBL_BILLING_ADDRESS_CITY',
            'type' => 'varchar',
            'group' => 'billing_address',
            'len' => '100',
        ),
        'billing_address_state' => array(
            'name' => 'billing_address_state',
            'vname' => 'LBL_BILLING_ADDRESS_STATE',
            'type' => 'varchar',
            'group' => 'billing_address',
            'len' => '100',
        ),
        'billing_address_postalcode' => array(
            'name' => 'billing_address_postalcode',
            'vname' => 'LBL_BILLING_ADDRESS_POSTAL_CODE',
            'type' => 'varchar',
            'group' => 'billing_address',
            'len' => '20',
        ),
        'billing_address_country' => array(
            'name' => 'billing_address_country',
            'vname' => 'LBL_BILLING_ADDRESS_COUNTRY',
            'type' => 'varchar',
            'group' => 'billing_address',
            'len' => '100',
        ),
        'shipping_address_street' => array(
            'name' => 'shipping_address_street',
            'vname' => 'LBL_SHIPPING_ADDRESS_STREET',
            'type' => 'text',
            'dbType' => 'varchar',
            'group' => 'shipping_address',
            'group_label' => 'LBL_SHIPPING_ADDRESS',
            'len' => '150',
            'full_text_search' => array(
                'enabled' => true,
                'searchable' => true,
                'boost' => 0.23,
            ),
        ),
        'shipping_address_city' => array(
            'name' => 'shipping_address_city',
            'vname' => 'LBL_SHIPPING_ADDRESS_CITY',
            'type' => 'varchar',
            'group' => 'shipping_address',
            'len' => '100',
        ),
        'shipping_address_state' => array(
            'name' => 'shipping_address_state',
            'vname' => 'LBL_SHIPPING_ADDRESS_STATE',
            'type' => 'varchar',
            'group' => 'shipping_address',
            'len' => '100',
        ),
        'shipping_address_postalcode' => array(
            'name' => 'shipping_address_postalcode',
            'vname' => 'LBL_SHIPPING_ADDRESS_POSTAL_CODE',
            'type' => 'varchar',
            'group' => 'shipping_address',
            'len' => '20',
        ),
        'shipping_address_country' => array(
            'name' => 'shipping_address_country',
            'vname' => 'LBL_SHIPPING_ADDRESS_COUNTRY',
            'type' => 'varchar',
            'group' => 'shipping_address',
            'len' => '100',
        ),
        'shipping_account_name' => array(
            'name' => 'shipping_account_name',
            'rname' => 'name',
            'id_name' => 'shipping_account_id',
            'vname' => 'LBL_SHIPPING_ACCOUNT_NAME',
            'type' => 'relate',
            'table' => 'shipping_accounts',
            'isnull' => 'true',
            'link' => 'shipping_accounts',
            'module' => 'Accounts',
            'source' => 'non-db',
            'populate_list' => array(
                'shipping_address_street' => 'shipping_address_street',
                'shipping_address_city' => 'shipping_address_city',
                'shipping_address_state' => 'shipping_address_state',
                'shipping_address_postalcode' => 'shipping_address_postalcode',
                'shipping_address_country' => 'shipping_address_country',
            ),
        ),
        'shipping_account_id' => array(
            'name' => 'shipping_account_id',
            'type' => 'relate',
            'vname' => 'LBL_SHIPPING_ACCOUNT_ID',
            'source' => 'non-db',
            'link' => 'shipping_accounts',
            'rname' => 'id',
            'massupdate' => false,
            'module' => 'Accounts',
            'studio' => 'false',
            'id_name' => 'account_id',
        ),
        'shipping_contact_name' => array(
            'name' => 'shipping_contact_name',
            'rname' => 'full_name',
            'id_name' => 'shipping_contact_id',
            'vname' => 'LBL_SHIPPING_CONTACT_NAME',
            'type' => 'relate',
            'link' => 'shipping_contacts',
            'table' => 'shipping_contacts',
            'isnull' => 'true',
            'module' => 'Contacts',
            'source' => 'non-db',
        ),
        'shipping_contact_id' => array(
            'name' => 'shipping_contact_id',
            'rname' => 'id',
            'id_name' => 'shipping_contact_id',
            'vname' => 'LBL_SHIPPING_CONTACT_ID',
            'type' => 'relate',
            'link' => 'shipping_contacts',
            'table' => 'shipping_contacts',
            'isnull' => 'true',
            'module' => 'Contacts',
            'source' => 'non-db',
            'massupdate' => false, //CL: set to false, shown via shipping_contact_name
        ),
        'account_name' => array(
            'name' => 'account_name',
            'rname' => 'name',
            'id_name' => 'account_id',
            'vname' => 'LBL_ACCOUNT_NAME',
            'type' => 'relate',
            'link' => 'billing_accounts',
            'table' => 'billing_accounts',
            'isnull' => 'true',
            'module' => 'Accounts',
            'source' => 'non-db',
            'massupdate' => false,
            'studio' => array(
                'edit' => 'false',
                'detail' => 'false',
                'list' => 'false',
            )
        ),
        'account_id' => array(
            'name' => 'account_id',
            'type' => 'relate',
            'link' => 'billing_accounts',
            'rname' => 'id',
            'vname' => 'LBL_ACCOUNT_ID',
            'source' => 'non-db',
            'massupdate' => false,
            'module' => 'Accounts',
            'studio' => 'false',
            'id_name' => 'account_id',
        ),
        'billing_account_name' => array(
            'name' => 'billing_account_name',
            'rname' => 'name',
            'id_name' => 'billing_account_id',
            'vname' => 'LBL_BILLING_ACCOUNT_NAME',
            'type' => 'relate',
            'link' => 'billing_accounts',
            'table' => 'billing_accounts',
            'isnull' => 'true',
            'module' => 'Accounts',
            'source' => 'non-db',
            'importable' => 'required',
            'required' => true,
            'populate_list' => array(
                'billing_address_street' => 'billing_address_street',
                'billing_address_city' => 'billing_address_city',
                'billing_address_state' => 'billing_address_state',
                'billing_address_postalcode' => 'billing_address_postalcode',
                'billing_address_country' => 'billing_address_country',
            ),
        ),
        'billing_account_id' => array(
            'name' => 'billing_account_id',
            'type' => 'relate',
            'vname' => 'LBL_BILLING_ACCOUNT_ID',
            'source' => 'non-db',
            'link' => 'billing_accounts',
            'rname' => 'id',
            'massupdate' => false,
            'module' => 'Accounts',
            'studio' => 'false',
            'id_name' => 'account_id',
        ),
        'billing_contact_name' => array(
            'name' => 'billing_contact_name',
            'rname' => 'full_name',
            'id_name' => 'billing_contact_id',
            'vname' => 'LBL_BILLING_CONTACT_NAME',
            'type' => 'relate',
            'link' => 'billing_contacts',
            'table' => 'billing_contacts',
            'isnull' => 'true',
            'module' => 'Contacts',
            'source' => 'non-db',
        ),
        'billing_contact_id' => array(
            'name' => 'billing_contact_id',
            'rname' => 'id',
            'id_name' => 'billing_contact_id',
            'vname' => 'LBL_BILLING_CONTACT_ID',
            'type' => 'relate',
            'link' => 'billing_contacts',
            'table' => 'billing_contacts',
            'isnull' => 'true',
            'module' => 'Contacts',
            'source' => 'non-db',
            'massupdate' => false, //CL: set to false, shown via billing_contact_name
        ),
        'tasks' => array(
            'name' => 'tasks',
            'type' => 'link',
            'relationship' => 'quote_tasks',
            'vname' => 'LBL_TASKS',
            'source' => 'non-db',
        ),
        'notes' => array(
            'name' => 'notes',
            'type' => 'link',
            'relationship' => 'quote_notes',
            'vname' => 'LBL_NOTES',
            'source' => 'non-db',
        ),
        'messages' => [
            'name' => 'messages',
            'type' => 'link',
            'relationship' => 'quote_messages',
            'vname' => 'LBL_MESSAGES',
            'source' => 'non-db',
        ],
        'meetings' => array(
            'name' => 'meetings',
            'type' => 'link',
            'relationship' => 'quote_meetings',
            'vname' => 'LBL_MEETINGS',
            'source' => 'non-db',
        ),
        'calls' => array(
            'name' => 'calls',
            'type' => 'link',
            'relationship' => 'quote_calls',
            'vname' => 'LBL_CALLS',
            'source' => 'non-db',
        ),
        'emails' => array(
            'name' => 'emails',
            'type' => 'link',
            'relationship' => 'emails_quotes',
            'vname' => 'LBL_EMAILS',
            'source' => 'non-db',
        ),
        'project' => array(
            'name' => 'project',
            'type' => 'link',
            'relationship' => 'projects_quotes',
            'vname' => 'LBL_PROJECTS',
            'source' => 'non-db',
        ),
        'products' => array(
            'name' => 'products',
            'type' => 'link',
            'relationship' => 'quote_products',
            'vname' => 'LBL_PRODUCTS',
            'source' => 'non-db',
        ),
        'revenuelineitems' => array(
            'name' => 'revenuelineitems',
            'type' => 'link',
            'relationship' => 'quote_revenuelineitems',
            'vname' => 'LBL_REVENUELINEITEMS',
            'source' => 'non-db',
            'workflow' => false
        ),
        'shipping_accounts' => array(
            'name' => 'shipping_accounts',
            'type' => 'link',
            'relationship' => 'quotes_shipto_accounts',
            'vname' => 'LBL_SHIP_TO_ACCOUNT',
            'source' => 'non-db',
            'link_type' => 'one',
        ),
        'billing_accounts' => array(
            'name' => 'billing_accounts',
            'type' => 'link',
            'relationship' => 'quotes_billto_accounts',
            'vname' => 'LBL_BILL_TO_ACCOUNT',
            'source' => 'non-db',
            'link_type' => 'one',
        ),
        'shipping_contacts' => array(
            'name' => 'shipping_contacts',
            'type' => 'link',
            'relationship' => 'quotes_contacts_shipto',
            'vname' => 'LBL_SHIP_TO_CONTACT',
            'source' => 'non-db',
            'link_type' => 'one',
        ),
        'billing_contacts' => array(
            'name' => 'billing_contacts',
            'type' => 'link',
            'link_type' => 'one',
            'vname' => 'LBL_BILL_TO_CONTACT',
            'relationship' => 'quotes_contacts_billto',
            'source' => 'non-db',
        ),
        'product_bundles' => array(
            'name' => 'product_bundles',
            'type' => 'link',
            'vname' => 'LBL_PRODUCT_BUNDLES',
            'module' => 'ProductBundles',
            'bean_name' => 'ProductBundle',
            'relationship' => 'product_bundle_quote',
            'rel_fields' => array('bundle_index' => array('type' => 'integer')),
            'source' => 'non-db',
        ),
        'bundles' => array(
            'name' => 'bundles',
            'type' => 'collection',
            'vname' => 'LBL_PRODUCT_BUNDLES',
            'links' => array('product_bundles'),
            'source' => 'non-db',
            'hideacl' => true,
        ),
        'opportunities' => array(
            'name' => 'opportunities',
            'type' => 'link',
            'vname' => 'LBL_OPPORTUNITY',
            'relationship' => 'quotes_opportunities',
            'link_type' => 'one',
            'source' => 'non-db',
        ),
        'created_by_link' => array(
            'name' => 'created_by_link',
            'type' => 'link',
            'relationship' => 'quotes_created_by',
            'vname' => 'LBL_CREATED_BY_USER',
            'link_type' => 'one',
            'module' => 'Users',
            'bean_name' => 'User',
            'source' => 'non-db',
        ),
        'modified_user_link' => array(
            'name' => 'modified_user_link',
            'type' => 'link',
            'relationship' => 'quotes_modified_user',
            'vname' => 'LBL_MODIFIED_BY_USER',
            'link_type' => 'one',
            'module' => 'Users',
            'bean_name' => 'User',
            'source' => 'non-db',
        ),
        'assigned_user_link' => array(
            'name' => 'assigned_user_link',
            'type' => 'link',
            'relationship' => 'quotes_assigned_user',
            'vname' => 'LBL_ASSIGNED_TO_USER',
            'link_type' => 'one',
            'module' => 'Users',
            'bean_name' => 'User',
            'source' => 'non-db',
        ),
        'opportunity_name' => array(
            'name' => 'opportunity_name',
            'rname' => 'name',
            'id_name' => 'opportunity_id',
            'vname' => 'LBL_OPPORTUNITY_NAME',
            'type' => 'relate',
            'table' => 'Opportunities',
            'isnull' => 'true',
            'module' => 'Opportunities',
            'link' => 'opportunities',
            'massupdate' => false,
            //'dbType' => 'varchar',
            'source' => 'non-db',
            'len' => 50,
        ),
        'opportunity_id' => array(
            'name' => 'opportunity_id',
            'type' => 'relate',
            'source' => 'non-db',
            'rname' => 'id',
            'id_name' => 'id',
            'vname' => 'LBL_OPPORTUNITY_ID',
            'table' => 'opportunities',
            'module' => 'Opportunities',
            'link' => 'opportunities',
        ),
        'documents' => array(
            'name' => 'documents',
            'type' => 'link',
            'relationship' => 'documents_quotes',
            'source' => 'non-db',
            'vname' => 'LBL_DOCUMENTS_SUBPANEL_TITLE',
        ),
        'contracts' => array(
            'name' => 'contracts',
            'type' => 'link',
            'vname' => 'LBL_CONTRACTS',
            'relationship' => 'contracts_quotes',
            'link_type' => 'one',
            'source' => 'non-db',
        ),
        'renewal' => [
            'name' => 'renewal',
            'vname' => 'LBL_RENEWAL',
            'type' => 'bool',
            'default' => 0,
            'readonly' => true,
            'comment' => 'Indicates whether this quote is a renewal',
        ],
    ),
    'indices' => array(
        array(
            'name' => 'quote_num',
            'type' => 'unique',
            'fields' => array(
                'quote_num',
            ),
        ),
        array(
            'name' => 'idx_qte_name',
            'type' => 'index',
            'fields' => array('name')
        ),
        array(
            'name' => 'idx_quote_quote_stage',
            'type' => 'index',
            'fields' => array('quote_stage')
        ),
        array(
            'name' => 'idx_quote_date_quote_expected_closed',
            'type' => 'index',
            'fields' => array(
                'date_quote_expected_closed'
            )
        ),
    ),
    'relationships' => array(
        'quote_tasks' => array(
            'lhs_module' => 'Quotes',
            'lhs_table' => 'quotes',
            'lhs_key' => 'id',
            'rhs_module' => 'Tasks',
            'rhs_table' => 'tasks',
            'rhs_key' => 'parent_id',
            'relationship_type' => 'one-to-many',
            'relationship_role_column' => 'parent_type',
            'relationship_role_column_value' => 'Quotes'
        ),
        'quote_notes' => array(
            'lhs_module' => 'Quotes',
            'lhs_table' => 'quotes',
            'lhs_key' => 'id',
            'rhs_module' => 'Notes',
            'rhs_table' => 'notes',
            'rhs_key' => 'parent_id',
            'relationship_type' => 'one-to-many',
            'relationship_role_column' => 'parent_type',
            'relationship_role_column_value' => 'Quotes'
        ),
        'quote_messages' => [
            'lhs_module' => 'Quotes',
            'lhs_table' => 'quotes',
            'lhs_key' => 'id',
            'rhs_module' => 'Messages',
            'rhs_table' => 'messages',
            'rhs_key' => 'parent_id',
            'relationship_type' => 'one-to-many',
            'relationship_role_column' => 'parent_type',
            'relationship_role_column_value' => 'Quotes',
        ],
        'quote_meetings' => array(
            'lhs_module' => 'Quotes',
            'lhs_table' => 'quotes',
            'lhs_key' => 'id',
            'rhs_module' => 'Meetings',
            'rhs_table' => 'meetings',
            'rhs_key' => 'parent_id',
            'relationship_type' => 'one-to-many',
            'relationship_role_column' => 'parent_type',
            'relationship_role_column_value' => 'Quotes'
        ),
        'quote_calls' => array(
            'lhs_module' => 'Quotes',
            'lhs_table' => 'quotes',
            'lhs_key' => 'id',
            'rhs_module' => 'Calls',
            'rhs_table' => 'calls',
            'rhs_key' => 'parent_id',
            'relationship_type' => 'one-to-many',
            'relationship_role_column' => 'parent_type',
            'relationship_role_column_value' => 'Quotes'
        ),
        'quote_emails' => array(
            'lhs_module' => 'Quotes',
            'lhs_table' => 'quotes',
            'lhs_key' => 'id',
            'rhs_module' => 'Emails',
            'rhs_table' => 'emails',
            'rhs_key' => 'parent_id',
            'relationship_type' => 'one-to-many',
            'relationship_role_column' => 'parent_type',
            'relationship_role_column_value' => 'Quotes'
        ),
        'quote_products' => array(
            'lhs_module' => 'Quotes',
            'lhs_table' => 'quotes',
            'lhs_key' => 'id',
            'rhs_module' => 'Products',
            'rhs_table' => 'products',
            'rhs_key' => 'quote_id',
            'relationship_type' => 'one-to-many'
        ),
        'quote_revenuelineitems' => array(
            'lhs_module' => 'Quotes',
            'lhs_table' => 'quotes',
            'lhs_key' => 'id',
            'rhs_module' => 'RevenueLineItems',
            'rhs_table' => 'revenue_line_items',
            'rhs_key' => 'quote_id',
            'relationship_type' => 'one-to-many'
        ),
        'quotes_assigned_user' => array(
            'lhs_module' => 'Users',
            'lhs_table' => 'users',
            'lhs_key' => 'id',
            'rhs_module' => 'Quotes',
            'rhs_table' => 'quotes',
            'rhs_key' => 'assigned_user_id',
            'relationship_type' => 'one-to-many'
        ),
        'quotes_modified_user' => array(
            'lhs_module' => 'Users',
            'lhs_table' => 'users',
            'lhs_key' => 'id',
            'rhs_module' => 'Quotes',
            'rhs_table' => 'quotes',
            'rhs_key' => 'modified_user_id',
            'relationship_type' => 'one-to-many'
        ),
        'quotes_created_by' => array(
            'lhs_module' => 'Users',
            'lhs_table' => 'users',
            'lhs_key' => 'id',
            'rhs_module' => 'Quotes',
            'rhs_table' => 'quotes',
            'rhs_key' => 'created_by',
            'relationship_type' => 'one-to-many'
        ),
    ),
    'duplicate_check' => array(
        'enabled' => false,
    ),
    'ignore_templates' => array(
        // FIXME: Disable commentlog on Quotes until we can handle collection resets of bundles
        'commentlog',
    ),
);
VardefManager::createVardef(
    'Quotes',
    'Quote',
    array(
        'default',
        'assignable',
        'team_security',
        'currency',
    )
);

//boost value for full text search
$dictionary['Quote']['fields']['description']['full_text_search']['boost'] = 0.57;
