import { test } from '@fixtures/AcceptanceTest';
import { expect } from '@playwright/test';

test('Shop administrator should be able to create a landing page.', {tag: '@Categories'}, async ({
    ShopAdmin,
    IdProvider,
    TestDataService,
    AdminCategories, CreateLandingPage, VerifyLandingPage, AdminApiContext,
}) => {
    const layoutUuid = IdProvider.getIdPair().uuid;
    const layoutName = `test ${layoutUuid}`;
    const layoutType = 'landingpage';
    const landingPageData = {
        name: `Landing Page ${IdProvider.getIdPair().uuid}`,
        salesChannel: 'Storefront',
        seoUrl: `landing-${IdProvider.getIdPair().id}`,
    };

    await test.step('Create a landing page layout via API.', async () => {

        await TestDataService.createBasicPageLayout(layoutType, {
                name: layoutName,
                id: layoutUuid,
                type: layoutType,
            },
        );
    });

    await test.step('Create a new landing page and assign layout.', async () => {
        await ShopAdmin.goesTo(AdminCategories.url());
        await ShopAdmin.attemptsTo(CreateLandingPage(layoutName, landingPageData, true));
    });

    await test.step('Verify a new landing page created and assigned layout.', async () => {
        await ShopAdmin.attemptsTo(VerifyLandingPage(layoutName, landingPageData, true));
    });

    await test.step('Clean up the created landing page via API.', async () => {
        const url = ShopAdmin.page.url();
        const landingPageId = url.split('/')[url.split('/').length - 2];
        const response = await AdminApiContext.delete(`./landing-page/${landingPageId}`);
        expect(response.status()).toBe(204);
    });

});
