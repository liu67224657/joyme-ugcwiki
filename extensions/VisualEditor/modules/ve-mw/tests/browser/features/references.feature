@en.wikipedia.beta.wmflabs.org @firefox @skip
Feature: VisualEditor References

  Background:
    Given I go to a page that has references
      And I click in the editable part

  Scenario: Creating References list
    Given I click Reference
      And I can see the References User Interface
    When I click Insert references list
    Then link to Insert menu should be visible
