import { test as base } from '@playwright/test';
import type { FixtureTypes, Task } from '@fixtures/AcceptanceTest';

export const CreateLandingPage = base.extend<{ CreateLandingPage: Task }, FixtureTypes>({
    CreateLandingPage: async ({ ShopAdmin, AdminCategories, AdminLandingPageCreate, AdminLandingPageDetail }, use ) => {

        const task = (layoutName: string, landingPageData, status: boolean) => {
            return async function CreateLandingPage() {
                await AdminCategories.landingPageHeadline.click();
                await AdminCategories.addLandingPageButton.click();

                await ShopAdmin.expects(AdminLandingPageDetail.saveLandingPageButton).toBeVisible();
                await ShopAdmin.expects(AdminLandingPageDetail.saveLandingPageButton).toContainText('Save');

                //Fill details and save
                await AdminLandingPageCreate.nameInput.fill(landingPageData.name);
                await AdminLandingPageCreate.landingPageStatus.setChecked(landingPageData.status);
                await AdminLandingPageCreate.salesChannelSelectionList.click();
                await AdminLandingPageCreate.filtersResultPopoverItemList.filter({ hasText: landingPageData.salesChannel }).click();
                await AdminLandingPageCreate.seoUrlInput.fill(landingPageData.seoUrl);

                if (layoutName) {
                    await AdminLandingPageCreate.layoutTab.click();
                    // Verify empty layout state
                    await ShopAdmin.expects(AdminLandingPageCreate.layoutEmptyState).toBeVisible();
                    await ShopAdmin.expects(AdminLandingPageCreate.createNewLayoutButton).toBeVisible();
                    // Select existing layout
                    await AdminLandingPageCreate.assignLayoutButton.click();
                    await AdminLandingPageCreate.loadingSpinner.waitFor({ state: 'hidden' });
                    await AdminLandingPageCreate.searchLayoutInput.dblclick();
                    // Search input need to delay press more than 300ms to mimic user typing in order to activate search action
                    await AdminLandingPageCreate.searchLayoutInput.pressSequentially(layoutName.split(' ')[1].substring(0,5), {delay: 500});
                    await AdminLandingPageCreate.layoutCheckboxes.first().waitFor({ state: 'visible' });
                    await AdminLandingPageCreate.layoutCheckboxes.first().click();
                    await AdminLandingPageCreate.layoutAddButton.click();
                }
                await AdminLandingPageCreate.saveLandingPageButton.click();
                await AdminLandingPageCreate.loadingSpinner.waitFor({ state: 'hidden' });
            }
        }

        await use(task);
    },
});
