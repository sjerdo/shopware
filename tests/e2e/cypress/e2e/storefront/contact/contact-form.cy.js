/// <reference types="Cypress" />
/**
 * @package buyers-experience
 */
const selector = {
    footerLinkContact: '.footer-contact-form a[data-ajax-modal="true"]',
    formContact: 'form[action="/form/contact"]',
    formContactSalutation: '#form-Salutation',
    formContactFirstName: '#form-firstName',
    formContactLastName: '#form-lastName',
    formContactMail: '#form-email',
    formContactPhone: '#form-phone',
    formContactSubject: '#form-subject',
    formContactComment: '#form-comment',
    formContactDataProtectionCheckbox: '.privacy-notice input[type="checkbox"]',
    formContactButtonSubmit: 'button[type="submit"]',
    modalButtonDismiss: '.modal-content .close',
};

describe('Contact form', () => {
    function openContactForm(callback) {
        cy.visit('/');

        cy.intercept({
            method: 'GET',
            url: '/widgets/cms/*',
        }).as('contactFormRequest');

        cy.get(selector.footerLinkContact).click();

        cy.wait('@contactFormRequest').then(() => {
            cy.get('.modal').should('be.visible');
            cy.get(selector.modalButtonDismiss).should('be.visible');

            if (typeof callback === 'function') {
                callback(arguments);
            }
        });
    }

    function fillOutContactForm() {
        cy.get(selector.formContact).within(() => {
            cy.get(selector.formContactSalutation).select('Not specified');
            cy.get(selector.formContactFirstName).type('Foo');
            cy.get(selector.formContactLastName).type('Bar');
            cy.get(selector.formContactMail).type('user@example.com');
            cy.get(selector.formContactPhone).type('+123456789');
            cy.get(selector.formContactSubject).type('Lorem ipsum');
            cy.get(selector.formContactComment).type('Dolor sit amet.');
            cy.get(selector.formContactDataProtectionCheckbox).check({force: true});
        });
    }

    function submitContactForm() {
        cy.intercept({
            method: 'POST',
            url: '/form/contact',
        }).as('contactFormPostRequest');

        cy.get(selector.formContact).within(() => {
            cy.get(selector.formContactButtonSubmit).click();
        });

        cy.wait('@contactFormPostRequest').its('response.statusCode').should('equals', 200);
    }

    function checkForCorrectlyLabelledPrivacyInformationCheckbox() {
        cy.get(selector.formContact).within(() => {
            cy.get(selector.formContactDataProtectionCheckbox).invoke('attr', 'id')
                .then((id) => {
                    cy.get(`label[for="${id}"]`).should('be.visible');
                });
        });
    }

    before(() => {
        openContactForm();
    });

    it('@contact @package: Should be possible to fill out and submit the contact form', { tags: ['pa-customers-orders', 'quarantined'] }, () => {
        /**
         * This is a regression test for 4460.
         *
         * @see https://github.com/shopware/shopware/issues/4460
         */
        checkForCorrectlyLabelledPrivacyInformationCheckbox();

        fillOutContactForm();

        submitContactForm();
    });
});
