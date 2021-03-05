# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@loginService @login @logout @local @multiple
Feature: Multiple identities support
  Existing user logs in different tenants.
  Existing user has an ability to choose between logged-in/-out sessions.
  Support user can log into one tenant with various users.


  Scenario: No active sessions and user logs-in/-out normally
    Given I am on "/"
    Then I login as "sarah" with password "sarah" to tenant "2000000009"
    And I should see "You are logged in as sarah"
    And I should see "Your tenant id is srn:cloud:iam:eu:2000000009:tenant"
    And I should see "Authentication provider - Local"
    Then I do logout
    Given I am on "/"
    Then I login as "jim" with password "jim" to tenant "2000000010"
    And I should see "You are logged in as jim"
    And I should see "srn:cloud:iam:eu:2000000010:tenant"
    And I should see "Authentication provider - Local"
    Then I do logout

  Scenario: One session is active & logged-in and user goes to base login page
    Given I am on "/"
    Then I login as "sarah" with password "sarah" to tenant "2000000009"
    And I should see "You are logged in as sarah"
    And I should see "Your tenant id is srn:cloud:iam:eu:2000000009:tenant"
    And I should see "Authentication provider - Local"
    Then I am on "/"
    And I should see "You are logged in as sarah"
    And I should see "Your tenant id is srn:cloud:iam:eu:2000000009:tenant"
    And I should see "Authentication provider - Local"

  Scenario: One session is active & logged-in and user wants to log-in into another tenant via URL parameter
    Given I am on "/"
    Then I login as "sarah" with password "sarah" to tenant "2000000009"
    And I should see "You are logged in as sarah"
    And I should see "Your tenant id is srn:cloud:iam:eu:2000000009:tenant"
    And I should see "Authentication provider - Local"
    Then I go to login page with tenant "2000000010"
    Then I login as user "jim" with password "jim"
    And I should see "You are logged in as jim"
    And I should see "srn:cloud:iam:eu:2000000010:tenant"
    And I should see "Authentication provider - Local"

  Scenario: One session is active & logged-in and user wants to log-in into the same tenant via URL parameter
    Given I am on "/"
    Then I login as "sarah" with password "sarah" to tenant "2000000009"
    And I should see "You are logged in as sarah"
    And I should see "Your tenant id is srn:cloud:iam:eu:2000000009:tenant"
    And I should see "Authentication provider - Local"
    Then I go to login page with tenant "2000000009"
    And I should see "You are logged in as sarah"
    And I should see "Your tenant id is srn:cloud:iam:eu:2000000009:tenant"
    And I should see "Authentication provider - Local"

  Scenario: All sessions are logged-in and user goes to the login page to select logged-in user
    Given I am on "/"
    Then I login as "sarah" with password "sarah" to tenant "2000000009"
    And I should see "You are logged in as sarah"
    Then I go to login page with tenant "2000000010"
    Then I login as user "jim" with password "jim"
    And I should see "You are logged in as jim"
    Then I am on "/"
    And I should see "Continue with"
    And I should see "jim jim_family Local for Login Service (multi) 2 2000000010"
    And I should see "sarah sarah_family Local for Login Service (multi) 1 2000000009"
    Then I click on user session item "sarah"
    And I should see "You are logged in as sarah"
    And I should see "Your tenant id is srn:cloud:iam:eu:2000000009:tenant"
    And I should see "Authentication provider - Local"

  Scenario: Several sessions are logged-in/-out and user goes to the login page to select logged-out user
    Given I am on "/"
    Then I login as "sarah" with password "sarah" to tenant "2000000009"
    And I should see "You are logged in as sarah"
    Then I go to login page with tenant "2000000010"
    Then I login as user "jim" with password "jim"
    And I should see "You are logged in as jim"
    Then I do logout
    Then I am on "/"
    And I should see "Continue with"
    And I should see "jim jim_family logged out Local for Login Service (multi) 2 2000000010"
    And I should see "sarah sarah_family Local for Login Service (multi) 1 2000000009"
    Then I click on user session item "jim"
    Then I login as user "jim" with password "jim"
    And I should see "You are logged in as jim"
    And I should see "Your tenant id is srn:cloud:iam:eu:2000000010:tenant"
    And I should see "Authentication provider - Local"

  Scenario: All sessions are logged-out and user goes to the login page to select logged-out user
    Given I am on "/"
    Then I login as "sarah" with password "sarah" to tenant "2000000009"
    And I should see "You are logged in as sarah"
    Then I do logout
    Then I go to login page with tenant "2000000010"
    Then I login as user "jim" with password "jim"
    And I should see "You are logged in as jim"
    Then I do logout
    Then I am on "/"
    And I should see "Continue with"
    And I should see "sarah sarah_family logged out Local for Login Service (multi) 1 2000000009"
    And I should see "jim jim_family logged out Local for Login Service (multi) 2 2000000010"
    Then I click on user session item "sarah"
    Then I login as user "sarah" with password "sarah"
    And I should see "You are logged in as sarah"
    And I should see "Your tenant id is srn:cloud:iam:eu:2000000009:tenant"
    And I should see "Authentication provider - Local"

  Scenario: Several sessions are present and user wants to log-in into another tenant via "Log into another tenant"
    Given I am on "/"
    Then I login as "sarah" with password "sarah" to tenant "2000000009"
    And I should see "You are logged in as sarah"
    Then I go to login page with tenant "2000000010"
    Then I login as user "jim" with password "jim"
    And I should see "You are logged in as jim"
    Then I am on "/"
    And I should see "Continue with"
    And I should see "Log into another tenant"
    Then I follow "Log into another tenant"
    Then I login as "chris" with password "chris" to tenant "2000000011"
    And I should see "You are logged in as chris"
    And I should see "srn:cloud:iam:eu:2000000011:tenant"
    And I should see "Authentication provider - Local"
    Then I do logout

  Scenario: Support user can log-in\-out with different users into one tenant
    Given I am on "/"
    Then I login as "sarah" with password "sarah" to tenant "2000000009"
    And I should see "You are logged in as sarah"
    Then I go to login page with tenant "2000000011"
    Then I login as user "chris" with password "chris"
    And I should see "You are logged in as chris"
    Then I do logout
    Then I am on "/?tenant_hint=2000000011"
    And I should see "Continue with"
    And I should see "Show log in form"
    Then I follow "Show log in form"
    Then I login as user "will" with password "will"
    And I should see "You are logged in as will"
    And I should see "srn:cloud:iam:eu:2000000011:tenant"
    # Login with logged-out user
    Then I am on "/"
    And I should see "Continue with"
    Then I click on user session item "Chris"
    Then I should see "Welcome to"
    And I fill in "chris" for "password"
    And I click "#submit_btn"
    And I should see "You are logged in as chris"
    And I should see "srn:cloud:iam:eu:2000000011:tenant"
    # Login with logged-in user
    Then I am on "/"
    And I should see "Continue with"
    Then I click on user session item "Will"
    And I should see "You are logged in as will"
    And I should see "srn:cloud:iam:eu:2000000011:tenant"
    # Login with another logged-in user
    Then I am on "/"
    And I should see "Continue with"
    Then I click on user session item "Chris"
    And I should see "You are logged in as chris"
    And I should see "srn:cloud:iam:eu:2000000011:tenant"

  Scenario: Support user can log-in with different user in the same tenant using "Log into another tenant"
    Given I am on "/"
    Then I login as "sarah" with password "sarah" to tenant "2000000009"
    And I should see "You are logged in as sarah"
    Then I go to login page with tenant "2000000011"
    Then I login as user "chris" with password "chris"
    And I should see "You are logged in as chris"
    Then I am on "/"
    And I should see "Continue with"
    Then I follow "Log into another tenant"
    Then I login as "will" with password "will" to tenant "2000000011"
    And I should see "You are logged in as will"
    And I should see "srn:cloud:iam:eu:2000000011:tenant"
    Then I am on "/"
    And I should see "Continue with"
    And I should see "Will Will_family_long_family_name_longer_then_30_charts Local for Login Service (multi) 3 2000000011"
    And I should see "Chris Chris_family_long_family_name_longer_then_30_charts Local for Login Service (multi) 3 2000000011"
    And I should see "sarah sarah_family Local for Login Service (multi) 1 2000000009"

  Scenario Outline: User is forwarded to the session if it's alone in the tenant
    Given I am on "/"
    Then I go to login page with tenant "2000000009"
    And I login as user "sarah" with password "sarah"
    Then I should see "You are logged in as sarah"
    Then I go to login page with tenant "2000000010"
    And I login as user "jim" with password "jim"
    Then I should see "You are logged in as jim"
    Then I go to login page with tenant "<tid>"
    And I should not see "Continue with"
    And I should see "You are logged in as <userName>"
    And I should see "Your tenant id is <tenant>"
    And I should see "Authentication provider - Local"
    Examples:
      | tid        | tenant                             | userName |
      | 2000000009 | srn:cloud:iam:eu:2000000009:tenant | sarah    |
      | 2000000010 | srn:cloud:iam:eu:2000000010:tenant | jim      |
