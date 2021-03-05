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
/**
 * The call/chat detail panel.
 *
 * @class View.Layouts.Base.OmnichannelDetailView
 * @alias SUGAR.App.view.layouts.BaseOmnichannelDetailView
 * @extends View.View
 */
({
    className: 'omni-detail',

    events: {
        'click [data-action=show-contact]': 'showContactTab',
        'click [data-action=show-case]': 'showCaseTab'
    },

    /**
     * Contact models.
     * @property {Object}
     */
    contactModels: {},

    /**
     * Case models.
     * @property {Object}
     */
    caseModels: {},

    /**
     * Current AWS connect contact id.
     * @property {string}
     */
    currentContactId: null,

    /**
     * Current contact model.
     * @property {Object}
     */
    currentContact: null,

    /**
     * Current case model.
     * @property {Object}
     */
    currentCase: null,

    /**
     * Editable information from the summary panel.
     * @property {Object}
     */
    summary: {},

    /**
     * Title of the detail block.
     * @property {string}
     */
    summaryTitle: null,

    /**
     * @inheritdoc
     */
    initialize: function(options) {
        options.model = app.data.createBean();

        this._super('initialize', [options]);
        this.currentCase = null;
        this.currentContact = null;
        this.currentContactId = null;
    },

    /**
     * @inheritdoc
     */
    bindDataChange: function() {
        this._super('bindDataChange');
        this.layout.on('omniconsole:toggle', this.toggle, this);
        this.layout.on('contact:view', this.showContact, this);
        this.layout.on('contact:destroyed', this.removeContact, this);
        this.layout.on('contact:model:loaded', this._setInitialSummary, this);
        this.model.on('change', this.saveSummaryToRecord, this);
    },

    /**
     * Show contact tab.
     */
    showContactTab: function() {
        var dashboardSwitch = this.layout.getComponent('omnichannel-dashboard-switch');
        dashboardSwitch.setContactModel(this.currentContactId, this.contactModels[this.currentContactId]);
    },

    /**
     * Show contact tab.
     */
    showCaseTab: function() {
        var dashboardSwitch = this.layout.getComponent('omnichannel-dashboard-switch');
        dashboardSwitch.setCaseModel(this.currentContactId, this.caseModels[this.currentContactId]);
    },

    /**
     * Set title of the detail panel.
     * @param {Object} contact AWS contact
     */
    setSummaryTitle: function(contact) {
        this.summaryTitle = app.lang.get(
            (contact.getType() === connect.ContactType.CHAT) ?
                'LBL_OMNICHANNEL_CHAT_SUMMARY' :
                'LBL_OMNICHANNEL_CALL_SUMMARY',
            this.module);
    },

    /**
     * Save the summary data.
     */
    saveSummaryToRecord: function() {
        var ccp = this.layout.getComponent('omnichannel-ccp');
        var contactId = ccp.activeContact.getContactId();

        this.summary[contactId] = {
            name: this.model.get('name'),
            description: this.model.get('description'),
        };

        ccp._updateConnectionRecord(ccp.activeContact, this.summary[contactId]);
    },

    /**
     * Set data of the active contact to model.
     * @param {Object} contact AWS contact
     */
    setSummary: function(contact) {
        this.setSummaryTitle(contact);

        var contactId = contact.getContactId();

        if (this.summary[contactId]) {
            this.model.set(this.summary[contactId], {silent: true});
        }
    },

    /**
     * Set the initial summary after the contact model is created
     *
     * @param contact
     * @private
     */
    _setInitialSummary: function(contact) {
        var contactId = contact.getContactId();
        var ccp = this.layout.getComponent('omnichannel-ccp');
        var contactModel = ccp.connectionRecords[contactId];

        if (!contactModel) {
            return;
        }

        this.summary[contactId] = {
            name: contactModel.get('name'),
            description: contactModel.get('description')
        };

        this.model.set(this.summary[contactId], {silent: true});
        this.render();
    },

    /**
     * Show/hide the detail panel
     */
    toggle: function() {
        this.$el.toggle();
    },

    /**
     * Show contact and case records for a different AWS contact.
     * @param {Object} contact AWS contact
     */
    showContact: function(contact) {
        this.setSummary(contact);

        var contactId = contact.getContactId();

        if (this.contactModels[contactId]) {
            this.currentContact = {
                id: this.contactModels[contactId].get('id'),
                name: app.utils.formatNameModel('Contacts', this.contactModels[contactId].attributes)
            };
        } else {
            this.currentContact = null;
        }
        if (this.caseModels[contactId]) {
            this.currentCase = {
                id: this.caseModels[contactId].get('id'),
                name: this.caseModels[contactId].get('name')
            };
        } else {
            this.currentCase = null;
        }
        this.currentContactId = contactId;
        this.render();
    },

    /**
     * Remove contact and case records for a AWS contact.
     * @param {Object} contact AWS contact
     */
    removeContact: function(contact) {
        this.contactModels = _.omit(this.contactModels, contact.contactId);
        this.caseModels = _.omit(this.caseModels, contact.contactId);
        this.model.clear({silent: true});
    },

    /**
     * Set contact or case model
     * @param {Bean} model
     */
    setModel: function(model) {
        var ccp = this.layout.getComponent('omnichannel-ccp');
        if (model.module === 'Contacts') {
            this.contactModels[this.currentContactId] = model;
            this.currentContact = {
                id: model.get('id'),
                name: app.utils.formatNameModel('Contacts', model.attributes)
            };
            ccp._updateConnectionRecord(ccp.activeContact, {contact: model});
        } else if (model.module === 'Cases') {
            this.caseModels[this.currentContactId] = model;
            this.currentCase = {
                id: model.get('id'),
                name: model.get('name')
            };
            ccp._updateConnectionRecord(ccp.activeContact, {case: model});
        }
        this.render();
    },

    /**
     * Set contact model.
     * @param {Object} contact AWS contact
     * @param {Bean} contactModel Sugar contact
     */
    setContactModel: function(contact, contactModel) {
        this.contactModels[contact.contactId] = contactModel;
        this.showContact(contact);
    },

    /**
     * Set case model.
     * @param {Object} contact AWS contact
     * @param {Bean} caseModel Sugar case
     */
    setCaseModel: function(contact, caseModel) {
        this.caseModels[contact.contactId] = caseModel;
        this.showContact(contact);
    },

    /**
     * Get contact model.
     * @param {Object} contact AWS contact
     * @return {Bean} contactModel Sugar contact
     */
    getContactModel: function(contact) {
        var contactId = contact ? contact.contactId : this.currentContactId;
        return this.contactModels[contactId];
    },

    /**
     * Get case model.
     * @param {Object} contact AWS contact
     * @return {Bean} caseModel Sugar case
     */
    getCaseModel: function(contact) {
        var contactId = contact ? contact.contactId : this.currentContactId;
        return this.caseModels[contactId];
    }
})
