# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@oidc @userManagement @extended
Feature: User management
  When Mango is in IDM mode
  User management should be limited but employee can be created and updated

  Scenario: No password tab on user profile
    Given I am on the homepage
    And I wait until the loading is completed
    Then I should see IdP login page
    Given I do IdP login as "admin" with password "admin"
    And I wait until the loading is completed
    And I wait for the page to be loaded
    And I skip cookie consent
    And I skip login wizard
    Then I should not see "You must specify a valid username and password."
    Then I should see "Home Dashboard"
    When I click "#userList"
    And I click ".profileactions-profile"
    When I wait for the page to be loaded
    And I wait until the loading is completed
    And I switch to BWC
    And I click "#edit_button"
    And I wait for the page to be loaded
    And I wait for the element "#EditView_tabs"
    Then I should not see "Password" in the "#EditView_tabs ul" element
    And I logout

  Scenario: Disabled user properties for editing in Mango
    Given I am on the homepage
    And I wait until the loading is completed
    Then I should see IdP login page
    Given I do IdP login as "admin" with password "admin"
    And I wait until the loading is completed
    And I wait for the page to be loaded
    And I skip cookie consent
    And I skip login wizard
    Then I should not see "You must specify a valid username and password."
    Then I should see "Home Dashboard"
    When I click "#userList"
    And I click ".profileactions-profile"
    When I wait for the page to be loaded
    And I wait until the loading is completed
    And I switch to BWC
    And I click "#edit_button"
    And I wait for the page to be loaded
    And I wait for the element "#EditView_tabs"
    Then I should see an "span#user_name.sugar_field" element
    And I should not see a "input#user_name.sugar_field" element
    Then I should see an "span#first_name.sugar_field" element
    And I should not see a "input#first_name.sugar_field" element
    Then I should see an "span#last_name.sugar_field" element
    And I should not see a "input#last_name.sugar_field" element
    Then I should see an "span#title.sugar_field" element
    And I should not see a "input#title.sugar_field" element
    Then I should see an "span#department.sugar_field" element
    And I should not see a "input#department.sugar_field" element
    Then I should see an "input#status[type='hidden']" element
    Then I should see an "span#address_street.sugar_field" element
    And I should not see a "input#address_street.sugar_field" element
    Then I should see an "span#address_city.sugar_field" element
    And I should not see a "input#address_city.sugar_field" element
    Then I should see an "span#address_state.sugar_field" element
    And I should not see a "input#address_state.sugar_field" element
    Then I should see an "span#address_country.sugar_field" element
    And I should not see a "input#address_country.sugar_field" element
    Then I should see an "span#address_postalcode.sugar_field" element
    And I should not see a "input#address_postalcode.sugar_field" element
    Then I should see a "input#reports_to_name" element
    And I logout

  Scenario: Disable mass update fields in IDM mode
    Given I am on the homepage
    And I wait until the loading is completed
    Then I should see IdP login page
    Given I do IdP login as "admin" with password "admin"
    And I wait until the loading is completed
    And I wait for the page to be loaded
    And I skip cookie consent
    And I skip login wizard
    Then I go to administration
    And I click "#user_management"
    And I switch to BWC
    And I wait for the page to be loaded
    And I wait until the loading is completed
    And I wait for the element "#massall_top"
    And I click "#massall_top"
    And I click "#massupdate_listview_top"
    Then I should not see an "#mass_status" element
    And I logout

  Scenario: User create link should redirect to SugarCloud Settings
    Given Mark scenario as pending because user_hint should be url_encoded but in Mango it is not url_encoded

    Given I am on the homepage
    And I wait until the loading is completed
    Then I should see IdP login page
    Given I do IdP login as "admin" with password "admin"
    And I wait until the loading is completed
    And I wait for the page to be loaded
    And I skip cookie consent
    And I skip login wizard
    And I close alerts
    And I go to administration
    And I follow "User Management"
    When I switch to sidecar
    And I click "button[aria-label='Users - More']"
    And I wait for the element "a[data-navbar-menu-item=LNK_NEW_USER]"
    And I follow "Create New User"
    Then The document should open in a new tab with url "http://console.sugarcrm.local/user-create?tenant_hint=srn%3Acloud%3Aiam%3Aeu%3A2000000001%3Atenant&user_hint=srn%3Acloud%3Aiam%3A%3A2000000001%3Auser%3A1"
    And I logout

  Scenario: Create employee and edit employee
    Given I am on the homepage
    And I wait until the loading is completed
    Then I should see IdP login page
    Given I do IdP login as "admin" with password "admin"
    And I wait until the loading is completed
    And I wait for the page to be loaded
    And I skip cookie consent
    And I skip login wizard
    And I close alerts
    When I click "#userList"
    And I wait for the element ".profileactions-employees"
    And I click ".profileactions-employees"
    And I wait for the page to be loaded
    And I wait until the loading is completed
    And I switch to BWC
    And I switch to sidecar
    When I click "button[aria-label='Employees - More']"
    And I wait for the element "a[data-navbar-menu-item=LNK_NEW_EMPLOYEE]"
    And I follow "Create Employee"
    And I wait for the page to be loaded
    And I wait until the loading is completed
    And I switch to BWC
    Then I fill in "last_name" with "TestLastName"
    And I fill in "Users0emailAddress0" with "test@host.com"
    When I click "#SAVE_FOOTER"
    And I wait for the element "#edit_button"
    Then I should see "TestLastName"
    And I should see "test@host.com"
    When I click "#edit_button"
    And I wait for the page to be loaded
    And I wait until the loading is completed
    And I wait for the element "#last_name"
    Then I fill in "last_name" with "TestLastNameUpd"
    When I click "#SAVE_FOOTER"
    And I wait for the element "#edit_button"
    Then I should see "TestLastNameUpd"
    And I should see "test@host.com"
    And I logout

  Scenario: User copy link should not be available
    Given I am on the homepage
    And I wait until the loading is completed
    Then I should see IdP login page
    Given I do IdP login as "admin" with password "admin"
    And I wait until the loading is completed
    And I wait for the page to be loaded
    And I skip cookie consent
    And I skip login wizard
    And I close alerts
    And I go to administration
    And I follow "User Management"
    And I switch to BWC
    And I follow "admin"
    And I switch to BWC
    When I click "span[class=ab]"
    Then I wait for the element "#reset_user_preferences_header"
    Then I should not see an "#duplicate_button" element
    And I logout

  Scenario: Employee copy link should not be available for User
    Given I am on the homepage
    And I wait until the loading is completed
    Then I should see IdP login page
    Given I do IdP login as "admin" with password "admin"
    And I wait until the loading is completed
    And I wait for the page to be loaded
    And I skip cookie consent
    And I skip login wizard
    And I close alerts
    When I click "#userList"
    And I wait for the element ".profileactions-employees"
    And I click ".profileactions-employees"
    And I switch to BWC
    And I follow "Administrator"
    And I switch to BWC
    Then I should not see an "span[class=ab]" element
    And I logout

  Scenario: Employee Copy and Delete link should be available for Employee
    Given I am on the homepage
    And I wait until the loading is completed
    Then I should see IdP login page
    Given I do IdP login as "admin" with password "admin"
    And I wait until the loading is completed
    And I wait for the page to be loaded
    And I skip cookie consent
    And I skip login wizard
    And I close alerts
    When I click "#userList"
    And I wait for the element ".profileactions-employees"
    And I click ".profileactions-employees"
    And I wait for the page to be loaded
    And I wait until the loading is completed
    And I switch to BWC
    And I switch to sidecar
    When I click "button[aria-label='Employees - More']"
    And I wait for the element "a[data-navbar-menu-item=LNK_NEW_EMPLOYEE]"
    And I follow "Create Employee"
    And I wait for the page to be loaded
    And I wait until the loading is completed
    And I switch to BWC
    Then I fill in "last_name" with "TestLastNameCheck"
    And I fill in "Users0emailAddress0" with "test1@host.com"
    When I click "#SAVE_FOOTER"
    And I wait for the element "#edit_button"
    Then I should see an "span[class=ab]" element
    And I click "span[class=ab]"
    Then I wait for the element "#duplicate_button"
    Then I wait for the element "#delete_button"
    And I logout
