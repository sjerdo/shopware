import { mergeTests } from '@playwright/test';

/**
 * Media
 */
import { UploadImage } from './ShopAdmin/Product/UploadImage';

/**
 * Product
 */
import { GenerateVariants } from './ShopAdmin/Product/GenerateVariants';

/**
 * First Run Wizard
 */
import { FRWSalesChannelSelectionPossibility } from '@tasks/ShopAdmin/FRW/FRWSalesChannelSelectionPossibility';

/**
 * Add Landing Page From Category
 */
import { CreateLandingPage } from '@tasks/ShopAdmin/Category/CreateLandingPage';

/**
 * Verify Landing Page From Category
 */
import { VerifyLandingPage } from '@tasks/ShopAdmin/Category/VerifyLandingPage';

export const test = mergeTests(
    GenerateVariants,
    UploadImage,
    FRWSalesChannelSelectionPossibility,
    CreateLandingPage,
    VerifyLandingPage,
);
