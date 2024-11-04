import { test as base } from '@playwright/test';
import type { FixtureTypes, Task } from '@fixtures/AcceptanceTest';

export const VerifyLandingPage = base.extend<{ VerifyLandingPage: Task }, FixtureTypes>({
    VerifyLandingPage: async ({ ShopAdmin, AdminCategories, AdminLandingPageDetail }, use ) => {

        const task = (layoutName: string, landingPageData, status: boolean) => {
            return async function VerifyLandingPage() {

                // Verify created landing page
                const createdLandingPage = AdminCategories.landingPageItems.locator(`text="${landingPageData.name}"`);
                await createdLandingPage.click();
                await AdminLandingPageDetail.loadingSpinner.waitFor({ state: 'hidden' });

                // Verify general tab detail
                await ShopAdmin.expects(AdminLandingPageDetail.nameInput).toHaveValue(landingPageData.name);
                await ShopAdmin.expects(AdminLandingPageDetail.landingPageStatus).toBeChecked({ checked: status });
                await ShopAdmin.expects(AdminLandingPageDetail.salesChannelSelectionList).toHaveText(landingPageData.salesChannel);
                await ShopAdmin.expects(AdminLandingPageDetail.seoUrlInput).toHaveValue(landingPageData.seoUrl);
                // Verify layout tab detail
                if (layoutName) {
                    await AdminLandingPageDetail.layoutTab.click();
                    await ShopAdmin.expects(AdminLandingPageDetail.layoutAssignmentCardTitle).toHaveText(layoutName);
                    await ShopAdmin.expects(AdminLandingPageDetail.layoutAssignmentCardHeadline).toHaveText(layoutName);

                    await ShopAdmin.expects(AdminLandingPageDetail.layoutAssignmentContentSection).toBeVisible();
                    await ShopAdmin.expects(AdminLandingPageDetail.layoutResetButton).toBeVisible();
                    await ShopAdmin.expects(AdminLandingPageDetail.changeLayoutButton).toBeVisible();
                    await ShopAdmin.expects(AdminLandingPageDetail.editInDesignerButton).toBeVisible();
                }
            }
        }

        await use(task);
    },
});
